<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
defineProps<{
    stats: {
        users: number;
        activeUsers: number;
        pendingVerifications: number;
        pendingImages: number;
        payments: number;
        countries: number;
        cities: number;
    };
    recentUsers: Array<{ id: number; name: string; username: string; email: string; is_locked: boolean; is_verified: boolean; created_at: string }>;
}>();
const sections = [
    ['admin.users.index', 'Manage members', 'ki-profile-user'],
    ['admin.verifications.index', 'Review verifications', 'ki-shield-tick'],
    ['admin.approvals.index', 'Approve images', 'ki-picture'],
    ['admin.payments.index', 'View payments', 'ki-credit-cart'],
    ['admin.countries.index', 'Edit countries', 'ki-geolocation'],
    ['admin.cities.index', 'Edit cities', 'ki-home-2'],
] as const;
</script>
<template>
    <main class="admin-page">
        <div class="admin-shell">
            <header class="admin-hero">
                <div>
                    <small>CONTROL CENTER / OVERVIEW</small>
                    <h1>Good to see you.</h1>
                    <p>Member activity, pending reviews and the tools that keep Khotobah running.</p>
                </div>
                <Link :href="route('admin.users.index')" class="admin-hero-action"><i class="ki-outline ki-arrow-right"></i> Open members</Link>
            </header>
            <section class="admin-stats" aria-label="Platform statistics">
                <div class="admin-stat">
                    <i class="ki-outline ki-people"></i><strong>{{ stats.users }}</strong
                    ><span>Total members</span>
                </div>
                <div class="admin-stat">
                    <i class="ki-outline ki-check-circle"></i><strong>{{ stats.activeUsers }}</strong
                    ><span>Open accounts</span>
                </div>
                <div class="admin-stat">
                    <i class="ki-outline ki-shield-tick"></i><strong>{{ stats.pendingVerifications }}</strong
                    ><span>Videos to review</span>
                </div>
                <div class="admin-stat">
                    <i class="ki-outline ki-picture"></i><strong>{{ stats.pendingImages }}</strong
                    ><span>Images to review</span>
                </div>
            </section>
            <div class="admin-panel">
                <div class="admin-panel-head">
                    <h2>Quick access</h2>
                    <span class="admin-muted">{{ stats.countries }} countries · {{ stats.cities }} cities · {{ stats.payments }} payments</span>
                </div>
                <div class="admin-panel-body">
                    <div class="admin-links">
                        <Link v-for="[name, label, icon] in sections" :key="name" :href="route(name)" class="admin-link"
                            ><i :class="['ki-outline', icon]"></i>{{ label }} <i class="ki-outline ki-arrow-right ms-auto"></i
                        ></Link>
                    </div>
                </div>
            </div>
            <div class="admin-panel">
                <div class="admin-panel-head">
                    <h2>Recently joined</h2>
                    <Link :href="route('admin.users.index')" class="admin-button">View all</Link>
                </div>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Verified</th>
                                <th>Joined</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in recentUsers" :key="user.id">
                                <td>
                                    <strong>{{ user.name || user.username }}</strong
                                    ><small>@{{ user.username }}</small>
                                </td>
                                <td>{{ user.email || '—' }}</td>
                                <td>
                                    <span :class="['admin-pill', user.is_locked ? 'bad' : 'good']">{{ user.is_locked ? 'Locked' : 'Open' }}</span>
                                </td>
                                <td>
                                    <span :class="['admin-pill', user.is_verified ? 'good' : 'warn']">{{
                                        user.is_verified ? 'Verified' : 'Pending'
                                    }}</span>
                                </td>
                                <td>{{ user.created_at?.slice(0, 10) }}</td>
                                <td><Link :href="route('admin.users.edit', user.id)" class="admin-button">Edit</Link></td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="!recentUsers.length" class="admin-empty">No members yet.</div>
                </div>
            </div>
        </div>
    </main>
</template>
