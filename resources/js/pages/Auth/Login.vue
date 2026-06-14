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
declare const toastr: any;

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
    form.post(route('login'), {
        onSuccess: () => {
            toastr.success(__('login.sign-in-success'));
        },
        onError: () => {
            const msg = form.errors.username || form.errors.password || __('login.sign-in-error');
            toastr.error(msg);
        },
    });
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
        <div class="auth-glow auth-glow--1"></div>
        <div class="auth-glow auth-glow--2"></div>

        <div class="auth-wrap">
            <div class="auth-card">

                <!-- Brand -->
                <a href="/" class="auth-brand">
                    <span class="auth-brand__mark">
                        <svg viewBox="0 0 40 28" fill="none">
                            <circle cx="14" cy="14" r="9" stroke="currentColor" stroke-width="2.2" />
                            <circle cx="26" cy="14" r="9" stroke="currentColor" stroke-width="2.2" opacity="0.55" />
                        </svg>
                    </span>
                    <span class="auth-brand__text">
                        <span class="auth-brand__name">Khotobah</span>
                        <span class="auth-brand__sub">خطوبة</span>
                    </span>
                </a>

                <!-- Heading -->
                <div class="auth-head">
                    <h1 class="auth-head__title">{{ __('login.sign-in-title') }}</h1>
                    <p class="auth-head__desc">{{ __('login.general-desc') }}</p>
                    <div class="auth-head__sub">
                        <span>{{ __('login.sign-in-head-desc') }}</span>
                        <Link :href="route('signup')" class="auth-link mx-1">{{ __('login.sign-in-head-link') }}</Link>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit">
                    <div class="field">
                        <input
                            type="text"
                            v-model="form.username"
                            :placeholder="__('login.sign-in-input-username')"
                            name="username"
                            autocomplete="off"
                            class="field__input"
                        />
                    </div>

                    <div class="field">
                        <input
                            v-model="form.password"
                            type="password"
                            :placeholder="__('login.sign-in-input-password')"
                            name="password"
                            autocomplete="off"
                            class="field__input"
                            :class="{ 'field__input--error': form.errors.username }"
                        />
                        <p v-if="form.errors.username" class="field__error">{{ form.errors.username }}</p>
                    </div>

                    <div class="auth-forgot-row">
                        <button type="button" class="auth-link auth-link--sm" @click="openForgotModal">
                            {{ __('login.sign-in-forgot-password') }}
                        </button>
                    </div>

                    <button type="submit" class="auth-submit" :disabled="!canSubmit">
                        <span v-if="!form.processing">{{ __('login.sign-in-submit') }}</span>
                        <span v-else class="auth-submit__loading">
                            {{ __('login.general-progress') }}
                            <span class="auth-spinner"></span>
                        </span>
                    </button>
                </form>

                <!-- Language switcher -->
                <div class="auth-lang">
                    <div class="lang">
                        <button type="button" class="lang__toggle" @click="showLangDropdown = !showLangDropdown">
                            <img :src="currentLocale === 'ar' ? 'assets/media/flags/saudi-arabia.svg' : 'assets/media/flags/united-states.svg'" alt="" />
                            <span>{{ currentLocale === 'ar' ? 'العربية' : 'EN' }}</span>
                        </button>
                        <transition name="fade">
                            <div v-if="showLangDropdown" class="lang__menu">
                                <button type="button" :class="{ active: currentLocale === 'en' }" @click="switchLang('en')">
                                    <img src="assets/media/flags/united-states.svg" alt="" /> English
                                </button>
                                <button type="button" :class="{ active: currentLocale === 'ar' }" @click="switchLang('ar')">
                                    <img src="assets/media/flags/saudi-arabia.svg" alt="" /> العربية
                                </button>
                            </div>
                        </transition>
                    </div>
                </div>

            </div>
        </div>

        <!-- Forgot password modal -->
        <div v-if="showForgotModal" class="modal-backdrop" @click.self="closeForgotModal">
            <div class="modal-card" :dir="isRtl ? 'rtl' : 'ltr'">
                <div class="modal-card__head">
                    <h2>{{ __('login.forgot-title') }}</h2>
                    <button type="button" class="modal-close" @click="closeForgotModal">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                <div v-if="forgotError" class="modal-error">{{ forgotError }}</div>

                <!-- Step 1 -->
                <form v-if="forgotStep === 'identify'" @submit.prevent="sendResetCode">
                    <p class="modal-desc">{{ __('login.forgot-step-identify-desc') }}</p>

                    <div class="field">
                        <label class="field__label">{{ __('login.forgot-username-label') }}</label>
                        <input type="text" v-model="forgot.username" autocomplete="off" class="field__input" />
                    </div>

                    <div class="field">
                        <label class="field__label">{{ __('login.forgot-channel-label') }}</label>
                        <div class="channel-group">
                            <button type="button" class="channel-btn" :class="{ active: forgotChannel === 'email' }" @click="forgotChannel = 'email'">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <rect x="2" y="4" width="20" height="16" rx="2" stroke="currentColor" stroke-width="1.6" />
                                    <path d="m2 7 10 7 10-7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                </svg>
                                {{ __('login.forgot-channel-email') }}
                            </button>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="auth-btn-outline" @click="closeForgotModal">{{ __('login.forgot-cancel') }}</button>
                        <button type="submit" class="auth-submit" style="flex:1" :disabled="!forgot.username || forgotSending">
                            <span v-if="!forgotSending">{{ __('login.forgot-send-code') }}</span>
                            <span v-else class="auth-submit__loading">{{ __('login.general-progress') }}<span class="auth-spinner"></span></span>
                        </button>
                    </div>
                </form>

                <!-- Step 2 -->
                <form v-else @submit.prevent="submitResetPassword">
                    <p class="modal-desc">
                        {{ __('login.forgot-step-verify-desc').replace(':destination', forgotDestination) }}
                    </p>

                    <div class="field">
                        <label class="field__label">{{ __('login.forgot-code-label') }}</label>
                        <input
                            type="text"
                            inputmode="numeric"
                            maxlength="6"
                            v-model="forgot.code"
                            autocomplete="one-time-code"
                            class="field__input field__input--center field__input--lg"
                        />
                    </div>

                    <PasswordInput v-model="forgot.password" :label="__('login.forgot-new-password-label')" />
                    <PasswordInput v-model="forgot.password_confirmation" :label="__('login.forgot-confirm-password-label')" />

                    <div class="modal-resend-row">
                        <button type="button" class="auth-link auth-link--sm" @click="forgotStep = 'identify'">{{ __('login.forgot-back') }}</button>
                        <button type="button" class="auth-link auth-link--sm" :disabled="forgotSending" @click="sendResetCode">{{ __('login.forgot-resend') }}</button>
                    </div>

                    <button type="submit" class="auth-submit" :disabled="!canResetPassword">
                        <span v-if="!forgotResetting">{{ __('login.forgot-reset-submit') }}</span>
                        <span v-else class="auth-submit__loading">{{ __('login.general-progress') }}<span class="auth-spinner"></span></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ===== Design tokens (matching landing) ===== */
.auth-root {
    --ink: #2c0a18;
    --plum: #45112a;
    --plum-deep: #2a0a1a;
    --rose: #d02e79;
    --rose-soft: #e85fa0;
    --gold: #c79a3f;
    --gold-soft: #e6c878;
    --cream: #fbf5ef;
    --cream-2: #f5ebe1;
    --blush: #fce8f3;
    --line: rgba(69, 17, 42, 0.12);

    position: relative;
    min-height: 100vh;
    background: var(--cream);
    font-family: 'Cairo', sans-serif;
    overflow-x: hidden;
}

/* Dot texture */
.auth-root::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image: radial-gradient(circle at 1px 1px, rgba(69, 17, 42, 0.05) 1px, transparent 0);
    background-size: 26px 26px;
    pointer-events: none;
    z-index: 0;
    opacity: 0.7;
}

/* Ambient glows */
.auth-glow {
    position: fixed;
    border-radius: 50%;
    filter: blur(90px);
    pointer-events: none;
    z-index: 0;
}
.auth-glow--1 { width: 500px; height: 500px; background: rgba(208, 46, 121, 0.15); top: -160px; inset-inline-end: -120px; }
.auth-glow--2 { width: 400px; height: 400px; background: rgba(199, 154, 63, 0.13); bottom: -120px; inset-inline-start: -100px; }

/* Layout */
.auth-wrap {
    position: relative;
    z-index: 1;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
}

/* Card */
.auth-card {
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(12px);
    border: 1px solid var(--line);
    border-radius: 1.5rem;
    padding: 2.5rem 2rem;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 24px 64px -24px rgba(69, 17, 42, 0.28);
}

/* Brand */
.auth-brand {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    text-decoration: none;
    margin-bottom: 2rem;
}
.auth-brand__mark { color: var(--rose); display: inline-flex; }
.auth-brand__mark svg { width: 36px; height: 25px; }
.auth-brand__text { display: flex; flex-direction: column; line-height: 1; }
.auth-brand__name {
    font-family: 'Cormorant Garamond', 'Cairo', serif;
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--plum);
}
.auth-brand__sub { font-size: 0.68rem; color: var(--rose); font-weight: 600; margin-top: 2px; }

/* Heading */
.auth-head { margin-bottom: 1.8rem; }
.auth-head__title {
    font-family: 'Cormorant Garamond', 'Cairo', serif;
    font-size: 2rem;
    font-weight: 600;
    color: var(--plum);
    margin: 0 0 0.35rem;
    line-height: 1.15;
}
.auth-head__desc { font-size: 0.9rem; color: rgba(44, 10, 24, 0.62); margin: 0 0 0.4rem; }
.auth-head__sub { font-size: 0.88rem; color: rgba(44, 10, 24, 0.6); }

/* Fields */
.field { margin-bottom: 1rem; }
.field__label { display: block; font-size: 0.82rem; font-weight: 700; color: var(--plum); margin-bottom: 0.4rem; }
.field__input {
    width: 100%;
    box-sizing: border-box;
    padding: 0.78rem 1rem;
    border: 1.5px solid var(--line);
    border-radius: 0.65rem;
    background: #fff;
    color: var(--ink);
    font-family: 'Cairo', sans-serif;
    font-size: 0.95rem;
    outline: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.field__input:focus { border-color: var(--rose); box-shadow: 0 0 0 3px rgba(208, 46, 121, 0.1); }
.field__input--error { border-color: #dc2626; }
.field__input--center { text-align: center; letter-spacing: 0.4rem; }
.field__input--lg { font-size: 1.2rem; padding: 0.85rem 1rem; }
.field__error { font-size: 0.8rem; color: #dc2626; margin: 0.3rem 0 0; }

/* Forgot row */
.auth-forgot-row { display: flex; justify-content: flex-end; margin-bottom: 1.2rem; }

/* Submit */
.auth-submit {
    width: 100%;
    padding: 0.9rem 1.5rem;
    background: linear-gradient(135deg, var(--rose), var(--rose-soft));
    color: #fff;
    border: none;
    border-radius: 999px;
    font-family: 'Cairo', sans-serif;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 8px 24px -8px rgba(208, 46, 121, 0.8);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}
.auth-submit:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 14px 32px -8px rgba(208, 46, 121, 0.9); }
.auth-submit:disabled { opacity: 0.55; cursor: not-allowed; }
.auth-submit__loading { display: inline-flex; align-items: center; gap: 0.5rem; }
.auth-spinner {
    display: inline-block;
    width: 14px; height: 14px;
    border: 2px solid rgba(255, 255, 255, 0.35);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Outline button */
.auth-btn-outline {
    flex: 1;
    padding: 0.85rem 1rem;
    background: transparent;
    border: 1.5px solid var(--line);
    border-radius: 999px;
    font-family: 'Cairo', sans-serif;
    font-weight: 600;
    font-size: 0.95rem;
    color: var(--plum);
    cursor: pointer;
    transition: border-color 0.2s ease, background 0.2s ease;
}
.auth-btn-outline:hover { background: var(--cream-2); border-color: var(--rose); color: var(--rose); }

/* Links */
.auth-link {
    color: var(--rose);
    font-weight: 700;
    font-size: 0.88rem;
    text-decoration: none;
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
    font-family: 'Cairo', sans-serif;
    transition: color 0.2s ease;
}
.auth-link:hover { color: var(--plum); }
.auth-link--sm { font-size: 0.82rem; }

/* Lang */
.auth-lang { display: flex; justify-content: center; margin-top: 1.6rem; padding-top: 1.4rem; border-top: 1px solid var(--line); }
.lang { position: relative; }
.lang__toggle {
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: rgba(255, 255, 255, 0.6);
    border: 1px solid var(--line);
    border-radius: 999px;
    padding: 0.4rem 0.75rem;
    font-size: 0.82rem; font-weight: 700; color: var(--plum);
    cursor: pointer;
    font-family: 'Cairo', sans-serif;
}
.lang__toggle img { width: 16px; height: 16px; border-radius: 3px; }
.lang__menu {
    position: absolute;
    bottom: calc(100% + 8px);
    left: 50%; transform: translateX(-50%);
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 0.75rem;
    box-shadow: 0 18px 40px -18px rgba(69, 17, 42, 0.4);
    padding: 0.3rem;
    min-width: 160px;
    z-index: 60;
    white-space: nowrap;
}
.lang__menu button {
    display: flex; align-items: center; gap: 0.55rem;
    width: 100%;
    padding: 0.55rem 0.7rem;
    border: none; background: none; cursor: pointer;
    border-radius: 0.5rem;
    font-size: 0.88rem; font-weight: 600; color: var(--plum);
    text-align: start;
    font-family: 'Cairo', sans-serif;
}
.lang__menu button img { width: 18px; height: 18px; border-radius: 3px; }
.lang__menu button:hover { background: var(--cream-2); }
.lang__menu button.active { color: var(--rose); background: var(--blush); }

/* Modal */
.modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: rgba(42, 10, 26, 0.55);
    backdrop-filter: blur(4px);
}
.modal-card {
    background: #fff;
    border-radius: 1.5rem;
    padding: 2rem;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 24px 64px -24px rgba(42, 10, 26, 0.5);
    max-height: 90vh;
    overflow-y: auto;
}
.modal-card__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.2rem;
}
.modal-card__head h2 {
    font-family: 'Cormorant Garamond', 'Cairo', serif;
    font-size: 1.6rem;
    font-weight: 600;
    color: var(--plum);
    margin: 0;
}
.modal-close {
    background: none; border: none; cursor: pointer;
    color: rgba(44, 10, 24, 0.45);
    width: 32px; height: 32px;
    display: grid; place-items: center;
    border-radius: 50%;
    transition: background 0.2s ease;
}
.modal-close:hover { background: var(--cream-2); color: var(--plum); }
.modal-close svg { width: 18px; height: 18px; }
.modal-desc { font-size: 0.9rem; color: rgba(44, 10, 24, 0.65); margin: 0 0 1.2rem; line-height: 1.6; }
.modal-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 0.65rem;
    padding: 0.65rem 0.9rem;
    font-size: 0.85rem;
    color: #991b1b;
    margin-bottom: 1rem;
}
.modal-actions { display: flex; gap: 0.75rem; margin-top: 1.2rem; }
.modal-resend-row { display: flex; justify-content: space-between; margin-bottom: 1.2rem; }

/* Channel */
.channel-group { display: flex; gap: 0.75rem; }
.channel-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.4rem;
    padding: 0.85rem 1.5rem;
    border: 1.5px solid var(--line);
    border-radius: 0.85rem;
    background: #fff;
    font-family: 'Cairo', sans-serif;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--plum);
    cursor: pointer;
    flex: 1;
    transition: border-color 0.2s ease, background 0.2s ease;
}
.channel-btn svg { width: 22px; height: 22px; }
.channel-btn:hover { background: var(--cream-2); }
.channel-btn.active { border-color: var(--rose); background: var(--blush); color: var(--rose); }

/* Transitions */
.fade-enter-active, .fade-leave-active { transition: opacity 0.18s ease, transform 0.18s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(6px); }

/* PasswordInput override */
:deep(.fv-row) { margin-bottom: 1rem; }
:deep(.form-label) { display: block; font-size: 0.82rem; font-weight: 700; color: var(--plum); margin-bottom: 0.4rem; }
:deep(.form-control) {
    width: 100%;
    box-sizing: border-box;
    padding: 0.78rem 1rem;
    border: 1.5px solid var(--line) !important;
    border-radius: 0.65rem !important;
    background: #fff !important;
    color: var(--ink) !important;
    font-family: 'Cairo', sans-serif;
    font-size: 0.95rem;
    box-shadow: none !important;
}
:deep(.form-control:focus) { border-color: var(--rose) !important; box-shadow: 0 0 0 3px rgba(208, 46, 121, 0.1) !important; }
</style>
