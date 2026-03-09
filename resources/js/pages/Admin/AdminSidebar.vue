<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed } from 'vue';

import { useLang } from '@/composables/useLang';
const { trans } = useLang();

const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');
const drawerDirection = computed(() => isRtl.value ? 'end' : 'start');
const menuPlacement = computed(() => isRtl.value ? 'left-end' : 'left-start');
const subMenuPlacement = computed(() => isRtl.value ? 'right-start' : 'left-start');
</script>

<template>
    <!--begin::Sidebar-->
    <div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
        data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="275px"
        :data-kt-drawer-direction="drawerDirection" data-kt-drawer-toggle="#kt_app_sidebar_toggle">
        <!--begin::Logo-->
        <div class="d-flex flex-stack px-lg-6 py-lg-8 px-4 py-3" id="kt_app_sidebar_logo">
            <!--begin::Logo image-->
            <Link href="/">
                <img alt="Logo" src="assets/media/logos/demo23.svg" class="h-20px h-lg-25px theme-light-show" />
                <img alt="Logo" src="assets/media/logos/demo23-dark.svg" class="h-20px h-lg-25px theme-dark-show" />
            </Link>
            <!--end::Logo image-->
            <!--begin::User menu-->
            <div class="ms-3">
                <!--begin::Menu wrapper-->
                <div class="position-relative symbol symbol-circle symbol-40px cursor-pointer"
                    data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent"
                    :data-kt-menu-placement="isRtl ? 'bottom-start' : 'bottom-end'">
                    <img :src="$page.props.auth?.profile_image" alt="user" />
                    <div class="position-absolute rounded-circle bg-success h-8px w-8px ms-n3 mt-n3 start-100 top-100">
                    </div>
                </div>
                <!--begin::User account menu-->
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold fs-6 w-275px py-4"
                    data-kt-menu="true">
                    <!--begin::Menu item-->
                    <div class="menu-item px-3">
                        <div class="menu-content d-flex align-items-center px-3">
                            <!--begin::Avatar-->
                            <div class="symbol symbol-50px me-5">
                                <img alt="Logo" :src="$page.props.auth?.profile_image" />
                            </div>
                            <!--end::Avatar-->
                            <!--begin::Username-->
                            <div class="d-flex flex-column">
                                <div class="fw-bold d-flex align-items-center fs-5">
                                    {{ $page.props.auth?.user.name
                                    }}<span class="badge badge-light-success fw-bold fs-8 ms-2 px-2 py-1">{{
                                        $page.props.auth?.user.subcription_type
                                        }}</span>
                                </div>
                                <a href="#" class="fw-semibold text-muted text-hover-primary fs-7">{{
                                    $page.props.auth?.user.email }}</a>
                            </div>
                            <!--end::Username-->
                        </div>
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu separator-->
                    <div class="separator my-2"></div>
                    <!--end::Menu separator-->
                    <!--begin::Menu item-->
                    <div class="menu-item px-5">
                        <Link :href="route('profile')" class="menu-link px-5"> {{ trans('sidebar.My Profile') }}</Link>
                    </div>
                    <!--end::Menu item-->

                    <!--begin::Menu item-->
                    <div class="menu-item px-5">
                        <Link :href="route('profile.gallery')" class="menu-link px-5">My Gallery</Link>
                    </div>
                    <!--end::Menu item-->

                    <!--begin::Menu item-->
                    <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                        :data-kt-menu-placement="subMenuPlacement" data-kt-menu-offset="-15px, 0">
                        <a href="#" class="menu-link px-5">
                            <span class="menu-title">My Subscription</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <!--begin::Menu sub-->
                        <div class="menu-sub menu-sub-dropdown w-175px py-4">
                            <!--begin::Menu item-->
                            <div class="menu-item px-3">
                                <Link :href="route('subscription.index')" class="menu-link px-5">My Subscription</Link>
                            </div>
                            <!--end::Menu item-->
                            <!--begin::Menu item-->
                            <div class="menu-item px-3">
                                <a href="account/statements.html" class="menu-link px-5">Payments</a>
                            </div>
                            <!--end::Menu item-->
                        </div>
                        <!--end::Menu sub-->
                    </div>
                    <!--end::Menu item-->

                    <!--begin::Menu item-->
                    <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                        :data-kt-menu-placement="subMenuPlacement" data-kt-menu-offset="-15px, 0">
                        <a href="#" class="menu-link px-5">
                            <span class="menu-title position-relative">Mode
                                <span class="position-absolute translate-middle-y end-0 top-50 ms-5">
                                    <i class="ki-outline ki-night-day theme-light-show fs-2"></i>
                                    <i class="ki-outline ki-moon theme-dark-show fs-2"></i> </span></span>
                        </a>
                        <!--begin::Menu-->
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold fs-base w-150px py-4"
                            data-kt-menu="true" data-kt-element="theme-mode-menu">
                            <!--begin::Menu item-->
                            <div class="menu-item my-0 px-3">
                                <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
                                    <span class="menu-icon" data-kt-element="icon">
                                        <i class="ki-outline ki-night-day fs-2"></i>
                                    </span>
                                    <span class="menu-title">Light</span>
                                </a>
                            </div>
                            <!--end::Menu item-->
                            <!--begin::Menu item-->
                            <div class="menu-item my-0 px-3">
                                <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
                                    <span class="menu-icon" data-kt-element="icon">
                                        <i class="ki-outline ki-moon fs-2"></i>
                                    </span>
                                    <span class="menu-title">Dark</span>
                                </a>
                            </div>
                            <!--end::Menu item-->
                            <!--begin::Menu item-->
                            <div class="menu-item my-0 px-3">
                                <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
                                    <span class="menu-icon" data-kt-element="icon">
                                        <i class="ki-outline ki-screen fs-2"></i>
                                    </span>
                                    <span class="menu-title">System</span>
                                </a>
                            </div>
                            <!--end::Menu item-->
                        </div>
                        <!--end::Menu-->
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                        :data-kt-menu-placement="subMenuPlacement" data-kt-menu-offset="-15px, 0">
                        <a href="#" class="menu-link px-5">
                            <span class="menu-title position-relative">Language
                                <span
                                    class="fs-8 bg-light position-absolute translate-middle-y end-0 top-50 rounded px-3 py-2">English
                                    <img class="w-15px h-15px rounded-1 ms-2" src="assets/media/flags/united-states.svg"
                                        alt="" /></span></span>
                        </a>
                        <!--begin::Menu sub-->
                        <div class="menu-sub menu-sub-dropdown w-175px py-4">
                            <!--begin::Menu item-->
                            <div class="menu-item px-3">
                                <Link :href="`/language/ar`" class="menu-link d-flex active px-5">
                                    <span class="symbol symbol-20px me-4">
                                        <img class="rounded-1" src="assets/media/flags/saudi-arabia.svg" alt="" />
                                    </span>{{ trans('sidebar.Arabic') }}
                                </Link>
                            </div>
                            <!--end::Menu item-->

                            <!--begin::Menu item-->
                            <div class="menu-item px-3">
                                <Link :href="`/language/en`" class="menu-link d-flex px-5">
                                    <span class="symbol symbol-20px me-4">
                                        <img class="rounded-1" src="assets/media/flags/united-states.svg" alt="" />
                                    </span>{{ trans('sidebar.English') }}
                                </Link>
                            </div>
                            <!--end::Menu item-->
                            <!--begin::Menu item-->
                            <div class="menu-item px-3">
                                <a href="account/settings.html" class="menu-link d-flex px-5">
                                    <span class="symbol symbol-20px me-4"> <img class="rounded-1"
                                            src="assets/media/flags/france.svg" alt="" /> </span>French</a>
                            </div>
                            <!--end::Menu item-->
                        </div>
                        <!--end::Menu sub-->
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="menu-item my-1 px-5">
                        <a href="account/settings.html" class="menu-link px-5">Account Settings</a>
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="menu-item px-5">
                        <Link :href="route('logout')" method="post" class="menu-link text-danger w-100 px-5">{{
                            trans('sidebar.Logout') }}</Link>
                    </div>
                    <!--end::Menu item-->
                </div>
                <!--end::User account menu-->
                <!--end::Menu wrapper-->
            </div>
            <!--end::User menu-->
        </div>
        <!--end::Logo-->
        <!--begin::Sidebar nav-->
        <div class="flex-column-fluid px-lg-8 px-4 py-4" id="kt_app_sidebar_nav">
            <!--begin::Nav wrapper-->
            <div id="kt_app_sidebar_nav_wrapper" class="d-flex flex-column hover-scroll-y me-n4 pe-4"
                data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto"
                data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
                data-kt-scroll-wrappers="#kt_app_sidebar, #kt_app_sidebar_nav" data-kt-scroll-offset="5px">


                <!--begin::Links-->
                <div class="mb-6">
                    <!--begin::Title-->
                    <h3 class="fw-bold mb-8 text-gray-800"></h3>
                    <!--end::Title-->
                    <!--begin::Row-->
                    <div class="row row-cols-3" data-kt-buttons="true" data-kt-buttons-target="[data-kt-button]">
                        <!--begin::Col-->
                        <div class="col mb-4">
                            <!--begin::Link-->
                            <Link :href="route('admin.users.index')" class="btn btn-icon btn-outline btn-bg-light btn-active-light-primary btn-flex flex-column
                                flex-center w-lg-90px h-lg-90px w-70px h-70px border-gray-200" data-kt-button="true">
                                <!--begin::Icon-->
                                <span class="mb-2">
                                    <i class="ki-outline ki-profile-user fs-1"></i>
                                </span>
                                <!--end::Icon-->
                                <!--begin::Label-->
                                <span class="fs-7 fw-bold">{{ trans('admin_sidebar.users') }}</span>
                                <!--end::Label-->
                            </Link>
                            <!--end::Link-->
                        </div>
                        <!--end::Col-->


                        <!--begin::Col-->
                        <div class="col mb-4">
                            <!--begin::Link-->
                            <Link :href="route('who-liked-me')"
                                class="btn btn-icon btn-outline btn-bg-light btn-active-light-primary btn-flex flex-column flex-center w-lg-90px h-lg-90px w-70px h-70px border-gray-200"
                                data-kt-button="true">
                                <!--begin::Icon-->
                                <span class="mb-2">
                                    <i class="ki-outline ki-abstract-35 fs-1"></i>
                                </span>
                                <!--end::Icon-->
                                <!--begin::Label-->
                                <span class="fs-7 fw-bold">{{ trans('admin_sidebar.approvals') }}</span>
                                <!--end::Label-->
                            </Link>
                            <!--end::Link-->
                        </div>
                        <!--end::Col-->

                        <!--begin::Col-->
                        <div class="col mb-4">
                            <!--begin::Link-->
                            <Link :href="route('admin.verifications.index')"
                                class="btn btn-icon btn-outline btn-bg-light btn-active-light-primary btn-flex flex-column flex-center w-lg-90px h-lg-90px w-70px h-70px border-gray-200"
                                data-kt-button="true">
                                <!--begin::Icon-->
                                <span class="mb-2">
                                    <i class="ki-outline ki-shield-tick fs-1"></i>
                                </span>
                                <!--end::Icon-->
                                <!--begin::Label-->
                                <span class="fs-7 fw-bold">Verifications</span>
                                <!--end::Label-->
                            </Link>
                            <!--end::Link-->
                        </div>
                        <!--end::Col-->

                        <!--begin::Col-->
                        <div class="col mb-4">
                            <!--begin::Link-->
                            <Link :href="route('who-liked-me')"
                                class="btn btn-icon btn-outline btn-bg-light btn-active-light-primary btn-flex flex-column flex-center w-lg-90px h-lg-90px w-70px h-70px border-gray-200"
                                data-kt-button="true">
                                <!--begin::Icon-->
                                <span class="mb-2">
                                    <i class="ki-outline ki-dollar fs-1"></i>
                                </span>
                                <!--end::Icon-->
                                <!--begin::Label-->
                                <span class="fs-7 fw-bold">{{ trans('admin_sidebar.payments') }}</span>
                                <!--end::Label-->
                            </Link>
                            <!--end::Link-->
                        </div>
                        <!--end::Col-->


                        <!--begin::Col-->
                        <div class="col mb-4">
                            <!--begin::Link-->
                            <Link :href="route('who-liked-me')"
                                class="btn btn-icon btn-outline btn-bg-light btn-active-light-primary btn-flex flex-column flex-center w-lg-90px h-lg-90px w-70px h-70px border-gray-200"
                                data-kt-button="true">
                                <!--begin::Icon-->
                                <span class="mb-2">
                                    <i class="ki-outline ki-flag fs-1"></i>
                                </span>
                                <!--end::Icon-->
                                <!--begin::Label-->
                                <span class="fs-7 fw-bold">{{ trans('admin_sidebar.Countries') }}</span>
                                <!--end::Label-->
                            </Link>
                            <!--end::Link-->
                        </div>
                        <!--end::Col-->


                        <!--begin::Col-->
                        <div class="col mb-4">
                            <!--begin::Link-->
                            <Link :href="route('who-liked-me')"
                                class="btn btn-icon btn-outline btn-bg-light btn-active-light-primary btn-flex flex-column flex-center w-lg-90px h-lg-90px w-70px h-70px border-gray-200"
                                data-kt-button="true">
                                <!--begin::Icon-->
                                <span class="mb-2">
                                    <i class="ki-outline ki-geolocation fs-1"></i>
                                </span>
                                <!--end::Icon-->
                                <!--begin::Label-->
                                <span class="fs-7 fw-bold">{{ trans('admin_sidebar.cities') }}</span>
                                <!--end::Label-->
                            </Link>
                            <!--end::Link-->
                        </div>
                        <!--end::Col-->

                    </div>
                    <!--end::Row-->
                </div>
                <!--end::Links-->
            </div>
            <!--end::Nav wrapper-->
        </div>
        <!--end::Sidebar nav-->

    </div>
    <!--end::Sidebar-->
</template>

<style scoped></style>
