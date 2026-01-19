<script setup lang="ts">
import { vueLang } from '@erag/lang-sync-inertia';

const { __ } = vueLang();
</script>

<template>
    <div class="col-xl-4">
        <!--begin::List widget 21-->
        <div class="card card-flush h-xl-100">
            <!--begin::Header-->
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900">{{ __('home.new_members') }}</span>
                </h3>

                <!--begin::Toolbar-->
                <div class="card-toolbar">
                    <a href="#" class="btn btn-sm btn-light">All Lessons</a>
                </div>
                <!--end::Toolbar-->
            </div>
            <!--end::Header-->

            <!--begin::Body-->
            <div class="card-body pt-5">
                <!--begin::Item-->
                <div class="d-flex flex-stack">
                    <!--begin::Wrapper-->
                    <div class="d-flex align-items-center me-3">
                        <!--begin::Logo-->
                        <img src="/metronic8/demo23/assets/media/svg/brand-logos/laravel-2.svg" class="w-30px me-4" alt="" />
                        <!--end::Logo-->

                        <!--begin::Section-->
                        <div class="flex-grow-1">
                            <!--begin::Text-->
                            <a href="#" class="text-hover-primary fs-5 fw-bold lh-0 text-gray-800">Laravel</a>
                            <!--end::Text-->

                            <!--begin::Description-->
                            <span class="fw-semibold d-block fs-6 text-gray-500">PHP Framework</span>
                            <!--end::Description--->
                        </div>
                        <!--end::Section-->
                    </div>
                    <!--end::Wrapper-->

                    <!--begin::Statistics-->
                    <div class="d-flex align-items-center mw-125px w-100">
                        <!--begin::Progress-->
                        <div class="progress h-6px bg-light-success me-2 w-100">
                            <div
                                class="progress-bar bg-success"
                                role="progressbar"
                                style="width: 65%"
                                aria-valuenow="65"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            ></div>
                        </div>
                        <!--end::Progress-->

                        <!--begin::Value-->
                        <span class="fw-semibold text-gray-500"> 65% </span>
                        <!--end::Value-->
                    </div>
                    <!--end::Statistics-->
                </div>
                <!--end::Item-->

                <!--begin::Separator-->
                <div class="separator separator-dashed my-3"></div>
                <!--end::Separator-->

                <!--begin::Item-->
                <div class="d-flex flex-stack">
                    <!--begin::Wrapper-->
                    <div class="d-flex align-items-center me-3">
                        <!--begin::Logo-->
                        <img src="/metronic8/demo23/assets/media/svg/brand-logos/vue-9.svg" class="w-30px me-4" alt="" />
                        <!--end::Logo-->

                        <!--begin::Section-->
                        <div class="flex-grow-1">
                            <!--begin::Text-->
                            <a href="#" class="text-hover-primary fs-5 fw-bold lh-0 text-gray-800">Vue 3</a>
                            <!--end::Text-->

                            <!--begin::Description-->
                            <span class="fw-semibold d-block fs-6 text-gray-500">JS Framework</span>
                            <!--end::Description--->
                        </div>
                        <!--end::Section-->
                    </div>
                    <!--end::Wrapper-->

                    <!--begin::Statistics-->
                    <div class="d-flex align-items-center mw-125px w-100">
                        <!--begin::Progress-->
                        <div class="progress h-6px bg-light-warning me-2 w-100">
                            <div
                                class="progress-bar bg-warning"
                                role="progressbar"
                                style="width: 87%"
                                aria-valuenow="87"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            ></div>
                        </div>
                        <!--end::Progress-->

                        <!--begin::Value-->
                        <span class="fw-semibold text-gray-500"> 87% </span>
                        <!--end::Value-->
                    </div>
                    <!--end::Statistics-->
                </div>
                <!--end::Item-->

                <!--begin::Separator-->
                <div class="separator separator-dashed my-3"></div>
                <!--end::Separator-->

                <!--begin::Item-->
                <div class="d-flex flex-stack">
                    <!--begin::Wrapper-->
                    <div class="d-flex align-items-center me-3">
                        <!--begin::Logo-->
                        <img src="/metronic8/demo23/assets/media/svg/brand-logos/bootstrap5.svg" class="w-30px me-4" alt="" />
                        <!--end::Logo-->

                        <!--begin::Section-->
                        <div class="flex-grow-1">
                            <!--begin::Text-->
                            <a href="#" class="text-hover-primary fs-5 fw-bold lh-0 text-gray-800">Bootstrap 5</a>
                            <!--end::Text-->

                            <!--begin::Description-->
                            <span class="fw-semibold d-block fs-6 text-gray-500">CSS Framework</span>
                            <!--end::Description--->
                        </div>
                        <!--end::Section-->
                    </div>
                    <!--end::Wrapper-->

                    <!--begin::Statistics-->
                    <div class="d-flex align-items-center mw-125px w-100">
                        <!--begin::Progress-->
                        <div class="progress h-6px bg-light-primary me-2 w-100">
                            <div
                                class="progress-bar bg-primary"
                                role="progressbar"
                                style="width: 44%"
                                aria-valuenow="44"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            ></div>
                        </div>
                        <!--end::Progress-->

                        <!--begin::Value-->
                        <span class="fw-semibold text-gray-500"> 44% </span>
                        <!--end::Value-->
                    </div>
                    <!--end::Statistics-->
                </div>
                <!--end::Item-->

                <!--begin::Separator-->
                <div class="separator separator-dashed my-3"></div>
                <!--end::Separator-->

                <!--begin::Item-->
                <div class="d-flex flex-stack">
                    <!--begin::Wrapper-->
                    <div class="d-flex align-items-center me-3">
                        <!--begin::Logo-->
                        <img src="/metronic8/demo23/assets/media/svg/brand-logos/angular-icon.svg" class="w-30px me-4" alt="" />
                        <!--end::Logo-->

                        <!--begin::Section-->
                        <div class="flex-grow-1">
                            <!--begin::Text-->
                            <a href="#" class="text-hover-primary fs-5 fw-bold lh-0 text-gray-800">Angular 16</a>
                            <!--end::Text-->

                            <!--begin::Description-->
                            <span class="fw-semibold d-block fs-6 text-gray-500">JS Framework</span>
                            <!--end::Description--->
                        </div>
                        <!--end::Section-->
                    </div>
                    <!--end::Wrapper-->

                    <!--begin::Statistics-->
                    <div class="d-flex align-items-center mw-125px w-100">
                        <!--begin::Progress-->
                        <div class="progress h-6px bg-light-info me-2 w-100">
                            <div
                                class="progress-bar bg-info"
                                role="progressbar"
                                style="width: 70%"
                                aria-valuenow="70"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            ></div>
                        </div>
                        <!--end::Progress-->

                        <!--begin::Value-->
                        <span class="fw-semibold text-gray-500"> 70% </span>
                        <!--end::Value-->
                    </div>
                    <!--end::Statistics-->
                </div>
                <!--end::Item-->

                <!--begin::Separator-->
                <div class="separator separator-dashed my-3"></div>
                <!--end::Separator-->

                <!--begin::Item-->
                <div class="d-flex flex-stack">
                    <!--begin::Wrapper-->
                    <div class="d-flex align-items-center me-3">
                        <!--begin::Logo-->
                        <img src="/metronic8/demo23/assets/media/svg/brand-logos/spring-3.svg" class="w-30px me-4" alt="" />
                        <!--end::Logo-->

                        <!--begin::Section-->
                        <div class="flex-grow-1">
                            <!--begin::Text-->
                            <a href="#" class="text-hover-primary fs-5 fw-bold lh-0 text-gray-800">Spring</a>
                            <!--end::Text-->

                            <!--begin::Description-->
                            <span class="fw-semibold d-block fs-6 text-gray-500">Java Framework</span>
                            <!--end::Description--->
                        </div>
                        <!--end::Section-->
                    </div>
                    <!--end::Wrapper-->

                    <!--begin::Statistics-->
                    <div class="d-flex align-items-center mw-125px w-100">
                        <!--begin::Progress-->
                        <div class="progress h-6px bg-light-danger me-2 w-100">
                            <div
                                class="progress-bar bg-danger"
                                role="progressbar"
                                style="width: 56%"
                                aria-valuenow="56"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            ></div>
                        </div>
                        <!--end::Progress-->

                        <!--begin::Value-->
                        <span class="fw-semibold text-gray-500"> 56% </span>
                        <!--end::Value-->
                    </div>
                    <!--end::Statistics-->
                </div>
                <!--end::Item-->

                <!--begin::Separator-->
                <div class="separator separator-dashed my-3"></div>
                <!--end::Separator-->

                <!--begin::Item-->
                <div class="d-flex flex-stack">
                    <!--begin::Wrapper-->
                    <div class="d-flex align-items-center me-3">
                        <!--begin::Logo-->
                        <img src="/metronic8/demo23/assets/media/svg/brand-logos/typescript-1.svg" class="w-30px me-4" alt="" />
                        <!--end::Logo-->

                        <!--begin::Section-->
                        <div class="flex-grow-1">
                            <!--begin::Text-->
                            <a href="#" class="text-hover-primary fs-5 fw-bold lh-0 text-gray-800">TypeScript</a>
                            <!--end::Text-->

                            <!--begin::Description-->
                            <span class="fw-semibold d-block fs-6 text-gray-500">Better Tooling</span>
                            <!--end::Description--->
                        </div>
                        <!--end::Section-->
                    </div>
                    <!--end::Wrapper-->

                    <!--begin::Statistics-->
                    <div class="d-flex align-items-center mw-125px w-100">
                        <!--begin::Progress-->
                        <div class="progress h-6px bg-light-success me-2 w-100">
                            <div
                                class="progress-bar bg-success"
                                role="progressbar"
                                style="width: 82%"
                                aria-valuenow="82"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            ></div>
                        </div>
                        <!--end::Progress-->

                        <!--begin::Value-->
                        <span class="fw-semibold text-gray-500"> 82% </span>
                        <!--end::Value-->
                    </div>
                    <!--end::Statistics-->
                </div>
                <!--end::Item-->
            </div>
            <!--end::Body-->
        </div>
        <!--end::List widget 21-->
    </div>
</template>

<style scoped></style>
