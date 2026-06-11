<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import ProfileSection from '@/components/Profile/ProfileSection.vue';
import ProfileField from '@/components/Profile/ProfileField.vue';
import { update } from '@/actions/App/Http/Controllers/ProfileController';
import DeleteAccountController from '@/actions/App/Http/Controllers/DeleteAccountController';
import ResetPasswordController from '@/actions/App/Http/Controllers/ResetPasswordController';
import { useLang } from '@/composables/useLang';
const { trans } = useLang();

type SelectOption = { value: string; label: string };

interface Props {
    countries: Array<{ id: number; name: string; ar_name: string; flag: string }>;
    cities: Array<{ id: number; country_id: number; name: string }>;
    marriage_types: SelectOption[];
    marriage_statuses: SelectOption[];
    skin_colors: SelectOption[];
    body_shapes: SelectOption[];
    devotions: SelectOption[];
    prayer_commitments: SelectOption[];
    yes_no_options: SelectOption[];
    education_levels: SelectOption[];
    financial_statuses: SelectOption[];
    health_statuses: SelectOption[];
    fields_of_work: SelectOption[];
    monthly_incomes: SelectOption[];
    delete_account_reasons: SelectOption[];
}

const props = defineProps<Props>();

const page = usePage();
const user = computed(() => page.props.auth.user);
const isArabic = computed(() => page.props.locale === 'ar');

const isEditing = ref(false);

const toStr = (val: unknown): string => (val !== null && val !== undefined ? String(val) : '');

const form = useForm({
    username: user.value.username,
    email: user.value.email,
    phone_number: user.value.phone_number,
    age: user.value.age,
    marriage_type: toStr(user.value.marriage_type),
    marriage_status: toStr(user.value.marriage_status),
    child_count: toStr(user.value.child_count),
    residence: toStr(user.value.residence?.id),
    nationality: toStr(user.value.nationality?.id),
    city: toStr(user.value.city?.id),
    weight: Number.parseInt(user.value.weight),
    height: Number.parseInt(user.value.height),
    skin_color: toStr(user.value.skin_color),
    body_shape: toStr(user.value.body_shape),
    devotion: toStr(user.value.devotion),
    prayer: toStr(user.value.prayer),
    smoking: toStr(user.value.smoking ? 1 : 0),
    beard: toStr(user.value.beard ? 1 : 0),
    education_level: toStr(user.value.education_level),
    financial_status: toStr(user.value.financial_status),
    field_of_work: toStr(user.value.field_of_work),
    job: user.value.job,
    monthly_income: user.value.monthly_income,
    health_status: toStr(user.value.health_status),
    about_partner: user.value.about_partner,
    about_self: user.value.about_self,
});

const ageRange = Array.from({ length: 73 }, (_, i) => ({ value: String(i + 18), label: String(i + 18) }));
const childCounts = Array.from({ length: 11 }, (_, i) => ({ value: String(i), label: String(i) }));

const countryOptions = computed(() =>
    props.countries.map((c) => ({ value: c.id.toString(), label: isArabic.value ? c.ar_name : c.name }))
);

const countryDisplayName = (country: any): string => {
    if (!country) return '';
    return isArabic.value ? (country.ar_name || country.name) : country.name;
};

// City options are scoped to the selected residence country.
const cityOptions = computed(() =>
    props.cities
        .filter((c) => String(c.country_id) === String(form.residence))
        .map((c) => ({ value: c.id.toString(), label: c.name }))
);

const cityDisplayName = (city: any): string => {
    if (!city) return '';
    return isArabic.value ? (city.ar_name || city.name) : city.name;
};

// Reset the chosen city whenever the residence country changes.
watch(
    () => form.residence,
    (newResidence, oldResidence) => {
        if (newResidence !== oldResidence) {
            form.city = '';
        }
    }
);

const toggleEdit = () => {
    if (isEditing.value) {
        // Cancel edit - reset form
        form.reset();
    }
    isEditing.value = !isEditing.value;
};

const saveProfile = () => {
    form.transform((data) => ({
        ...data,
        age: data.age ? parseInt(data.age) : null,
        marriage_type: data.marriage_type ? parseInt(data.marriage_type) : null,
        marriage_status: data.marriage_status ? parseInt(data.marriage_status) : null,
        child_count: data.child_count ? parseInt(data.child_count) : null,
        residence: data.residence ? parseInt(data.residence) : null,
        nationality: data.nationality ? parseInt(data.nationality) : null,
        city: data.city ? parseInt(data.city) : null,
        skin_color: data.skin_color ? parseInt(data.skin_color) : null,
        body_shape: data.body_shape ? parseInt(data.body_shape) : null,
        devotion: data.devotion ? parseInt(data.devotion) : null,
        prayer: data.prayer ? parseInt(data.prayer) : null,
        smoking: data.smoking === '1',
        beard: data.beard === '1',
        education_level: data.education_level ? parseInt(data.education_level) : null,
        financial_status: data.financial_status ? parseInt(data.financial_status) : null,
        field_of_work: data.field_of_work ? parseInt(data.field_of_work) : null,
        health_status: data.health_status ? parseInt(data.health_status) : null,
        monthly_income: data.monthly_income ? parseInt(data.monthly_income) : null,
    })).submit(update(), {
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};

// Reset Password
const showResetPasswordModal = ref(false);
const resetPasswordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const openResetPasswordModal = () => {
    showResetPasswordModal.value = true;
};

const closeResetPasswordModal = () => {
    showResetPasswordModal.value = false;
    resetPasswordForm.reset();
    resetPasswordForm.clearErrors();
};

const submitResetPassword = () => {
    resetPasswordForm.submit(ResetPasswordController(), {
        onSuccess: () => {
            closeResetPasswordModal();
        },
    });
};

// Delete Account
const showDeleteModal = ref(false);
const deleteForm = useForm({
    reason: '' as string | number,
    message: '',
});

const openDeleteModal = () => {
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteForm.reset();
};

const submitDeleteAccount = () => {
    deleteForm.submit(DeleteAccountController(), {
        onSuccess: () => {
            closeDeleteModal();
        },
    });
};
</script>

<template>
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-lg-0 py-3">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center me-3">
                        <h1
                            class="page-heading d-flex fw-bold fs-3 flex-column justify-content-center my-0 text-gray-900">
                            {{ trans('profile.profile') }}
                        </h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="/" class="text-muted text-hover-primary">{{ trans('home.home') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet w-5px h-2px bg-gray-500"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">{{ trans('profile.profile') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <!-- Profile Header -->
                    <div class="card mb-xl-10 mb-5">
                        <div class="card-body pt-9 pb-0">
                            <div class="d-flex flex-sm-nowrap flex-wrap">
                                <div class="me-7 mb-4">
                                    <div class="symbol symbol-100px symbol-lg-100px symbol-fixed position-relative">
                                        <img 
                                        style="object-fit: cover;"
                                        :src="$page.props.auth?.profile_image || '/assets/media/auth/no-image-for-user.png'" alt="image" />
                                        <div
                                            class="translate-middle bg-success rounded-circle border-body
                                             h-20px w-20px bottom-0 mb-6 border border-4 start-100 top-100">
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex align-items-center mb-2">
                                                <a href="#" class="text-hover-primary fs-2 fw-bold me-1 text-gray-900">
                                                    {{ user.name }}
                                                </a>
                                                <a href="#"><i class="ki-outline ki-verify fs-1 text-primary"></i></a>
                                            </div>
                                            <div class="d-flex fw-semibold fs-6 mb-4 flex-wrap pe-2">
                                                <a href="#"
                                                    class="d-flex align-items-center text-hover-primary me-5 mb-2 text-gray-500">
                                                    <i class="ki-outline ki-profile-circle fs-4 me-1"></i>{{
                                                    user.username }}
                                                </a>
                                                <a href="#"
                                                    class="d-flex align-items-center text-hover-primary me-5 mb-2 text-gray-500">
                                                    <i class="ki-outline ki-geolocation fs-4 me-1"></i>{{
                                                    user.residence.name }}
                                                </a>
                                                <a href="#" v-if="user.email"
                                                    class="d-flex align-items-center text-hover-primary mb-2 text-gray-500">
                                                    <i class="ki-outline ki-sms fs-4"></i>{{ user.email }}
                                                </a>
                                            </div>
                                        </div>
                                        <div class="d-flex my-4">
                                            <button v-if="!isEditing" @click="toggleEdit"
                                                class="btn btn-sm btn-primary me-2">
                                                <i class="ki-outline ki-pencil fs-3"></i>
                                                {{ trans('profile.edit_profile') }}
                                            </button>
                                            <template v-else>
                                                <button @click="saveProfile" :disabled="form.processing"
                                                    class="btn btn-sm btn-success me-2">
                                                    <i class="ki-outline ki-check fs-3"></i>
                                                    {{ trans('profile.save_profile') }}
                                                </button>
                                                <button @click="toggleEdit" :disabled="form.processing"
                                                    class="btn btn-sm btn-light-danger">
                                                    <i class="ki-outline ki-cross fs-3"></i>
                                                    {{ trans('profile.cancel') }}
                                                </button>
                                            </template>
                                            <button v-if="!isEditing" @click="openResetPasswordModal"
                                                class="btn btn-sm btn-light-warning me-2">
                                                <i class="ki-outline ki-lock-2 fs-3"></i>
                                                {{ trans('profile.reset_password') }}
                                            </button>
                                            <button v-if="!isEditing" @click="openDeleteModal"
                                                class="btn btn-sm btn-light-danger">
                                                <i class="ki-outline ki-trash fs-3"></i>
                                                {{ trans('profile.delete_account') }}
                                            </button>
                                        </div>
                                    </div>
                                    <!--                                    <div class="d-flex flex-stack flex-wrap">-->
                                    <!--                                        <div class="d-flex align-items-center w-200px w-sm-300px flex-column mt-3">-->
                                    <!--                                            <div class="d-flex justify-content-between mt-auto mb-2 w-100">-->
                                    <!--                                                <span class="fw-semibold fs-6 text-gray-500">Profile Completion</span>-->
                                    <!--                                                <span class="fw-bold fs-6">50%</span>-->
                                    <!--                                            </div>-->
                                    <!--                                            <div class="h-5px bg-light mx-3 mb-3 w-100">-->
                                    <!--                                                <div-->
                                    <!--                                                    class="bg-success h-5px rounded"-->
                                    <!--                                                    role="progressbar"-->
                                    <!--                                                    style="width: 50%"-->
                                    <!--                                                ></div>-->
                                    <!--                                            </div>-->
                                    <!--                                        </div>-->
                                    <!--                                    </div>-->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account & Location Details -->
                    <div class="d-flex col-12 gap-2 flex-wrap flex-lg-nowrap">
                        <ProfileSection :title="trans('profile.account_details')" class="col-12 col-lg-6">
                            <ProfileField :label="trans('profile.username')" :value="form.username"
                                :is-editing="false"
                                :error="form.errors.username" />
                            <ProfileField :label="trans('profile.email')" type="email" :value="form.email"
                                @update:value="(v) => (form.email = v)" :is-editing="isEditing"
                                :error="form.errors.email" />
                            <ProfileField :label="trans('profile.phone_number')" :value="form.phone_number"
                                @update:value="(v) => (form.phone_number = v)" :is-editing="isEditing"
                                :error="form.errors.phone_number" />
                        </ProfileSection>

                            <ProfileSection :title="trans('profile.location_details')" class="col-12 col-lg-6">
                            <ProfileField :label="trans('profile.nationality')" type="select" :value="form.nationality"
                                :display-value="countryDisplayName(user.nationality)" @update:value="(v) => (form.nationality = v)"
                                :is-editing="isEditing" :options="countryOptions" :error="form.errors.nationality" />
                            <ProfileField :label="trans('profile.residence')" type="select" :value="form.residence"
                                :display-value="countryDisplayName(user.residence)" @update:value="(v) => (form.residence = v)"
                                :is-editing="isEditing" :options="countryOptions" :error="form.errors.residence" />
                            <ProfileField :label="trans('profile.city')" type="select" :value="form.city"
                                :display-value="cityDisplayName(user.city)" @update:value="(v) => (form.city = v)"
                                :is-editing="isEditing" :options="cityOptions" :error="form.errors.city" />
                        </ProfileSection>
                    </div>

                    <!-- Personal Details -->
                    <ProfileSection :title="trans('profile.personal_details')">
                        <div class="row">
                            <ProfileField :label="trans('profile.age')" type="select" :value="form.age"
                                @update:value="(v) => (form.age = v)" :is-editing="isEditing" :options="ageRange"
                                :error="form.errors.age" col-span="half" />
                            <ProfileField :label="trans('profile.child_count')" type="select" :value="form.child_count"
                                @update:value="(v) => (form.child_count = v)" :is-editing="isEditing"
                                :options="childCounts" :error="form.errors.child_count" col-span="half" />
                        </div>
                        <div class="row">
                            <ProfileField :label="trans('profile.weight') + ' (kg)'" type="number" :value="form.weight"
                                @update:value="(v) => (form.weight = v)" :is-editing="isEditing"
                                :error="form.errors.weight" col-span="half" />
                            <ProfileField :label="trans('profile.height') + ' (cm)'" type="number" :value="form.height"
                                @update:value="(v) => (form.height = v)" :is-editing="isEditing"
                                :error="form.errors.height" col-span="half" />
                        </div>
                        <div class="row">
                            <ProfileField :label="trans('profile.skin_color')" type="select" :value="form.skin_color"
                                :display-value="user.skin_color_label" @update:value="(v) => (form.skin_color = v)"
                                :is-editing="isEditing" :options="skin_colors" :error="form.errors.skin_color"
                                col-span="half" />
                            <ProfileField :label="trans('profile.body_shape')" type="select" :value="form.body_shape"
                                :display-value="user.body_shape_label" @update:value="(v) => (form.body_shape = v)"
                                :is-editing="isEditing" :options="body_shapes" :error="form.errors.body_shape"
                                col-span="half" />
                        </div>
                        <div class="row">
                            <ProfileField :label="trans('profile.marriage_status')" type="select" :value="form.marriage_status"
                                :display-value="user.marriage_status_label"
                                @update:value="(v) => (form.marriage_status = v)" :is-editing="isEditing"
                                :options="marriage_statuses" :error="form.errors.marriage_status" col-span="half" />
                            <ProfileField :label="trans('profile.marriage_type')" type="select" :value="form.marriage_type"
                                :display-value="user.marriage_type_label"
                                @update:value="(v) => (form.marriage_type = v)" :is-editing="isEditing"
                                :options="marriage_types" :error="form.errors.marriage_type" col-span="half" />
                        </div>
                    </ProfileSection>

                    <!-- Religious & Social Details -->
                    <ProfileSection :title="trans('profile.religious_and_social_details')">
                        <div class="row">
                            <ProfileField :label="trans('profile.religion')" :value="trans('profile.muslim')" :is-editing="false" col-span="half" />
                            <ProfileField :label="trans('profile.prayer')" type="select" :value="form.prayer"
                                :display-value="user.prayer_label" @update:value="(v) => (form.prayer = v)"
                                :is-editing="isEditing" :options="prayer_commitments" :error="form.errors.prayer"
                                col-span="half" />
                        </div>
                        <div class="row">
                            <ProfileField :label="trans('profile.devotion')" type="select" :value="form.devotion"
                                :display-value="user.devotion_label" @update:value="(v) => (form.devotion = v)"
                                :is-editing="isEditing" :options="devotions" :error="form.errors.devotion"
                                col-span="half" />
                        </div>
                        <div class="row">
                            <ProfileField :label="trans('profile.smoking')" type="select" :value="form.smoking"
                                :display-value="user.smoking ? trans('enums.yes') : trans('enums.no')" @update:value="(v) => (form.smoking = v)"
                                :is-editing="isEditing" :options="yes_no_options" :error="form.errors.smoking"
                                col-span="half" />
                            <ProfileField v-if="user.registration_type === 1" :label="trans('profile.beard')" type="select"
                                :value="form.beard" :display-value="user.beard ? trans('enums.yes') : trans('enums.no')"
                                @update:value="(v) => (form.beard = v)" :is-editing="isEditing"
                                :options="yes_no_options" :error="form.errors.beard" col-span="half" />
                        </div>
                    </ProfileSection>

                    <!-- Professional Details -->
                    <ProfileSection :title="trans('profile.professional_details')">
                        <div class="row">
                            <ProfileField :label="trans('profile.education_level')" type="select" :value="form.education_level"
                                :display-value="user.education_level_label"
                                @update:value="(v) => (form.education_level = v)" :is-editing="isEditing"
                                :options="education_levels" :error="form.errors.education_level" col-span="half" />
                            <ProfileField :label="trans('profile.financial_status')" type="select" :value="form.financial_status"
                                :display-value="user.financial_status_label"
                                @update:value="(v) => (form.financial_status = v)" :is-editing="isEditing"
                                :options="financial_statuses" :error="form.errors.financial_status" col-span="half" />
                        </div>
                        <div class="row">
                            <ProfileField :label="trans('profile.field_of_work')" type="select" :value="form.field_of_work"
                                :display-value="user.field_of_work_label"
                                @update:value="(v) => (form.field_of_work = v)" :is-editing="isEditing"
                                :options="fields_of_work" :error="form.errors.field_of_work" col-span="half" />
                            <ProfileField :label="trans('profile.job_title')" :value="form.job" @update:value="(v) => (form.job = v)"
                                :is-editing="isEditing" :error="form.errors.job" col-span="half" />
                        </div>
                        <div class="row">
                            <ProfileField :label="trans('profile.monthly_income')" type="select" :value="form.monthly_income"
                                :display-value="user.monthly_income_label"
                                @update:value="(v) => (form.monthly_income = v)" :is-editing="isEditing"
                                :options="monthly_incomes" :error="form.errors.monthly_income" col-span="half" />
                            <ProfileField :label="trans('profile.health_status')" type="select" :value="form.health_status"
                                :display-value="user.health_status_label"
                                @update:value="(v) => (form.health_status = v)" :is-editing="isEditing"
                                :options="health_statuses" :error="form.errors.health_status" col-span="half" />
                        </div>
                    </ProfileSection>

                    <!-- About Section -->
                        <ProfileSection :title="trans('profile.about')">
                        <ProfileField :label="trans('profile.about_yourself')" type="textarea" :value="form.about_self"
                            @update:value="(v) => (form.about_self = v)" :is-editing="isEditing"
                            :error="form.errors.about_self" />
                        <ProfileField :label="trans('profile.about_your_ideal_partner')" type="textarea" :value="form.about_partner"
                            @update:value="(v) => (form.about_partner = v)" :is-editing="isEditing"
                            :error="form.errors.about_partner" />
                    </ProfileSection>
                </div>
            </div>
        </div>

        <!-- Reset Password Modal -->
        <div v-if="showResetPasswordModal" class="modal fade show d-block" tabindex="-1"
            style="background-color: rgba(0, 0, 0, 0.5)">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-warning">
                            <i class="ki-outline ki-lock-2 fs-2 text-warning me-2"></i>
                            {{ trans('profile.reset_password') }}
                        </h5>
                        <button type="button" class="btn-close" @click="closeResetPasswordModal"></button>
                    </div>
                    <form @submit.prevent="submitResetPassword">
                        <div class="modal-body">
                            <div class="alert alert-warning d-flex align-items-center p-5 mb-5">
                                <i class="ki-outline ki-information-5 fs-2hx text-warning me-4"></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-warning">{{ trans('profile.notice') }}</h4>
                                    <span>{{ trans('profile.you_will_be_logged_out_after_resetting_your_password_please_log_in_again_with_your_new_password') }}</span>
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="form-label required">{{ trans('profile.current_password') }}</label>
                                <input v-model="resetPasswordForm.current_password" type="password" class="form-control"
                                    :class="{ 'is-invalid': resetPasswordForm.errors.current_password }"
                                    :placeholder="trans('profile.enter_your_current_password')" />
                                <div v-if="resetPasswordForm.errors.current_password" class="invalid-feedback">
                                    {{ resetPasswordForm.errors.current_password }}
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="form-label required">{{ trans('profile.new_password') }}</label>
                                <input v-model="resetPasswordForm.password" type="password" class="form-control"
                                    :class="{ 'is-invalid': resetPasswordForm.errors.password }"
                                    :placeholder="trans('profile.enter_your_new_password')" />
                                <div v-if="resetPasswordForm.errors.password" class="invalid-feedback">
                                    {{ resetPasswordForm.errors.password }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">{{ trans('profile.confirm_new_password') }}</label>
                                <input v-model="resetPasswordForm.password_confirmation" type="password"
                                    class="form-control"
                                    :class="{ 'is-invalid': resetPasswordForm.errors.password_confirmation }"
                                    :placeholder="trans('profile.re_enter_your_new_password')" />
                                <div v-if="resetPasswordForm.errors.password_confirmation" class="invalid-feedback">
                                    {{ resetPasswordForm.errors.password_confirmation }}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" @click="closeResetPasswordModal">{{ trans('profile.cancel') }}</button>
                            <button type="submit" class="btn btn-warning"
                                :disabled="resetPasswordForm.processing || !resetPasswordForm.current_password || !resetPasswordForm.password || !resetPasswordForm.password_confirmation">
                                <span v-if="resetPasswordForm.processing"
                                    class="spinner-border spinner-border-sm me-2"></span>
                                {{ trans('profile.reset_password') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Account Modal -->
        <div v-if="showDeleteModal" class="modal fade show d-block" tabindex="-1"
            style="background-color: rgba(0, 0, 0, 0.5)">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">
                            <i class="ki-outline ki-information-5 fs-2 text-danger me-2"></i>
                            {{ trans('profile.delete_account') }}
                        </h5>
                        <button type="button" class="btn-close" @click="closeDeleteModal"></button>
                    </div>
                    <form @submit.prevent="submitDeleteAccount">
                        <div class="modal-body">
                            <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
                                <i class="ki-outline ki-shield-cross fs-2hx text-danger me-4"></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-danger">{{ trans('profile.warning') }}</h4>
                                    <span>{{ trans('profile.this_action_is_permanent_your_account_and_all_associated_data_will_be_deleted') }}</span>
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="form-label required">{{ trans('profile.why_are_you_deleting_your_account') }}</label>
                                <select v-model="deleteForm.reason" class="form-select"
                                    :class="{ 'is-invalid': deleteForm.errors.reason }">
                                    <option value="" disabled>{{ trans('profile.select_a_reason') }}</option>
                                    <option v-for="reason in props.delete_account_reasons" :key="reason.value"
                                        :value="reason.value">
                                        {{ reason.label }}
                                    </option>
                                </select>
                                <div v-if="deleteForm.errors.reason" class="invalid-feedback">
                                    {{ deleteForm.errors.reason }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ trans('profile.additional_feedback_optional') }}</label>
                                <textarea v-model="deleteForm.message" class="form-control"
                                    :class="{ 'is-invalid': deleteForm.errors.message }" rows="4"
                                    :placeholder="trans('profile.tell_us_more_about_why_you_re_leaving')" maxlength="1000"></textarea>
                                <div v-if="deleteForm.errors.message" class="invalid-feedback">
                                    {{ deleteForm.errors.message }}
                                </div>
                                <div class="form-text text-end">
                                    {{ deleteForm.message?.length || 0 }}/1000
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" @click="closeDeleteModal">{{ trans('profile.cancel') }}</button>
                            <button type="submit" class="btn btn-danger"
                                :disabled="deleteForm.processing || !deleteForm.reason">
                                <span v-if="deleteForm.processing" class="spinner-border spinner-border-sm me-2"></span>
                                {{ trans('profile.delete_my_account') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
