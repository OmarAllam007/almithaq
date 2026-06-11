<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';

declare const Swal: any;
import { route } from 'ziggy-js';
import { computed } from 'vue';
import GoogleAd from '@/components/GoogleAd.vue';

import { useLang } from '@/composables/useLang';
import { useUnreadConversations } from '@/composables/useUnreadConversations';
const { trans } = useLang();
const { unreadConversations } = useUnreadConversations();

const pendingImageRequests = computed(() => (page.props as any).pending_image_requests ?? 0);

const page = usePage();
const currentLocale = computed(() => String(page.props.locale ?? 'en'));
const isRtl = computed(() => currentLocale.value === 'ar');
const drawerDirection = computed(() => isRtl.value ? 'end' : 'start');
const menuPlacement = computed(() => isRtl.value ? 'left-end' : 'left-start');
const subMenuPlacement = computed(() => isRtl.value ? 'right-start' : 'left-start');

const currentPath = computed(() => {
    const url = page.url;
    try {
        return new URL(url).pathname;
    } catch {
        return url.split('?')[0];
    }
});

const isRouteActive = (routeName: string): boolean => {
    try {
        const targetPath = new URL(route(routeName)).pathname;
        return currentPath.value === targetPath || currentPath.value.startsWith(targetPath + '/');
    } catch {
        return false;
    }
};

const confirmLogout = async () => {
    const result = await Swal.fire({
        text: trans('sidebar.Logout Confirm'),
        icon: 'question',
        showDenyButton: true,
        buttonsStyling: false,
        confirmButtonText: trans('sidebar.Logout'),
        denyButtonText: trans('sidebar.Cancel'),
        customClass: {
            confirmButton: 'btn btn-danger',
            denyButton: 'btn btn-secondary',
        },
    });

    if (result.isConfirmed) {
        router.post(route('logout'));
    }
};

const currentLanguageBadge = computed(() => {
    if (currentLocale.value === 'ar') {
        return {
            label: trans('sidebar.Arabic'),
            flagSrc: '/assets/media/flags/saudi-arabia.svg',
        };
    }

    return {
        label: trans('sidebar.English'),
        flagSrc: '/assets/media/flags/united-states.svg',
    };
});
</script>

<template>
    <!--begin::Sidebar-->
    <div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
        data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="275px"
        :data-kt-drawer-direction="drawerDirection" data-kt-drawer-toggle="#kt_app_sidebar_toggle">
        <!--begin::Logo-->
        <div class="d-flex flex-stack px-lg-6 py-lg-8 px-4 py-3" id="kt_app_sidebar_logo">
            <!--begin::Logo image-->
            <Link href="/" class="brand">
                <span class="brand__mark" aria-hidden="true">
                    <svg viewBox="0 0 40 28" fill="none">
                        <circle cx="14" cy="14" r="9" stroke="currentColor" stroke-width="2.2" />
                        <circle cx="26" cy="14" r="9" stroke="currentColor" stroke-width="2.2" opacity="0.55" />
                    </svg>
                </span>
                <span class="brand__text">
                    <span class="brand__name">{{ isRtl ? 'خطوبة' : 'Khotobah' }}</span>
                    <span class="brand__sub">{{ isRtl ? 'Khotobah' : 'خطوبة' }}</span>
                </span>
            </Link>
            <!--end::Logo image-->
            <!--begin::User menu-->
            <div class="ms-3">
                <!--begin::Menu wrapper-->
                <div class="position-relative symbol symbol-circle symbol-40px cursor-pointer"
                    :class="{ 'sidebar-subscriber-avatar': $page.props.auth?.user?.has_active_subscription }"
                    data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent"
                    :data-kt-menu-placement="isRtl ? 'bottom-start' : 'bottom-end'">
                    <img :src="$page.props.auth?.profile_image || '/assets/media/auth/no-image-for-user.png'" alt="user" style="width: 40px; height: 40px; object-fit: cover;" />
                    <div v-if="$page.props.auth?.user?.has_active_subscription"
                        :class="[
                            'position-absolute rounded-circle h-8px w-8px mt-n3 top-100',
                            isRtl ? 'end-100 me-n3' : 'start-100 ms-n3',
                        ]"
                        style="background: linear-gradient(135deg, #f6d365, #fda085);"></div>
                    <div v-else
                        :class="[
                            'position-absolute rounded-circle bg-success h-8px w-8px mt-n3 top-100',
                            isRtl ? 'end-100 me-n3' : 'start-100 ms-n3',
                        ]">
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
                                <img alt="Logo" :src="$page.props.auth?.profile_image || '/assets/media/auth/no-image-for-user.png'" style="width: 50px; height: 50px; object-fit: cover;" />
                            </div>
                            <!--end::Avatar-->
                            <!--begin::Username-->
                            <div class="d-flex flex-column">
                                <div class="fw-bold d-flex align-items-center fs-5">
                                    {{ $page.props.auth?.user?.name
                                    }}
                                    <!-- <span class="fw-bold fs-8 ms-2 px-2 py-1 badge"
                                        :class="$page.props.auth?.user.has_active_subscription ? 'subscriber-type-badge' : 'badge-light-success'">
                                        <i v-if="$page.props.auth?.user.has_active_subscription"
                                            class="ki-outline ki-crown fs-9 me-1" style="color: inherit;"></i>
                                        {{ $page.props.auth?.user.subcription_type
                                        }}</span> -->
                                </div>
                                <a href="#" class="fw-semibold text-muted text-hover-primary fs-7">{{
                                    $page.props.auth?.user?.email }}</a>
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
                        <Link :href="route('profile.gallery')" class="menu-link px-5">{{ trans('sidebar.My Gallery') }}</Link>
                    </div>
                    <!--end::Menu item-->

                    <!--begin::Menu item-->
<!--                    <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}"-->
<!--                        :data-kt-menu-placement="subMenuPlacement" data-kt-menu-offset="-15px, 0">-->
<!--                        <a href="#" class="menu-link px-5">-->
<!--                            <span class="menu-title">{{ trans('sidebar.My Subscription') }}</span>-->
<!--                            <span class="menu-arrow" :class="isRtl ? 'rotate-180' : 'rotate-0'"></span>-->
<!--                        </a>-->
<!--                        &lt;!&ndash;begin::Menu sub&ndash;&gt;-->
<!--                        <div class="menu-sub menu-sub-dropdown w-175px py-4">-->
<!--                            &lt;!&ndash;begin::Menu item&ndash;&gt;-->
<!--                            <div class="menu-item px-3">-->
<!--                                <Link :href="route('subscription.index')" class="menu-link px-5">{{ trans('sidebar.My Subscription') }}</Link>-->
<!--                            </div>-->
<!--                            &lt;!&ndash;end::Menu item&ndash;&gt;-->
<!--                            &lt;!&ndash;begin::Menu item&ndash;&gt;-->
<!--                            <div class="menu-item px-3">-->
<!--                                <a href="account/statements.html" class="menu-link px-5">{{ trans('sidebar.Payments') }}</a>-->
<!--                            </div>-->
<!--                            &lt;!&ndash;end::Menu item&ndash;&gt;-->
<!--                        </div>-->
<!--                        &lt;!&ndash;end::Menu sub&ndash;&gt;-->
<!--                    </div>-->
                    <!--end::Menu item-->

                    <!--begin::Menu item-->
                    <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                        :data-kt-menu-placement="subMenuPlacement" data-kt-menu-offset="-15px, 0">
                        <a href="#" class="menu-link px-5">
                            <span class="menu-title">{{ trans('sidebar.Mode') }}</span>
                            <span class="d-flex align-items-center flex-shrink-0">
                                <i class="ki-outline ki-night-day theme-light-show fs-2"></i>
                                <i class="ki-outline ki-moon theme-dark-show fs-2"></i>
                            </span>
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
                                    <span class="menu-title">{{ trans('sidebar.Light') }}</span>
                                </a>
                            </div>
                            <!--end::Menu item-->
                            <!--begin::Menu item-->
                            <div class="menu-item my-0 px-3">
                                <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
                                    <span class="menu-icon" data-kt-element="icon" >
                                        <i class="ki-outline ki-moon fs-2"></i>
                                    </span>
                                    <span class="menu-title">{{ trans('sidebar.Dark') }}</span>
                                </a>
                            </div>
                            <!--end::Menu item-->
                            <!--begin::Menu item-->
                            <div class="menu-item my-0 px-3">
                                <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
                                    <span class="menu-icon" data-kt-element="icon">
                                        <i class="ki-outline ki-screen fs-2"></i>
                                    </span>
                                    <span class="menu-title">{{ trans('sidebar.System') }}</span>
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
                            <span class="menu-title">{{ trans('sidebar.Language') }}</span>
                            <span
                                class="fs-8 bg-light rounded px-3 py-2 d-inline-flex align-items-center gap-2 flex-shrink-0">
                                {{ currentLanguageBadge.label }}
                                <img class="w-15px h-15px rounded-1" :src="currentLanguageBadge.flagSrc"
                                    :alt="currentLanguageBadge.label" />
                            </span>
                        </a>
                        <!--begin::Menu sub-->
                        <div class="menu-sub menu-sub-dropdown w-175px py-4">
                            <!--begin::Menu item-->
                            <div class="menu-item px-3">
                                <Link :href="`/language/ar`" class="menu-link d-flex px-5"
                                    :class="{ active: currentLocale === 'ar' }">
                                    <span class="symbol symbol-20px me-4">
                                        <img class="rounded-1" src="/assets/media/flags/saudi-arabia.svg" alt="" />
                                    </span>{{ trans('sidebar.Arabic') }}
                                </Link>
                            </div>
                            <!--end::Menu item-->

                            <!--begin::Menu item-->
                            <div class="menu-item px-3">
                                <Link :href="`/language/en`" class="menu-link d-flex px-5"
                                    :class="{ active: currentLocale === 'en' }">
                                    <span class="symbol symbol-20px me-4">
                                        <img class="rounded-1" src="/assets/media/flags/united-states.svg" alt="" />
                                    </span>{{ trans('sidebar.English') }}
                                </Link>
                            </div>
                            <!--end::Menu item-->

                        </div>
                        <!--end::Menu sub-->
                    </div>
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
<!--                    <div class="menu-item my-1 px-5">-->
<!--                        <a href="account/settings.html" class="menu-link px-5">{{ trans('sidebar.Account Settings') }}</a>-->
<!--                    </div>-->
                    <!--end::Menu item-->
                    <!--begin::Menu item-->
                    <div class="menu-item px-5">
                        <a @click.prevent="confirmLogout" href="#" class="menu-link text-danger w-100 px-5">{{
                            trans('sidebar.Logout') }}</a>
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
                            <Link :href="route('inbox.index')"
                                class="btn btn-icon btn-outline btn-bg-light btn-active-light-primary btn-flex flex-column flex-center w-lg-90px h-lg-90px w-70px h-70px border-gray-200 position-relative"
                                :class="{ active: isRouteActive('inbox.index') }"
                                data-kt-button="true">
                                <!--begin::Icon-->
                                <span class="mb-2 position-relative">
                                    <i class="ki-outline ki-messages fs-1"></i>
                                    
                                    <span
                                        v-if="unreadConversations > 0"
                                        class="position-absolute  top-50 start-2 translate-middle badge badge-circle badge-danger"
                                        style="font-size: 9px; min-width: 16px; height: 16px; padding: 0 3px;"
                                    >
                                        {{ unreadConversations > 99 ? '99+' : unreadConversations }}
                                    </span>
                                </span>
                                <!--end::Icon-->
                                <!--begin::Label-->
                                <span class="fs-7 fw-bold">{{ trans('sidebar.inbox') }}</span>
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
                                :class="{ active: isRouteActive('who-liked-me') }"
                                data-kt-button="true">
                                <!--begin::Icon-->
                                <span class="mb-2">
                                    <i class="ki-outline ki-heart fs-1"></i>
                                </span>
                                <!--end::Icon-->
                                <!--begin::Label-->
                                <span class="fs-7 fw-bold">{{ trans('sidebar.Who Liked Me') }}</span>
                                <!--end::Label-->
                            </Link>
                            <!--end::Link-->
                        </div>
                        <!--end::Col-->
                        <!--begin::Col-->
                        <div class="col mb-4">
                            <!--begin::Link-->
                            <Link :href="route('who-visited-me')"
                                class="btn btn-icon btn-outline btn-bg-light btn-active-light-primary btn-flex flex-column flex-center w-lg-90px h-lg-90px w-70px h-70px border-gray-200"
                                :class="{ active: isRouteActive('who-visited-me') }"
                                data-kt-button="true">
                                <!--begin::Icon-->
                                <i class="ki-outline ki-eye fs-1"></i>
                                <span class="mb-2"> </span>
                                <!--end::Icon-->
                                <!--begin::Label-->
                                <span class="fs-7 fw-bold">{{ trans('sidebar.Who Visit Me?') }}</span>
                                <!--end::Label-->
                            </Link>
                            <!--end::Link-->
                        </div>
                        <!--end::Col-->
                        <!--begin::Col-->
                        <div class="col mb-4">
                            <!--begin::Link-->
                            <Link :href="route('my-interactions')"
                                class="btn btn-icon btn-outline btn-bg-light btn-active-light-primary btn-flex flex-column flex-center w-lg-90px h-lg-90px w-70px h-70px border-gray-200"
                                :class="{ active: isRouteActive('my-interactions') }"
                                data-kt-button="true">
                                <!--begin::Icon-->
                                <span class="mb-2">
                                    <i class="ki-outline ki-like-2 fs-1"></i>
                                </span>
                                <!--end::Icon-->
                                <!--begin::Label-->
                                <span class="fs-7 fw-bold">{{ trans('sidebar.Like and Ignore List') }}</span>
                                <!--end::Label-->
                            </Link>
                            <!--end::Link-->
                        </div>
                        <!--end::Col-->

                        <!--begin::Col-->
                        <div class="col mb-4">
                            <!--begin::Link-->
                            <Link :href="route('smart-search.index')"
                                class="btn btn-icon btn-outline btn-bg-light btn-active-light-primary btn-flex flex-column flex-center w-lg-90px h-lg-90px w-70px h-70px border-gray-200"
                                :class="{ active: isRouteActive('smart-search.index') }"
                                data-kt-button="true">
                                <!--begin::Icon-->
                                <span class="mb-2">
                                    <i class="ki-outline ki-filter-search fs-1"></i>
                                </span>
                                <!--end::Icon-->
                                <!--begin::Label-->
                                <span class="fs-7 fw-bold">{{ trans('sidebar.Smart Search') }}</span>
                                <!--end::Label-->
                            </Link>
                            <!--end::Link-->
                        </div>
                        <!--end::Col-->
                        <!--begin::Col-->
                        <div class="col mb-4">
                            <!--begin::Link-->
                            <Link :href="route('image-requests.index')"
                                class="btn btn-icon btn-outline btn-bg-light btn-active-light-primary btn-flex flex-column flex-center w-lg-90px h-lg-90px w-70px h-70px border-gray-200 position-relative"
                                :class="{ active: isRouteActive('image-requests.index') }"
                                data-kt-button="true">
                                <!--begin::Icon-->
                                <span class="mb-2 position-relative">
                                    <i class="ki-outline ki-picture fs-1"></i>
                                    <span
                                        v-if="pendingImageRequests > 0"
                                        class="position-absolute top-50 start-2 translate-middle badge badge-circle badge-danger"
                                        style="font-size: 9px; min-width: 16px; height: 16px; padding: 0 3px;"
                                    >
                                        {{ pendingImageRequests > 99 ? '99+' : pendingImageRequests }}
                                    </span>
                                </span>
                                <!--end::Icon-->
                                <!--begin::Label-->
                                <span class="fs-7 fw-bold">{{ trans('sidebar.Image Requests') }}</span>
                                <!--end::Label-->
                            </Link>
                            <!--end::Link-->
                        </div>
                        <!--end::Col-->
                    </div>
                    <!--end::Row-->
                </div>
                <!--end::Links-->

                <!-- Ad: bottom of sidebar nav -->
                <div class="mt-4">
                    <GoogleAd slot-id="SIDEBAR_BOTTOM" format="rectangle" :full-width-responsive="false" />
                </div>
            </div>
            <!--end::Nav wrapper-->
        </div>
        <!--end::Sidebar nav-->

    </div>
    <!--end::Sidebar-->
</template>

<style scoped>
.brand {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    text-decoration: none;
    color: #45112a;
}
.brand__mark {
    color: #d02e79;
    display: inline-flex;
    flex-shrink: 0;
}
.brand__mark svg {
    width: 36px;
    height: 25px;
}
.brand__text {
    display: flex;
    flex-direction: column;
    line-height: 1;
}
.brand__name {
    font-size: 1.15rem;
    font-weight: 700;
    letter-spacing: 0.01em;
    color: #45112a;
}
.brand__sub {
    font-size: 0.65rem;
    color: #d02e79;
    font-weight: 600;
    margin-top: 2px;
}

.sidebar-subscriber-avatar {
    position: relative;
}

.sidebar-subscriber-avatar::before {
    content: '';
    position: absolute;
    inset: -3px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f6d365, #fda085, #f6d365);
    z-index: 0;
    animation: rotate-glow 4s linear infinite;
}

.sidebar-subscriber-avatar img {
    position: relative;
    z-index: 1;
    border: 2px solid #fff;
}

@keyframes rotate-glow {
    0% {
        background: linear-gradient(0deg, #f6d365, #fda085, #f6d365);
    }

    25% {
        background: linear-gradient(90deg, #f6d365, #fda085, #f6d365);
    }

    50% {
        background: linear-gradient(180deg, #f6d365, #fda085, #f6d365);
    }

    75% {
        background: linear-gradient(270deg, #f6d365, #fda085, #f6d365);
    }

    100% {
        background: linear-gradient(360deg, #f6d365, #fda085, #f6d365);
    }
}

.subscriber-type-badge {
    background: linear-gradient(135deg, #f6d365, #fda085) !important;
    color: #fff !important;
    border: none;
}
</style>
