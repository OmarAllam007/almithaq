<script setup lang="ts">
import { useLang } from '@/composables/useLang';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import AuthStory from './AuthStory.vue';

defineProps<{ email: string }>();

const page = usePage();
const { trans } = useLang();
const isRtl = computed(() => (page.props as any).locale === 'ar');
const flash = computed(() => (page.props as any).flash);
const resendForm = useForm({});

const resend = () => {
    resendForm.post(route('verification.resend'), { preserveScroll: true });
};
</script>

<template>
    <Head :title="trans('email_verification.title')" />
    <div class="auth-root auth-scene auth-scene--verify" :dir="isRtl ? 'rtl' : 'ltr'">
        <div class="auth-shell">
            <AuthStory mode="verify" :is-rtl="isRtl" />
            <main class="auth-wrap">
                <div class="auth-card">
                    <Link href="/" class="auth-brand">
                        <span class="auth-brand__mark">
                            <svg viewBox="0 0 40 28" fill="none" aria-hidden="true">
                                <circle cx="14" cy="14" r="9" stroke="currentColor" stroke-width="2.2" />
                                <circle cx="26" cy="14" r="9" stroke="currentColor" stroke-width="2.2" opacity="0.55" />
                            </svg>
                        </span>
                        <span class="auth-brand__text"><span class="auth-brand__name">Khotobah</span><span class="auth-brand__sub">خطوبة</span></span>
                    </Link>

                    <div class="verify-emblem mt-5" aria-hidden="true">
                        <span class="verify-emblem__orbit"></span>
                        <svg viewBox="0 0 48 48" fill="none">
                            <rect x="6" y="12" width="36" height="25" rx="6" stroke="currentColor" stroke-width="2" />
                            <path d="m8 16 16 12 16-12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M33 7v8M29 11h8" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>

                    <header class="auth-head verify-head">
                        <span class="verify-eyebrow">{{ isRtl ? 'خطوة التأكيد' : 'CONFIRM YOUR ADDRESS' }}</span>
                        <h1 class="auth-head__title">{{ trans('email_verification.title') }}</h1>
                        <p class="auth-head__desc">{{ trans('email_verification.subtitle') }}</p>
                    </header>

                    <div v-if="flash?.success" class="verify-success" role="status">
                        <span class="verify-success__icon" aria-hidden="true">✓</span>
                        <span>{{ flash.success }}</span>
                    </div>

                    <section class="verify-delivery" aria-labelledby="verify-delivery-title">
                        <div class="verify-delivery__top">
                            <span class="verify-delivery__number">01</span>
                            <span id="verify-delivery-title">{{ trans('email_verification.sent_to') }}</span>
                        </div>
                        <div class="verify-delivery__address" dir="ltr">{{ email }}</div>
                        <p>{{ trans('email_verification.click_link') }}</p>
                    </section>

                    <div class="verify-help">
                        <span class="verify-help__spark" aria-hidden="true">✦</span>
                        <p>{{ trans('email_verification.spam_note') }}</p>
                    </div>

                    <button type="button" class="auth-submit verify-resend" :disabled="resendForm.processing" @click="resend">
                        <span v-if="resendForm.processing" class="auth-submit__loading">
                            {{ trans('email_verification.resending') }} <span class="auth-spinner"></span>
                        </span>
                        <span v-else class="verify-resend__label">
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path
                                    d="M17 3 3 9l6 2 2 6 6-14ZM9 11l4-4"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            {{ trans('email_verification.resend_btn') }}
                        </span>
                    </button>

                    <div class="verify-footer">
                        <div>
                            <strong>{{ trans('email_verification.wrong_email') }}</strong>
                            <p>{{ trans('email_verification.wrong_email_note') }}</p>
                        </div>
                        <Link :href="route('logout')" method="post" as="button" type="button" class="verify-logout">
                            {{ trans('email_verification.logout') }}
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path
                                    d="M8 4H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h3m4-3 3-3-3-3m3 3H7"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </Link>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
