<script setup lang="ts">
import GoogleAd from '@/components/GoogleAd.vue';
import { useLang } from '@/composables/useLang';
import { useUnreadConversations } from '@/composables/useUnreadConversations';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';

declare const Swal: any;
const { trans } = useLang();
const { unreadConversations } = useUnreadConversations();
const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');
const drawerDirection = computed(() => (isRtl.value ? 'end' : 'start'));
const pendingImageRequests = computed(() => Number((page.props as any).pending_image_requests ?? 0));
const user = computed(() => (page.props.auth as any)?.user);
const profileImage = computed(
    () =>
        (page.props.auth as any)?.profile_image ||
        (user.value?.registration_type === 2 ? '/assets/media/auth/female_bgd.png' : '/assets/media/auth/male_bgd.png'),
);
const currentPath = computed(() => page.url.split('?')[0]);
const isRouteActive = (name: string) => {
    try {
        const path = new URL(route(name)).pathname;
        return currentPath.value === path || currentPath.value.startsWith(path + '/');
    } catch {
        return false;
    }
};
const navigation = computed(() => [
    { label: trans('sidebar.Home'), route: 'home', icon: 'ki-home-2' },
    { label: trans('sidebar.New Members'), route: 'new-members', icon: 'ki-user-tick' },
    { label: trans('sidebar.Online'), route: 'members-online', icon: 'ki-wifi' },
    { label: trans('sidebar.Smart Search'), route: 'smart-search.index', icon: 'ki-filter-search' },
]);
const activity = computed(() => [
    { label: trans('sidebar.inbox'), route: 'inbox.index', icon: 'ki-messages', badge: unreadConversations.value },
    { label: trans('sidebar.Who Liked Me'), route: 'who-liked-me', icon: 'ki-heart' },
    { label: trans('sidebar.Who Visit Me?'), route: 'who-visited-me', icon: 'ki-eye' },
    { label: trans('sidebar.Like and Ignore List'), route: 'my-interactions', icon: 'ki-like-2' },
    { label: trans('sidebar.Image Requests'), route: 'image-requests.index', icon: 'ki-picture', badge: pendingImageRequests.value },
]);
const confirmLogout = async () => {
    const result = await Swal.fire({
        text: trans('sidebar.Logout Confirm'),
        icon: 'question',
        showDenyButton: true,
        buttonsStyling: false,
        confirmButtonText: trans('sidebar.Logout'),
        denyButtonText: trans('sidebar.Cancel'),
        customClass: { confirmButton: 'btn btn-danger', denyButton: 'btn btn-secondary' },
    });
    if (result.isConfirmed) router.post(route('logout'));
};
</script>

<template>
    <aside
        id="kt_app_sidebar"
        class="app-sidebar flex-column refined-sidebar"
        data-kt-drawer="true"
        data-kt-drawer-name="app-sidebar"
        data-kt-drawer-activate="{default: true, lg: false}"
        data-kt-drawer-overlay="true"
        data-kt-drawer-width="275px"
        :data-kt-drawer-direction="drawerDirection"
        data-kt-drawer-toggle="#kt_app_sidebar_toggle"
    >
        <div id="kt_app_sidebar_logo" class="sidebar-top">
            <Link :href="route('home')" class="brand" aria-label="Khotobah home">
                <span class="brand-mark" aria-hidden="true"
                    ><svg viewBox="0 0 44 36" fill="none">
                        <circle cx="16" cy="18" r="11" stroke="currentColor" stroke-width="2.6" />
                        <circle cx="28" cy="18" r="11" stroke="currentColor" stroke-width="2.6" opacity=".58" /></svg
                ></span>
                <span class="brand-type"
                    ><strong>{{ isRtl ? 'خطوبة' : 'Khotobah' }}</strong
                    ><small>{{ isRtl ? 'Khotobah' : 'خطوبة' }}</small></span
                >
            </Link>
            <button type="button" class="sidebar-close d-lg-none" data-kt-drawer-dismiss="true" :aria-label="isRtl ? 'إغلاق القائمة' : 'Close menu'">
                <i class="ki-outline ki-cross"></i>
            </button>
        </div>

        <div id="kt_app_sidebar_nav" class="sidebar-scroll flex-column-fluid">
            <div
                id="kt_app_sidebar_nav_wrapper"
                class="sidebar-inner hover-scroll-y"
                data-kt-scroll="true"
                data-kt-scroll-activate="true"
                data-kt-scroll-height="auto"
                data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
                data-kt-scroll-wrappers="#kt_app_sidebar, #kt_app_sidebar_nav"
                data-kt-scroll-offset="5px"
            >
                <div class="sidebar-intro">{{ isRtl ? 'مساحتك الخاصة' : 'YOUR SPACE' }}</div>
                <nav class="sidebar-nav" :aria-label="isRtl ? 'التصفح' : 'Browse'">
                    <Link
                        v-for="item in navigation"
                        :key="item.route"
                        :href="route(item.route)"
                        class="nav-link"
                        :class="{ active: isRouteActive(item.route) }"
                        :aria-current="isRouteActive(item.route) ? 'page' : undefined"
                        ><i :class="['ki-outline', item.icon]"></i><span>{{ item.label }}</span
                        ><i class="ki-outline ki-arrow-right nav-arrow"></i
                    ></Link>
                </nav>
                <div class="sidebar-divider"></div>
                <div class="sidebar-intro">{{ isRtl ? 'نشاطك' : 'YOUR ACTIVITY' }}</div>
                <nav class="sidebar-nav" :aria-label="isRtl ? 'نشاطك' : 'Your activity'">
                    <Link
                        v-for="item in activity"
                        :key="item.route"
                        :href="route(item.route)"
                        class="nav-link"
                        :class="{ active: isRouteActive(item.route) }"
                        :aria-current="isRouteActive(item.route) ? 'page' : undefined"
                        ><i :class="['ki-outline', item.icon]"></i><span>{{ item.label }}</span
                        ><b v-if="item.badge" class="nav-badge">{{ item.badge > 99 ? '99+' : item.badge }}</b></Link
                    >
                </nav>
                <div class="sidebar-divider"></div>
                <div class="sidebar-intro">{{ isRtl ? 'الحساب' : 'ACCOUNT' }}</div>
                <nav class="sidebar-nav" :aria-label="isRtl ? 'الحساب' : 'Account'">
                    <Link :href="route('profile')" class="nav-link" :class="{ active: isRouteActive('profile') && !isRouteActive('profile.gallery') }"
                        ><i class="ki-outline ki-user"></i><span>{{ trans('sidebar.My Profile') }}</span></Link
                    >
                    <Link :href="route('profile.gallery')" class="nav-link" :class="{ active: isRouteActive('profile.gallery') }"
                        ><i class="ki-outline ki-picture"></i><span>{{ trans('sidebar.My Gallery') }}</span></Link
                    >
                </nav>
                <!-- <div class="sidebar-ad"><GoogleAd slot-id="SIDEBAR_BOTTOM" format="rectangle" :full-width-responsive="false" /></div> -->
            </div>
        </div>
        <div id="kt_app_sidebar_footer" class="sidebar-footer">
            <Link :href="route('profile')" class="footer-user"
                ><img :src="profileImage" :alt="user?.name || ''" /><span
                    ><strong>{{ user?.name }}</strong
                    ><small>{{
                        user?.has_active_subscription ? (isRtl ? 'عضوية مميزة' : 'Premium member') : isRtl ? 'عضو في خطوبة' : 'Khotobah member'
                    }}</small></span
                ></Link
            >
            <button type="button" class="footer-logout" :title="trans('sidebar.Logout')" :aria-label="trans('sidebar.Logout')" @click="confirmLogout">
                <i class="ki-outline ki-exit-right-corner"></i>
            </button>
        </div>
    </aside>
</template>

<style scoped>
.refined-sidebar {
    background: #fffaf8;
    border: 1px solid #f1e5e7;
    box-shadow: 8px 10px 35px #5a284016;
    overflow: hidden;
    color: #44283a;
}
.sidebar-top {
    min-height: 96px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 22px 25px 16px;
}
.brand {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    text-decoration: none;
}
.brand-mark {
    width: 43px;
    height: 43px;
    display: grid;
    place-items: center;
    background: #f9e6eb;
    color: #a34569;
    border-radius: 13px;
}
.brand-mark svg {
    width: 34px;
}
.brand-type {
    display: flex;
    flex-direction: column;
    line-height: 1.08;
}
.brand-type strong {
    font-size: 18px;
    font-weight: 800;
    color: #46283a;
    letter-spacing: -0.03em;
}
.brand-type small {
    font-size: 10px;
    color: #ad6580;
    font-weight: 700;
    margin-top: 4px;
}
.sidebar-close {
    border: 0;
    background: transparent;
    color: #6a5260;
    font-size: 22px;
}
.sidebar-scroll {
    min-height: 0;
}
.sidebar-inner {
    height: 100%;
    padding: 13px 14px 24px;
}
.sidebar-intro {
    color: #b395a2;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.15em;
    padding: 11px 13px 9px;
}
.sidebar-nav {
    display: grid;
    gap: 3px;
}
.nav-link,
.sidebar-settings summary {
    display: flex;
    align-items: center;
    gap: 13px;
    min-height: 43px;
    padding: 9px 13px;
    border-radius: 10px;
    color: #705b66;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition:
        color 0.2s,
        background 0.2s,
        transform 0.2s;
}
.nav-link > i:first-child,
.sidebar-settings summary > i:first-child {
    width: 19px;
    text-align: center;
    font-size: 19px;
    color: #aa8797;
}
.nav-link > span {
    flex: 1;
    min-width: 0;
}
.nav-link:hover,
.sidebar-settings summary:hover {
    background: #f8eef1;
    color: #8e3559;
    transform: translateX(2px);
}
.nav-link.active {
    background: #f2dfe6;
    color: #863455;
}
.nav-link.active > i:first-child {
    color: #a74268;
}
.nav-arrow {
    font-size: 11px;
    opacity: 0;
}
.nav-link.active .nav-arrow {
    opacity: 1;
}
.nav-badge {
    background: #a34268;
    color: white;
    font-size: 10px;
    border-radius: 7px;
    min-width: 19px;
    height: 19px;
    padding: 1px 4px;
    text-align: center;
}
.sidebar-divider {
    height: 1px;
    background: #f0e5e7;
    margin: 16px 12px 10px;
}
.sidebar-settings {
    margin-top: 6px;
}
.sidebar-settings summary {
    list-style: none;
}
.sidebar-settings summary::-webkit-details-marker {
    display: none;
}
.sidebar-settings summary span {
    flex: 1;
}
.settings-chevron {
    font-size: 12px;
    transition: transform 0.2s;
}
.sidebar-settings[open] .settings-chevron {
    transform: rotate(180deg);
}
.settings-content {
    margin: 5px 10px 0;
    padding: 13px;
    border-radius: 10px;
    background: #f7f0f1;
    display: grid;
    gap: 9px;
    font-size: 11px;
    font-weight: 700;
    color: #765c69;
}
.settings-options {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 6px;
}
.settings-options a {
    color: #694956;
    text-decoration: none;
    font-size: 10px;
    padding: 5px 8px;
    background: white;
    border: 1px solid #eadde1;
    border-radius: 6px;
}
.settings-options a.selected,
.settings-options a:hover {
    background: #a34268;
    color: white;
}
.sidebar-ad {
    margin: 20px 4px 0;
}
.sidebar-footer {
    display: flex;
    align-items: center;
    gap: 8px;
    border-top: 1px solid #f0e4e7;
    padding: 13px 16px;
    background: #fff;
}
.footer-user {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
    flex: 1;
    text-decoration: none;
}
.footer-user img {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #f3dce4;
}
.footer-user span {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.footer-user strong {
    color: #4b2c3d;
    font-size: 11px;
    font-weight: 800;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.footer-user small {
    color: #a18a95;
    font-size: 10px;
}
.footer-logout {
    border: 0;
    background: #fbf2f3;
    width: 32px;
    height: 32px;
    border-radius: 9px;
    color: #a34268;
    font-size: 17px;
}
.footer-logout:hover {
    background: #f4e1e7;
}
[dir='rtl'] .nav-arrow {
    transform: rotate(180deg);
}
[dir='rtl'] .nav-link:hover {
    transform: translateX(-2px);
}
@media (min-width: 992px) {
    .refined-sidebar {
        border-radius: 20px;
    }
}
@media (max-width: 991px) {
    .refined-sidebar {
        border-radius: 0;
    }
    .sidebar-top {
        min-height: 75px;
    }
}
</style>
