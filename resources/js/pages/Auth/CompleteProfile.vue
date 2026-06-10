<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/CompleteProfileController';
import Select2Input from '@/components/Inputs/Select2Input.vue';
import { showAlertError } from '@/lib/utils';
import { vueLang } from '@erag/lang-sync-inertia';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch, type PropType } from 'vue';

const { __ } = vueLang();
const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');

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

interface City {
    id: number;
    country_id: number;
    name: string;
}

const props = defineProps({
    countries: { type: Array as PropType<Country[]>, required: true },
    cities: { type: Array as PropType<City[]>, required: true },
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

const showBeardField = computed(() => props.user.registration_type === 1);

const form = useForm({
    email: '',
    marriage_type: '',
    marriage_status: '',
    child_count: 0,
    residence: '',
    nationality: '',
    city: '',
    religion: '1',
    weight: '',
    height: '',
    skin_color: '',
    body_shape: '',
    devotion: '',
    prayer: '',
    smoking: '',
    beard: '',
    education_level: '',
    financial_status: '',
    field_of_work: '',
    job: '',
    monthly_income: '',
    health_status: '',
    about_partner: '',
    about_self: '',
    full_name: '',
});

// City options are scoped to the selected residence country.
const cityOptions = computed(() =>
    props.cities
        .filter((c) => String(c.country_id) === String(form.residence))
        .map((c) => ({ value: c.id, label: c.name })),
);

// Reset the chosen city whenever the residence country changes.
watch(
    () => form.residence,
    (newResidence, oldResidence) => {
        if (newResidence !== oldResidence) {
            form.city = '';
        }
    },
);

const childCount = [0, 1, 2, 3, 4, 5, 6, 7, 8];
const weightRange = Array.from({ length: 126 }, (_, i) => i + 45);
const heightRange = Array.from({ length: 100 }, (_, i) => i + 120);

onMounted(() => {
    const stepperEl = document.querySelector('#kt_stepper');
    if (stepperEl) {
        // @ts-ignore
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
        if (!form.marriage_type) errors.push(__('complete_profile.validation-marriage-type'));
        if (!form.marriage_status) errors.push(__('complete_profile.validation-marriage-status'));
        if (form.child_count === null || form.child_count.toString() === '') errors.push(__('complete_profile.validation-child-count'));
        if (form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) errors.push(__('complete_profile.validation-email'));
    } else if (step === 2) {
        if (!form.residence) errors.push(__('complete_profile.validation-residence'));
        if (!form.nationality) errors.push(__('complete_profile.validation-nationality'));
        if (!form.city) errors.push(__('complete_profile.validation-city'));
        if (!form.weight) errors.push(__('complete_profile.validation-weight'));
        if (!form.height) errors.push(__('complete_profile.validation-height'));
        if (!form.skin_color) errors.push(__('complete_profile.validation-skin-color'));
        if (!form.body_shape) errors.push(__('complete_profile.validation-body-shape'));
    } else if (step === 3) {
        if (!form.devotion) errors.push(__('complete_profile.validation-devotion'));
        if (!form.prayer) errors.push(__('complete_profile.validation-prayer'));
        if (!form.smoking) errors.push(__('complete_profile.validation-smoking'));
        if (props.user.registration_type === 1 && !form.beard) errors.push(__('complete_profile.validation-beard'));
        if (!form.education_level) errors.push(__('complete_profile.validation-education-level'));
        if (!form.financial_status) errors.push(__('complete_profile.validation-financial-status'));
        if (!form.field_of_work) errors.push(__('complete_profile.validation-field-of-work'));
        if (!form.job) errors.push(__('complete_profile.validation-job'));
    } else if (step === 4) {
        if (!form.monthly_income) errors.push(__('complete_profile.validation-monthly-income'));
        if (!form.health_status) errors.push(__('complete_profile.validation-health-status'));
        if (!form.about_partner) errors.push(__('complete_profile.validation-about-partner'));
        if (!form.about_self) errors.push(__('complete_profile.validation-about-self'));
        if (!form.full_name) errors.push(__('complete_profile.validation-full-name'));
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

const canContinue = computed(() => validateCurrentStep());

function goNext() {
    if (!stepperInstance) return;

    if (validateCurrentStep()) {
        stepperInstance.goNext();
    } else {
        const errors = getStepValidationErrors(currentStep.value);
        if (errors.length > 0) {
            showAlertError(errors, __('complete_profile.validation-title'));
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
                    showAlertError(errorMessages, __('complete_profile.validation-error-title'));
                }
            },
        });
    } else {
        const errors = getStepValidationErrors(currentStep.value);
        if (errors.length > 0) {
            showAlertError(errors, __('complete_profile.validation-title'));
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
                            <h2 class="fw-bold fs-2 text-gray-900">{{ __('complete_profile.title') }}</h2>
                            <div class="text-muted fw-semibold fs-6">{{ __('complete_profile.subtitle') }}</div>
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

                                    <!-- Step 1: Familial Status -->
                                    <div class="current" data-kt-stepper-element="content">
                                        <div class="w-100">
                                            <div class="pb-lg-10 pb-5">
                                                <h2 class="fw-bold fs-3 fs-lg-2 text-gray-900">{{ __('complete_profile.step-1-title') }}</h2>
                                                <div class="text-muted fw-semibold fs-6">{{ __('complete_profile.step-1-subtitle') }}</div>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="form-label">{{ __('complete_profile.email') }}</label>
                                                <input type="email" class="form-control form-control-lg rounded-2" v-model="form.email" />
                                                <div v-if="form.errors.email" class="fv-plugins-message-container">
                                                    <div class="fv-help-block"><span role="alert">{{ form.errors.email }}</span></div>
                                                </div>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.marriage-type') }}</label>
                                                <Select2Input v-model="form.marriage_type" :options="marriage_types" :placeholder="__('complete_profile.select-placeholder')" />
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.marriage-status') }}</label>
                                                <Select2Input v-model="form.marriage_status" :options="marriage_statuses" :placeholder="__('complete_profile.select-placeholder')" />
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.child-count') }}</label>
                                                <select class="form-select form-select-lg themed-select" v-model="form.child_count">
                                                    <option v-for="count in childCount" :key="count" :value="count">{{ count }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 2: Personal Information -->
                                    <div data-kt-stepper-element="content">
                                        <div class="w-100">
                                            <div class="pb-lg-10 pb-5">
                                                <h2 class="fw-bold fs-3 fs-lg-2 text-gray-900">{{ __('complete_profile.step-2-title') }}</h2>
                                                <div class="text-muted fw-semibold fs-6">{{ __('complete_profile.step-2-subtitle') }}</div>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.residence') }}</label>
                                                <Select2Input v-model="form.residence" :options="countries.map(c => ({ value: c.id, label: c.name }))" :placeholder="__('complete_profile.select-placeholder')" />
                                                <div v-if="form.errors.residence" class="fv-plugins-message-container">
                                                    <div class="fv-help-block"><span role="alert">{{ form.errors.residence }}</span></div>
                                                </div>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.nationality') }}</label>
                                                <Select2Input v-model="form.nationality" :options="countries.map(c => ({ value: c.id, label: c.name }))" :placeholder="__('complete_profile.select-placeholder')" />
                                                <div v-if="form.errors.nationality" class="fv-plugins-message-container">
                                                    <div class="fv-help-block"><span role="alert">{{ form.errors.nationality }}</span></div>
                                                </div>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.city') }}</label>
                                                <Select2Input
                                                    v-model="form.city"
                                                    :options="cityOptions"
                                                    :disabled="!form.residence"
                                                    :placeholder="form.residence ? __('complete_profile.select-placeholder') : __('complete_profile.city-select-residence-first')"
                                                />
                                                <div v-if="form.errors.city" class="fv-plugins-message-container">
                                                    <div class="fv-help-block"><span role="alert">{{ form.errors.city }}</span></div>
                                                </div>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.religion') }}</label>
                                                <select class="form-select form-select-lg themed-select" v-model="form.religion" disabled>
                                                    <option value="1">{{ __('complete_profile.religion-option') }}</option>
                                                </select>
                                            </div>

                                            <div class="row g-5 mb-0">
                                                <div class="col-6">
                                                    <div class="fv-row mb-5">
                                                        <label class="required form-label">{{ __('complete_profile.weight') }}</label>
                                                        <select class="form-select form-select-lg themed-select" v-model="form.weight">
                                                            <option value="">{{ __('complete_profile.select-placeholder') }}</option>
                                                            <option v-for="w in weightRange" :key="w" :value="w">{{ w }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="fv-row mb-5">
                                                        <label class="required form-label">{{ __('complete_profile.height') }}</label>
                                                        <select class="form-select form-select-lg themed-select" v-model="form.height">
                                                            <option value="">{{ __('complete_profile.select-placeholder') }}</option>
                                                            <option v-for="h in heightRange" :key="h" :value="h">{{ h }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-5 mb-0">
                                                <div class="col-6">
                                                    <div class="fv-row mb-5">
                                                        <label class="required form-label">{{ __('complete_profile.skin-color') }}</label>
                                                        <Select2Input v-model="form.skin_color" :options="skin_colors" :placeholder="__('complete_profile.select-placeholder')" />
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="fv-row mb-5">
                                                        <label class="required form-label">{{ __('complete_profile.body-shape') }}</label>
                                                        <Select2Input v-model="form.body_shape" :options="body_shapes" :placeholder="__('complete_profile.select-placeholder')" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 3: Lifestyle & Work -->
                                    <div data-kt-stepper-element="content">
                                        <div class="w-100">
                                            <div class="pb-lg-10 pb-5">
                                                <h2 class="fw-bold fs-3 fs-lg-2 text-gray-900">{{ __('complete_profile.step-3-title') }}</h2>
                                                <div class="text-muted fw-semibold fs-6">{{ __('complete_profile.step-3-subtitle') }}</div>
                                            </div>

                                            <div class="row g-5 mb-0">
                                                <div class="col-6">
                                                    <div class="fv-row mb-5">
                                                        <label class="required form-label">{{ __('complete_profile.devotion') }}</label>
                                                        <Select2Input v-model="form.devotion" :options="devotions" :placeholder="__('complete_profile.select-placeholder')" />
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="fv-row mb-5">
                                                        <label class="required form-label">{{ __('complete_profile.prayer') }}</label>
                                                        <Select2Input v-model="form.prayer" :options="prayer_commitments" :placeholder="__('complete_profile.select-placeholder')" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-5 mb-0">
                                                <div class="col-6">
                                                    <div class="fv-row mb-5">
                                                        <label class="required form-label">{{ __('complete_profile.smoking') }}</label>
                                                        <Select2Input v-model="form.smoking" :options="yes_no_list" :placeholder="__('complete_profile.select-placeholder')" />
                                                    </div>
                                                </div>
                                                <div v-if="showBeardField" class="col-6">
                                                    <div class="fv-row mb-5">
                                                        <label class="required form-label">{{ __('complete_profile.beard') }}</label>
                                                        <Select2Input v-model="form.beard" :options="yes_no_list" :placeholder="__('complete_profile.select-placeholder')" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-5 mb-0">
                                                <div class="col-6">
                                                    <div class="fv-row mb-5">
                                                        <label class="required form-label">{{ __('complete_profile.education-level') }}</label>
                                                        <Select2Input v-model="form.education_level" :options="education_levels" :placeholder="__('complete_profile.select-placeholder')" />
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="fv-row mb-5">
                                                        <label class="required form-label">{{ __('complete_profile.financial-status') }}</label>
                                                        <Select2Input v-model="form.financial_status" :options="financial_statuses" :placeholder="__('complete_profile.select-placeholder')" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-5 mb-0">
                                                <div class="col-6">
                                                    <div class="fv-row mb-5">
                                                        <label class="required form-label">{{ __('complete_profile.field-of-work') }}</label>
                                                        <Select2Input v-model="form.field_of_work" :options="fields_of_work" :placeholder="__('complete_profile.select-placeholder')" />
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="fv-row mb-5">
                                                        <label class="required form-label">{{ __('complete_profile.job') }}</label>
                                                        <input type="text" class="form-control form-control-lg rounded-2" v-model="form.job" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 4: Additional Information -->
                                    <div data-kt-stepper-element="content">
                                        <div class="w-100">
                                            <div class="pb-lg-10 pb-5">
                                                <h2 class="fw-bold fs-3 fs-lg-2 text-gray-900">{{ __('complete_profile.step-4-title') }}</h2>
                                                <div class="text-muted fw-semibold fs-6">{{ __('complete_profile.step-4-subtitle') }}</div>
                                            </div>

                                            <div class="row g-5 mb-0">
                                                <div class="col-6">
                                                    <div class="fv-row mb-5">
                                                        <label class="required form-label">{{ __('complete_profile.monthly-income') }}</label>
                                                        <Select2Input v-model="form.monthly_income" :options="monthly_incomes" :placeholder="__('complete_profile.select-placeholder')" />
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="fv-row mb-5">
                                                        <label class="required form-label">{{ __('complete_profile.health-status') }}</label>
                                                        <Select2Input v-model="form.health_status" :options="health_statuses" :placeholder="__('complete_profile.select-placeholder')" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.full-name') }}</label>
                                                <input type="text" class="form-control form-control-lg" v-model="form.full_name" />
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.about-partner') }}</label>
                                                <textarea class="form-control form-control-lg" rows="4" v-model="form.about_partner"></textarea>
                                            </div>

                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.about-self') }}</label>
                                                <textarea class="form-control form-control-lg" rows="4" v-model="form.about_self"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="d-flex flex-stack pt-lg-10 pt-5">
                                        <div class="me-2">
                                            <button type="button" class="btn btn-lg btn-light-primary" @click="goPrev"
                                                data-kt-stepper-action="previous">
                                                <i :class="['ki-outline', isRtl ? 'ki-arrow-right' : 'ki-arrow-left', 'fs-3 me-1']"></i>
                                                <span class="d-none d-sm-inline">{{ __('complete_profile.btn-back') }}</span>
                                            </button>
                                        </div>
                                        <div class="mb-10">
                                            <button type="button" class="btn btn-lg btn-primary"
                                                :disabled="!canContinue" @click="goNext" data-kt-stepper-action="next">
                                                <span class="d-none d-sm-inline">{{ __('complete_profile.btn-continue') }}</span>
                                                <span class="d-inline d-sm-none">{{ __('complete_profile.btn-next') }}</span>
                                                <i :class="['ki-outline', isRtl ? 'ki-arrow-left' : 'ki-arrow-right', 'fs-3 ms-1']"></i>
                                            </button>
                                            <button type="submit" class="btn btn-lg btn-primary"
                                                data-kt-stepper-action="submit" :disabled="form.processing">
                                                <span v-if="!form.processing">
                                                    <span class="d-none d-sm-inline">{{ __('complete_profile.btn-submit') }}</span>
                                                    <span class="d-inline d-sm-none">{{ __('complete_profile.btn-submit-short') }}</span>
                                                </span>
                                                <span v-else>
                                                    <span class="spinner-border spinner-border-sm align-middle"></span>
                                                </span>
                                                <i v-if="!form.processing" :class="['ki-outline', isRtl ? 'ki-arrow-left' : 'ki-arrow-right', 'fs-3 ms-1']"></i>
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

[data-bs-theme='dark'] .themed-select {
    background-color: var(--bs-gray-200, #1b1b29);
    color: var(--bs-gray-700, #cdcdde);
    border-color: var(--bs-gray-300, #323248);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23cdcdde' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3E%3C/svg%3E");
}

[data-bs-theme='dark'] .themed-select:focus {
    border-color: var(--bs-primary, #009ef7);
    box-shadow: 0 0 0 0.25rem rgba(0, 158, 247, 0.2);
}

[data-bs-theme='dark'] .themed-select option {
    background-color: var(--bs-gray-200, #1b1b29);
    color: var(--bs-gray-700, #cdcdde);
}

[dir='rtl'] .themed-select {
    background-position: left 1rem center;
    padding-right: 1rem;
    padding-left: 2.5rem;
    text-align: right;
}
</style>
