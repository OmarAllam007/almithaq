<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import Pagination from '@/components/Pagination.vue';

interface User {
    id: number;
    name: string;
    username: string;
    email: string;
    phone_number: string;
    nationality: number | null;
    registration_type: number | null;
    is_active: boolean;
    is_verified: boolean;
    is_online: boolean;
    created_at: string;
}

interface Country {
    id: number;
    name: string;
    ar_name: string;
}

interface PaginationData {
    data: User[];
    current_page: number;
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: Array<{ url: string | null; label: string; active: boolean }>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

interface Props {
    users: PaginationData;
    countries: Country[];
    filters: {
        nationality?: string;
        registration_type?: string;
        is_active?: string;
        is_verified?: string;
        search?: string;
    };
}

const props = defineProps<Props>();
const page = usePage();

const selectedNationality = ref<string | undefined>(props.filters.nationality);
const selectedRegistrationType = ref<string | undefined>(props.filters.registration_type);
const selectedIsActive = ref<string | undefined>(props.filters.is_active);
const selectedIsVerified = ref<string | undefined>(props.filters.is_verified);
const searchQuery = ref<string>(props.filters.search || '');

const confirmingDelete = ref<number | null>(null);
const processing = ref(false);

const getCountryName = (nationalityId: number | null): string => {
    if (!nationalityId) return '—';
    const country = props.countries.find((c) => c.id === nationalityId);
    return country ? country.name : '—';
};

const getRegistrationLabel = (type: number | null): string => {
    if (type === 1) return 'Husband';
    if (type === 2) return 'Wife';
    return '—';
};

const getRegistrationBadgeClass = (type: number | null): string => {
    if (type === 1) return 'badge-light-primary';
    if (type === 2) return 'badge-light-info';
    return 'badge-light';
};

const applyFilters = () => {
    router.get(
        route('admin.users.index'),
        {
            nationality: selectedNationality.value || undefined,
            registration_type: selectedRegistrationType.value || undefined,
            is_active: selectedIsActive.value ?? undefined,
            is_verified: selectedIsVerified.value ?? undefined,
            search: searchQuery.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const resetFilters = () => {
    selectedNationality.value = undefined;
    selectedRegistrationType.value = undefined;
    selectedIsActive.value = undefined;
    selectedIsVerified.value = undefined;
    searchQuery.value = '';
    applyFilters();
};

const hasActiveFilters = computed(() => {
    return (
        selectedNationality.value ||
        selectedRegistrationType.value ||
        selectedIsActive.value !== undefined && selectedIsActive.value !== '' ||
        selectedIsVerified.value !== undefined && selectedIsVerified.value !== '' ||
        searchQuery.value
    );
});

// Actions
const deleteUser = (userId: number) => {
    processing.value = true;
    router.delete(route('admin.users.destroy', userId), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDelete.value = null;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

const deactivateUser = (userId: number) => {
    processing.value = true;
    router.post(route('admin.users.deactivate', userId), {}, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
        },
    });
};

const unverifyUser = (userId: number) => {
    processing.value = true;
    router.post(route('admin.users.unverify', userId), {}, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
        },
    });
};

let searchTimeout: ReturnType<typeof setTimeout>;
const onSearchInput = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 400);
};

watch([selectedNationality, selectedRegistrationType, selectedIsActive, selectedIsVerified], () => {
    applyFilters();
});
</script>

<template>
    <div>
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!-- Page Header -->
                <div class="card mb-5 mb-xl-10">
                    <div class="card-body pt-9 pb-0">
                        <div class="d-flex flex-wrap flex-sm-nowrap mb-6">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="text-gray-900 fs-2 fw-bold me-3">
                                                <i class="ki-outline ki-profile-user fs-2 me-2"></i>
                                                Users Management
                                            </span>
                                            <span class="badge badge-light-primary fs-7 fw-semibold">
                                                {{ users.total }} total
                                            </span>
                                        </div>
                                        <div class="d-flex flex-wrap fw-semibold mb-4 fs-5 text-gray-500">
                                            Manage all registered users
                                        </div>
                                    </div>
                                    <Link :href="route('admin.users.create')" class="btn btn-primary">
                                        <i class="ki-outline ki-plus fs-4 me-1"></i>Add User
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="d-flex flex-wrap gap-4 mb-6">
                            <!-- Search -->
                            <div class="d-flex align-items-center position-relative">
                                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                                <input type="text" v-model="searchQuery" @input="onSearchInput"
                                    class="form-control form-control-sm ps-12"
                                    placeholder="Search name, username, email..." style="min-width: 230px" />
                            </div>

                            <!-- Nationality -->
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label mb-0 text-nowrap fs-7 text-gray-600">Nationality:</label>
                                <select v-model="selectedNationality" class="form-select form-select-sm"
                                    style="min-width: 150px">
                                    <option :value="undefined">All</option>
                                    <option v-for="country in countries" :key="country.id" :value="country.id">
                                        {{ country.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Registration Type -->
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label mb-0 text-nowrap fs-7 text-gray-600">Type:</label>
                                <select v-model="selectedRegistrationType" class="form-select form-select-sm"
                                    style="min-width: 130px">
                                    <option :value="undefined">All</option>
                                    <option value="1">Husband</option>
                                    <option value="2">Wife</option>
                                </select>
                            </div>

                            <!-- Is Active -->
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label mb-0 text-nowrap fs-7 text-gray-600">Active:</label>
                                <select v-model="selectedIsActive" class="form-select form-select-sm"
                                    style="min-width: 110px">
                                    <option :value="undefined">All</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                            <!-- Is Verified -->
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label mb-0 text-nowrap fs-7 text-gray-600">Verified:</label>
                                <select v-model="selectedIsVerified" class="form-select form-select-sm"
                                    style="min-width: 110px">
                                    <option :value="undefined">All</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                            <!-- Reset Filters -->
                            <button v-if="hasActiveFilters" @click="resetFilters" class="btn btn-sm btn-light-danger">
                                <i class="ki-outline ki-cross fs-3"></i>
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="card" v-if="users.data.length > 0">
                    <div class="card-body py-4">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="admin_users_table">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-50px">ID</th>
                                        <th class="min-w-150px">User</th>
                                        <th class="min-w-100px">Nationality</th>
                                        <th class="min-w-80px">Type</th>
                                        <th class="min-w-80px">Status</th>
                                        <th class="min-w-80px">Verified</th>
                                        <th class="min-w-100px">Joined</th>
                                        <th class="min-w-100px text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-semibold">
                                    <tr v-for="user in users.data" :key="user.id">
                                        <!-- ID -->
                                        <td>
                                            <span class="text-gray-800 fw-bold">#{{ user.id }}</span>
                                        </td>

                                        <!-- User Info -->
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 fw-bold mb-1">
                                                    {{ user.name || user.username || '—' }}
                                                    <span v-if="user.is_online"
                                                        class="bullet bullet-dot bg-success h-6px w-6px ms-2"
                                                        title="Online"></span>
                                                </span>
                                                <span class="text-gray-500 fs-7">{{ user.email || user.phone_number ||
                                                    '—' }}</span>
                                            </div>
                                        </td>

                                        <!-- Nationality -->
                                        <td>
                                            <span class="text-gray-700">{{ getCountryName(user.nationality) }}</span>
                                        </td>

                                        <!-- Registration Type -->
                                        <td>
                                            <span class="badge"
                                                :class="getRegistrationBadgeClass(user.registration_type)">
                                                {{ getRegistrationLabel(user.registration_type) }}
                                            </span>
                                        </td>

                                        <!-- Active Status -->
                                        <td>
                                            <span class="badge"
                                                :class="user.is_active ? 'badge-light-success' : 'badge-light-danger'">
                                                {{ user.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>

                                        <!-- Verified -->
                                        <td>
                                            <span class="badge"
                                                :class="user.is_verified ? 'badge-light-success' : 'badge-light-warning'">
                                                {{ user.is_verified ? 'Verified' : 'Unverified' }}
                                            </span>
                                        </td>

                                        <!-- Created At -->
                                        <td>
                                            <span class="text-gray-600 fs-7">{{ user.created_at }}</span>
                                        </td>

                                        <!-- Actions -->
                                        <td class="text-end">
                                            <div v-if="confirmingDelete !== user.id"
                                                class="d-flex justify-content-end gap-1">
                                                <!-- Edit -->
                                                <Link :href="route('admin.users.edit', user.id)"
                                                    class="btn btn-icon btn-sm btn-light-primary"
                                                    title="Edit">
                                                    <i class="ki-outline ki-pencil fs-5"></i>
                                                </Link>

                                                <!-- Deactivate -->
                                                <button v-if="user.is_active" @click="deactivateUser(user.id)"
                                                    class="btn btn-icon btn-sm btn-light-warning" :disabled="processing"
                                                    title="Deactivate">
                                                    <i class="ki-outline ki-lock fs-5"></i>
                                                </button>

                                                <!-- Unverify -->
                                                <button v-if="user.is_verified" @click="unverifyUser(user.id)"
                                                    class="btn btn-icon btn-sm btn-light-info" :disabled="processing"
                                                    title="Unverify">
                                                    <i class="ki-outline ki-shield-cross fs-5"></i>
                                                </button>

                                                <!-- Delete -->
                                                <button @click="confirmingDelete = user.id"
                                                    class="btn btn-icon btn-sm btn-light-danger" :disabled="processing"
                                                    title="Delete">
                                                    <i class="ki-outline ki-trash fs-5"></i>
                                                </button>
                                            </div>

                                            <!-- Confirm Delete -->
                                            <div v-else class="d-flex justify-content-end align-items-center gap-2">
                                                <span class="text-danger fs-8 fw-semibold">Delete?</span>
                                                <button @click="deleteUser(user.id)"
                                                    class="btn btn-sm btn-danger py-1 px-3" :disabled="processing">
                                                    Yes
                                                </button>
                                                <button @click="confirmingDelete = null"
                                                    class="btn btn-sm btn-light py-1 px-3">
                                                    No
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <Pagination :data="users" />
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="card">
                    <div class="card-body text-center py-20">
                        <div class="mb-10">
                            <i class="ki-outline ki-profile-user fs-5x text-gray-400"></i>
                        </div>
                        <h3 class="text-gray-800 mb-3">No Users Found</h3>
                        <p class="text-gray-600 fs-5">
                            Try adjusting your filters to see more results.
                        </p>
                        <button v-if="hasActiveFilters" @click="resetFilters" class="btn btn-primary mt-5">
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.form-select-sm {
    padding: 0.5rem 2rem 0.5rem 0.75rem;
}

.table td {
    vertical-align: middle;
}
</style>
