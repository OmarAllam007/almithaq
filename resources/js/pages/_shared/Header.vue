<script setup lang="ts">
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';
import UserProfileModal from '@/components/UserProfileModal.vue';
import { useLang } from '@/composables/useLang';
import { useNotificationCount } from '@/composables/useNotificationCount';
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { route } from 'ziggy-js';
const { trans } = useLang();
const { unreadCount } = useNotificationCount();

const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');
const drawerDirection = computed(() => (isRtl.value ? 'start' : 'end'));

function isActive(routeName: string): boolean {
    try {
        return route().current(routeName) ?? false;
    } catch {
        return false;
    }
}

// Notification state
interface Notification {
    id: number;
    type: string;
    label: string;
    actor: { id: number; username: string; profile_image: string | null } | null;
    data: Record<string, any> | null;
    read_at: string | null;
    created_at: string;
}

const notifications = ref<Notification[]>([]);
const showNotifications = ref(false);
const loadingNotifications = ref(false);
let pollInterval: ReturnType<typeof setInterval> | null = null;

// Profile modal state
const profileModalOpen = ref(false);
const profileModalUserId = ref<number | undefined>(undefined);

// Chat drawer state
const chatDrawerOpen = ref(false);
const chatConversationId = ref<number | undefined>(undefined);
const chatRecipientId = ref<number | undefined>(undefined);

const notificationMeta: Record<string, { icon: string; color: string }> = {
    like: { icon: 'ki-outline ki-heart fs-3', color: 'text-danger' },
    ignore: { icon: 'ki-outline ki-eye-slash fs-3', color: 'text-warning' },
    profile_visit: { icon: 'ki-outline ki-eye fs-3', color: 'text-info' },
    new_message: { icon: 'ki-outline ki-sms fs-3', color: 'text-primary' },
    subscription_renewed: { icon: 'ki-outline ki-verify fs-3', color: 'text-success' },
};

function getMeta(type: string) {
    return notificationMeta[type] || { icon: 'ki-outline ki-notification-on fs-3', color: 'text-muted' };
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

    closeDropdown();

    if (notification.type === 'new_message') {
        chatConversationId.value = notification.data?.conversation_id;
        chatRecipientId.value = notification.actor?.id;
        chatDrawerOpen.value = true;
    } else if (notification.actor?.id) {
        profileModalUserId.value = notification.actor.id;
        profileModalOpen.value = true;
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
    <header
        id="kt_app_header"
        class="app-header refined-header"
        data-kt-sticky="true"
        data-kt-sticky-activate="true"
        data-kt-sticky-name="app-header-sticky"
        data-kt-sticky-offset="{default: '200px', lg: '300px'}"
    >
        <div id="kt_app_header_container" class="app-container container-xxl header-container">
            <div id="kt_app_header_wrapper" class="app-header-wrapper header-inner">
                <div class="header-mobile-brand d-lg-none">
                    <button
                        v-if="$page.props.auth?.user"
                        id="kt_app_sidebar_toggle"
                        type="button"
                        class="header-icon mobile-menu-button"
                        :aria-label="isRtl ? 'فتح القائمة' : 'Open menu'"
                    >
                        <i class="ki-outline ki-abstract-14"></i>
                    </button>
                    <Link :href="$page.props.auth?.user ? route('home') : '/'" class="mobile-brand" aria-label="Khotobah home">
                        <span class="mobile-brand-mark" aria-hidden="true">
                            <svg viewBox="0 0 44 36" fill="none">
                                <circle cx="16" cy="18" r="11" stroke="currentColor" stroke-width="2.6" />
                                <circle cx="28" cy="18" r="11" stroke="currentColor" stroke-width="2.6" opacity=".58" />
                            </svg>
                        </span>
                        <span class="mobile-brand-type">
                            <strong>{{ isRtl ? 'خطوبة' : 'Khotobah' }}</strong>
                            <small>{{ isRtl ? 'Khotobah' : 'خطوبة' }}</small>
                        </span>
                    </Link>
                </div>
                <div v-if="$page.props.auth?.user" class="header-left d-none d-lg-flex">
                    <span class="header-kicker">{{ isRtl ? 'اكتشف قصتك' : 'DISCOVER YOUR STORY' }}</span>
                    <div class="header-rule"></div>
                    <span class="header-context">{{ isRtl ? 'مكان لبدايات جميلة' : 'A place for meaningful beginnings' }}</span>
                </div>
                <Link v-else href="/" class="guest-brand d-none d-lg-flex"><span aria-hidden="true">◎</span>{{ isRtl ? 'خطوبة' : 'Khotobah' }}</Link>
                <nav class="header-nav d-none d-lg-flex" :aria-label="isRtl ? 'التنقل السريع' : 'Quick navigation'">
                    <template v-if="$page.props.auth?.user">
                        <Link :href="route('home')" :class="{ active: isActive('home') }">{{ trans('home.home') }}</Link>
                        <Link :href="route('new-members')" :class="{ active: isActive('new-members') }">{{ trans('home.new-members') }}</Link>
                        <Link :href="route('smart-search.index')" :class="{ active: isActive('smart-search.index') }">{{
                            trans('home.search')
                        }}</Link>
                    </template>
                    <template v-else
                        ><Link :href="route('signup')">{{ isRtl ? 'إنشاء حساب' : 'Join now' }}</Link
                        ><Link :href="route('login')">{{ trans('home.sign-in-title') }}</Link></template
                    >
                </nav>
                <Link
                    :href="isRtl ? '/language/en' : '/language/ar'"
                    class="header-language"
                    :aria-label="isRtl ? 'تغيير اللغة إلى الإنجليزية' : 'Switch language to Arabic'"
                    :title="isRtl ? 'English' : 'العربية'"
                >
                    <i class="ki-outline ki-global" aria-hidden="true"></i>
                    <span>{{ isRtl ? 'English' : 'العربية' }}</span>
                </Link>
                <div class="header-actions" v-if="$page.props.auth?.user">
                    <Link :href="route('inbox.index')" class="header-icon" :aria-label="trans('sidebar.inbox')" :title="trans('sidebar.inbox')"
                        ><i class="ki-outline ki-messages"></i
                    ></Link>
                    <div class="notification-wrap">
                        <button
                            type="button"
                            class="header-icon"
                            :aria-label="trans('home.notifications')"
                            :aria-expanded="showNotifications"
                            @click.stop="toggleDropdown"
                        >
                            <i class="ki-outline ki-notification-on"></i
                            ><span v-if="unreadCount > 0" class="notification-count">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
                        </button>
                        <div v-if="showNotifications" class="notification-popover">
                            <div class="popover-head">
                                <div>
                                    <small>{{ isRtl ? 'آخر المستجدات' : 'YOUR UPDATES' }}</small
                                    ><strong>{{ trans('home.notifications') }}</strong>
                                </div>
                                <button v-if="unreadCount > 0" type="button" @click.stop="markAllAsRead">{{ trans('home.mark_all_read') }}</button>
                            </div>
                            <div class="popover-body">
                                <div v-if="loadingNotifications" class="popover-state"><span class="spinner-border spinner-border-sm"></span></div>
                                <div v-else-if="notifications.length === 0" class="popover-state">{{ trans('home.no_notifications_yet') }}</div>
                                <button
                                    v-for="n in notifications"
                                    v-else
                                    :key="n.id"
                                    type="button"
                                    class="popover-item"
                                    :class="{ unread: !n.read_at }"
                                    @click="markAsRead(n)"
                                >
                                    <span class="popover-symbol"><i :class="getMeta(n.type).icon"></i></span
                                    ><span class="popover-text"
                                        ><strong v-if="n.actor">{{ n.actor.username }}</strong> {{ n.label }}<small>{{ n.created_at }}</small></span
                                    ><span v-if="!n.read_at" class="unread-dot"></span>
                                </button>
                            </div>
                            <Link :href="route('notifications.index')" class="popover-footer" @click="closeDropdown"
                                >{{ trans('home.view_all') }} <span aria-hidden="true">↗</span></Link
                            >
                        </div>
                    </div>
                    <Link :href="route('profile')" class="header-avatar" :aria-label="trans('sidebar.My Profile')"
                        ><img
                            :src="
                                ($page.props.auth as any)?.profile_image ||
                                (($page.props.auth as any)?.user?.registration_type === 2
                                    ? '/assets/media/auth/female_bgd.png'
                                    : '/assets/media/auth/male_bgd.png')
                            "
                            :alt="($page.props.auth as any)?.user?.name || ''"
                    /></Link>
                </div>
            </div>
        </div>
    </header>
    <div v-if="showNotifications" class="notification-dismiss" @click="closeDropdown"></div>
    <UserProfileModal :is-open="profileModalOpen" :user-id="profileModalUserId" @close="profileModalOpen = false" />
    <ChatDrawer
        :is-open="chatDrawerOpen"
        :conversation-id="chatConversationId"
        :recipient-id="chatRecipientId"
        @close="
            chatDrawerOpen = false;
            chatConversationId = undefined;
            chatRecipientId = undefined;
        "
    />
</template>

<style scoped>
.refined-header {
    position: relative;
    z-index: 1045;
    background: #fbf8f5;
    border-bottom: 1px solid #efe5e7;
    box-shadow: none;
}
.header-container {
    max-width: 1450px;
}
.header-inner {
    width: 100%;
    min-height: 74px;
    display: flex;
    align-items: center;
    gap: 25px;
    justify-content: space-between;
}
.header-left {
    display: flex;
    align-items: center;
    gap: 15px;
    min-width: 0;
    margin-inline-end: auto;
}
.header-kicker {
    font-size: 10px;
    color: #9e4165;
    letter-spacing: 0.13em;
    font-weight: 800;
    white-space: nowrap;
}
.header-rule {
    height: 16px;
    width: 1px;
    background: #e7d8dd;
}
.header-context {
    font-size: 11px;
    color: #a18895;
    font-weight: 700;
    white-space: nowrap;
}
.header-nav {
    display: flex;
    align-items: center;
    gap: 5px;
}
.header-nav a {
    text-decoration: none;
    color: #816a75;
    font-size: 12px;
    font-weight: 800;
    padding: 9px 13px;
    border-radius: 9px;
    transition:
        background 0.2s,
        color 0.2s;
    white-space: nowrap;
}
.header-nav a:hover,
.header-nav a.active {
    background: #f3e2e8;
    color: #933d60;
}
.header-actions {
    display: flex;
    align-items: center;
    gap: 9px;
    border-inline-start: 1px solid #e9dfe1;
    padding-inline-start: 19px;
}
.header-language {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    flex: none;
    min-height: 36px;
    padding: 7px 11px;
    border: 1px solid #ead9df;
    border-radius: 10px;
    background: #fff;
    color: #884562;
    font-size: 11px;
    font-weight: 800;
    text-decoration: none;
    white-space: nowrap;
    transition: background .2s ease, transform .2s ease;
}
.header-language:hover {
    background: #f5e6eb;
    color: #783554;
    transform: translateY(-2px);
}
.header-language i { font-size: 16px; }
.header-icon {
    position: relative;
    flex: 0 0 auto;
    width: 36px;
    height: 36px;
    display: grid;
    place-items: center;
    border: 1px solid #eee0e4;
    border-radius: 10px;
    background: #fff;
    color: #765262;
    text-decoration: none;
    font-size: 19px;
    cursor: pointer;
    transition:
        background 0.2s,
        transform 0.2s;
}
.header-icon:hover {
    background: #f5e6eb;
    color: #963d61;
    transform: translateY(-2px);
}
.notification-count {
    position: absolute;
    top: -6px;
    inset-inline-end: -6px;
    height: 17px;
    min-width: 17px;
    padding: 1px 4px;
    border-radius: 8px;
    background: #a34268;
    color: #fff;
    font-size: 9px;
    line-height: 15px;
    font-weight: 800;
}
.header-avatar {
    display: block;
    margin-inline-start: 4px;
}
.header-avatar img {
    width: 36px;
    height: 36px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #e9cbd6;
}
.notification-wrap {
    position: relative;
}
.notification-popover {
    position: absolute;
    top: 47px;
    inset-inline-end: 0;
    width: min(360px, calc(100vw - 28px));
    max-height: 465px;
    z-index: 1050;
    background: #fff;
    border: 1px solid #efdee4;
    border-radius: 17px;
    box-shadow: 0 25px 65px #45213436;
    overflow: hidden;
}
.popover-head {
    padding: 17px 19px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1e9ea;
    gap: 8px;
}
.popover-head div {
    display: flex;
    flex-direction: column;
}
.popover-head small {
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.13em;
    color: #ab6480;
}
.popover-head strong {
    font-size: 17px;
    color: #3d2734;
}
.popover-head button {
    border: 0;
    background: #f9ebf0;
    color: #9d4265;
    border-radius: 7px;
    padding: 6px 9px;
    font-size: 10px;
    font-weight: 800;
}
.popover-body {
    max-height: 333px;
    overflow: auto;
}
.popover-state {
    padding: 42px 18px;
    text-align: center;
    color: #9a8490;
    font-size: 12px;
}
.popover-item {
    width: 100%;
    border: 0;
    border-bottom: 1px solid #f4edef;
    background: white;
    display: flex;
    align-items: center;
    gap: 11px;
    text-align: start;
    padding: 13px 17px;
    color: #624c59;
}
.popover-item:hover,
.popover-item.unread {
    background: #fdf6f8;
}
.popover-symbol {
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    display: grid;
    place-items: center;
    background: #f7e6ed;
    color: #a34569;
    border-radius: 10px;
    font-size: 17px;
}
.popover-text {
    min-width: 0;
    flex: 1;
    font-size: 11px;
    line-height: 1.5;
}
.popover-text strong {
    color: #3c2734;
}
.popover-text small {
    display: block;
    color: #ad98a2;
    font-size: 10px;
    margin-top: 3px;
}
.unread-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #ad426c;
}
.popover-footer {
    display: flex;
    justify-content: space-between;
    padding: 12px 19px;
    text-decoration: none;
    color: #9c4164;
    font-size: 11px;
    font-weight: 800;
}
.notification-dismiss {
    position: fixed;
    inset: 0;
    z-index: 1040;
}
.guest-brand {
    color: #46283a;
    font-weight: 800;
    font-size: 18px;
    text-decoration: none;
}
.guest-brand span {
    color: #a34569;
    margin-inline-end: 8px;
}
.header-mobile-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}
.mobile-brand {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}
.mobile-brand-mark {
    width: 38px;
    height: 38px;
    flex: none;
    display: grid;
    place-items: center;
    border-radius: 11px;
    background: #f9e6eb;
    color: #a34569;
}
.mobile-brand-mark svg {
    width: 31px;
}
.mobile-brand-type {
    display: flex;
    flex-direction: column;
    line-height: 1.08;
}
.mobile-brand-type strong {
    color: #46283a;
    font-size: 16px;
    font-weight: 800;
    letter-spacing: -0.03em;
}
.mobile-brand-type small {
    margin-top: 3px;
    color: #ad6580;
    font-size: 9px;
    font-weight: 700;
}
@media (max-width: 991px) {
    .header-inner {
        min-height: 62px;
    }
    .header-actions {
        border: 0;
        padding: 0;
    }
    .header-avatar {
        display: none;
    }
    .refined-header {
        padding-inline: 3px;
    }
}
@media (max-width: 430px) {
    .header-inner { gap: 7px; }
    .header-language { padding: 7px 8px; font-size: 10px; }
    .header-actions {
        gap: 5px;
    }
    .header-icon {
        width: 34px;
        height: 34px;
    }
}
</style>
