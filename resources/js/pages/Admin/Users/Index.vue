<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import type { AdminPagination } from '@/pages/Admin/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';
interface UserRow {
    id: number;
    name: string | null;
    username: string | null;
    email: string | null;
    phone_number: string | null;
    nationality: number | null;
    registration_type: number | null;
    is_locked: boolean;
    is_verified: boolean;
    is_email_verified: boolean;
    is_online: boolean;
    created_at: string | null;
}
interface Country {
    id: number;
    name: string;
    ar_name: string;
}
const props = defineProps<{
    users: AdminPagination<UserRow>;
    countries: Country[];
    filters: { nationality?: string; registration_type?: string; is_locked?: string; is_verified?: string; search?: string };
}>();
const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');
const filters = ref({
    search: props.filters.search || '',
    nationality: props.filters.nationality || '',
    registration_type: props.filters.registration_type || '',
    is_locked: props.filters.is_locked ?? '',
    is_verified: props.filters.is_verified ?? '',
});
const action = ref<{ user: UserRow; kind: 'activate' | 'deactivate' | 'verify' | 'unverify' | 'destroy' } | null>(null);
const processing = ref(false);
const reminder = ref<UserRow | null>(null);
const names: { [key: string]: string } = {
    activate: 'Unlock account',
    deactivate: 'Lock account',
    verify: 'Verify member',
    unverify: 'Remove verification',
    destroy: 'Delete member',
};
const descriptions: { [key: string]: string } = {
    activate: 'This member will be able to access their account again.',
    deactivate: 'This member will lose access until the account is unlocked.',
    verify: 'This member will receive verified status.',
    unverify: 'Verified status will be removed from this member.',
    destroy: 'This removes the member account from the directory.',
};
const countryName = (id: number | null) => props.countries.find((c) => c.id === Number(id))?.[isRtl.value ? 'ar_name' : 'name'] || '—';
let timer: ReturnType<typeof setTimeout>;
const apply = () =>
    router.get(route('admin.users.index'), Object.fromEntries(Object.entries(filters.value).filter(([, v]) => v !== '')), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
const search = () => {
    clearTimeout(timer);
    timer = setTimeout(apply, 350);
};
watch(() => [filters.value.nationality, filters.value.registration_type, filters.value.is_locked, filters.value.is_verified], apply);
const reset = () => {
    filters.value = { search: '', nationality: '', registration_type: '', is_locked: '', is_verified: '' };
    router.get(route('admin.users.index'));
};
const confirm = () => {
    if (!action.value) return;
    processing.value = true;
    const { user, kind } = action.value;
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            action.value = null;
        },
        onFinish: () => {
            processing.value = false;
        },
    };
    if (kind === 'destroy') router.delete(route('admin.users.destroy', user.id), options);
    else router.post(route(`admin.users.${kind}`, user.id), {}, options);
};
const sendReminder = (lang: 'en' | 'ar') => {
    if (!reminder.value) return;
    processing.value = true;
    router.post(
        route('admin.users.send-verification-reminder', reminder.value.id),
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
                    <small>CONTROL CENTER / MEMBERS</small>
                    <h1>Members</h1>
                    <p>Search, review and manage every account in one place.</p>
                </div>
                <Link :href="route('admin.users.create')" class="admin-hero-action"><i class="ki-outline ki-plus"></i> Add member</Link>
            </header>
            <div class="admin-panel">
                <div class="admin-panel-head">
                    <h2>Find a member</h2>
                    <span class="admin-muted">{{ users.total }} members</span>
                </div>
                <div class="admin-panel-body">
                    <div class="admin-filters">
                        <div class="admin-field">
                            <label for="user-search">Search</label
                            ><input
                                id="user-search"
                                v-model="filters.search"
                                placeholder="Name, username, email or phone"
                                @input="search"
                                @keyup.enter="apply"
                            />
                        </div>
                        <div class="admin-field">
                            <label for="nationality">Nationality</label
                            ><select id="nationality" v-model="filters.nationality">
                                <option value="">All countries</option>
                                <option v-for="country in countries" :key="country.id" :value="country.id">
                                    {{ isRtl ? country.ar_name || country.name : country.name }}
                                </option>
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
                            <label for="status">Account</label
                            ><select id="status" v-model="filters.is_locked">
                                <option value="">All statuses</option>
                                <option value="0">Open</option>
                                <option value="1">Locked</option>
                            </select>
                        </div>
                        <div class="admin-field">
                            <label for="verified">Verification</label
                            ><select id="verified" v-model="filters.is_verified">
                                <option value="">All statuses</option>
                                <option value="1">Verified</option>
                                <option value="0">Unverified</option>
                            </select>
                        </div>
                        <button type="button" class="admin-button" @click="reset">Clear</button>
                    </div>
                </div>
            </div>
            <div class="admin-panel">
                <div class="admin-panel-head">
                    <h2>Member directory</h2>
                    <span class="admin-muted">Page {{ users.current_page }} of {{ users.last_page }}</span>
                </div>
                <div v-if="users.data.length" class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Nationality</th>
                                <th>Type</th>
                                <th>Account</th>
                                <th>Verification</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users.data" :key="user.id">
                                <td>
                                    <strong
                                        >{{ user.name || user.username || '—' }}
                                        <span v-if="user.is_online" class="admin-online-dot" title="Online"></span></strong
                                    ><small>#{{ user.id }} · {{ user.email || user.phone_number || '—' }}</small>
                                </td>
                                <td>{{ countryName(user.nationality) }}</td>
                                <td>{{ user.registration_type === 1 ? 'Husband' : user.registration_type === 2 ? 'Wife' : '—' }}</td>
                                <td>
                                    <span :class="['admin-pill', user.is_locked ? 'bad' : 'good']">{{ user.is_locked ? 'Locked' : 'Open' }}</span>
                                </td>
                                <td>
                                    <span :class="['admin-pill', user.is_verified ? 'good' : 'warn']">{{
                                        user.is_verified ? 'Verified' : 'Unverified'
                                    }}</span>
                                </td>
                                <td>{{ user.created_at || '—' }}</td>
                                <td>
                                    <div class="admin-actions">
                                        <Link :href="route('admin.users.edit', user.id)" class="admin-button" title="Edit member"
                                            ><i class="ki-outline ki-pencil"></i></Link
                                        ><button
                                            type="button"
                                            class="admin-button"
                                            :title="user.is_locked ? 'Unlock' : 'Lock'"
                                            @click="action = { user, kind: user.is_locked ? 'activate' : 'deactivate' }"
                                        >
                                            <i :class="['ki-outline', user.is_locked ? 'ki-lock' : 'ki-lock-2']"></i></button
                                        ><button
                                            type="button"
                                            class="admin-button"
                                            :title="user.is_verified ? 'Unverify' : 'Verify'"
                                            @click="action = { user, kind: user.is_verified ? 'unverify' : 'verify' }"
                                        >
                                            <i class="ki-outline ki-shield-tick"></i></button
                                        ><button
                                            v-if="!user.is_email_verified && user.email"
                                            type="button"
                                            class="admin-button"
                                            title="Send email reminder"
                                            @click="reminder = user"
                                        >
                                            <i class="ki-outline ki-sms"></i></button
                                        ><button type="button" class="admin-button danger" title="Delete" @click="action = { user, kind: 'destroy' }">
                                            <i class="ki-outline ki-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="admin-empty"><i class="ki-outline ki-profile-user"></i>No members match these filters.</div>
                <div v-if="users.last_page > 1" class="admin-panel-body"><Pagination :data="users" /></div>
            </div>
        </div>
    </main>
    <div v-if="action" class="admin-dialog-backdrop" @click.self="action = null">
        <div class="admin-dialog" role="alertdialog" aria-modal="true" :aria-label="names[action.kind]">
            <h2>{{ names[action.kind] }}?</h2>
            <p>
                {{ descriptions[action.kind] }}<br /><strong>{{ action.user.name || action.user.username }}</strong>
            </p>
            <div class="admin-dialog-actions">
                <button class="admin-button" @click="action = null">Cancel</button
                ><button :class="['admin-button', action.kind === 'destroy' ? 'danger' : 'primary']" :disabled="processing" @click="confirm">
                    {{ processing ? 'Working…' : 'Confirm' }}
                </button>
            </div>
        </div>
    </div>
    <div v-if="reminder" class="admin-dialog-backdrop" @click.self="reminder = null">
        <div class="admin-dialog" role="dialog" aria-modal="true">
            <h2>Send verification reminder</h2>
            <p>Choose the email language for {{ reminder.name || reminder.username }}.</p>
            <div class="admin-dialog-actions">
                <button class="admin-button" @click="reminder = null">Cancel</button
                ><button class="admin-button" :disabled="processing" @click="sendReminder('ar')">Arabic</button
                ><button class="admin-button primary" :disabled="processing" @click="sendReminder('en')">English</button>
            </div>
        </div>
    </div>
</template>
<style scoped>
.admin-online-dot {
    display: inline-block;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #40b27b;
    margin-inline: 4px;
}
.admin-table {
    min-width: 980px;
}
</style>
