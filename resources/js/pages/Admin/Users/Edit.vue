<script setup lang="ts">
import { Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';

interface EnumOption {
    value: number;
    label: string;
}

interface Country {
    id: number;
    name: string;
    ar_name: string;
}

interface City {
    id: number;
    country_id: number;
    name: string;
    ar_name: string;
}

interface ProfileImage {
    id: number;
    is_main: boolean;
    is_approved: boolean;
    thumbnail_url: string;
    original_url: string;
}

interface AdminUser {
    id: number;
    name: string | null;
    full_name: string | null;
    username: string | null;
    email: string | null;
    country_code: string | null;
    phone_number: string | null;
    registration_type: number | null;
    is_active: boolean;
    is_locked: boolean;
    is_verified: boolean;
    is_admin: boolean;
    age: number | null;
    marriage_type: number | null;
    marriage_status: number | null;
    child_count: number | null;
    religion: number | null;
    nationality: number | null;
    residence: number | null;
    city: number | null;
    weight: number | null;
    height: number | null;
    skin_color: number | null;
    body_shape: number | null;
    devotion: number | null;
    prayer: number | null;
    smoking: boolean | null;
    beard: boolean | null;
    education_level: number | null;
    financial_status: number | null;
    field_of_work: number | null;
    job: string | null;
    monthly_income: number | null;
    health_status: number | null;
    about_self: string | null;
    about_partner: string | null;
}

const props = defineProps<{
    user: AdminUser;
    images: ProfileImage[];
    countries: Country[];
    cities: City[];
    marriage_types: EnumOption[];
    marriage_statuses: EnumOption[];
    skin_colors: EnumOption[];
    body_shapes: EnumOption[];
    devotion_types: EnumOption[];
    prayer_types: EnumOption[];
    education_levels: EnumOption[];
    financial_statuses: EnumOption[];
    fields_of_work: EnumOption[];
    monthly_incomes: EnumOption[];
    health_statuses: EnumOption[];
}>();

const lightbox = ref<ProfileImage | null>(null);

const form = useForm({
    name: props.user.name ?? '',
    full_name: props.user.full_name ?? '',
    username: props.user.username ?? '',
    email: props.user.email ?? '',
    password: '',
    password_confirmation: '',
    country_code: props.user.country_code ?? '+966',
    phone_number: props.user.phone_number ?? '',
    registration_type: props.user.registration_type ?? '',
    is_active: props.user.is_active,
    is_verified: props.user.is_verified,
    is_admin: props.user.is_admin,
    age: props.user.age ?? '',
    marriage_type: props.user.marriage_type ?? '',
    marriage_status: props.user.marriage_status ?? '',
    child_count: props.user.child_count ?? '',
    religion: String(props.user.religion ?? 1),
    nationality: props.user.nationality ?? '',
    residence: props.user.residence ?? '',
    city: props.user.city ?? '',
    weight: props.user.weight ?? '',
    height: props.user.height ?? '',
    skin_color: props.user.skin_color ?? '',
    body_shape: props.user.body_shape ?? '',
    devotion: props.user.devotion ?? '',
    prayer: props.user.prayer ?? '',
    smoking: props.user.smoking !== null ? (props.user.smoking ? '1' : '0') : '',
    beard: props.user.beard !== null ? (props.user.beard ? '1' : '0') : '',
    education_level: props.user.education_level ?? '',
    financial_status: props.user.financial_status ?? '',
    field_of_work: props.user.field_of_work ?? '',
    job: props.user.job ?? '',
    monthly_income: props.user.monthly_income ?? '',
    health_status: props.user.health_status ?? '',
    about_self: props.user.about_self ?? '',
    about_partner: props.user.about_partner ?? '',
});

const isHusband = computed(() => Number(form.registration_type) === 1);

watch(isHusband, (husband) => {
    if (!husband) {
        form.beard = '';
    }
});

const filteredCities = computed(() => {
    if (!form.residence) return [];
    return props.cities.filter((c) => String(c.country_id) === String(form.residence));
});

watch(
    () => form.residence,
    (newVal, oldVal) => {
        if (newVal !== oldVal) {
            form.city = '';
        }
    },
);

const submit = () => {
    form.transform(({ is_active, is_verified, ...data }) => data).put(route('admin.users.update', props.user.id));
};
const action = ref<'activate' | 'deactivate' | 'verify' | 'unverify' | null>(null);
const actionBusy = ref(false);
const confirmAction = () => {
    if (!action.value) return;
    actionBusy.value = true;
    router.post(
        route(`admin.users.${action.value}`, props.user.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                action.value = null;
            },
            onFinish: () => {
                actionBusy.value = false;
            },
        },
    );
};
</script>

<template>
    <div class="admin-page admin-edit">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl admin-shell">
                <header class="admin-hero">
                    <div>
                        <small>CONTROL CENTER / MEMBERS / EDIT</small>
                        <h1>{{ user.name || user.username || 'Member' }}</h1>
                        <p>Update profile details and review account access.</p>
                    </div>
                    <Link :href="route('admin.users.index')" class="admin-hero-action">Back to members</Link>
                </header>

                <!-- Page Header -->
                <div class="card mb-xl-10 mb-5">
                    <div class="card-body pt-9 pb-6">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <Link :href="route('admin.users.index')" class="btn btn-sm btn-icon btn-light">
                                    <i class="ki-outline ki-arrow-left fs-3"></i>
                                </Link>
                                <div>
                                    <h2 class="fw-bold mb-1 text-gray-900">
                                        <i class="ki-outline ki-pencil fs-2 me-2"></i>
                                        Edit User #{{ user.id }}
                                    </h2>
                                    <div class="fs-6 text-gray-500">
                                        {{ user.name || user.username || user.email || '—' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Photos -->
                <div class="card mb-5">
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-900">
                                <i class="ki-outline ki-picture fs-4 text-warning me-2"></i>Profile Photos
                                <span class="text-gray-500 fw-semibold fs-7 ms-2">({{ images.length }})</span>
                            </span>
                        </h3>
                    </div>
                    <div class="card-body pt-0">
                        <div v-if="images.length" class="d-flex flex-wrap gap-5">
                            <figure v-for="image in images" :key="image.id" class="admin-photo m-0">
                                <button type="button" class="admin-photo-button" @click="lightbox = image">
                                    <img :src="image.thumbnail_url" :alt="`Photo ${image.id}`" loading="lazy" />
                                </button>
                                <figcaption class="d-flex gap-2 mt-2">
                                    <span v-if="image.is_main" class="admin-pill good">Main</span>
                                    <span :class="['admin-pill', image.is_approved ? 'good' : 'warn']">{{
                                        image.is_approved ? 'Approved' : 'Pending'
                                    }}</span>
                                </figcaption>
                            </figure>
                        </div>
                        <div v-else class="text-gray-500 fs-6 py-4">This member has not uploaded any photos.</div>
                    </div>
                </div>

                <form @submit.prevent="submit">
                    <!-- Account Information -->
                    <div class="card mb-5">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">
                                    <i class="ki-outline ki-lock fs-4 text-primary me-2"></i>Account Information
                                </span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-5">
                                <div class="col-md-6">
                                    <label class="form-label">Display Name</label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.name }"
                                        placeholder="Display name"
                                    />
                                    <div v-if="form.errors.name" class="invalid-feedback">{{ form.errors.name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input
                                        v-model="form.full_name"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.full_name }"
                                        placeholder="Full legal name"
                                    />
                                    <div v-if="form.errors.full_name" class="invalid-feedback">{{ form.errors.full_name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Username</label>
                                    <input
                                        v-model="form.username"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.username }"
                                        placeholder="@username"
                                    />
                                    <div v-if="form.errors.username" class="invalid-feedback">{{ form.errors.username }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.email }"
                                        placeholder="email@example.com"
                                    />
                                    <div v-if="form.errors.email" class="invalid-feedback">{{ form.errors.email }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">New Password <span class="text-muted fs-8">(leave blank to keep current)</span></label>
                                    <input
                                        v-model="form.password"
                                        type="password"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.password }"
                                        placeholder="Min 8 characters"
                                    />
                                    <div v-if="form.errors.password" class="invalid-feedback">{{ form.errors.password }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm New Password</label>
                                    <input
                                        v-model="form.password_confirmation"
                                        type="password"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.password_confirmation }"
                                        placeholder="Repeat new password"
                                    />
                                    <div v-if="form.errors.password_confirmation" class="invalid-feedback">
                                        {{ form.errors.password_confirmation }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Country Code</label>
                                    <select v-model="form.country_code" class="form-select" :class="{ 'is-invalid': form.errors.country_code }">
                                        <option value="+966">+966 (SA)</option>
                                        <option value="+971">+971 (UAE)</option>
                                        <option value="+965">+965 (KW)</option>
                                        <option value="+974">+974 (QA)</option>
                                        <option value="+973">+973 (BH)</option>
                                        <option value="+968">+968 (OM)</option>
                                        <option value="+962">+962 (JO)</option>
                                        <option value="+20">+20 (EG)</option>
                                        <option value="+212">+212 (MA)</option>
                                        <option value="+216">+216 (TN)</option>
                                        <option value="+213">+213 (DZ)</option>
                                    </select>
                                    <div v-if="form.errors.country_code" class="invalid-feedback">{{ form.errors.country_code }}</div>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Phone Number</label>
                                    <input
                                        v-model="form.phone_number"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.phone_number }"
                                        placeholder="5XXXXXXXX"
                                    />
                                    <div v-if="form.errors.phone_number" class="invalid-feedback">{{ form.errors.phone_number }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Registration Type</label>
                                    <select
                                        v-model="form.registration_type"
                                        class="form-select"
                                        :class="{ 'is-invalid': form.errors.registration_type }"
                                    >
                                        <option value="">— Select —</option>
                                        <option value="1">Husband</option>
                                        <option value="2">Wife</option>
                                    </select>
                                    <div v-if="form.errors.registration_type" class="invalid-feedback">{{ form.errors.registration_type }}</div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex gap-8">
                                        <button type="button" class="admin-button" @click="action = user.is_locked ? 'activate' : 'deactivate'">
                                            {{ user.is_locked ? 'Unlock account' : 'Lock account' }}
                                        </button>
                                        <button type="button" class="admin-button" @click="action = user.is_verified ? 'unverify' : 'verify'">
                                            {{ user.is_verified ? 'Remove verification' : 'Verify member' }}
                                        </button>
                                        <div class="form-check form-switch">
                                            <input v-model="form.is_admin" type="checkbox" class="form-check-input" id="edit_is_admin" />
                                            <label class="form-check-label fw-semibold text-danger" for="edit_is_admin">Admin</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="card mb-5">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">
                                    <i class="ki-outline ki-user fs-4 text-info me-2"></i>Personal Information
                                </span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-5">
                                <div class="col-md-3">
                                    <label class="form-label">Age</label>
                                    <input
                                        v-model="form.age"
                                        type="number"
                                        class="form-control"
                                        min="18"
                                        max="100"
                                        :class="{ 'is-invalid': form.errors.age }"
                                        placeholder="Age"
                                    />
                                    <div v-if="form.errors.age" class="invalid-feedback">{{ form.errors.age }}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Children</label>
                                    <input
                                        v-model="form.child_count"
                                        type="number"
                                        class="form-control"
                                        min="0"
                                        max="50"
                                        :class="{ 'is-invalid': form.errors.child_count }"
                                        placeholder="0"
                                    />
                                    <div v-if="form.errors.child_count" class="invalid-feedback">{{ form.errors.child_count }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Marriage Status</label>
                                    <select v-model="form.marriage_status" class="form-select" :class="{ 'is-invalid': form.errors.marriage_status }">
                                        <option value="">— Select —</option>
                                        <option v-for="opt in marriage_statuses" :key="opt.value" :value="opt.value">
                                            {{ opt.label }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.marriage_status" class="invalid-feedback">{{ form.errors.marriage_status }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Marriage Type</label>
                                    <select v-model="form.marriage_type" class="form-select" :class="{ 'is-invalid': form.errors.marriage_type }">
                                        <option value="">— Select —</option>
                                        <option v-for="opt in marriage_types" :key="opt.value" :value="opt.value">
                                            {{ opt.label }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.marriage_type" class="invalid-feedback">{{ form.errors.marriage_type }}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Religion</label>
                                    <select v-model="form.religion" class="form-select" disabled>
                                        <option value="1">Islamic only</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="card mb-5">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">
                                    <i class="ki-outline ki-geolocation fs-4 text-success me-2"></i>Location
                                </span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-5">
                                <div class="col-md-4">
                                    <label class="form-label">Nationality</label>
                                    <select v-model="form.nationality" class="form-select" :class="{ 'is-invalid': form.errors.nationality }">
                                        <option value="">— Select —</option>
                                        <option v-for="country in countries" :key="country.id" :value="country.id">
                                            {{ country.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.nationality" class="invalid-feedback">{{ form.errors.nationality }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Country of Residence</label>
                                    <select v-model="form.residence" class="form-select" :class="{ 'is-invalid': form.errors.residence }">
                                        <option value="">— Select —</option>
                                        <option v-for="country in countries" :key="country.id" :value="country.id">
                                            {{ country.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.residence" class="invalid-feedback">{{ form.errors.residence }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">City</label>
                                    <select
                                        v-model="form.city"
                                        class="form-select"
                                        :class="{ 'is-invalid': form.errors.city }"
                                        :disabled="!form.residence"
                                    >
                                        <option value="">— Select city —</option>
                                        <option v-for="city in filteredCities" :key="city.id" :value="city.id">
                                            {{ city.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.city" class="invalid-feedback">{{ form.errors.city }}</div>
                                    <div v-if="!form.residence" class="form-text text-muted">Select a country first</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Physical Attributes -->
                    <div class="card mb-5">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">
                                    <i class="ki-outline ki-abstract-26 fs-4 text-warning me-2"></i>Physical Attributes
                                </span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-5">
                                <div class="col-md-3">
                                    <label class="form-label">Weight (kg)</label>
                                    <input
                                        v-model="form.weight"
                                        type="number"
                                        class="form-control"
                                        min="30"
                                        max="300"
                                        step="0.01"
                                        :class="{ 'is-invalid': form.errors.weight }"
                                        placeholder="e.g. 70"
                                    />
                                    <div v-if="form.errors.weight" class="invalid-feedback">{{ form.errors.weight }}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Height (cm)</label>
                                    <input
                                        v-model="form.height"
                                        type="number"
                                        class="form-control"
                                        min="100"
                                        max="250"
                                        step="0.01"
                                        :class="{ 'is-invalid': form.errors.height }"
                                        placeholder="e.g. 175"
                                    />
                                    <div v-if="form.errors.height" class="invalid-feedback">{{ form.errors.height }}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Skin Color</label>
                                    <select v-model="form.skin_color" class="form-select" :class="{ 'is-invalid': form.errors.skin_color }">
                                        <option value="">— Select —</option>
                                        <option v-for="opt in skin_colors" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>
                                    <div v-if="form.errors.skin_color" class="invalid-feedback">{{ form.errors.skin_color }}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Body Shape</label>
                                    <select v-model="form.body_shape" class="form-select" :class="{ 'is-invalid': form.errors.body_shape }">
                                        <option value="">— Select —</option>
                                        <option v-for="opt in body_shapes" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>
                                    <div v-if="form.errors.body_shape" class="invalid-feedback">{{ form.errors.body_shape }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Religious & Social -->
                    <div class="card mb-5">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">
                                    <i class="ki-outline ki-star fs-4 text-warning me-2"></i>Religious & Social
                                </span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-5">
                                <div class="col-md-3">
                                    <label class="form-label">Devotion</label>
                                    <select v-model="form.devotion" class="form-select" :class="{ 'is-invalid': form.errors.devotion }">
                                        <option value="">— Select —</option>
                                        <option v-for="opt in devotion_types" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>
                                    <div v-if="form.errors.devotion" class="invalid-feedback">{{ form.errors.devotion }}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Prayer</label>
                                    <select v-model="form.prayer" class="form-select" :class="{ 'is-invalid': form.errors.prayer }">
                                        <option value="">— Select —</option>
                                        <option v-for="opt in prayer_types" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>
                                    <div v-if="form.errors.prayer" class="invalid-feedback">{{ form.errors.prayer }}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Smoking</label>
                                    <select v-model="form.smoking" class="form-select" :class="{ 'is-invalid': form.errors.smoking }">
                                        <option value="">— Select —</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                    <div v-if="form.errors.smoking" class="invalid-feedback">{{ form.errors.smoking }}</div>
                                </div>
                                <div v-if="isHusband" class="col-md-3">
                                    <label class="form-label">Beard</label>
                                    <select v-model="form.beard" class="form-select" :class="{ 'is-invalid': form.errors.beard }">
                                        <option value="">— Select —</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                    <div v-if="form.errors.beard" class="invalid-feedback">{{ form.errors.beard }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Professional -->
                    <div class="card mb-5">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">
                                    <i class="ki-outline ki-briefcase fs-4 text-primary me-2"></i>Professional
                                </span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-5">
                                <div class="col-md-4">
                                    <label class="form-label">Education Level</label>
                                    <select v-model="form.education_level" class="form-select" :class="{ 'is-invalid': form.errors.education_level }">
                                        <option value="">— Select —</option>
                                        <option v-for="opt in education_levels" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>
                                    <div v-if="form.errors.education_level" class="invalid-feedback">{{ form.errors.education_level }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Financial Status</label>
                                    <select
                                        v-model="form.financial_status"
                                        class="form-select"
                                        :class="{ 'is-invalid': form.errors.financial_status }"
                                    >
                                        <option value="">— Select —</option>
                                        <option v-for="opt in financial_statuses" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>
                                    <div v-if="form.errors.financial_status" class="invalid-feedback">{{ form.errors.financial_status }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Field of Work</label>
                                    <select v-model="form.field_of_work" class="form-select" :class="{ 'is-invalid': form.errors.field_of_work }">
                                        <option value="">— Select —</option>
                                        <option v-for="opt in fields_of_work" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>
                                    <div v-if="form.errors.field_of_work" class="invalid-feedback">{{ form.errors.field_of_work }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Job Title</label>
                                    <input
                                        v-model="form.job"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.job }"
                                        placeholder="e.g. Software Engineer"
                                    />
                                    <div v-if="form.errors.job" class="invalid-feedback">{{ form.errors.job }}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Monthly Income</label>
                                    <select v-model="form.monthly_income" class="form-select" :class="{ 'is-invalid': form.errors.monthly_income }">
                                        <option value="">— Select —</option>
                                        <option v-for="opt in monthly_incomes" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>
                                    <div v-if="form.errors.monthly_income" class="invalid-feedback">{{ form.errors.monthly_income }}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Health Status</label>
                                    <select v-model="form.health_status" class="form-select" :class="{ 'is-invalid': form.errors.health_status }">
                                        <option value="">— Select —</option>
                                        <option v-for="opt in health_statuses" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>
                                    <div v-if="form.errors.health_status" class="invalid-feedback">{{ form.errors.health_status }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- About -->
                    <div class="card mb-5">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">
                                    <i class="ki-outline ki-message-text fs-4 text-success me-2"></i>About
                                </span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-5">
                                <div class="col-md-6">
                                    <label class="form-label">About Self</label>
                                    <textarea
                                        v-model="form.about_self"
                                        class="form-control"
                                        rows="5"
                                        :class="{ 'is-invalid': form.errors.about_self }"
                                        placeholder="User's self description..."
                                    ></textarea>
                                    <div v-if="form.errors.about_self" class="invalid-feedback">{{ form.errors.about_self }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">About Partner</label>
                                    <textarea
                                        v-model="form.about_partner"
                                        class="form-control"
                                        rows="5"
                                        :class="{ 'is-invalid': form.errors.about_partner }"
                                        placeholder="Partner requirements..."
                                    ></textarea>
                                    <div v-if="form.errors.about_partner" class="invalid-feedback">{{ form.errors.about_partner }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end mb-10 gap-3">
                        <Link :href="route('admin.users.index')" class="btn btn-light"> Cancel </Link>
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                            <span v-if="form.processing"> <span class="spinner-border spinner-border-sm me-2"></span>Saving... </span>
                            <span v-else> <i class="ki-outline ki-check fs-4 me-1"></i>Save Changes </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div v-if="lightbox" class="admin-dialog-backdrop" @click.self="lightbox = null">
            <div class="admin-lightbox" role="dialog" aria-modal="true" aria-label="Profile photo">
                <img :src="lightbox.original_url" alt="Profile photo full size" />
                <button type="button" class="admin-button mt-4" @click="lightbox = null">Close</button>
            </div>
        </div>

        <div v-if="action" class="admin-dialog-backdrop" @click.self="action = null">
            <div class="admin-dialog" role="alertdialog" aria-modal="true">
                <h2>
                    {{
                        action === 'activate'
                            ? 'Unlock account'
                            : action === 'deactivate'
                              ? 'Lock account'
                              : action === 'verify'
                                ? 'Verify member'
                                : 'Remove verification'
                    }}?
                </h2>
                <p>This changes the account status for {{ user.name || user.username }}.</p>
                <div class="admin-dialog-actions">
                    <button class="admin-button" @click="action = null">Cancel</button
                    ><button class="admin-button primary" :disabled="actionBusy" @click="confirmAction">Confirm</button>
                </div>
            </div>
        </div>
    </div>
</template>
