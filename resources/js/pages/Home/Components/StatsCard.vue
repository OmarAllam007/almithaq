<script setup lang="ts">
import { vueLang } from '@erag/lang-sync-inertia';
const { __ } = vueLang();
</script>

<template>
    <!--begin::Col-->
    <div class="col-xl-12 mb-10">
        <!--begin::Lists Widget 19-->
        <div class="">
            <!--begin::Body-->
            <p class="fs-2 fw-bold">{{ __('home.stats') }}</p>
            <div class="card-body">
                <!--begin::Stats-->
                <div class="position-relative">
                    <!--begin::Row-->
                    <div class="row g-3 g-lg-8">
                        <!--begin::Col-->
                        <div class="col-xs-4 col-3">
                            <!--begin::Items-->
                            <div class="rounded-2 bg-white px-6 py-5 shadow-md">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-30px me-5 mb-8">
                                    <span class="symbol-label">
                                        <i class="ki-outline ki-eye fs-1 text-primary"></i>
                                    </span>
                                </div>
                                <!--end::Symbol-->

                                <!--begin::Stats-->
                                <div class="m-0">
                                    <!--begin::Number-->
                                    <span class="fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1 text-gray-700">37</span>
                                    <!--end::Number-->

                                    <!--begin::Desc-->
                                    <span class="fw-semibold fs-6 text-gray-500">Views</span>
                                    <!--end::Desc-->
                                </div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Items-->
                        </div>
                        <!--end::Col-->
                        <!--begin::Col-->
                        <div class="col-xs-4 col-3">
                            <!--begin::Items-->
                            <div class="rounded-2 bg-white px-6 py-5 shadow-md">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-30px me-5 mb-8">
                                    <span class="symbol-label">
                                        <i class="ki-solid ki-heart-circle fs-1 text-danger"></i>
                                    </span>
                                </div>
                                <!--end::Symbol-->

                                <!--begin::Stats-->
                                <div class="m-0">
                                    <!--begin::Number-->
                                    <span class="fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1 text-gray-700">6</span>
                                    <!--end::Number-->

                                    <!--begin::Desc-->
                                    <span class="fw-semibold fs-6 text-gray-500">Like</span>
                                    <!--end::Desc-->
                                </div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Items-->
                        </div>
                        <!--end::Col-->
                        <!--begin::Col-->
                        <div class="col-xs-4 col-3">
                            <!--begin::Items-->
                            <div class="rounded-2 bg-white px-6 py-5 shadow-md">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-30px me-5 mb-8">
                                    <span class="symbol-label">
                                        <i class="ki-outline ki-send fs-1 text-primary"></i>
                                    </span>
                                </div>
                                <!--end::Symbol-->

                                <!--begin::Stats-->
                                <div class="m-0">
                                    <!--begin::Number-->
                                    <span class="fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1 text-gray-700">10</span>
                                    <!--end::Number-->

                                    <!--begin::Desc-->
                                    <span class="fw-semibold fs-6 text-gray-500">Request</span>
                                    <!--end::Desc-->
                                </div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Items-->
                        </div>
                        <!--end::Col-->
                        <!--begin::Col-->
                        <div class="col-xs-4 col-3">
                            <!--begin::Items-->
                            <div class="rounded-2 bg-white px-6 py-5 shadow-md">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-30px me-5 mb-8">
                                    <span class="symbol-label">
                                        <i class="ki-outline ki-calendar fs-1 text-warning"></i>
                                    </span>
                                </div>
                                <!--end::Symbol-->

                                <!--begin::Stats-->
                                <div class="m-0">
                                    <!--begin::Number-->
                                    <span class="fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1 text-gray-700">100</span>
                                    <!--end::Number-->

                                    <!--begin::Desc-->
                                    <span class="fw-semibold fs-6 text-gray-500">days</span>
                                    <!--end::Desc-->
                                </div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Items-->
                        </div>
                        <!--end::Col-->
                    </div>
                    <!--end::Row-->
                </div>
                <!--end::Stats-->
            </div>
            <!--end::Body-->
        </div>
        <!--end::Lists Widget 19-->
    </div>
</template>

<style scoped></style>
