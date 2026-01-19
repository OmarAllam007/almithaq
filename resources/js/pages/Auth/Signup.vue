<script setup lang="ts">
import { check } from '@/actions/App/Http/Controllers/Api/UsernameCheckController';
import { store } from '@/actions/App/Http/Controllers/Auth/RegisterController';
import { showAlertError } from '@/lib/utils';
import StepFive from '@/pages/Auth/_Wizard/StepFive.vue';
import StepFour from '@/pages/Auth/_Wizard/StepFour.vue';
import StepOne from '@/pages/Auth/_Wizard/StepOne.vue';
import StepThree from '@/pages/Auth/_Wizard/StepThree.vue';
import StepTwo from '@/pages/Auth/_Wizard/StepTwo.vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    countries: { type: Array, required: true },
    devotions: { type: Array, required: true },
    prayer_commitments: { type: Array, required: true },
    yes_no_list: { type: Array, required: true },
    education_levels: { type: Array, required: true },
    financial_statuses: { type: Array, required: true },
    fields_of_work: { type: Array, required: true },
});
const currentStep = ref(1);
const completedSteps = ref(new Set<number>([1])); // Step 1 is always accessible
const usernameAvailable = ref<boolean | null>(null);
const checkingUsername = ref(false);
let stepperInstance: any = null;

const form = useForm({
    // Step 1
    registration_type: '',

    // Step 2
    username: '',
    email: '',
    password: '',
    confirm_password: '',
    marriage_type: '',
    marriage_status: '',
    age: null,
    child_count: 0,

    // Step 3
    residence: '',
    nationality: '',
    city: '',
    religion: '1',
    ethnicity: '',
    weight: '',
    height: '',
    skin_color: '',
    body_shape: '',

    // Step 4
    devotion: '',
    prayer: '',
    smoking: '',
    beard: '',
    education_level: '',
    financial_status: '',
    field_of_work: '',
    job: '',

    // Step 5
    monthly_income: null,
    health_status: '',
    about_partner: '',
    about_self: '',
    full_name: '',
    phone_number: '',
});

const showBeardField = computed(() => {
    return form.registration_type === 'husband';
});

// Validation helper functions
function isValidEmail(email: string): boolean {
    if (!email) return true; // Email is optional
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isPasswordMatch(): boolean {
    if (!form.confirm_password) return true; // Not filled yet
    return form.password === form.confirm_password;
}

function isUsernameLengthValid(): boolean {
    if (!form.username) return true; // Not filled yet
    return form.username.length <= 20;
}

// Check username availability via API
async function checkUsernameAvailability() {
    if (!form.username || form.username.length === 0) {
        usernameAvailable.value = null;
        return;
    }

    if (!isUsernameLengthValid()) {
        showAlertError('Username must be 20 characters or less', 'Invalid Username Length');
        usernameAvailable.value = false;
        return;
    }

    checkingUsername.value = true;
    usernameAvailable.value = null;

    try {
        const response = await axios.post(check().url, {
            username: form.username,
        });

        usernameAvailable.value = response.data.available;

        if (!response.data.available) {
            showAlertError(response.data.message, 'Username Not Available');
        }
    } catch (error: any) {
        if (error.response?.data?.errors?.username) {
            showAlertError(error.response.data.errors.username[0], 'Validation Error');
        } else {
            showAlertError('Failed to check username availability', 'Error');
        }
        usernameAvailable.value = false;
    } finally {
        checkingUsername.value = false;
    }
}

// Watchers for real-time validation
watch(
    () => form.registration_type,
    (newValue) => {
        if (newValue === 'wife') {
            form.beard = '';
        }
    },
);

watch(
    () => form.username,
    () => {
        usernameAvailable.value = null; // Reset when username changes
        if (form.username && form.username.length > 20) {
            showAlertError('Username must be 20 characters or less', 'Invalid Username Length');
        }
    },
);

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
    }
});

const canContinue = computed(() => {
    return validateCurrentStep();
});

function getStepValidationErrors(step: number): string[] {
    const errors: string[] = [];

    if (step === 1) {
        if (!form.registration_type) errors.push('Please select your registration type (Wife or Husband)');
    } else if (step === 2) {
        if (!form.username) errors.push('Username is required');
        if (form.username && !isUsernameLengthValid()) errors.push('Username must be 20 characters or less');
        if (usernameAvailable.value === false) errors.push('Username is already taken');
        if (checkingUsername.value) errors.push('Please wait while we check username availability');
        if (form.email && !isValidEmail(form.email)) errors.push('Please enter a valid email address');
        if (!form.password) errors.push('Password is required');
        if (!form.confirm_password) errors.push('Password confirmation is required');
        if (!isPasswordMatch()) errors.push('Passwords do not match');
        if (!form.marriage_type) errors.push('Marriage Type is required');
        if (!form.marriage_status) errors.push('Marriage Status is required');
        if (!form.age) errors.push('Age is required');
        if (form.child_count === null || form.child_count === '') errors.push('Child Count is required');
    } else if (step === 3) {
        if (!form.residence) errors.push('Residence is required');
        if (!form.nationality) errors.push('Nationality is required');
        if (!form.city) errors.push('City is required');
        if (!form.religion) errors.push('Religion is required');
        if (!form.ethnicity) errors.push('Ethnicity is required');
        if (!form.weight) errors.push('Weight is required');
        if (!form.height) errors.push('Height is required');
        if (!form.skin_color) errors.push('Skin Color is required');
        if (!form.body_shape) errors.push('Body Shape is required');
    } else if (step === 4) {
        if (!form.devotion) errors.push('Devotion is required');
        if (!form.prayer) errors.push('Prayer is required');
        if (!form.smoking) errors.push('Smoking is required');
        if (form.registration_type === 'husband' && !form.beard) errors.push('Beard is required');
        if (!form.education_level) errors.push('Education Level is required');
        if (!form.financial_status) errors.push('Financial Status is required');
        if (!form.field_of_work) errors.push('Field of Work is required');
        if (!form.job) errors.push('Job is required');
    } else if (step === 5) {
        if (form.monthly_income === null) errors.push('Monthly Income is required');
        if (!form.health_status) errors.push('Health Status is required');
        if (!form.about_partner) errors.push('About Your Partner is required');
        if (!form.about_self) errors.push('About Yourself is required');
        if (!form.full_name) errors.push('Full Name is required');
        if (!form.phone_number) errors.push('Phone Number is required');
    }

    return errors;
}

function validateCurrentStep(): boolean {
    const step = currentStep.value;

    if (step === 1) {
        return !!form.registration_type;
    } else if (step === 2) {
        const baseValid = !!(
            form.username &&
            isUsernameLengthValid() &&
            // usernameAvailable.value === true &&
            // !checkingUsername.value &&
            form.password &&
            form.confirm_password &&
            form.password == form.confirm_password &&
            form.marriage_type &&
            form.marriage_status &&
            form.age &&
            form.child_count !== null &&
            form.child_count.toString() !== ''
        );

        const emailValid = !form.email || isValidEmail(form.email);

        return baseValid && emailValid;
    } else if (step === 3) {
        return !!(
            form.residence &&
            form.nationality &&
            form.city &&
            form.religion &&
            // form.ethnicity &&
            form.weight &&
            form.height &&
            form.skin_color &&
            form.body_shape
        );
    } else if (step === 4) {
        const baseFields = !!(
            form.devotion &&
            form.prayer &&
            form.smoking &&
            form.education_level &&
            form.financial_status &&
            form.field_of_work &&
            form.job
        );
        if (form.registration_type === 'husband') {
            return baseFields && !!form.beard;
        }
        return baseFields;
    } else if (step === 5) {
        return !!(form.monthly_income !== null && form.health_status && form.about_partner && form.about_self && form.full_name && form.phone_number);
    }

    return true;
}

function goNext() {
    if (!stepperInstance) return;

    if (validateCurrentStep()) {
        // Mark current step as completed
        completedSteps.value.add(currentStep.value);
        // Allow access to next step
        completedSteps.value.add(currentStep.value + 1);
        stepperInstance.goNext();
    } else {
        // Show validation errors
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
        form.submit(store());
    } else {
        // Show validation errors if user tries to bypass by removing disabled attribute
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

                                    <div class="stepper-item" data-kt-stepper-element="nav">
                                        <div class="stepper-wrapper d-flex align-items-center">
                                            <div class="stepper-icon w-40px h-40px">
                                                <i class="stepper-check ki-outline ki-check fs-2"></i>
                                                <span class="stepper-number">5</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Stepper Content -->
                            <div class="w-100">
                                <form class="form w-100" @submit.prevent="handleSubmit">
                                    <!-- Step 1 -->

                                    <StepOne :form="form" />
                                    <!-- Step 2 -->
                                    <StepTwo :form="form" />

                                    <StepThree :form="form" :countries="countries" />

                                    <StepFour
                                        :form="form"
                                        :devotions="devotions"
                                        :prayer_commitments="prayer_commitments"
                                        :yes_no_list="yes_no_list"
                                        :education_levels="education_levels"
                                        :financial_statuses="financial_statuses"
                                        :fields_of_work="fields_of_work"
                                        :showBeardField="showBeardField"
                                    />

                                    <StepFive :form="form" />

                                    <!-- Actions -->
                                    <div class="d-flex flex-stack pt-lg-10 pt-5">
                                        <div class="me-2">
                                            <button
                                                type="button"
                                                class="btn btn-lg btn-light-primary"
                                                @click="goPrev"
                                                data-kt-stepper-action="previous"
                                            >
                                                <i class="ki-outline ki-arrow-left fs-3 me-1"></i>
                                                <span class="d-none d-sm-inline">Back</span>
                                            </button>
                                        </div>

                                        <div class="mb-10">
                                            <button
                                                type="button"
                                                class="btn btn-lg btn-primary"
                                                :disabled="!canContinue"
                                                @click="goNext"
                                                data-kt-stepper-action="next"
                                            >
                                                <span class="d-none d-sm-inline">Continue</span>
                                                <span class="d-inline d-sm-none">Next</span>
                                                <i class="ki-outline ki-arrow-right fs-3 ms-1"></i>
                                            </button>

                                            <button
                                                type="submit"
                                                class="btn btn-lg btn-primary"
                                                data-kt-stepper-action="submit"
                                                :disabled="form.processing"
                                            >
                                                <span v-if="!form.processing">
                                                    <span class="d-none d-sm-inline">Submit</span>
                                                    <span class="d-inline d-sm-none">Done</span>
                                                </span>
                                                <span v-else>
                                                    <span class="spinner-border spinner-border-sm align-middle"></span>
                                                </span>
                                                <i v-if="!form.processing" class="ki-outline ki-arrow-right fs-3 ms-1"></i>
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
