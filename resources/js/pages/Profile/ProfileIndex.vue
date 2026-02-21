<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import ProfileSection from '@/components/Profile/ProfileSection.vue';
import ProfileField from '@/components/Profile/ProfileField.vue';
import { update } from '@/actions/App/Http/Controllers/ProfileController';
import DeleteAccountController from '@/actions/App/Http/Controllers/DeleteAccountController';

type SelectOption = { value: string; label: string };

interface Props {
    countries: Array<{ id: number; name: string; ar_name: string; flag: string }>;
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
    delete_account_reasons: SelectOption[];
}

const props = defineProps<Props>();

const page = usePage();
const user = computed(() => page.props.auth.user);

const isEditing = ref(false);

const toStr = (val: unknown): string => (val !== null && val !== undefined ? String(val) : '');

const form = useForm({
    username: user.value.username,
    email: user.value.email,
    phone_number: user.value.phone_number,
    age: toStr(user.value.age),
    marriage_type: toStr(user.value.marriage_type),
    marriage_status: toStr(user.value.marriage_status),
    child_count: toStr(user.value.child_count),
    residence: toStr(user.value.residence?.id),
    nationality: toStr(user.value.nationality?.id),
    city: user.value.city,
    weight: user.value.weight,
    height: user.value.height,
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
    props.countries.map((c) => ({ value: c.id.toString(), label: c.name }))
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
    })).submit(update(), {
        onSuccess: () => {
            isEditing.value = false;
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
                        <h1 class="page-heading d-flex fw-bold fs-3 flex-column justify-content-center my-0 text-gray-900">
                            Account Overview
                        </h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="/" class="text-muted text-hover-primary">Home</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet w-5px h-2px bg-gray-500"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Account</li>
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
                                    <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                                        <img :src="$page.props.auth?.profile_image" alt="image" />
                                        <div
                                            class="position-absolute translate-middle bg-success rounded-circle border-body h-20px w-20px start-100 bottom-0 mb-6 border border-4"
                                        ></div>
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
                                                <a href="#" class="d-flex align-items-center text-hover-primary me-5 mb-2 text-gray-500">
                                                    <i class="ki-outline ki-profile-circle fs-4 me-1"></i>{{ user.username }}
                                                </a>
                                                <a href="#" class="d-flex align-items-center text-hover-primary me-5 mb-2 text-gray-500">
                                                    <i class="ki-outline ki-geolocation fs-4 me-1"></i
                                                    >{{ user.residence.name }}
                                                </a>
                                                <a href="#" class="d-flex align-items-center text-hover-primary mb-2 text-gray-500">
                                                    <i class="ki-outline ki-sms fs-4"></i>{{ user.email }}
                                                </a>
                                            </div>
                                        </div>
                                        <div class="d-flex my-4">
                                            <button
                                                v-if="!isEditing"
                                                @click="toggleEdit"
                                                class="btn btn-sm btn-primary me-2"
                                            >
                                                <i class="ki-outline ki-pencil fs-3"></i>
                                                Edit Profile
                                            </button>
                                            <template v-else>
                                                <button
                                                    @click="saveProfile"
                                                    :disabled="form.processing"
                                                    class="btn btn-sm btn-success me-2"
                                                >
                                                    <i class="ki-outline ki-check fs-3"></i>
                                                    Save Changes
                                                </button>
                                                <button
                                                    @click="toggleEdit"
                                                    :disabled="form.processing"
                                                    class="btn btn-sm btn-light-danger"
                                                >
                                                    <i class="ki-outline ki-cross fs-3"></i>
                                                    Cancel
                                                </button>
                                            </template>
                                            <button
                                                v-if="!isEditing"
                                                @click="openDeleteModal"
                                                class="btn btn-sm btn-light-danger"
                                            >
                                                <i class="ki-outline ki-trash fs-3"></i>
                                                Delete Account
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
                        <ProfileSection title="Account Details" class="col-12 col-lg-6">
                            <ProfileField
                                label="Username"
                                :value="form.username"
                                @update:value="(v) => (form.username = v)"
                                :is-editing="isEditing"
                                :error="form.errors.username"
                            />
                            <ProfileField
                                label="Email"
                                type="email"
                                :value="form.email"
                                @update:value="(v) => (form.email = v)"
                                :is-editing="isEditing"
                                :error="form.errors.email"
                            />
                            <ProfileField
                                label="Phone Number"
                                :value="form.phone_number"
                                @update:value="(v) => (form.phone_number = v)"
                                :is-editing="isEditing"
                                :error="form.errors.phone_number"
                            />
                        </ProfileSection>

                        <ProfileSection title="Location Details" class="col-12 col-lg-6">
                            <ProfileField
                                label="Nationality"
                                type="select"
                                :value="form.nationality"
                                :display-value="user.nationality.name"
                                @update:value="(v) => (form.nationality = v)"
                                :is-editing="isEditing"
                                :options="countryOptions"
                                :error="form.errors.nationality"
                            />
                            <ProfileField
                                label="Residence"
                                type="select"
                                :value="form.residence"
                                :display-value="user.residence.name"
                                @update:value="(v) => (form.residence = v)"
                                :is-editing="isEditing"
                                :options="countryOptions"
                                :error="form.errors.residence"
                            />
                            <ProfileField
                                label="City"
                                :value="form.city"
                                @update:value="(v) => (form.city = v)"
                                :is-editing="isEditing"
                                :error="form.errors.city"
                            />
                        </ProfileSection>
                    </div>

                    <!-- Personal Details -->
                    <ProfileSection title="Personal Details">
                        <div class="row">
                            <ProfileField
                                label="Age"
                                type="select"
                                :value="form.age"
                                @update:value="(v) => (form.age = v)"
                                :is-editing="isEditing"
                                :options="ageRange"
                                :error="form.errors.age"
                                col-span="half"
                            />
                            <ProfileField
                                label="Child Count"
                                type="select"
                                :value="form.child_count"
                                @update:value="(v) => (form.child_count = v)"
                                :is-editing="isEditing"
                                :options="childCounts"
                                :error="form.errors.child_count"
                                col-span="half"
                            />
                        </div>
                        <div class="row">
                            <ProfileField
                                label="Weight (kg)"
                                type="number"
                                :value="form.weight"
                                @update:value="(v) => (form.weight = v)"
                                :is-editing="isEditing"
                                :error="form.errors.weight"
                                col-span="half"
                            />
                            <ProfileField
                                label="Height (cm)"
                                type="number"
                                :value="form.height"
                                @update:value="(v) => (form.height = v)"
                                :is-editing="isEditing"
                                :error="form.errors.height"
                                col-span="half"
                            />
                        </div>
                        <div class="row">
                            <ProfileField
                                label="Skin Color"
                                type="select"
                                :value="form.skin_color"
                                :display-value="user.skin_color_label"
                                @update:value="(v) => (form.skin_color = v)"
                                :is-editing="isEditing"
                                :options="skin_colors"
                                :error="form.errors.skin_color"
                                col-span="half"
                            />
                            <ProfileField
                                label="Body Shape"
                                type="select"
                                :value="form.body_shape"
                                :display-value="user.body_shape_label"
                                @update:value="(v) => (form.body_shape = v)"
                                :is-editing="isEditing"
                                :options="body_shapes"
                                :error="form.errors.body_shape"
                                col-span="half"
                            />
                        </div>
                        <div class="row">
                            <ProfileField
                                label="Marriage Status"
                                type="select"
                                :value="form.marriage_status"
                                :display-value="user.marriage_status_label"
                                @update:value="(v) => (form.marriage_status = v)"
                                :is-editing="isEditing"
                                :options="marriage_statuses"
                                :error="form.errors.marriage_status"
                                col-span="half"
                            />
                            <ProfileField
                                label="Marriage Type"
                                type="select"
                                :value="form.marriage_type"
                                :display-value="user.marriage_type_label"
                                @update:value="(v) => (form.marriage_type = v)"
                                :is-editing="isEditing"
                                :options="marriage_types"
                                :error="form.errors.marriage_type"
                                col-span="half"
                            />
                        </div>
                    </ProfileSection>

                    <!-- Religious & Social Details -->
                    <ProfileSection title="Religious & Social Details">
                        <div class="row">
                            <ProfileField
                                label="Religion"
                                :value="user.religion"
                                :is-editing="false"
                                col-span="half"
                            />
                            <ProfileField
                                label="Prayer"
                                type="select"
                                :value="form.prayer"
                                :display-value="user.prayer_label"
                                @update:value="(v) => (form.prayer = v)"
                                :is-editing="isEditing"
                                :options="prayer_commitments"
                                :error="form.errors.prayer"
                                col-span="half"
                            />
                        </div>
                        <div class="row">
                            <ProfileField
                                label="Devotion"
                                type="select"
                                :value="form.devotion"
                                :display-value="user.devotion_label"
                                @update:value="(v) => (form.devotion = v)"
                                :is-editing="isEditing"
                                :options="devotions"
                                :error="form.errors.devotion"
                                col-span="half"
                            />
                        </div>
                        <div class="row">
                            <ProfileField
                                label="Smoking"
                                type="select"
                                :value="form.smoking"
                                :display-value="user.smoking ? 'Yes' : 'No'"
                                @update:value="(v) => (form.smoking = v)"
                                :is-editing="isEditing"
                                :options="yes_no_options"
                                :error="form.errors.smoking"
                                col-span="half"
                            />
                            <ProfileField
                                v-if="user.registration_type === 1"
                                label="Beard"
                                type="select"
                                :value="form.beard"
                                :display-value="user.beard ? 'Yes' : 'No'"
                                @update:value="(v) => (form.beard = v)"
                                :is-editing="isEditing"
                                :options="yes_no_options"
                                :error="form.errors.beard"
                                col-span="half"
                            />
                        </div>
                    </ProfileSection>

                    <!-- Professional Details -->
                    <ProfileSection title="Professional Details">
                        <div class="row">
                            <ProfileField
                                label="Education Level"
                                type="select"
                                :value="form.education_level"
                                :display-value="user.education_level_label"
                                @update:value="(v) => (form.education_level = v)"
                                :is-editing="isEditing"
                                :options="education_levels"
                                :error="form.errors.education_level"
                                col-span="half"
                            />
                            <ProfileField
                                label="Financial Status"
                                type="select"
                                :value="form.financial_status"
                                :display-value="user.financial_status_label"
                                @update:value="(v) => (form.financial_status = v)"
                                :is-editing="isEditing"
                                :options="financial_statuses"
                                :error="form.errors.financial_status"
                                col-span="half"
                            />
                        </div>
                        <div class="row">
                            <ProfileField
                                label="Field of Work"
                                type="select"
                                :value="form.field_of_work"
                                :display-value="user.field_of_work_label"
                                @update:value="(v) => (form.field_of_work = v)"
                                :is-editing="isEditing"
                                :options="fields_of_work"
                                :error="form.errors.field_of_work"
                                col-span="half"
                            />
                            <ProfileField
                                label="Job Title"
                                :value="form.job"
                                @update:value="(v) => (form.job = v)"
                                :is-editing="isEditing"
                                :error="form.errors.job"
                                col-span="half"
                            />
                        </div>
                        <div class="row">
                            <ProfileField
                                label="Monthly Income"
                                type="number"
                                :value="form.monthly_income"
                                @update:value="(v) => (form.monthly_income = v)"
                                :is-editing="isEditing"
                                :error="form.errors.monthly_income"
                                col-span="half"
                            />
                            <ProfileField
                                label="Health Status"
                                type="select"
                                :value="form.health_status"
                                :display-value="user.health_status_label"
                                @update:value="(v) => (form.health_status = v)"
                                :is-editing="isEditing"
                                :options="health_statuses"
                                :error="form.errors.health_status"
                                col-span="half"
                            />
                        </div>
                    </ProfileSection>

                    <!-- About Section -->
                    <ProfileSection title="About">
                        <ProfileField
                            label="About Yourself"
                            type="textarea"
                            :value="form.about_self"
                            @update:value="(v) => (form.about_self = v)"
                            :is-editing="isEditing"
                            :error="form.errors.about_self"
                        />
                        <ProfileField
                            label="About Your Ideal Partner"
                            type="textarea"
                            :value="form.about_partner"
                            @update:value="(v) => (form.about_partner = v)"
                            :is-editing="isEditing"
                            :error="form.errors.about_partner"
                        />
                    </ProfileSection>
                </div>
            </div>
        </div>

        <!-- Delete Account Modal -->
        <div
            v-if="showDeleteModal"
            class="modal fade show d-block"
            tabindex="-1"
            style="background-color: rgba(0, 0, 0, 0.5)"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">
                            <i class="ki-outline ki-information-5 fs-2 text-danger me-2"></i>
                            Delete Account
                        </h5>
                        <button type="button" class="btn-close" @click="closeDeleteModal"></button>
                    </div>
                    <form @submit.prevent="submitDeleteAccount">
                        <div class="modal-body">
                            <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
                                <i class="ki-outline ki-shield-cross fs-2hx text-danger me-4"></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-danger">Warning</h4>
                                    <span>This action is permanent. Your account and all associated data will be deleted.</span>
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="form-label required">Why are you deleting your account?</label>
                                <select
                                    v-model="deleteForm.reason"
                                    class="form-select"
                                    :class="{ 'is-invalid': deleteForm.errors.reason }"
                                >
                                    <option value="" disabled>Select a reason</option>
                                    <option
                                        v-for="reason in props.delete_account_reasons"
                                        :key="reason.value"
                                        :value="reason.value"
                                    >
                                        {{ reason.label }}
                                    </option>
                                </select>
                                <div v-if="deleteForm.errors.reason" class="invalid-feedback">
                                    {{ deleteForm.errors.reason }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Additional feedback (optional)</label>
                                <textarea
                                    v-model="deleteForm.message"
                                    class="form-control"
                                    :class="{ 'is-invalid': deleteForm.errors.message }"
                                    rows="4"
                                    placeholder="Tell us more about why you're leaving..."
                                    maxlength="1000"
                                ></textarea>
                                <div v-if="deleteForm.errors.message" class="invalid-feedback">
                                    {{ deleteForm.errors.message }}
                                </div>
                                <div class="form-text text-end">
                                    {{ deleteForm.message?.length || 0 }}/1000
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" @click="closeDeleteModal">Cancel</button>
                            <button
                                type="submit"
                                class="btn btn-danger"
                                :disabled="deleteForm.processing || !deleteForm.reason"
                            >
                                <span v-if="deleteForm.processing" class="spinner-border spinner-border-sm me-2"></span>
                                Delete My Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
