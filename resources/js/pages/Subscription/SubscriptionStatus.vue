<script setup lang="ts">

</script>

<template>
    <div class="card mb-xl-10 mb-5">
        <!--begin::Card body-->
        <div class="card-body">

            <!--begin::Row-->
            <div class="row">
                <!--begin::Col-->
                <div class="col-lg-7">
                    <!--begin::Heading-->
                    <h3 class="mb-2">Active until Dec 09, 2026</h3>
                    <p class="fs-6 fw-semibold mb-lg-15 mb-6 text-gray-600">
                        We will send you a notification upon Subscription expiration
                    </p>
                    <!--end::Heading-->

                    <!--begin::Info-->
                    <div class="fs-5 mb-2">
                        <span class="fw-bold me-1 text-gray-800">$24.99</span>
                        <span class="fw-semibold text-gray-600">Per Month</span>
                    </div>
                    <!--end::Info-->

                    <!--begin::Notice-->
                    <div class="fs-6 fw-semibold text-gray-600">Extended Pro Package. Up to 100 Agents &amp; 25 Projects</div>
                    <!--end::Notice-->
                </div>
                <!--end::Col-->

                <!--begin::Col-->
                <div class="col-lg-5">
                    <!--begin::Heading-->
                    <div class="d-flex text-muted fw-bold fs-5 mb-3">
                        <span class="flex-grow-1 text-gray-800">Users</span>
                        <span class="text-gray-800">86 of 100 Used</span>
                    </div>
                    <!--end::Heading-->

                    <!--begin::Progress-->
                    <div class="progress h-8px bg-light-primary mb-2">
                        <div
                            class="progress-bar bg-primary"
                            role="progressbar"
                            style="width: 86%"
                            aria-valuenow="86"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        ></div>
                    </div>
                    <!--end::Progress-->

                    <!--begin::Description-->
                    <div class="fs-6 fw-semibold mb-10 text-gray-600">14 Users remaining until your plan requires update</div>
                    <!--end::Description-->

                    <!--begin::Action-->
                    <div class="d-flex justify-content-end px-0 pb-0">
                        <a
                            href="#"
                            class="btn btn-light-danger btn-active-danger me-2"
                            id="kt_account_billing_cancel_subscription_btn"
                        >Cancel Subscription</a
                        >
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_upgrade_plan">
                            Upgrade Plan
                        </button>
                    </div>
                    <!--end::Action-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
        </div>
        <!--end::Card body-->
    </div>
</template>

<style scoped>

</style>
