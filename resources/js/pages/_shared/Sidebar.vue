<script setup lang="ts">
import { vueLang } from '@erag/lang-sync-inertia';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
const { __ } = vueLang();
</script>

<template>
    <!--begin::Sidebar-->
    <div
        id="kt_app_sidebar"
        class="app-sidebar flex-column"
        data-kt-drawer="true"
        data-kt-drawer-name="app-sidebar"
        data-kt-drawer-activate="{default: true, lg: false}"
        data-kt-drawer-overlay="true"
        data-kt-drawer-width="275px"
        data-kt-drawer-direction="start"
        data-kt-drawer-toggle="#kt_app_sidebar_toggle"
    >
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
                <div
                    class="position-relative symbol symbol-circle symbol-40px cursor-pointer"
                    data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                    data-kt-menu-attach="parent"
                    data-kt-menu-placement="bottom-end"
                >
                    <img :src="$page.props.auth?.profile_image" alt="user" />
                    <div class="position-absolute rounded-circle bg-success h-8px w-8px ms-n3 mt-n3 start-100 top-100"></div>
                </div>
                <!--begin::User account menu-->
                <div
                    class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold fs-6 w-275px py-4"
                    data-kt-menu="true"
                >
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
                                <a href="#" class="fw-semibold text-muted text-hover-primary fs-7">{{ $page.props.auth?.user.email }}</a>
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
                        <Link :href="route('profile')" class="menu-link px-5">My Profile</Link>
                    </div>
                    <!--end::Menu item-->

                    <!--begin::Menu item-->
                    <div class="menu-item px-5">
                        <Link :href="route('profile.gallery')" class="menu-link px-5">My Gallery</Link>
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="menu-item px-5">
                        <a href="apps/projects/list.html" class="menu-link px-5">
                            <span class="menu-text">My Projects</span>
                            <span class="menu-badge">
                                <span class="badge badge-light-danger badge-circle fw-bold fs-7">3</span>
                            </span>
                        </a>
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div
                        class="menu-item px-5"
                        data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                        data-kt-menu-placement="left-start"
                        data-kt-menu-offset="-15px, 0"
                    >
                        <a href="#" class="menu-link px-5">
                            <span class="menu-title">My Subscription</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <!--begin::Menu sub-->
                        <div class="menu-sub menu-sub-dropdown w-175px py-4">
                            <!--begin::Menu item-->
                            <div class="menu-item px-3">
                                <a href="account/referrals.html" class="menu-link px-5">Referrals</a>
                            </div>
                            <!--end::Menu item-->
                            <!--begin::Menu item-->
                            <div class="menu-item px-3">
                                <a href="account/billing.html" class="menu-link px-5">Billing</a>
                            </div>
                            <!--end::Menu item-->
                            <!--begin::Menu item-->
                            <div class="menu-item px-3">
                                <a href="account/statements.html" class="menu-link px-5">Payments</a>
                            </div>
                            <!--end::Menu item-->
                            <!--begin::Menu item-->
                            <div class="menu-item px-3">
                                <a href="account/statements.html" class="menu-link d-flex flex-stack px-5"
                                    >Statements
                                    <span class="lh-0 ms-2" data-bs-toggle="tooltip" title="View your statements">
                                        <i class="ki-outline ki-information-5 fs-5"></i> </span
                                ></a>
                            </div>
                            <!--end::Menu item-->
                            <!--begin::Menu separator-->
                            <div class="separator my-2"></div>
                            <!--end::Menu separator-->
                            <!--begin::Menu item-->
                            <div class="menu-item px-3">
                                <div class="menu-content px-3">
                                    <label class="form-check form-switch form-check-custom form-check-solid">
                                        <input
                                            class="form-check-input w-30px h-20px"
                                            type="checkbox"
                                            value="1"
                                            checked="checked"
                                            name="notifications"
                                        />
                                        <span class="form-check-label text-muted fs-7">Notifications</span>
                                    </label>
                                </div>
                            </div>
                            <!--end::Menu item-->
                        </div>
                        <!--end::Menu sub-->
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="menu-item px-5">
                        <a href="account/statements.html" class="menu-link px-5">My Statements</a>
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu separator-->
                    <div class="separator my-2"></div>
                    <!--end::Menu separator-->
                    <!--begin::Menu item-->
                    <div
                        class="menu-item px-5"
                        data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                        data-kt-menu-placement="left-start"
                        data-kt-menu-offset="-15px, 0"
                    >
                        <a href="#" class="menu-link px-5">
                            <span class="menu-title position-relative"
                                >Mode
                                <span class="position-absolute translate-middle-y end-0 top-50 ms-5">
                                    <i class="ki-outline ki-night-day theme-light-show fs-2"></i>
                                    <i class="ki-outline ki-moon theme-dark-show fs-2"></i> </span
                            ></span>
                        </a>
                        <!--begin::Menu-->
                        <div
                            class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold fs-base w-150px py-4"
                            data-kt-menu="true"
                            data-kt-element="theme-mode-menu"
                        >
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
                    <div
                        class="menu-item px-5"
                        data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                        data-kt-menu-placement="left-start"
                        data-kt-menu-offset="-15px, 0"
                    >
                        <a href="#" class="menu-link px-5">
                            <span class="menu-title position-relative"
                                >Language
                                <span class="fs-8 bg-light position-absolute translate-middle-y end-0 top-50 rounded px-3 py-2"
                                    >English <img class="w-15px h-15px rounded-1 ms-2" src="assets/media/flags/united-states.svg" alt="" /></span
                            ></span>
                        </a>
                        <!--begin::Menu sub-->
                        <div class="menu-sub menu-sub-dropdown w-175px py-4">
                            <!--begin::Menu item-->
                            <div class="menu-item px-3">
                                <a href="account/settings.html" class="menu-link d-flex active px-5">
                                    <span class="symbol symbol-20px me-4">
                                        <img class="rounded-1" src="assets/media/flags/saudi-arabia.svg" alt="" /> </span
                                    >Arabic</a
                                >
                            </div>
                            <!--end::Menu item-->

                            <!--begin::Menu item-->
                            <div class="menu-item px-3">
                                <a href="account/settings.html" class="menu-link d-flex px-5">
                                    <span class="symbol symbol-20px me-4">
                                        <img class="rounded-1" src="assets/media/flags/united-states.svg" alt="" /> </span
                                    >English</a
                                >
                            </div>
                            <!--end::Menu item-->
                            <!--begin::Menu item-->
                            <div class="menu-item px-3">
                                <a href="account/settings.html" class="menu-link d-flex px-5">
                                    <span class="symbol symbol-20px me-4"> <img class="rounded-1" src="assets/media/flags/france.svg" alt="" /> </span
                                    >French</a
                                >
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
                        <Link :href="route('logout')" method="post" class="menu-link text-danger w-100 px-5">{{ __('Logout') }}</Link>
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
            <div
                id="kt_app_sidebar_nav_wrapper"
                class="d-flex flex-column hover-scroll-y me-n4 pe-4"
                data-kt-scroll="true"
                data-kt-scroll-activate="true"
                data-kt-scroll-height="auto"
                data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
                data-kt-scroll-wrappers="#kt_app_sidebar, #kt_app_sidebar_nav"
                data-kt-scroll-offset="5px"
            >
                <!--begin::Progress-->
                <div class="d-flex align-items-center flex-column mb-6 w-100">
                    <div class="d-flex justify-content-between fw-bolder fs-6 mt-auto mb-3 w-100 text-gray-800">
                        <span>Your Goal</span>
                    </div>
                    <div class="bg-light-primary mb-2 w-100 rounded" style="height: 24px">
                        <div
                            class="bg-primary rounded"
                            role="progressbar"
                            style="height: 24px; width: 37%"
                            aria-valuenow="50"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        ></div>
                    </div>
                    <div class="fw-semibold fs-7 text-primary mt-auto w-100">
                        <span>reached 37% of your target</span>
                    </div>
                </div>
                <!--end::Progress-->

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
                            <Link
                                :href="route('inbox.index')"
                                class="btn btn-icon btn-outline btn-bg-light btn-active-light-primary btn-flex flex-column flex-center w-lg-90px h-lg-90px w-70px h-70px border-gray-200"
                                data-kt-button="true"
                            >
                                <!--begin::Icon-->
                                <span class="mb-2">
                                    <i class="ki-outline ki-messages fs-1"></i>
                                </span>
                                <!--end::Icon-->
                                <!--begin::Label-->
                                <span class="fs-7 fw-bold">Inbox</span>
                                <!--end::Label-->
                            </Link>
                            <!--end::Link-->
                        </div>
                        <!--end::Col-->
                        <!--begin::Col-->
                        <div class="col mb-4">
                            <!--begin::Link-->
                            <Link
                                :href="route('who-liked-me')"
                                class="btn btn-icon btn-outline btn-bg-light btn-active-light-primary btn-flex flex-column flex-center w-lg-90px h-lg-90px w-70px h-70px border-gray-200"
                                data-kt-button="true"
                            >
                                <!--begin::Icon-->
                                <span class="mb-2">
                                    <i class="ki-outline ki-heart fs-1"></i>
                                </span>
                                <!--end::Icon-->
                                <!--begin::Label-->
                                <span class="fs-7 fw-bold">Who Liked Me</span>
                                <!--end::Label-->
                            </Link>
                            <!--end::Link-->
                        </div>
                        <!--end::Col-->
                        <!--begin::Col-->
                        <div class="col mb-4">
                            <!--begin::Link-->
                            <Link
                                :href="route('who-visited-me')"
                                class="btn btn-icon btn-outline btn-bg-light btn-active-light-primary btn-flex flex-column flex-center w-lg-90px h-lg-90px w-70px h-70px border-gray-200"
                                data-kt-button="true"
                            >
                                <!--begin::Icon-->
                                <i class="ki-outline ki-eye fs-1"></i>
                                <span class="mb-2"> </span>
                                <!--end::Icon-->
                                <!--begin::Label-->
                                <span class="fs-7 fw-bold">Who Visit Me?</span>
                                <!--end::Label-->
                            </Link>
                            <!--end::Link-->
                        </div>
                        <!--end::Col-->
                        <!--begin::Col-->
                        <div class="col mb-4">
                            <!--begin::Link-->
                            <Link
                                :href="route('my-interactions')"
                                class="btn btn-icon btn-outline btn-bg-light btn-active-light-primary btn-flex flex-column flex-center w-lg-90px h-lg-90px w-70px h-70px border-gray-200"
                                data-kt-button="true"
                            >
                                <!--begin::Icon-->
                                <span class="mb-2">
                                    <i class="ki-outline ki-like-2 fs-1"></i>
                                </span>
                                <!--end::Icon-->
                                <!--begin::Label-->
                                <span class="fs-7 fw-bold">Like and Ignore List</span>
                                <!--end::Label-->
                            </Link>
                            <!--end::Link-->
                        </div>
                        <!--end::Col-->

                        <!--begin::Col-->
                        <div class="col mb-4">
                            <!--begin::Link-->
                            <Link
                                :href="route('smart-search.index')"
                                class="btn btn-icon btn-outline btn-bg-light btn-active-light-primary btn-flex flex-column flex-center w-lg-90px h-lg-90px w-70px h-70px border-gray-200"
                                data-kt-button="true"
                            >
                                <!--begin::Icon-->
                                <span class="mb-2">
                                    <i class="ki-outline ki-filter-search fs-1"></i>
                                </span>
                                <!--end::Icon-->
                                <!--begin::Label-->
                                <span class="fs-7 fw-bold">Smart Search</span>
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
        <!--begin::Footer-->
        <div class="flex-column-auto d-flex flex-center px-lg-8 py-lg-8 px-4 py-3" id="kt_app_sidebar_footer">
            <!--begin::Settings-->
            <div class="app-footer-item">
                <!--begin::Menu- wrapper-->
                <a
                    href="account/settings.html"
                    class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-35px h-35px w-md-40px h-md-40px"
                >
                    <i class="ki-outline ki-setting-2 fs-2"></i>
                </a>
                <!--end::Menu wrapper-->
            </div>
            <!--begin::Settings-->
        </div>
        <!--end::Footer-->
    </div>
    <!--end::Sidebar-->
</template>

<style scoped></style>
