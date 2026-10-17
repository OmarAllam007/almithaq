<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import type { AdminPagination } from '@/pages/Admin/types';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { route } from 'ziggy-js';
interface StuckUser {
    id: number;
    name: string | null;
    username: string | null;
    email: string | null;
    registration_type: number | null;
    stage: 'email' | 'profile';
    campaign: string | null;
    created_at: string | null;
    reminded_at: string | null;
}
const props = defineProps<{
    users: AdminPagination<StuckUser>;
    counts: { email: number; profile: number };
    filters: { stage?: string; registration_type?: string; reminded?: string; search?: string };
}>();
const filters = ref({
    search: props.filters.search || '',
    stage: props.filters.stage || '',
    registration_type: props.filters.registration_type || '',
    reminded: props.filters.reminded ?? '',
});
const reminder = ref<StuckUser | null>(null);
const processing = ref(false);
let timer: ReturnType<typeof setTimeout>;
const apply = () =>
    router.get(route('admin.onboarding.index'), Object.fromEntries(Object.entries(filters.value).filter(([, v]) => v !== '')), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
const search = () => {
    clearTimeout(timer);
    timer = setTimeout(apply, 350);
};
watch(() => [filters.value.stage, filters.value.registration_type, filters.value.reminded], apply);
const reset = () => {
    filters.value = { search: '', stage: '', registration_type: '', reminded: '' };
    router.get(route('admin.onboarding.index'));
};
const sendReminder = (lang: 'en' | 'ar') => {
    if (!reminder.value) return;
    processing.value = true;
    router.post(
        route('admin.onboarding.remind', reminder.value.id),
        { lang },
        {
            preserveScroll: true,
            onSuccess: () => {
                reminder.value = null;
            },
            onFinish: () => {
                processing.value = false;
            },
        },
    );
};
</script>
<template>
    <main class="admin-page">
        <div class="admin-shell">
            <header class="admin-hero">
                <div>
                    <small>CONTROL CENTER / ONBOARDING</small>
                    <h1>Incomplete signups</h1>
                    <p>Members who created an account but never finished. Send them the reminder for the step they are stuck on.</p>
                </div>
            </header>
            <div class="admin-panel">
                <div class="admin-panel-head">
                    <h2>Where they are stuck</h2>
                    <span class="admin-muted">{{ users.total }} in total</span>
                </div>
                <div class="admin-panel-body">
                    <div class="admin-filters">
                        <div class="admin-field">
                            <label for="onboarding-search">Search</label
                            ><input
                                id="onboarding-search"
                                v-model="filters.search"
                                placeholder="Name, username or email"
                                @input="search"
                                @keyup.enter="apply"
                            />
                        </div>
                        <div class="admin-field">
                            <label for="stage">Stage</label
                            ><select id="stage" v-model="filters.stage">
                                <option value="">All stages</option>
                                <option value="email">Email not verified ({{ counts.email }})</option>
                                <option value="profile">Profile not completed ({{ counts.profile }})</option>
                            </select>
                        </div>
                        <div class="admin-field">
                            <label for="type">Type</label
                            ><select id="type" v-model="filters.registration_type">
                                <option value="">All types</option>
                                <option value="1">Husband</option>
                                <option value="2">Wife</option>
                            </select>
                        </div>
                        <div class="admin-field">
                            <label for="reminded">Reminder</label
                            ><select id="reminded" v-model="filters.reminded">
                                <option value="">All members</option>
                                <option value="0">Never reminded</option>
                                <option value="1">Already reminded</option>
                            </select>
                        </div>
                        <button type="button" class="admin-button" @click="reset">Clear</button>
                    </div>
                </div>
            </div>
            <div class="admin-panel">
                <div class="admin-panel-head">
                    <h2>Members to follow up</h2>
                    <span class="admin-muted">Page {{ users.current_page }} of {{ users.last_page }}</span>
                </div>
                <div v-if="users.data.length" class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Type</th>
                                <th>Stuck on</th>
                                <th>Campaign</th>
                                <th>Joined</th>
                                <th>Last reminder</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users.data" :key="user.id">
                                <td>
                                    <strong>{{ user.name || user.username || '—' }}</strong
                                    ><small>#{{ user.id }} · {{ user.email || '—' }}</small>
                                </td>
                                <td>{{ user.registration_type === 1 ? 'Husband' : user.registration_type === 2 ? 'Wife' : '—' }}</td>
                                <td>
                                    <span :class="['admin-pill', user.stage === 'email' ? 'bad' : 'warn']">
                                        {{ user.stage === 'email' ? 'Email verification' : 'Profile completion' }}
                                    </span>
                                </td>
                                <td>{{ user.campaign || '—' }}</td>
                                <td>{{ user.created_at || '—' }}</td>
                                <td>{{ user.reminded_at || 'Never' }}</td>
                                <td>
                                    <button class="admin-button primary" :disabled="!user.email" @click="reminder = user">
                                        <i class="ki-outline ki-sms"></i> Send reminder
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="admin-empty"><i class="ki-outline ki-user-edit"></i>Nobody is stuck. Every member finished onboarding.</div>
                <div v-if="users.last_page > 1" class="admin-panel-body"><Pagination :data="users" /></div>
            </div>
        </div>
    </main>
    <div v-if="reminder" class="admin-dialog-backdrop" @click.self="reminder = null">
        <div class="admin-dialog" role="alertdialog" aria-modal="true">
            <h2>Send reminder</h2>
            <p>
                {{ reminder.name || reminder.username }} · {{ reminder.email }}<br />
                They will receive the
                {{ reminder.stage === 'email' ? 'email verification' : 'profile completion' }} reminder. Choose the language.
            </p>
            <div class="admin-dialog-actions">
                <button class="admin-button" :disabled="processing" @click="reminder = null">Cancel</button
                ><button class="admin-button" :disabled="processing" @click="sendReminder('ar')">العربية</button
                ><button class="admin-button primary" :disabled="processing" @click="sendReminder('en')">English</button>
            </div>
        </div>
    </div>
</template>
