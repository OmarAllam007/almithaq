<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/CompleteProfileController';
import { showAlertError } from '@/lib/utils';
import { useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref, type PropType } from 'vue';

interface EnumOption {
    value: number;
    label: string;
}

interface Country {
    id: number;
    name: string;
    ar_name: string;
    flag: string;
}

const props = defineProps({
    countries: { type: Array as PropType<Country[]>, required: true },
    marriage_types: { type: Array as PropType<EnumOption[]>, required: true },
    marriage_statuses: { type: Array as PropType<EnumOption[]>, required: true },
    devotions: { type: Array as PropType<EnumOption[]>, required: true },
    prayer_commitments: { type: Array as PropType<EnumOption[]>, required: true },
    yes_no_list: { type: Array as PropType<EnumOption[]>, required: true },
    education_levels: { type: Array as PropType<EnumOption[]>, required: true },
    financial_statuses: { type: Array as PropType<EnumOption[]>, required: true },
    fields_of_work: { type: Array as PropType<EnumOption[]>, required: true },
    skin_colors: { type: Array as PropType<EnumOption[]>, required: true },
    body_shapes: { type: Array as PropType<EnumOption[]>, required: true },
    health_statuses: { type: Array as PropType<EnumOption[]>, required: true },
    monthly_incomes: { type: Array as PropType<EnumOption[]>, required: true },
    user: { type: Object as PropType<{ registration_type: number }>, required: true },
});

const currentStep = ref(1);
const totalSteps = 4;
let stepperInstance: any = null;

const showBeardField = computed(() => {
    return props.user.registration_type === 1;
});

const form = useForm({
    // Step 1: Familial Status + Email
    email: '',
    marriage_type: '',
    marriage_status: '',
    child_count: 0,

    // Step 2: Personal Information
    residence: '',
    nationality: '',
    city: '',
    religion: '1',
    weight: '',
    height: '',
    skin_color: '',
    body_shape: '',

    // Step 3: Lifestyle & Work
    devotion: '',
    prayer: '',
    smoking: '',
    beard: '',
    education_level: '',
    financial_status: '',
    field_of_work: '',
    job: '',

    // Step 4: Additional Information
    monthly_income: '',
    health_status: '',
    about_partner: '',
    about_self: '',
    full_name: '',
});

const childCount = [0, 1, 2, 3, 4, 5, 6, 7, 8];
const weightRange = Array.from({ length: 126 }, (_, i) => i + 45);
const heightRange = Array.from({ length: 100 }, (_, i) => i + 120);

onMounted(() => {
    const stepperEl = document.querySelector('#kt_stepper');
    if (stepperEl) {
        // @ts-ignore - KTStepper is loaded from Metronic bundle
        stepperInstance = new KTStepper(stepperEl);

        stepperInstance.on('kt.stepper.changed', function () {
            currentStep.value = stepperInstance.getCurrentStepIndex();
        });

        stepperInstance.on('kt.stepper.next', function (stepper: any) {
            if (validateCurrentStep()) {
                stepper.goNext();
            }
        });

        stepperInstance.on('kt.stepper.submit', function () {
            handleSubmit();
        });
    }
});

function getStepValidationErrors(step: number): string[] {
    const errors: string[] = [];

    if (step === 1) {
        if (!form.marriage_type) errors.push('Marriage Type is required');
        if (!form.marriage_status) errors.push('Marriage Status is required');
        if (form.child_count === null || form.child_count.toString() === '') errors.push('Child Count is required');
        if (form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) errors.push('Please enter a valid email');
    } else if (step === 2) {
        if (!form.residence) errors.push('Residence is required');
        if (!form.nationality) errors.push('Nationality is required');
        if (!form.city) errors.push('City is required');
        if (!form.weight) errors.push('Weight is required');
        if (!form.height) errors.push('Height is required');
        if (!form.skin_color) errors.push('Skin Color is required');
        if (!form.body_shape) errors.push('Body Shape is required');
    } else if (step === 3) {
        if (!form.devotion) errors.push('Devotion is required');
        if (!form.prayer) errors.push('Prayer is required');
        if (!form.smoking) errors.push('Smoking is required');
        if (props.user.registration_type === 1 && !form.beard) errors.push('Beard is required');
        if (!form.education_level) errors.push('Education Level is required');
        if (!form.financial_status) errors.push('Financial Status is required');
        if (!form.field_of_work) errors.push('Field of Work is required');
        if (!form.job) errors.push('Job is required');
    } else if (step === 4) {
        if (!form.monthly_income) errors.push('Monthly Income is required');
        if (!form.health_status) errors.push('Health Status is required');
        if (!form.about_partner) errors.push('About Your Partner is required');
        if (!form.about_self) errors.push('About Yourself is required');
        if (!form.full_name) errors.push('Full Name is required');
    }

    return errors;
}

function validateCurrentStep(): boolean {
    const step = currentStep.value;

    if (step === 1) {
        const emailValid = !form.email || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email);
        return !!(form.marriage_type && form.marriage_status && form.child_count !== null && form.child_count.toString() !== '' && emailValid);
    } else if (step === 2) {
        return !!(form.residence && form.nationality && form.city && form.religion && form.weight && form.height && form.skin_color && form.body_shape);
    } else if (step === 3) {
        const baseFields = !!(form.devotion && form.prayer && form.smoking && form.education_level && form.financial_status && form.field_of_work && form.job);
        if (props.user.registration_type === 1) {
            return baseFields && !!form.beard;
        }
        return baseFields;
    } else if (step === 4) {
        return !!(form.monthly_income && form.health_status && form.about_partner && form.about_self && form.full_name);
    }

    return true;
}

const canContinue = computed(() => {
    return validateCurrentStep();
});

function goNext() {
    if (!stepperInstance) return;

    if (validateCurrentStep()) {
        stepperInstance.goNext();
    } else {
        const errors = getStepValidationErrors(currentStep.value);
        if (errors.length > 0) {
            showAlertError(errors, 'Please Complete All Required Fields');
        }
    }
}

function goPrev() {
    if (stepperInstance) {
        stepperInstance.goPrevious();
    }
}

function handleSubmit() {
    if (validateCurrentStep()) {
        form.submit(store(), {
            onError: (errors) => {
                const errorMessages = Object.values(errors).flat() as string[];
                if (errorMessages.length > 0) {
                    showAlertError(errorMessages, 'Validation Error');
                }
            },
        });
    } else {
        const errors = getStepValidationErrors(currentStep.value);
        if (errors.length > 0) {
            showAlertError(errors, 'Please Complete All Required Fields');
        }
    }
}
</script>

<template>
    <div class="d-flex flex-column flex-root" id="kt_app_root" style="margin-bottom: 150px; padding-bottom: 20px">
        <div class="d-flex flex-column flex-column-fluid">
            <div class="d-flex flex-column flex-lg-row-fluid p-lg-10 w-100 p-5">
                <div class="d-flex flex-center flex-column w-100">
                    <div class="mw-900px w-100">
                        <div class="pb-lg-10 pb-5 text-center">
                            <h2 class="fw-bold fs-2 text-gray-900">Complete Your Profile</h2>
                            <div class="text-muted fw-semibold fs-6">Please fill in the remaining details to start using
                                the platform</div>
                        </div>

                        <div class="stepper stepper-pills d-flex flex-column" id="kt_stepper">
                            <!-- Stepper Nav -->
                            <div class="d-flex justify-content-center mb-10 w-100">
                                <div class="stepper-nav flex-center flex-wrap gap-3">
                                    <div class="stepper-item current" data-kt-stepper-element="nav">
                                        <div class="stepper-wrapper d-flex align-items-center">
                                            <div class="stepper-icon w-40px h-40px">
                                                <i class="stepper-check ki-outline ki-check fs-2"></i>
                                                <span class="stepper-number">1</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="stepper-item" data-kt-stepper-element="nav">
                                        <div class="stepper-wrapper d-flex align-items-center">
                                            <div class="stepper-icon w-40px h-40px">
                                                <i class="stepper-check ki-outline ki-check fs-2"></i>
                                                <span class="stepper-number">2</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="stepper-item" data-kt-stepper-element="nav">
                                        <div class="stepper-wrapper d-flex align-items-center">
                                            <div class="stepper-icon w-40px h-40px">
                                                <i class="stepper-check ki-outline ki-check fs-2"></i>
                                                <span class="stepper-number">3</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="stepper-item" data-kt-stepper-element="nav">
                                        <div class="stepper-wrapper d-flex align-items-center">
                                            <div class="stepper-icon w-40px h-40px">
                                                <i class="stepper-check ki-outline ki-check fs-2"></i>
                                                <span class="stepper-number">4</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Stepper Content -->
                            <div class="w-100">
                                <form class="form w-100" @submit.prevent="handleSubmit">
                                    <!-- Step 1: Familial Status + Email -->
                                    <div class="current" data-kt-stepper-element="content">
                                        <div class="w-100">
                                            <div class="pb-lg-10 pb-5">
                                                <h2 class="fw-bold fs-3 fs-lg-2 text-gray-900">Familial Status</h2>
                                                <div class="text-muted fw-semibold fs-6">Your marital and family
                                                    information</div>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="form-label">Email (optional)</label>
                                                <input type="email" class="form-control form-control-lg rounded-2"
                                                    v-model="form.email" />
                                                <div v-if="form.errors.email" class="fv-plugins-message-container">
                                                    <div class="fv-help-block">
                                                        <span role="alert">{{ form.errors.email }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Marriage Type</label>
                                                <select class="form-select form-select-lg themed-select"
                                                    v-model="form.marriage_type">
                                                    <option value="">Select...</option>
                                                    <option v-for="opt in marriage_types" :key="opt.value"
                                                        :value="opt.value">{{ opt.label }}</option>
                                                </select>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Marriage Status</label>
                                                <select class="form-select form-select-lg themed-select"
                                                    v-model="form.marriage_status">
                                                    <option value="">Select...</option>
                                                    <option v-for="opt in marriage_statuses" :key="opt.value"
                                                        :value="opt.value">{{ opt.label }}</option>
                                                </select>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Child Count</label>
                                                <select class="form-select form-select-lg themed-select"
                                                    v-model="form.child_count">
                                                    <option v-for="count in childCount" :key="count" :value="count">
                                                        {{ count }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 2: Personal Information -->
                                    <div data-kt-stepper-element="content">
                                        <div class="w-100">
                                            <div class="pb-lg-10 pb-5">
                                                <h2 class="fw-bold fs-3 fs-lg-2 text-gray-900">Personal Information</h2>
                                                <div class="text-muted fw-semibold fs-6">Tell us more about yourself
                                                </div>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Country of residence</label>
                                                <select class="form-select form-select-lg themed-select"
                                                    v-model="form.residence">
                                                    <option value="">Select...</option>
                                                    <option v-for="country in countries" :key="country.id"
                                                        :value="country.id">
                                                        {{ country.name }}
                                                    </option>
                                                </select>
                                                <div v-if="form.errors.residence" class="fv-plugins-message-container">
                                                    <div class="fv-help-block">
                                                        <span role="alert">{{ form.errors.residence }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Nationality</label>
                                                <select class="form-select form-select-lg themed-select"
                                                    v-model="form.nationality">
                                                    <option value="">Select...</option>
                                                    <option v-for="country in countries" :key="country.id"
                                                        :value="country.id">
                                                        {{ country.name }}
                                                    </option>
                                                </select>
                                                <div v-if="form.errors.nationality"
                                                    class="fv-plugins-message-container">
                                                    <div class="fv-help-block">
                                                        <span role="alert">{{ form.errors.nationality }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">City</label>
                                                <input type="text" class="form-control form-control-lg rounded-2"
                                                    v-model="form.city" />
                                                <div v-if="form.errors.city" class="fv-plugins-message-container">
                                                    <div class="fv-help-block">
                                                        <span role="alert">{{ form.errors.city }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Religion</label>
                                                <select class="form-select form-select-lg themed-select" v-model="form.religion"
                                                    disabled>
                                                    <option value="">Select...</option>
                                                    <option value="1">Islamic only</option>
                                                </select>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Weight (kg)</label>
                                                <select class="form-select form-select-lg themed-select" v-model="form.weight">
                                                    <option value="">Select...</option>
                                                    <option v-for="weight in weightRange" :key="weight" :value="weight">
                                                        {{ weight }}</option>
                                                </select>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Height (cm)</label>
                                                <select class="form-select form-select-lg themed-select" v-model="form.height">
                                                    <option value="">Select...</option>
                                                    <option v-for="height in heightRange" :key="height" :value="height">
                                                        {{ height }}</option>
                                                </select>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Skin Color</label>
                                                <select class="form-select form-select-lg themed-select"
                                                    v-model="form.skin_color">
                                                    <option value="">Select...</option>
                                                    <option v-for="opt in skin_colors" :key="opt.value"
                                                        :value="opt.value">{{ opt.label }}</option>
                                                </select>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Body Shape</label>
                                                <select class="form-select form-select-lg themed-select"
                                                    v-model="form.body_shape">
                                                    <option value="">Select...</option>
                                                    <option v-for="opt in body_shapes" :key="opt.value"
                                                        :value="opt.value">{{ opt.label }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 3: Lifestyle & Work -->
                                    <div data-kt-stepper-element="content">
                                        <div class="w-100">
                                            <div class="pb-lg-10 pb-5">
                                                <h2 class="fw-bold fs-3 fs-lg-2 text-gray-900">Lifestyle & Work</h2>
                                                <div class="text-muted fw-semibold fs-6">Share your lifestyle and career
                                                    information</div>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Devotion</label>
                                                <select class="form-select form-select-lg themed-select"
                                                    v-model="form.devotion">
                                                    <option value="">Select...</option>
                                                    <option v-for="opt in devotions" :key="opt.value"
                                                        :value="opt.value">{{ opt.label }}</option>
                                                </select>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Prayer</label>
                                                <select class="form-select form-select-lg themed-select"
                                                    v-model="form.prayer">
                                                    <option value="">Select...</option>
                                                    <option v-for="opt in prayer_commitments" :key="opt.value"
                                                        :value="opt.value">{{ opt.label }}</option>
                                                </select>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Smoking</label>
                                                <select class="form-select form-select-lg themed-select"
                                                    v-model="form.smoking">
                                                    <option value="">Select...</option>
                                                    <option v-for="opt in yes_no_list" :key="opt.value"
                                                        :value="opt.value">{{ opt.label }}</option>
                                                </select>
                                            </div>

                                            <div v-if="showBeardField" class="fv-row mb-5">
                                                <label class="required form-label">Beard</label>
                                                <select class="form-select form-select-lg themed-select"
                                                    v-model="form.beard">
                                                    <option value="">Select...</option>
                                                    <option v-for="opt in yes_no_list" :key="opt.value"
                                                        :value="opt.value">{{ opt.label }}</option>
                                                </select>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Education Level</label>
                                                <select class="form-select form-select-lg themed-select"
                                                    v-model="form.education_level">
                                                    <option value="">Select...</option>
                                                    <option v-for="opt in education_levels" :key="opt.value"
                                                        :value="opt.value">{{ opt.label }}</option>
                                                </select>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Financial Status</label>
                                                <select class="form-select form-select-lg themed-select"
                                                    v-model="form.financial_status">
                                                    <option value="">Select...</option>
                                                    <option v-for="opt in financial_statuses" :key="opt.value"
                                                        :value="opt.value">{{ opt.label }}</option>
                                                </select>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Field of Work</label>
                                                <select class="form-select form-select-lg themed-select"
                                                    v-model="form.field_of_work">
                                                    <option value="">Select...</option>
                                                    <option v-for="opt in fields_of_work" :key="opt.value"
                                                        :value="opt.value">{{ opt.label }}</option>
                                                </select>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Job</label>
                                                <input type="text" class="form-control form-control-lg rounded-2"
                                                    v-model="form.job" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 4: Additional Information -->
                                    <div data-kt-stepper-element="content">
                                        <div class="w-100">
                                            <div class="pb-lg-10 pb-5">
                                                <h2 class="fw-bold fs-3 fs-lg-2 text-gray-900">Additional Information
                                                </h2>
                                                <div class="text-muted fw-semibold fs-6">Final details to complete your
                                                    profile</div>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Monthly Income</label>
                                                <select class="form-select form-select-lg themed-select"
                                                    v-model="form.monthly_income">
                                                    <option value="">Select...</option>
                                                    <option v-for="opt in monthly_incomes" :key="opt.value"
                                                        :value="opt.value">{{ opt.label }}</option>
                                                </select>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Health Status</label>
                                                <select class="form-select form-select-lg themed-select"
                                                    v-model="form.health_status">
                                                    <option value="">Select...</option>
                                                    <option v-for="opt in health_statuses" :key="opt.value"
                                                        :value="opt.value">{{ opt.label }}</option>
                                                </select>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">About Your Partner</label>
                                                <textarea class="form-control form-control-lg" rows="4"
                                                    v-model="form.about_partner"></textarea>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">About Yourself</label>
                                                <textarea class="form-control form-control-lg" rows="4"
                                                    v-model="form.about_self"></textarea>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">Full Name</label>
                                                <input type="text" class="form-control form-control-lg"
                                                    v-model="form.full_name" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="d-flex flex-stack pt-lg-10 pt-5">
                                        <div class="me-2">
                                            <button type="button" class="btn btn-lg btn-light-primary" @click="goPrev"
                                                data-kt-stepper-action="previous">
                                                <i class="ki-outline ki-arrow-left fs-3 me-1"></i>
                                                <span class="d-none d-sm-inline">Back</span>
                                            </button>
                                        </div>

                                        <div class="mb-10">
                                            <button type="button" class="btn btn-lg btn-primary"
                                                :disabled="!canContinue" @click="goNext" data-kt-stepper-action="next">
                                                <span class="d-none d-sm-inline">Continue</span>
                                                <span class="d-inline d-sm-none">Next</span>
                                                <i class="ki-outline ki-arrow-right fs-3 ms-1"></i>
                                            </button>

                                            <button type="submit" class="btn btn-lg btn-primary"
                                                data-kt-stepper-action="submit" :disabled="form.processing">
                                                <span v-if="!form.processing">
                                                    <span class="d-none d-sm-inline">Complete Profile</span>
                                                    <span class="d-inline d-sm-none">Done</span>
                                                </span>
                                                <span v-else>
                                                    <span class="spinner-border spinner-border-sm align-middle"></span>
                                                </span>
                                                <i v-if="!form.processing"
                                                    class="ki-outline ki-arrow-right fs-3 ms-1"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.themed-select {
    background-color: var(--bs-body-bg, #fff);
    color: var(--bs-body-color, #181c32);
    border: 1px solid var(--bs-gray-300, #e1e3ea);
    border-radius: 0.625rem;
    padding: 0.775rem 1rem;
    font-size: 1.075rem;
    font-weight: 500;
    line-height: 1.5;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%237e8299' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 16px 12px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    cursor: pointer;
}

.themed-select:focus {
    border-color: var(--bs-primary, #009ef7);
    box-shadow: 0 0 0 0.25rem rgba(0, 158, 247, 0.15);
    outline: none;
}

.themed-select:hover:not(:disabled) {
    border-color: var(--bs-gray-400, #c4cada);
}

.themed-select:disabled {
    background-color: var(--bs-gray-200, #f1f1f4);
    color: var(--bs-gray-500, #99a1b7);
    cursor: not-allowed;
    opacity: 0.7;
}

.themed-select option {
    padding: 0.5rem 1rem;
    font-weight: 500;
    background-color: var(--bs-body-bg, #fff);
    color: var(--bs-body-color, #181c32);
}

.themed-select option:checked {
    background-color: var(--bs-primary, #009ef7);
    color: #fff;
}

[data-bs-theme="dark"] .themed-select {
    background-color: var(--bs-gray-200, #1b1b29);
    color: var(--bs-gray-700, #cdcdde);
    border-color: var(--bs-gray-300, #323248);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23cdcdde' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3E%3C/svg%3E");
}

[data-bs-theme="dark"] .themed-select:focus {
    border-color: var(--bs-primary, #009ef7);
    box-shadow: 0 0 0 0.25rem rgba(0, 158, 247, 0.2);
}

[data-bs-theme="dark"] .themed-select option {
    background-color: var(--bs-gray-200, #1b1b29);
    color: var(--bs-gray-700, #cdcdde);
}
</style>
