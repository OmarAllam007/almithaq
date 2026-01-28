<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import PlanLineItem from '@/pages/Subscription/PlanLineItem.vue';
import PlanItem from '@/pages/Subscription/PlanItem.vue';

const selectedPlan = ref<string>('1');

const form = useForm({
    plan_duration: '1',
});

const selectPlan = (value: string) => {
    selectedPlan.value = value;
    form.plan_duration = value;
};

const closeModal = () => {
    const modalElement = document.getElementById('kt_modal_upgrade_plan');
    if (modalElement) {
        const modal = window.bootstrap?.Modal?.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }
        // Remove backdrop manually if it exists
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
        // Remove modal-open class from body
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }
};

const submit = () => {
    if (!selectedPlan.value) {
        return;
    }

    form.plan_duration = selectedPlan.value;

    // Close modal before redirecting
    closeModal();
    // Small delay to ensure modal is closed
    setTimeout(() => {
        form.post(route('subscription.initiate'), {
            preserveScroll: true,
        });
    }, 300);
};
</script>

<template>
    <div class="modal fade" id="kt_modal_upgrade_plan" tabindex="-1" style="display: none" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-xl ">
            <!--begin::Modal content-->
            <div class="modal-content rounded">
                <!--begin::Modal header-->
                <div class="modal-header justify-content-end border-0 pb-0">
                    <!--begin::Close-->
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->

                <!--begin::Modal body-->
                <div class="modal-body px-xl-20 px-5 pt-0 pb-15">
                    <!--begin::Heading-->
                    <div class="mb-13 text-center">
                        <h1 class="mb-3">Become a VIP member</h1>
                    </div>
                    <!--end::Heading-->

                    <!--begin::Plans-->
                    <div class="d-flex flex-column">
                        <!--begin::Nav group-->
                        <div class="nav-group nav-group-outline mx-auto" data-kt-buttons="true" data-kt-initialized="1">

                        </div>
                        <!--end::Nav group-->

                        <!--begin::Row-->
                        <div class="row mt-10">
                            <!--begin::Col-->
                            <div class="col-lg-6 mb-lg-0 mb-10">
                                <!--begin::Tabs-->
                                <div class="nav flex-column" role="tablist">
                                    <PlanItem
                                        title="1 Month"
                                        price="50"
                                        duration="1 Month"
                                        value="1"
                                        v-model="selectedPlan"
                                        @update:modelValue="selectPlan"
                                    />
                                    <PlanItem
                                        title="3 Months"
                                        price="100"
                                        duration="3 Months"
                                        value="3"
                                        v-model="selectedPlan"
                                        @update:modelValue="selectPlan"
                                    />
                                    <PlanItem
                                        title="6 Months"
                                        price="150"
                                        duration="6 Months"
                                        value="6"
                                        v-model="selectedPlan"
                                        @update:modelValue="selectPlan"
                                    />
                                    <PlanItem
                                        title="1 Year"
                                        price="250"
                                        duration="1 Year"
                                        value="12"
                                        v-model="selectedPlan"
                                        @update:modelValue="selectPlan"
                                    />
                                </div>
                                <!--end::Tabs-->
                            </div>
                            <!--end::Col-->

                            <!--begin::Col-->
                            <div class="col-lg-6">
                                <!--begin::Tab content-->
                                <div class="bg-light h-100 rounded p-10">
                                    <!--begin::Tab Pane-->
                                    <div class="fade active show" >
                                        <!--begin::Heading-->
                                        <div class="pb-5">
                                            <h2 class="fw-bold text-gray-900">What’s in VIP Plan?</h2>
                                        </div>
                                        <!--end::Heading-->

                                        <!--begin::Body-->
                                        <div class="pt-1">
                                            <PlanLineItem title="No Ads" />
                                            <PlanLineItem title="Send unlimited messages to any one" />
                                            <PlanLineItem title="Control Who Contact you" />
                                            <PlanLineItem title="Display VIP Members and Online Members" />
                                            <PlanLineItem title="Special Profile and VIP Badge" />
                                            <PlanLineItem title="Login in offline mode" />
                                            <PlanLineItem title="Show Member images after their approval" />
                                            <PlanLineItem title="Much more features" />

                                        </div>
                                        <!--end::Body-->
                                    </div>
                                    <!--end::Tab Pane-->
                                </div>
                                <!--end::Tab content-->
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Row-->
                    </div>
                    <!--end::Plans-->

                    <!--begin::Error Messages-->
                    <div v-if="form.errors.plan_duration" class="alert alert-danger d-flex align-items-center p-5 mb-10">
                        <i class="ki-outline ki-information-5 fs-2hx text-danger me-4"></i>
                        <div class="d-flex flex-column">
                            <span class="fw-bold">{{ form.errors.plan_duration }}</span>
                        </div>
                    </div>
                    <!--end::Error Messages-->

                    <!--begin::Actions-->
                    <div class="d-flex flex-center flex-row-fluid pt-12">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>

                        <button
                            type="button"
                            class="btn btn-primary"
                            id="kt_modal_upgrade_plan_btn"
                            :disabled="!selectedPlan || form.processing"
                            @click="submit"
                        >
                            <!--begin::Indicator label-->
                            <span class="indicator-label"> Subscribe to this Plan</span>
                            <!--end::Indicator label-->

                            <!--begin::Indicator progress-->
                            <span class="indicator-progress" v-if="form.processing">
                                Please wait... <span class="spinner-border spinner-border-sm ms-2 align-middle"></span>
                            </span>
                            <!--end::Indicator progress-->
                        </button>
                    </div>
                    <!--end::Actions-->
                </div>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
</template>

<style scoped></style>
