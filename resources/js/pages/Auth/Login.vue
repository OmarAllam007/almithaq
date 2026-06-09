<script setup lang="ts">
import { switchLanguage } from '@/actions/App/Http/Controllers/LanguageController';
import { vueLang } from '@erag/lang-sync-inertia';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';

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
    window.location.href = switchLanguage.url(lang);
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
                        <a href="#" class="link-primary fs-7">
                            {{ __('login.sign-in-forgot-password') }}
                        </a>
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
</style>
