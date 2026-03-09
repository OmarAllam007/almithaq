<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');
const drawerDirection = computed(() => isRtl.value ? 'start' : 'end');

// Notification state
interface Notification {
    id: number;
    type: string;
    actor: { id: number; username: string; profile_image: string | null } | null;
    data: Record<string, any> | null;
    read_at: string | null;
    created_at: string;
}

const unreadCount = ref(0);
const notifications = ref<Notification[]>([]);
const showNotifications = ref(false);
const loadingNotifications = ref(false);
let pollInterval: ReturnType<typeof setInterval> | null = null;

const notificationMeta: Record<string, { icon: string; label: string; color: string }> = {
    like: { icon: 'ki-outline ki-heart fs-3', label: 'liked you', color: 'text-danger' },
    ignore: { icon: 'ki-outline ki-eye-slash fs-3', label: 'ignored you', color: 'text-warning' },
    profile_visit: { icon: 'ki-outline ki-eye fs-3', label: 'visited your profile', color: 'text-info' },
    new_message: { icon: 'ki-outline ki-sms fs-3', label: 'sent you a message', color: 'text-primary' },
    subscription_renewed: { icon: 'ki-outline ki-verify fs-3', label: 'Subscription renewed!', color: 'text-success' },
};

function getMeta(type: string) {
    return notificationMeta[type] || { icon: 'ki-outline ki-notification-on fs-3', label: type, color: 'text-muted' };
}

async function fetchUnreadCount() {
    try {
        const { data } = await axios.get(route('notifications.unread-count'));
        unreadCount.value = data.count;
    } catch (e) {
        // silently ignore
    }
}

async function fetchNotifications() {
    loadingNotifications.value = true;
    try {
        const { data } = await axios.get(route('notifications.index'));
        notifications.value = data.data || [];
    } catch (e) {
        // silently ignore
    } finally {
        loadingNotifications.value = false;
    }
}

function toggleDropdown() {
    showNotifications.value = !showNotifications.value;
    if (showNotifications.value) {
        fetchNotifications();
    }
}

function closeDropdown() {
    showNotifications.value = false;
}

async function markAsRead(notification: any) {
    if (!notification.read_at) {
        try {
            await axios.post(route('notifications.read', { notification: notification.id }));
            notification.read_at = new Date().toISOString();
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        } catch (e) {
            // silently ignore
        }
    }

    // Navigate to actor profile if there's an actor
    if (notification.actor?.id) {
        if (notification.type === 'new_message' && notification.data?.conversation_id) {
            router.visit(route('conversations.show', { conversation: notification.data.conversation_id }));
        } else {
            router.visit(route('users.show', { user: notification.actor.id }));
        }
        closeDropdown();
    }
}

async function markAllAsRead() {
    try {
        await axios.post(route('notifications.read-all'));
        unreadCount.value = 0;
        notifications.value = notifications.value.map((n: any) => ({
            ...n,
            read_at: n.read_at || new Date().toISOString(),
        }));
    } catch (e) {
        // silently ignore
    }
}

onMounted(() => {
    fetchUnreadCount();
    pollInterval = setInterval(fetchUnreadCount, 30000); // every 30s
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
    <div id="kt_app_header" class="app-header" data-kt-sticky="true" data-kt-sticky-activate-="true"
        data-kt-sticky-name="app-header-sticky" data-kt-sticky-offset="{default: '200px', lg: '300px'}">
        <!--begin::Header container-->
        <div class="app-container container-xxl d-flex align-items-stretch justify-content-between"
            id="kt_app_header_container">
            <!--begin::Header wrapper-->
            <div class="app-header-wrapper d-flex align-items-stretch justify-content-between flex-grow-1"
                id="kt_app_header_wrapper">
                <!--begin::Menu wrapper-->
                <div class="app-header-menu app-header-mobile-drawer align-items-start align-items-lg-center w-100"
                    data-kt-drawer="true" data-kt-drawer-name="app-header-menu"
                    data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
                    data-kt-drawer-width="250px" :data-kt-drawer-direction="drawerDirection"
                    data-kt-drawer-toggle="#kt_app_header_menu_toggle" data-kt-swapper="true"
                    data-kt-swapper-mode="{default: 'append', lg: 'prepend'}"
                    data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_wrapper'}">
                    <!--begin::Menu-->
                    <div class="menu menu-rounded menu-column menu-lg-row menu-active-bg menu-state-primary menu-title-gray-700 menu-arrow-gray-500 menu-bullet-gray-500 my-lg-0 align-items-stretch fw-semibold px-lg-0 my-5 px-2"
                        id="#kt_header_menu" data-kt-menu="true">
                        <!--begin:Menu item-->
                        <Link :href="route('signup')" data-kt-menu-placement="bottom-start"
                            class="menu-item here show menu-here-bg menu-lg-down-accordion me-lg-2 me-0"
                            v-if="!$page.props.auth?.user">
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                <span class="menu-title">Sign up</span>
                                <span class="menu-arrow d-lg-none"></span>
                            </span>
                            <!--end:Menu link-->
                        </Link>
                        <!--end:Menu item-->

                        <Link :href="route('login')" data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                            data-kt-menu-placement="bottom-start" class="menu-item menu-lg-down-accordion me-lg-2 me-0"
                            v-if="!$page.props.auth?.user">
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                <span class="menu-title">Login</span>
                                <span class="menu-arrow d-lg-none"></span>
                            </span>
                            <!--end:Menu link-->
                        </Link>

                        <!--begin:Menu item-->
                        <Link :href="route('signup')" data-kt-menu-placement="bottom-start"
                            class="menu-item menu-here-bg menu-lg-down-accordion me-lg-2 me-0"
                            v-if="$page.props.auth?.user">
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                <span class="menu-title"> <i class="fa fa-home fs-4 me-1 text-hover-primary"></i>
                                    Home</span>
                            </span>
                            <!--end:Menu link-->
                        </Link>
                        <!--end:Menu item-->

                        <!--begin:Menu item-->
                        <Link :href="route('members-online')" data-kt-menu-placement="bottom-start"
                            class="menu-item  menu-here-bg menu-lg-down-accordion me-lg-2 me-0"
                            v-if="$page.props.auth?.user">
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                <span class="menu-title"> <i class="fa fa-users fs-4 me-1 text-hover-primary"></i>
                                    Online Members</span>
                            </span>
                            <!--end:Menu link-->
                        </Link>
                        <!--end:Menu item-->

                        <!--begin:Menu item-->
                        <Link :href="route('new-members')" data-kt-menu-placement="bottom-start"
                            class="menu-item  menu-here-bg menu-lg-down-accordion me-lg-2 me-0"
                            v-if="$page.props.auth?.user">
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                <span class="menu-title"> <i class="fa fa-users-rays fs-4 me-1 text-hover-primary"></i>
                                    New Members</span>
                            </span>
                            <!--end:Menu link-->
                        </Link>
                        <!--end:Menu item-->

                        <!--begin:Menu item-->
                        <Link :href="route('signup')" data-kt-menu-placement="bottom-start"
                            class="menu-item menu-here-bg menu-lg-down-accordion me-lg-2 me-0"
                            v-if="$page.props.auth?.user">
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                <span class="menu-title"> <i class="fa fa-search fs-4 me-1 text-hover-primary"></i>
                                    Search</span>
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
                    <div class="btn btn-icon btn-color-gray-600 btn-active-color-primary ms-n3 d-flex d-lg-none me-2"
                        id="kt_app_sidebar_toggle">
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

                    <!--begin::Notifications-->
                    <div class="app-navbar-item ms-lg-3 ms-1 position-relative" v-if="$page.props.auth?.user">
                        <div class="btn btn-icon btn-circle btn-color-gray-500 btn-active-color-primary btn-custom bg-body shadow-xs position-relative"
                            @click.stop="toggleDropdown">
                            <i class="ki-outline ki-notification-on fs-1"></i>
                            <span v-if="unreadCount > 0"
                                class="position-absolute top-0 start-100 translate-middle badge badge-circle badge-sm bg-danger"
                                style="font-size: 10px; min-width: 16px; min-height: 16px;">
                                {{ unreadCount > 99 ? '99+' : unreadCount }}
                            </span>
                        </div>

                        <!--begin::Notification dropdown-->
                        <div v-if="showNotifications" class="position-absolute bg-body shadow rounded-3 border"
                            :style="{ top: '50px', [isRtl ? 'left' : 'right']: '0', width: '340px', maxHeight: '420px', zIndex: 1050 }">
                            <!--header-->
                            <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                                <h6 class="mb-0 fw-bold text-gray-800">Notifications</h6>
                                <button v-if="unreadCount > 0" class="btn btn-sm btn-light-primary py-1 px-3"
                                    @click.stop="markAllAsRead">
                                    Mark all read
                                </button>
                            </div>

                            <!--body-->
                            <div class="overflow-auto" style="max-height: 350px;">
                                <div v-if="loadingNotifications" class="text-center py-5">
                                    <span class="spinner-border spinner-border-sm text-primary"></span>
                                </div>

                                <div v-else-if="notifications.length === 0" class="text-center py-5 text-muted fs-7">
                                    No notifications yet
                                </div>

                                <template v-else>
                                    <div v-for="n in notifications" :key="n.id"
                                        class="d-flex align-items-start gap-3 px-4 py-3 cursor-pointer notification-item"
                                        :class="{ 'bg-light-primary': !n.read_at }" @click="markAsRead(n)">
                                        <!--icon-->
                                        <div class="flex-shrink-0 mt-1">
                                            <i :class="[getMeta(n.type).icon, getMeta(n.type).color]"></i>
                                        </div>
                                        <!--content-->
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fs-7 text-gray-800">
                                                <span class="fw-bold" v-if="n.actor">{{ n.actor.username }}</span>
                                                <span> {{ getMeta(n.type).label }}</span>
                                            </div>
                                            <div class="text-muted fs-8 mt-1">{{ n.created_at }}</div>
                                        </div>
                                        <!--unread dot-->
                                        <div v-if="!n.read_at" class="flex-shrink-0 mt-2">
                                            <span class="bullet bullet-dot bg-primary h-6px w-6px"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <!--end::Notification dropdown-->
                    </div>
                    <!--end::Notifications-->


                    <!--begin::Header menu mobile toggle-->
                    <div class="app-navbar-item d-lg-none me-n4 ms-2" title="Show header menu">
                        <div class="btn btn-icon btn-color-gray-600 btn-active-color-primary"
                            id="kt_app_header_menu_toggle">
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

    <!--click-away overlay to close notifications-->
    <div v-if="showNotifications" class="position-fixed top-0 start-0 w-100 h-100" style="z-index: 1040;"
        @click="closeDropdown"></div>
</template>

<style scoped>
.notification-item:hover {
    background-color: var(--bs-gray-100) !important;
}

.cursor-pointer {
    cursor: pointer;
}
</style>
