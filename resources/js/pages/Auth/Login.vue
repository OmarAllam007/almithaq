<script setup lang="ts">
import { vueLang } from '@erag/lang-sync-inertia';
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';
const { trans, __ } = vueLang();

const form = useForm({
    username: '',
    password: '',
});

const canSubmit = computed(() => {
    return form.username && form.password && !form.processing;
});

const submit = () => {
    form.processing = true;
    form.post(route('login'));
};
</script>

<template>
    <!--begin::Authentication - Sign-in -->
    <div class="d-flex flex-column flex-lg-row flex-column-fluid h-full">
        <!--begin::Logo-->
        <a href="index.html" class="d-block d-lg-none mx-auto py-20">
            <img alt="Logo" src="/assets/media/logos/default.svg" class="theme-light-show h-25px" />
            <img alt="Logo" src="/assets/media/logos/default-dark.svg" class="theme-dark-show h-25px" />
        </a>
        <!--end::Logo-->
        <!--begin::Aside-->
        <div class="d-flex flex-column flex-column-fluid flex-center w-lg-50 p-10">
            <!--begin::Wrapper-->
            <div class="d-flex justify-content-between flex-column-fluid flex-column mw-450px w-100">
                <!--begin::Header-->
                <div class="d-flex flex-stack py-2">
                    <!--begin::Back link-->
                    <div class="me-2"></div>
                    <!--end::Back link-->
                    <!--begin::Sign Up link-->
                    <div class="m-0">
                        <span class="fw-bold fs-5 me-2 text-gray-500" data-kt-translate="sign-in-head-desc">{{ __('login.sign-in-head-desc') }}</span>
                        <Link :href="route('signup')" class="link-primary fw-bold fs-5" data-kt-translate="sign-in-head-link">{{
                            __('login.sign-in-head-link')
                        }}</Link>
                    </div>
                    <!--end::Sign Up link=-->
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="py-20">
                    <!--begin::Form-->
                    <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" data-kt-redirect-url="index.html" action="#">
                        <!--begin::Body-->
                        <div class="card-body">
                            <!--begin::Heading-->
                            <div class="mb-10 text-start">
                                <!--begin::Title-->
                                <h1 class="fs-3x mb-3 text-gray-900" data-kt-translate="sign-in-title">{{ __('login.sign-in-title') }}</h1>
                                <!--end::Title-->
                                <!--begin::Text-->
                                <div class="fw-semibold fs-6 text-gray-500" data-kt-translate="general-desc">{{ __('login.general-desc') }}</div>
                                <!--end::Link-->
                            </div>
                            <!--begin::Heading-->
                            <!--begin::Input group=-->
                            <div class="fv-row mb-8">
                                <!--begin::Email-->
                                <input
                                    type="text"
                                    v-model="form.username"
                                    :placeholder="__('login.sign-in-input-username')"
                                    name="username"
                                    autocomplete="off"
                                    data-kt-translate="sign-in-input-email"
                                    class="form-control"
                                />

                                <!--end::Email-->
                            </div>
                            <!--end::Input group=-->
                            <div class="fv-row mb-7">
                                <!--begin::Password-->
                                <input
                                    v-model="form.password"
                                    type="password"
                                    :placeholder="__('login.sign-in-input-password')"
                                    name="password"
                                    autocomplete="off"
                                    data-kt-translate="sign-in-input-password"
                                    class="form-control"
                                />
                                <div
                                    class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"
                                    v-if="form.errors.username"
                                >
                                    <div data-field="text_input" data-validator="notEmpty">{{ form.errors.username }}</div>
                                </div>
                                <!--end::Password-->
                            </div>
                            <!--end::Input group=-->
                            <!--begin::Wrapper-->
                            <div class="d-flex flex-stack fs-base fw-semibold mb-10 flex-wrap gap-3">
                                <div></div>
                                <!--begin::Link-->
                                <a
                                    href="authentication/layouts/fancy/reset-password.html"
                                    class="link-primary"
                                    data-kt-translate="sign-in-forgot-password"
                                    >{{ __('login.sign-in-forgot-password') }}</a
                                >
                                <!--end::Link-->
                            </div>
                            <!--end::Wrapper-->
                            <!--begin::Actions-->
                            <div class="d-flex flex-stack">
                                <!--begin::Submit-->
                                <button
                                    id="kt_sign_in_submit"
                                    class="btn btn-primary me-2 flex-shrink-0"
                                    @click.prevent="submit"
                                    :disabled="!canSubmit"
                                >
                                    <!--begin::Indicator label-->
                                    <span class="indicator-label" data-kt-translate="sign-in-submit">{{ __('login.sign-in-submit') }}</span>
                                    <!--end::Indicator label-->
                                    <!--begin::Indicator progress-->
                                    <span class="indicator-progress" v-if="form.processing">
                                        <span data-kt-translate="general-progress">{{ __('login.general-progress') }}</span>
                                        <span class="spinner-border spinner-border-sm ms-2 align-middle"></span>
                                    </span>
                                    <!--end::Indicator progress-->
                                </button>
                                <!--end::Submit-->
                                <!--begin::Social-->
                                <div class="d-flex align-items-center">
                                    <div class="fw-semibold fs-6 me-md-6 me-3 text-gray-500" data-kt-translate="general-or">
                                        {{ __('login.general-or') }}
                                    </div>
                                    <!--begin::Symbol-->
                                    <a href="#" class="symbol symbol-circle symbol-45px w-45px bg-light me-3">
                                        <img alt="Logo" src="assets/media/svg/brand-logos/google-icon.svg" class="p-4" />
                                    </a>
                                    <!--end::Symbol-->
                                    <!--begin::Symbol-->
                                    <a href="#" class="symbol symbol-circle symbol-45px w-45px bg-light me-3">
                                        <img alt="Logo" src="assets/media/svg/brand-logos/facebook-3.svg" class="p-4" />
                                    </a>
                                    <!--end::Symbol-->
                                    <!--begin::Symbol-->
                                    <a href="#" class="symbol symbol-circle symbol-45px w-45px bg-light">
                                        <img alt="Logo" src="assets/media/svg/brand-logos/apple-black.svg" class="theme-light-show p-4" />
                                        <img alt="Logo" src="assets/media/svg/brand-logos/apple-black-dark.svg" class="theme-dark-show p-4" />
                                    </a>
                                    <!--end::Symbol-->
                                </div>
                                <!--end::Social-->
                            </div>
                            <!--end::Actions-->
                        </div>
                        <!--begin::Body-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Body-->
                <!--begin::Footer-->
                <div class="m-0">
                    <!--begin::Toggle-->
                    <button
                        class="btn btn-flex btn-link rotate"
                        data-kt-menu-trigger="click"
                        data-kt-menu-placement="bottom-start"
                        data-kt-menu-offset="0px, 0px"
                    >
                        <img
                            data-kt-element="current-lang-flag"
                            class="w-25px h-25px rounded-circle me-3"
                            src="assets/media/flags/united-states.svg"
                            alt=""
                        />
                        <span data-kt-element="current-lang-name" class="me-2">English</span>
                        <i class="ki-outline ki-down fs-2 text-muted m-0 rotate-180"></i>
                    </button>
                    <!--end::Toggle-->
                    <!--begin::Menu-->
                    <div
                        class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-4"
                        data-kt-menu="true"
                        id="kt_auth_lang_menu"
                    >
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link d-flex px-5" data-kt-lang="English">
                                <span class="symbol symbol-20px me-4">
                                    <img data-kt-element="lang-flag" class="rounded-1" src="assets/media/flags/united-states.svg" alt="" />
                                </span>
                                <span data-kt-element="lang-name">English</span>
                            </a>
                        </div>
                        <!--end::Menu item-->
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link d-flex px-5" data-kt-lang="Spanish">
                                <span class="symbol symbol-20px me-4">
                                    <img data-kt-element="lang-flag" class="rounded-1" src="assets/media/flags/spain.svg" alt="" />
                                </span>
                                <span data-kt-element="lang-name">Spanish</span>
                            </a>
                        </div>
                        <!--end::Menu item-->
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link d-flex px-5" data-kt-lang="German">
                                <span class="symbol symbol-20px me-4">
                                    <img data-kt-element="lang-flag" class="rounded-1" src="assets/media/flags/germany.svg" alt="" />
                                </span>
                                <span data-kt-element="lang-name">German</span>
                            </a>
                        </div>
                        <!--end::Menu item-->
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link d-flex px-5" data-kt-lang="Japanese">
                                <span class="symbol symbol-20px me-4">
                                    <img data-kt-element="lang-flag" class="rounded-1" src="assets/media/flags/japan.svg" alt="" />
                                </span>
                                <span data-kt-element="lang-name">Japanese</span>
                            </a>
                        </div>
                        <!--end::Menu item-->
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link d-flex px-5" data-kt-lang="French">
                                <span class="symbol symbol-20px me-4">
                                    <img data-kt-element="lang-flag" class="rounded-1" src="assets/media/flags/france.svg" alt="" />
                                </span>
                                <span data-kt-element="lang-name">French</span>
                            </a>
                        </div>
                        <!--end::Menu item-->
                    </div>
                    <!--end::Menu-->
                </div>
                <!--end::Footer-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Aside-->
        <!--begin::Body-->
        <div
            class="d-none d-lg-flex flex-lg-row-fluid bgi-size-cover bgi-position-y-center bgi-position-x-start bgi-no-repeat w-50"
            style="background-image: url(assets/media/auth/bg11.png)"
        ></div>
        <!--begin::Body-->
    </div>
    <!--end::Authentication - Sign-in-->
</template>

<style scoped></style>
