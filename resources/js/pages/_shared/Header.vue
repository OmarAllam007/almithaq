<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed } from 'vue';

const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');
const drawerDirection = computed(() => isRtl.value ? 'start' : 'end');
</script>

<template>
    <div
        id="kt_app_header"
        class="app-header"
        data-kt-sticky="true"
        data-kt-sticky-activate-="true"
        data-kt-sticky-name="app-header-sticky"
        data-kt-sticky-offset="{default: '200px', lg: '300px'}"
    >
        <!--begin::Header container-->
        <div class="app-container container-xxl d-flex align-items-stretch justify-content-between" id="kt_app_header_container">
            <!--begin::Header wrapper-->
            <div class="app-header-wrapper d-flex align-items-stretch justify-content-between flex-grow-1" id="kt_app_header_wrapper">
                <!--begin::Menu wrapper-->
                <div
                    class="app-header-menu app-header-mobile-drawer align-items-start align-items-lg-center w-100"
                    data-kt-drawer="true"
                    data-kt-drawer-name="app-header-menu"
                    data-kt-drawer-activate="{default: true, lg: false}"
                    data-kt-drawer-overlay="true"
                    data-kt-drawer-width="250px"
                    :data-kt-drawer-direction="drawerDirection"
                    data-kt-drawer-toggle="#kt_app_header_menu_toggle"
                    data-kt-swapper="true"
                    data-kt-swapper-mode="{default: 'append', lg: 'prepend'}"
                    data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_wrapper'}"
                >
                    <!--begin::Menu-->
                    <div
                        class="menu menu-rounded menu-column menu-lg-row menu-active-bg menu-state-primary menu-title-gray-700 menu-arrow-gray-500 menu-bullet-gray-500 my-lg-0 align-items-stretch fw-semibold px-lg-0 my-5 px-2"
                        id="#kt_header_menu"
                        data-kt-menu="true"
                    >
                        <!--begin:Menu item-->
                        <Link
                            :href="route('signup')"
                            data-kt-menu-placement="bottom-start"
                            class="menu-item here show menu-here-bg menu-lg-down-accordion me-lg-2 me-0"
                            v-if="!$page.props.auth?.user"
                        >
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                <span class="menu-title">Sign up</span>
                                <span class="menu-arrow d-lg-none"></span>
                            </span>
                            <!--end:Menu link-->
                        </Link>
                        <!--end:Menu item-->

                        <Link
                            :href="route('login')"
                            data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                            data-kt-menu-placement="bottom-start"
                            class="menu-item menu-lg-down-accordion me-lg-2 me-0"
                            v-if="!$page.props.auth?.user"
                        >
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                <span class="menu-title">Login</span>
                                <span class="menu-arrow d-lg-none"></span>
                            </span>
                            <!--end:Menu link-->
                        </Link>

                        <!--begin:Menu item-->
                        <Link
                            :href="route('signup')"
                            data-kt-menu-placement="bottom-start"
                            class="menu-item menu-here-bg menu-lg-down-accordion me-lg-2 me-0"
                            v-if="$page.props.auth?.user"
                        >
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                <span class="menu-title"> <i class="fa fa-home fs-4 me-1 text-hover-primary"></i> Home</span>
                            </span>
                            <!--end:Menu link-->
                        </Link>
                        <!--end:Menu item-->

                        <!--begin:Menu item-->
                        <Link
                            :href="route('members-online')"
                            data-kt-menu-placement="bottom-start"
                            class="menu-item  menu-here-bg menu-lg-down-accordion me-lg-2 me-0"
                            v-if="$page.props.auth?.user"
                        >
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                <span class="menu-title"> <i class="fa fa-users fs-4 me-1 text-hover-primary"></i> Online Members</span>
                            </span>
                            <!--end:Menu link-->
                        </Link>
                        <!--end:Menu item-->

                        <!--begin:Menu item-->
                        <Link
                            :href="route('new-members')"
                            data-kt-menu-placement="bottom-start"
                            class="menu-item  menu-here-bg menu-lg-down-accordion me-lg-2 me-0"
                            v-if="$page.props.auth?.user"
                        >
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                <span class="menu-title"> <i class="fa fa-users-rays fs-4 me-1 text-hover-primary"></i> New Members</span>
                            </span>
                            <!--end:Menu link-->
                        </Link>
                        <!--end:Menu item-->

                        <!--begin:Menu item-->
                        <Link
                            :href="route('signup')"
                            data-kt-menu-placement="bottom-start"
                            class="menu-item menu-here-bg menu-lg-down-accordion me-lg-2 me-0"
                            v-if="$page.props.auth?.user"
                        >
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                <span class="menu-title"> <i class="fa fa-search fs-4 me-1 text-hover-primary"></i> Search</span>
                            </span>
                            <!--end:Menu link-->
                        </Link>
                        <!--end:Menu item-->
                    </div>
                    <!--end::Menu-->
                </div>
                <!--end::Menu wrapper-->
                <!--begin::Logo wrapper-->
                <div class="d-flex align-items-center">
                    <!--begin::Logo wrapper-->
                    <div class="btn btn-icon btn-color-gray-600 btn-active-color-primary ms-n3 d-flex d-lg-none me-2" id="kt_app_sidebar_toggle">
                        <i class="ki-outline ki-abstract-14 fs-2"></i>
                    </div>
                    <!--end::Logo wrapper-->
                    <!--begin::Logo image-->
                    <a href="index.html" class="d-flex d-lg-none">
                        <img alt="Logo" src="assets/media/logos/demo23.svg" class="h-20px theme-light-show" />
                        <img alt="Logo" src="assets/media/logos/demo23-dark.svg" class="h-20px theme-dark-show" />
                    </a>
                    <!--end::Logo image-->
                </div>
                <!--end::Logo wrapper-->
                <!--begin::Navbar-->
                <div class="app-navbar flex-shrink-0">

                    <!--begin::Chat-->
                    <div class="app-navbar-item ms-lg-3 ms-1">
                        <!--begin::Menu wrapper-->
                        <div
                            class="btn btn-icon btn-circle btn-color-gray-500 btn-active-color-primary btn-custom bg-body shadow-xs"
                            id="kt_drawer_chat_toggle"
                        >
                            <i class="ki-outline ki-message-notif fs-1"></i>
                        </div>
                        <!--end::Menu wrapper-->
                    </div>
                    <!--end::Chat-->
                    <!--begin::Header menu mobile toggle-->
                    <div class="app-navbar-item d-lg-none me-n4 ms-2" title="Show header menu">
                        <div class="btn btn-icon btn-color-gray-600 btn-active-color-primary" id="kt_app_header_menu_toggle">
                            <i class="ki-outline ki-text-align-left fs-2 fw-bold"></i>
                        </div>
                    </div>
                    <!--end::Header menu mobile toggle-->
                </div>
                <!--end::Navbar-->
            </div>
            <!--end::Header wrapper-->
        </div>
        <!--end::Header container-->
    </div>
</template>

<style scoped></style>
