<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import axios from 'axios';

interface Props {
    payment: {
        id: number | null;
        amount: number;
        currency: string;
        subscription: {
            plan_duration: number;
            price: number;
        } | null;
        plan_duration?: number;
    };
}

const props = defineProps<Props>();

const selectedMethod = ref<string>('');

const form = useForm({
    payment_method: '',
    plan_duration: props.payment.plan_duration || props.payment.subscription?.plan_duration || 1,
});

const planDurations: Record<number, string> = {
    1: '1 Month',
    3: '3 Months',
    6: '6 Months',
    12: '1 Year',
};

const paymentMethods = [
    {
        value: 'visa',
        label: 'Visa',
        icon: 'ki-outline ki-credit-cart',
    },
    {
        value: 'mada',
        label: 'Mada',
        icon: 'ki-outline ki-wallet',
    },
    {
        value: 'apple_pay',
        label: 'Apple Pay',
        icon: 'ki-outline ki-apple',
    },
];

const selectMethod = (method: string) => {
    selectedMethod.value = method;
    form.payment_method = method;
};

const submit = () => {
    if (!form.payment_method) {
        return;
    }

    // If payment ID exists, use the existing route, otherwise use create route
    if (props.payment.id) {
        form.post(route('payment.process.existing', props.payment.id));
    } else {
        axios.post(route('payment.process'), {
            payment_method: form.payment_method,
            plan_duration: form.plan_duration,
        }).then(res => {
            window.location.href = res.data.url;
        });
        // form.post(route('payment.process'));
    }
};
</script>

<template>
    <Head title="Payment" />

    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <!--begin::Content wrapper-->
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-lg-0 py-3">
                <!--begin::Toolbar container-->
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <!--begin::Page title-->
                    <div class="page-title d-flex flex-column justify-content-center me-3">
                        <!--begin::Title-->
                        <h1 class="page-heading d-flex fw-bold fs-3 flex-column justify-content-center my-0 text-gray-900">Payment</h1>
                        <!--end::Title-->
                    </div>
                    <!--end::Page title-->
                </div>
                <!--end::Toolbar container-->
            </div>
            <!--end::Toolbar-->

            <!--begin::Content-->
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <!--begin::Content container-->
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <!--begin::Card-->
                            <div class="card">
                                <!--begin::Card body-->
                                <div class="card-body p-10">
                                    <!--begin::Heading-->
                                    <div class="text-center mb-10">
                                        <h1 class="mb-3">Complete Your Payment</h1>
                                        <div class="text-gray-600 fw-semibold fs-5">
                                            Select your preferred payment method to continue
                                        </div>
                                    </div>
                                    <!--end::Heading-->

                                    <!--begin::Plan Summary-->
                                    <div v-if="payment.subscription || payment.plan_duration" class="bg-light rounded p-8 mb-10">
                                        <div class="d-flex justify-content-between align-items-center mb-5">
                                            <span class="fw-semibold fs-6 text-gray-600">Plan Duration</span>
                                            <span class="fw-bold fs-5 text-gray-900">{{ planDurations[payment.subscription?.plan_duration || payment.plan_duration || 1] }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold fs-6 text-gray-600">Amount</span>
                                            <span class="fw-bold fs-3x text-primary">${{ payment.amount }}</span>
                                        </div>
                                    </div>
                                    <!--end::Plan Summary-->

                                    <!--begin::Payment Methods-->
                                    <div class="mb-10">
                                        <h3 class="fw-bold mb-6">Select Payment Method</h3>
                                        <div class="d-flex flex-column gap-4">
                                            <label
                                                v-for="method in paymentMethods"
                                                :key="method.value"
                                                class="nav-link btn btn-outline btn-outline-dashed btn-color-dark d-flex flex-stack p-6 cursor-pointer"
                                                :class="{ 'btn-active btn-active-primary': selectedMethod === method.value }"
                                                @click="selectMethod(method.value)"
                                            >
                                                <div class="d-flex align-items-center">
                                                    <i :class="method.icon" class="fs-2x me-4"></i>
                                                    <span class="fw-bold fs-4">{{ method.label }}</span>
                                                </div>
                                                <div class="form-check form-check-custom form-check-solid form-check-success">
                                                    <input
                                                        class="form-check-input"
                                                        type="radio"
                                                        :value="method.value"
                                                        :checked="selectedMethod === method.value"
                                                        @change="selectMethod(method.value)"
                                                    />
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <!--end::Payment Methods-->

                                    <!--begin::Error Messages-->
                                    <div v-if="form.errors.payment_method" class="alert alert-danger d-flex align-items-center p-5 mb-10">
                                        <i class="ki-outline ki-information-5 fs-2hx text-danger me-4"></i>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">{{ form.errors.payment_method }}</span>
                                        </div>
                                    </div>
                                    <!--end::Error Messages-->

                                    <!--begin::Actions-->
                                    <div class="d-flex flex-center flex-row-fluid pt-12">
                                        <a :href="route('subscription.index')" class="btn btn-light me-3">Cancel</a>
                                        <button
                                            type="button"
                                            class="btn btn-primary"
                                            :disabled="!selectedMethod || form.processing"
                                            @click="submit"
                                        >
                                            <!--begin::Indicator label-->
                                            <span class="indicator-label"> Proceed to Payment</span>
                                            <!--end::Indicator label-->

                                            <!--begin::Indicator progress-->
                                            <span class="indicator-progress" v-if="form.processing">
                                                Please wait...
                                                <span class="spinner-border spinner-border-sm ms-2 align-middle"></span>
                                            </span>
                                            <!--end::Indicator progress-->
                                        </button>
                                    </div>
                                    <!--end::Actions-->
                                </div>
                                <!--end::Card body-->
                            </div>
                            <!--end::Card-->
                        </div>
                    </div>
                </div>
                <!--end::Content container-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Content wrapper-->
    </div>
</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
</style>
