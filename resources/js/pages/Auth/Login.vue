<script setup lang="ts">
import { sendCode, reset as resetPassword } from '@/actions/App/Http/Controllers/ForgotPasswordController';
import { switchLanguage } from '@/actions/App/Http/Controllers/LanguageController';
import PasswordInput from '@/components/Inputs/PasswordInput.vue';
import { showAlertError } from '@/lib/utils';
import { vueLang } from '@erag/lang-sync-inertia';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';

declare const Swal: any;

const { __ } = vueLang();
const page = usePage();
const currentLocale = computed(() => page.props.locale as string);
const isRtl = computed(() => currentLocale.value === 'ar');
const showLangDropdown = ref(false);

const form = useForm({
    username: '',
    password: '',
});

const canSubmit = computed(() => {
    return form.username && form.password && !form.processing;
});

const submit = () => {
    form.processing = true;
    form.post(route('login'));
};

function switchLang(lang: string) {
    showLangDropdown.value = false;
    router.visit(switchLanguage.url(lang), { preserveScroll: true });
}

// --- Forgot password ---
type ForgotChannel = 'whatsapp' | 'email';

const showForgotModal = ref(false);
const forgotStep = ref<'identify' | 'verify'>('identify');
const forgotChannel = ref<ForgotChannel>('whatsapp');
const forgotDestination = ref('');
const forgotSending = ref(false);
const forgotResetting = ref(false);
const forgotError = ref('');

const forgot = ref({
    username: '',
    code: '',
    password: '',
    password_confirmation: '',
});

function openForgotModal() {
    forgot.value = { username: form.username, code: '', password: '', password_confirmation: '' };
    forgotChannel.value = 'whatsapp';
    forgotStep.value = 'identify';
    forgotDestination.value = '';
    forgotError.value = '';
    showForgotModal.value = true;
}

function closeForgotModal() {
    showForgotModal.value = false;
}

function extractError(error: any): string {
    const errors = error?.response?.data?.errors;
    if (errors) {
        const first = Object.values(errors)[0];
        return Array.isArray(first) ? first[0] : String(first);
    }
    return error?.response?.data?.message ?? 'Something went wrong. Please try again.';
}

async function sendResetCode() {
    if (!forgot.value.username) {
        return;
    }
    forgotSending.value = true;
    forgotError.value = '';
    try {
        const response = await axios.post(sendCode().url, {
            username: forgot.value.username,
            channel: forgotChannel.value,
        });
        forgotDestination.value = response.data.destination;
        forgotStep.value = 'verify';
    } catch (error: any) {
        forgotError.value = extractError(error);
    } finally {
        forgotSending.value = false;
    }
}

const canResetPassword = computed(() => {
    return (
        forgot.value.code.length === 6 &&
        forgot.value.password.length >= 8 &&
        forgot.value.password === forgot.value.password_confirmation &&
        !forgotResetting.value
    );
});

async function submitResetPassword() {
    if (!canResetPassword.value) {
        return;
    }
    forgotResetting.value = true;
    forgotError.value = '';
    try {
        const response = await axios.post(resetPassword().url, {
            username: forgot.value.username,
            code: forgot.value.code,
            password: forgot.value.password,
            password_confirmation: forgot.value.password_confirmation,
        });
        showForgotModal.value = false;
        Swal.fire({
            title: __('login.forgot-title'),
            text: response.data.message,
            icon: 'success',
            buttonsStyling: false,
            confirmButtonText: 'Ok',
            customClass: { confirmButton: 'btn btn-primary' },
        });
    } catch (error: any) {
        forgotError.value = extractError(error);
    } finally {
        forgotResetting.value = false;
    }
}
</script>

<template>
    <div class="auth-root" :dir="isRtl ? 'rtl' : 'ltr'">
        <!--begin::Background-->
        <div class="auth-bg" style="background-image: url(assets/media/auth/background_logo.png)"></div>
        <div class="auth-overlay"></div>
        <!--end::Background-->

        <!--begin::Content layer-->
        <div class="auth-scroll">
            <!--begin::Card-->
            <div class="auth-card">

                <!--begin::Heading-->
                <div class="mb-8 text-start">
                    <h1 class="fs-2x mb-1 text-gray-900">{{ __('login.sign-in-title') }}</h1>
                    <div class="fw-semibold fs-7 text-gray-500">{{ __('login.general-desc') }}</div>
                    <div class="mt-2">
                        <span class="fw-semibold fs-7 text-gray-500 me-2">{{ __('login.sign-in-head-desc') }}</span>
                        <Link :href="route('signup')" class="link-primary fw-bold fs-7">{{ __('login.sign-in-head-link') }}</Link>
                    </div>
                </div>
                <!--end::Heading-->

                <form class="form w-100" @submit.prevent="submit">
                    <div class="fv-row mb-4">
                        <input
                            type="text"
                            v-model="form.username"
                            :placeholder="__('login.sign-in-input-username')"
                            name="username"
                            autocomplete="off"
                            class="form-control rounded-2"
                        />
                    </div>

                    <div class="fv-row mb-2">
                        <input
                            v-model="form.password"
                            type="password"
                            :placeholder="__('login.sign-in-input-password')"
                            name="password"
                            autocomplete="off"
                            class="form-control rounded-2"
                        />
                        <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"
                            v-if="form.errors.username">
                            <div>{{ form.errors.username }}</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mb-6">
                        <button type="button" class="btn btn-link link-primary fs-7 p-0" @click="openForgotModal">
                            {{ __('login.sign-in-forgot-password') }}
                        </button>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                        :disabled="!canSubmit"
                    >
                        <span v-if="!form.processing">{{ __('login.sign-in-submit') }}</span>
                        <span v-else>
                            {{ __('login.general-progress') }}
                            <span class="spinner-border spinner-border-sm ms-2 align-middle"></span>
                        </span>
                    </button>
                </form>

                <!--begin::Lang dropdown-->
                <div class="d-flex justify-content-center mt-6">
                    <div class="position-relative">
                        <button type="button" class="lang-toggle" @click="showLangDropdown = !showLangDropdown">
                            <img
                                :src="currentLocale === 'ar' ? 'assets/media/flags/saudi-arabia.svg' : 'assets/media/flags/united-states.svg'"
                                style="width:16px;height:16px;border-radius:3px;flex-shrink:0"
                                alt=""
                            />
                            <span>{{ currentLocale === 'ar' ? 'العربية' : 'English' }}</span>
                            <i class="ki-outline ki-down fs-8"></i>
                        </button>
                        <div v-if="showLangDropdown" class="lang-menu">
                            <button type="button" class="lang-option" :class="{ active: currentLocale === 'en' }"
                                @click="switchLang('en')">
                                <img src="assets/media/flags/united-states.svg" style="width:16px;height:16px;border-radius:3px;flex-shrink:0" alt="" />
                                <span>English</span>
                                <i v-if="currentLocale === 'en'" class="ki-outline ki-check fs-7 text-primary ms-auto"></i>
                            </button>
                            <button type="button" class="lang-option" :class="{ active: currentLocale === 'ar' }"
                                @click="switchLang('ar')">
                                <img src="assets/media/flags/saudi-arabia.svg" style="width:16px;height:16px;border-radius:3px;flex-shrink:0" alt="" />
                                <span>العربية</span>
                                <i v-if="currentLocale === 'ar'" class="ki-outline ki-check fs-7 text-primary ms-auto"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!--end::Lang dropdown-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Content layer-->

        <!--begin::Forgot password modal-->
        <div v-if="showForgotModal" class="forgot-backdrop" @click.self="closeForgotModal">
            <div class="forgot-modal" :dir="isRtl ? 'rtl' : 'ltr'">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h2 class="fs-3 fw-bold text-gray-900 mb-0">{{ __('login.forgot-title') }}</h2>
                    <button type="button" class="forgot-close" @click="closeForgotModal" aria-label="Close">
                        <i class="ki-outline ki-cross fs-2"></i>
                    </button>
                </div>

                <div v-if="forgotError" class="alert alert-danger py-2 px-3 fs-7 mb-4">{{ forgotError }}</div>

                <!--begin::Step 1 - identify-->
                <form v-if="forgotStep === 'identify'" @submit.prevent="sendResetCode">
                    <div class="fw-semibold fs-7 text-gray-500 mb-4">{{ __('login.forgot-step-identify-desc') }}</div>

                    <div class="fv-row mb-4">
                        <label class="required form-label fs-7">{{ __('login.forgot-username-label') }}</label>
                        <input type="text" v-model="forgot.username" autocomplete="off" class="form-control rounded-2" />
                    </div>

                    <div class="fv-row mb-5">
                        <label class="form-label fs-7">{{ __('login.forgot-channel-label') }}</label>
                        <div class="d-flex gap-3">
                           <!-- 
                            <button type="button" class="channel-option flex-grow-1"
                                :class="{ active: forgotChannel === 'whatsapp' }" @click="forgotChannel = 'whatsapp'">
                                <i class="ki-outline ki-whatsapp fs-2 text-success"></i>
                                <span>{{ __('login.forgot-channel-whatsapp') }}</span>
                            </button> 
                           
                           -->
                            <button type="button" class="channel-option flex-grow-1"
                                :class="{ active: forgotChannel === 'email' }" @click="forgotChannel = 'email'">
                                <i class="ki-outline ki-sms fs-2 text-primary"></i>
                                <span>{{ __('login.forgot-channel-email') }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light flex-grow-1" @click="closeForgotModal">
                            {{ __('login.forgot-cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary flex-grow-1" :disabled="!forgot.username || forgotSending">
                            <span v-if="!forgotSending">{{ __('login.forgot-send-code') }}</span>
                            <span v-else>
                                {{ __('login.general-progress') }}
                                <span class="spinner-border spinner-border-sm ms-2 align-middle"></span>
                            </span>
                        </button>
                    </div>
                </form>
                <!--end::Step 1-->

                <!--begin::Step 2 - verify + reset-->
                <form v-else @submit.prevent="submitResetPassword">
                    <div class="fw-semibold fs-7 text-gray-500 mb-4">
                        {{ __('login.forgot-step-verify-desc').replace(':destination', forgotDestination) }}
                    </div>

                    <div class="fv-row mb-4">
                        <label class="required form-label fs-7">{{ __('login.forgot-code-label') }}</label>
                        <input type="text" inputmode="numeric" maxlength="6" v-model="forgot.code"
                            autocomplete="one-time-code" class="form-control rounded-2 text-center fs-3 tracking-wide" />
                    </div>

                    <PasswordInput v-model="forgot.password" :label="__('login.forgot-new-password-label')" />
                    <PasswordInput v-model="forgot.password_confirmation" :label="__('login.forgot-confirm-password-label')" />

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <button type="button" class="btn btn-link link-primary fs-8 p-0" @click="forgotStep = 'identify'">
                            {{ __('login.forgot-back') }}
                        </button>
                        <button type="button" class="btn btn-link link-primary fs-8 p-0" :disabled="forgotSending"
                            @click="sendResetCode">
                            {{ __('login.forgot-resend') }}
                        </button>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" :disabled="!canResetPassword">
                        <span v-if="!forgotResetting">{{ __('login.forgot-reset-submit') }}</span>
                        <span v-else>
                            {{ __('login.general-progress') }}
                            <span class="spinner-border spinner-border-sm ms-2 align-middle"></span>
                        </span>
                    </button>
                </form>
                <!--end::Step 2-->
            </div>
        </div>
        <!--end::Forgot password modal-->
    </div>
</template>

<style scoped>
.auth-root {
    position: relative;
    min-height: 100vh;
    overflow-x: hidden;
}

.auth-bg {
    position: fixed;
    inset: 0;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    z-index: 0;
}

.auth-overlay {
    position: fixed;
    inset: 0;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.55) 0%, rgba(0, 0, 0, 0.3) 100%);
    z-index: 1;
}

.auth-scroll {
    position: relative;
    z-index: 2;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
}

.auth-card {
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(8px);
    border-radius: 1rem;
    padding: 2rem;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.lang-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: var(--bs-gray-600);
    border-radius: 2rem;
    padding: 0.35rem 0.9rem;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease;
    backdrop-filter: blur(4px);
}

.lang-toggle:hover {
    background: rgba(255, 255, 255, 0.25);
}

.lang-menu {
    position: absolute;
    bottom: calc(100% + 6px);
    left: 50%;
    transform: translateX(-50%);
    background: #fff;
    border: 1px solid var(--bs-border-color);
    border-radius: 0.5rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    min-width: 150px;
    padding: 0.25rem;
    z-index: 100;
}

.lang-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--bs-gray-700);
    text-decoration: none;
    transition: background 0.12s ease;
    width: 100%;
    background: none;
    border: none;
    cursor: pointer;
    text-align: start;
}

.lang-option:hover,
.lang-option.active {
    background: var(--bs-light);
    color: var(--bs-gray-900);
}

.forgot-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: rgba(0, 0, 0, 0.55);
    backdrop-filter: blur(2px);
}

.forgot-modal {
    background: #fff;
    border-radius: 1rem;
    padding: 2rem;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-height: 90vh;
    overflow-y: auto;
}

.forgot-close {
    background: none;
    border: none;
    color: var(--bs-gray-500);
    cursor: pointer;
    line-height: 1;
    padding: 0;
}

.forgot-close:hover {
    color: var(--bs-gray-800);
}

.channel-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.35rem;
    padding: 0.85rem 0.5rem;
    border: 1px solid var(--bs-border-color);
    border-radius: 0.5rem;
    background: #fff;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--bs-gray-700);
    cursor: pointer;
    transition: all 0.12s ease;
}

.channel-option:hover {
    background: var(--bs-light);
}

.channel-option.active {
    border-color: var(--bs-primary);
    background: var(--bs-primary-light, rgba(59, 130, 246, 0.08));
    color: var(--bs-gray-900);
}

.tracking-wide {
    letter-spacing: 0.4rem;
}
</style>
