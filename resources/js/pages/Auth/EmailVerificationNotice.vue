<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import { useLang } from '@/composables/useLang';

interface Props {
    email: string;
}

const props = defineProps<Props>();
const page = usePage();
const { trans } = useLang();
const isRtl = computed(() => (page.props as any).locale === 'ar');
const flash = computed(() => (page.props as any).flash);

const resendForm = useForm({});

const resend = () => {
    resendForm.post(route('verification.resend'));
};
</script>

<template>
    <div class="verification-page d-flex flex-column flex-root" id="kt_app_root" :dir="isRtl ? 'rtl' : 'ltr'">
        <div class="d-flex flex-column flex-column-fluid">
            <div class="d-flex flex-column flex-lg-row-fluid p-lg-10 w-100 p-5">
                <div class="d-flex flex-center flex-column w-100">
                    <div class="mw-600px w-100">

                        <!-- Icon -->
                        <div class="text-center mb-10">
                            <div class="email-icon-wrapper mb-5">
                                <i class="ki-outline ki-sms fs-3x text-primary"></i>
                            </div>
                            <h1 class="fw-bold fs-2x text-gray-900 mb-2">{{ trans('email_verification.title') }}</h1>
                            <p class="text-muted fw-semibold fs-5">{{ trans('email_verification.subtitle') }}</p>
                        </div>

                        <!-- Success flash -->
                        <div v-if="flash?.success" class="alert alert-success d-flex align-items-center p-5 mb-8">
                            <i class="ki-outline ki-shield-tick fs-2hx text-success me-4"></i>
                            <span class="fw-semibold">{{ flash.success }}</span>
                        </div>

                        <div class="card shadow-sm border-0">
                            <div class="card-body text-center py-15 px-lg-10 px-6">

                                <!-- Email display -->
                                <div class="mb-8">
                                    <p class="text-gray-600 fs-5 mb-2">{{ trans('email_verification.sent_to') }}</p>
                                    <span class="badge badge-light-primary fs-5 py-3 px-5 fw-bold">
                                        {{ props.email }}
                                    </span>
                                </div>

                                <p class="text-gray-600 fs-6 mb-3">
                                    {{ trans('email_verification.click_link') }}
                                </p>
                                <p class="text-gray-500 fs-7 mb-10">
                                    {{ trans('email_verification.spam_note') }}
                                </p>

                                <div class="separator my-8"></div>

                                <!-- Resend button -->
                                <button
                                    type="button"
                                    class="btn btn-primary mb-5"
                                    :disabled="resendForm.processing"
                                    @click="resend"
                                >
                                    <span v-if="!resendForm.processing">
                                        <i class="ki-outline ki-send fs-4 me-1"></i>
                                        {{ trans('email_verification.resend_btn') }}
                                    </span>
                                    <span v-else class="d-flex align-items-center gap-2">
                                        <span class="spinner-border spinner-border-sm"></span>
                                        {{ trans('email_verification.resending') }}
                                    </span>
                                </button>

                                <div class="separator my-8"></div>

                                <div class="d-flex flex-center gap-4 flex-wrap">
                                    <div class="text-center">
                                        <p class="text-gray-500 fs-7 mb-2">{{ trans('email_verification.wrong_email') }}</p>
                                        <p class="text-gray-400 fs-8 mb-3">{{ trans('email_verification.wrong_email_note') }}</p>
                                    </div>
                                </div>

                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    class="btn btn-sm btn-light-danger"
                                >
                                    <i class="ki-outline ki-exit-right fs-4 me-1"></i>
                                    {{ trans('email_verification.logout') }}
                                </Link>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.verification-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #f5f8fa 0%, #eef2f7 100%);
}

.email-icon-wrapper {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(0, 158, 247, 0.1) 0%, rgba(0, 158, 247, 0.05) 100%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-6px); }
}

[data-bs-theme="dark"] .verification-page {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
}
</style>
