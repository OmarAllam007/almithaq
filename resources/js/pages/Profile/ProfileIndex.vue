<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import ProfileSection from '@/components/Profile/ProfileSection.vue';
import ProfileField from '@/components/Profile/ProfileField.vue';
import { update } from '@/actions/App/Http/Controllers/ProfileController';
import DeleteAccountController from '@/actions/App/Http/Controllers/DeleteAccountController';

interface Props {
    countries: Array<{ id: number; name: string; ar_name: string; flag: string }>;
    devotions: string[];
    prayer_commitments: string[];
    yes_no_list: string[];
    education_levels: string[];
    financial_statuses: string[];
    fields_of_work: string[];
    delete_account_reasons: Array<{ value: number; label: string }>;
}

const props = defineProps<Props>();

const page = usePage();
const user = computed(() => page.props.auth.user);

const isEditing = ref(false);

const form = useForm({
    username: user.value.username,
    email: user.value.email,
    phone_number: user.value.phone_number,
    age: user.value.age,
    marriage_type: user.value.marriage_type,
    marriage_status: user.value.marriage_status,
    child_count: user.value.child_count,
    residence: user.value.residence,
    nationality: user.value.nationality,
    city: user.value.city,
    weight: user.value.weight,
    height: user.value.height,
    skin_color: user.value.skin_color,
    body_shape: user.value.body_shape,
    religion: user.value.religion,
    ethnicity: user.value.ethnicity,
    devotion: user.value.devotion,
    prayer: user.value.prayer,
    smoking: user.value.smoking,
    beard: user.value.beard,
    education_level: user.value.education_level,
    financial_status: user.value.financial_status,
    field_of_work: user.value.field_of_work,
    job: user.value.job,
    monthly_income: user.value.monthly_income,
    health_status: user.value.health_status,
    about_partner: user.value.about_partner,
    about_self: user.value.about_self,
    full_name: user.value.full_name,
});

const marriageTypes = ['First wife', 'Second wife', 'Only one wife', 'Accept polygamy'];
const marriageStatuses = ['Single', 'Divorced', 'Widowed', 'Married'];
const ageRange = Array.from({ length: 73 }, (_, i) => i + 18);
const childCounts = Array.from({ length: 11 }, (_, i) => i);
const skinColors = ['White', 'Light Brown', 'Brown', 'Dark Brown', 'Black'];
const bodyShapes = ['Slim', 'Sporty', 'Average', 'Muscular', 'Overweight'];

const countryOptions = computed(() =>
    props.countries.map((c) => ({ value: c.id.toString(), label: c.name }))
);

const devotionOptions = computed(() =>
    props.devotions.map((d, index) => ({ value: index.toString(), label: d }))
);

const prayerOptions = computed(() =>
    props.prayer_commitments.map((p, index) => ({ value: index.toString(), label: p }))
);

const yesNoOptions = computed(() =>
    props.yes_no_list.map((yn, index) => ({ value: index.toString(), label: yn }))
);

const educationOptions = computed(() =>
    props.education_levels.map((e, index) => ({ value: index.toString(), label: e }))
);

const financialOptions = computed(() =>
    props.financial_statuses.map((f, index) => ({ value: index.toString(), label: f }))
);

const workFieldOptions = computed(() =>
    props.fields_of_work.map((w, index) => ({ value: index.toString(), label: w }))
);

const toggleEdit = () => {
    if (isEditing.value) {
        // Cancel edit - reset form
        form.reset();
    }
    isEditing.value = !isEditing.value;
};

const saveProfile = () => {
    form.submit(update(), {
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};

const getCountryName = (id: number | string) => {
    const country = props.countries.find((c) => c.id === Number(id));
    return country?.name || id;
};

const getOptionLabel = (options: string[], value: number | string) => {
    return options[Number(value)] || value;
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
                                                    >{{ getCountryName(user.residence.name) }}
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
                                    <div class="d-flex flex-stack flex-wrap">
                                        <div class="d-flex align-items-center w-200px w-sm-300px flex-column mt-3">
                                            <div class="d-flex justify-content-between mt-auto mb-2 w-100">
                                                <span class="fw-semibold fs-6 text-gray-500">Profile Completion</span>
                                                <span class="fw-bold fs-6">50%</span>
                                            </div>
                                            <div class="h-5px bg-light mx-3 mb-3 w-100">
                                                <div
                                                    class="bg-success h-5px rounded"
                                                    role="progressbar"
                                                    style="width: 50%"
                                                ></div>
                                            </div>
                                        </div>
                                    </div>
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
                                :value="isEditing ? form.nationality : getCountryName(user.nationality.name)"
                                @update:value="(v) => (form.nationality = v)"
                                :is-editing="isEditing"
                                :options="countryOptions"
                                :error="form.errors.nationality"
                            />
                            <ProfileField
                                label="Residence"
                                type="select"
                                :value="isEditing ? form.residence : getCountryName(user.residence.name)"
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
                                :options="ageRange.map((a) => ({ value: a.toString(), label: a.toString() }))"
                                :error="form.errors.age"
                                col-span="half"
                            />
                            <ProfileField
                                label="Child Count"
                                type="select"
                                :value="form.child_count"
                                @update:value="(v) => (form.child_count = v)"
                                :is-editing="isEditing"
                                :options="childCounts.map((c) => ({ value: c.toString(), label: c.toString() }))"
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
                                @update:value="(v) => (form.skin_color = v)"
                                :is-editing="isEditing"
                                :options="skinColors.map((s) => ({ value: s, label: s }))"
                                :error="form.errors.skin_color"
                                col-span="half"
                            />
                            <ProfileField
                                label="Body Shape"
                                type="select"
                                :value="form.body_shape"
                                @update:value="(v) => (form.body_shape = v)"
                                :is-editing="isEditing"
                                :options="bodyShapes.map((b) => ({ value: b, label: b }))"
                                :error="form.errors.body_shape"
                                col-span="half"
                            />
                        </div>
                        <div class="row">
                            <ProfileField
                                label="Marriage Status"
                                type="select"
                                :value="form.marriage_status"
                                @update:value="(v) => (form.marriage_status = v)"
                                :is-editing="isEditing"
                                :options="marriageStatuses.map((m) => ({ value: m, label: m }))"
                                :error="form.errors.marriage_status"
                                col-span="half"
                            />
                            <ProfileField
                                label="Marriage Type"
                                type="select"
                                :value="form.marriage_type"
                                @update:value="(v) => (form.marriage_type = v)"
                                :is-editing="isEditing"
                                :options="marriageTypes.map((m) => ({ value: m, label: m }))"
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
                                :value="form.religion"
                                @update:value="(v) => (form.religion = v)"
                                :is-editing="isEditing"
                                :error="form.errors.religion"
                                col-span="half"
                            />
                            <ProfileField
                                label="Prayer"
                                type="select"
                                :value="isEditing ? form.prayer : getOptionLabel(prayer_commitments, user.prayer)"
                                @update:value="(v) => (form.prayer = v)"
                                :is-editing="isEditing"
                                :options="prayerOptions"
                                :error="form.errors.prayer"
                                col-span="half"
                            />
                        </div>
                        <div class="row">
                            <ProfileField
                                label="Devotion"
                                type="select"
                                :value="isEditing ? form.devotion : getOptionLabel(devotions, user.devotion)"
                                @update:value="(v) => (form.devotion = v)"
                                :is-editing="isEditing"
                                :options="devotionOptions"
                                :error="form.errors.devotion"
                                col-span="half"
                            />

                        </div>
                        <div class="row">
                            <ProfileField
                                label="Smoking"
                                type="select"
                                :value="isEditing ? form.smoking : getOptionLabel(yes_no_list, user.smoking)"
                                @update:value="(v) => (form.smoking = v)"
                                :is-editing="isEditing"
                                :options="yesNoOptions"
                                :error="form.errors.smoking"
                                col-span="half"
                            />
                            <ProfileField
                                v-if="user.registration_type === 'husband'"
                                label="Beard"
                                type="select"
                                :value="isEditing ? form.beard : getOptionLabel(yes_no_list, user.beard)"
                                @update:value="(v) => (form.beard = v)"
                                :is-editing="isEditing"
                                :options="yesNoOptions"
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
                                :value="isEditing ? form.education_level : getOptionLabel(education_levels, user.education_level)"
                                @update:value="(v) => (form.education_level = v)"
                                :is-editing="isEditing"
                                :options="educationOptions"
                                :error="form.errors.education_level"
                                col-span="half"
                            />
                            <ProfileField
                                label="Financial Status"
                                type="select"
                                :value="isEditing ? form.financial_status : getOptionLabel(financial_statuses, user.financial_status)"
                                @update:value="(v) => (form.financial_status = v)"
                                :is-editing="isEditing"
                                :options="financialOptions"
                                :error="form.errors.financial_status"
                                col-span="half"
                            />
                        </div>
                        <div class="row">
                            <ProfileField
                                label="Field of Work"
                                type="select"
                                :value="isEditing ? form.field_of_work : getOptionLabel(fields_of_work, user.field_of_work)"
                                @update:value="(v) => (form.field_of_work = v)"
                                :is-editing="isEditing"
                                :options="workFieldOptions"
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
                                :value="form.health_status"
                                @update:value="(v) => (form.health_status = v)"
                                :is-editing="isEditing"
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
