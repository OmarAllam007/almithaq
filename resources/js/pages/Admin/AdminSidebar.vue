<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
const links = [
    ['admin.dashboard', 'Overview', 'ki-element-11'],
    ['admin.users.index', 'Members', 'ki-profile-user'],
    ['admin.verifications.index', 'Verifications', 'ki-shield-tick'],
    ['admin.approvals.index', 'Image approvals', 'ki-picture'],
    ['admin.payments.index', 'Payments', 'ki-credit-cart'],
    ['admin.countries.index', 'Countries', 'ki-geolocation'],
    ['admin.cities.index', 'Cities', 'ki-home-2'],
] as const;
</script>
<template>
    <aside
        id="kt_app_sidebar"
        class="app-sidebar admin-sidebar"
        data-kt-drawer="true"
        data-kt-drawer-name="app-sidebar"
        data-kt-drawer-activate="{default: true, lg: false}"
        data-kt-drawer-overlay="true"
        data-kt-drawer-width="250px"
        data-kt-drawer-toggle="#kt_app_sidebar_toggle"
    >
        <div class="admin-sidebar-brand">
            <span class="mark">◎</span><span><strong>Khotobah</strong><small>CONTROL CENTER</small></span>
        </div>
        <div class="admin-sidebar-label">WORKSPACE</div>
        <nav aria-label="Admin navigation">
            <Link v-for="[name, label, icon] in links" :key="name" :href="route(name)" :class="{ active: route().current(name) }"
                ><i :class="['ki-outline', icon]"></i>{{ label }}</Link
            >
        </nav>
        <div class="admin-sidebar-foot">
            <Link :href="route('landing')"><i class="ki-outline ki-arrow-left"></i> Back to site</Link>
        </div>
    </aside>
</template>
<style scoped>
.admin-sidebar {
    display: flex;
    flex-direction: column;
    width: 250px;
    min-width: 250px;
    background: #fffaf9;
    border-inline-end: 1px solid #eddee4;
    padding: 26px 15px 16px;
    box-shadow: none;
}
.admin-sidebar-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 12px 26px;
    color: #482b3a;
}
.admin-sidebar-brand .mark {
    display: grid;
    place-items: center;
    width: 39px;
    height: 39px;
    border-radius: 11px;
    background: #f7e5eb;
    color: #a4496d;
    font-size: 26px;
}
.admin-sidebar-brand strong,
.admin-sidebar-brand small {
    display: block;
}
.admin-sidebar-brand strong {
    font-size: 17px;
}
.admin-sidebar-brand small {
    font-size: 9px;
    letter-spacing: 0.13em;
    color: #aa7289;
    font-weight: 800;
}
.admin-sidebar-label {
    padding: 10px 13px;
    color: #b0909f;
    font-size: 9px;
    letter-spacing: 0.17em;
    font-weight: 900;
}
nav {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
nav a,
.admin-sidebar-foot a {
    display: flex;
    align-items: center;
    gap: 11px;
    min-height: 42px;
    padding: 10px 13px;
    border-radius: 10px;
    color: #735968;
    font-size: 12px;
    font-weight: 800;
    text-decoration: none;
}
nav a i,
.admin-sidebar-foot i {
    font-size: 18px;
    color: #ad7990;
}
nav a:hover,
nav a.active {
    background: #f5e3e9;
    color: #8b3b5e;
}
nav a.active i {
    color: #9c4166;
}
.admin-sidebar-foot {
    margin-top: auto;
    border-top: 1px solid #eedfe4;
    padding-top: 12px;
}
@media (max-width: 991px) {
    .admin-sidebar {
        position: fixed;
        top: 0;
        bottom: 0;
        z-index: 1100;
        overflow: auto;
    }
}
</style>
