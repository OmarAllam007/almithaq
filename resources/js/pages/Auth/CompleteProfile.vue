<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/CompleteProfileController';
import { switchLanguage } from '@/actions/App/Http/Controllers/LanguageController';
import Select2Input from '@/components/Inputs/Select2Input.vue';
import { showAlertError } from '@/lib/utils';
import { vueLang } from '@erag/lang-sync-inertia';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch, type PropType } from 'vue';

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
const stepTitles = computed(() =>
    [1, 2, 3, 4].map((step) => ({
        title: __('complete_profile.step-' + step + '-title'),
        subtitle: __('complete_profile.step-' + step + '-subtitle'),
    })),
);
const progressPercent = computed(() => Math.round((currentStep.value / totalSteps) * 100));

const showBeardField = computed(() => props.user.registration_type === 1);
const isHusband = computed(() => props.user.registration_type === 1);
const isSingle = computed(() => String(form.marriage_status) === '1');
const showChildCount = computed(() => !isSingle.value);

const isMarried = computed(() => String(form.marriage_status) === '2');

const filteredMarriageTypes = computed(() => {
    if (!isHusband.value) return props.marriage_types;
    if (isMarried.value) {
        // Married husband: second (2), third (5), fourth (6)
        return props.marriage_types.filter((t) => [2, 5, 6].includes(t.value));
    }
    // Single / divorced / widowed: first wife only (1)
    return props.marriage_types.filter((t) => t.value === 1);
});

function switchLang(lang: string): void {
    router.visit(switchLanguage.url(lang), { preserveScroll: true });
}

const form = useForm({
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
    props.cities.filter((c) => String(c.country_id) === String(form.residence)).map((c) => ({ value: c.id, label: c.name })),
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

// When marriage status changes, reset dependent fields.
watch(
    () => form.marriage_status,
    (newStatus, oldStatus) => {
        if (newStatus === oldStatus) return;
        form.marriage_type = '';
        // Auto-select first wife for husband when not married (single/divorced/widowed)
        if (isHusband.value && String(newStatus) !== '2') {
            form.marriage_type = 1 as any;
        }
        if (String(newStatus) === '1') {
            form.child_count = 0;
        }
    },
);

const childCount = [0, 1, 2, 3, 4, 5, 6, 7, 8];
const weightRange = Array.from({ length: 126 }, (_, i) => i + 45);
const heightRange = Array.from({ length: 100 }, (_, i) => i + 120);

function getStepValidationErrors(step: number): string[] {
    const errors: string[] = [];

    if (step === 1) {
        if (!form.marriage_status) errors.push(__('complete_profile.validation-marriage-status'));
        if (!form.marriage_type) errors.push(__('complete_profile.validation-marriage-type'));
        if (showChildCount.value && (form.child_count === null || form.child_count.toString() === ''))
            errors.push(__('complete_profile.validation-child-count'));
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
        const childCountValid = !showChildCount.value || (form.child_count !== null && form.child_count.toString() !== '');
        return !!(form.marriage_status && form.marriage_type && childCountValid);
    } else if (step === 2) {
        return !!(
            form.residence &&
            form.nationality &&
            form.city &&
            form.religion &&
            form.weight &&
            form.height &&
            form.skin_color &&
            form.body_shape
        );
    } else if (step === 3) {
        const baseFields = !!(
            form.devotion &&
            form.prayer &&
            form.smoking &&
            form.education_level &&
            form.financial_status &&
            form.field_of_work &&
            form.job
        );
        if (props.user.registration_type === 1) {
            return baseFields && !!form.beard;
        }
        return baseFields;
    } else if (step === 4) {
        return !!(form.monthly_income && form.health_status && form.about_partner && form.about_self && form.full_name);
    }

    return true;
}

function goNext() {
    if (validateCurrentStep()) {
        currentStep.value = Math.min(totalSteps, currentStep.value + 1);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        const errors = getStepValidationErrors(currentStep.value);
        if (errors.length > 0) showAlertError(errors, __('complete_profile.validation-title'));
    }
}

function goPrev() {
    currentStep.value = Math.max(1, currentStep.value - 1);
    window.scrollTo({ top: 0, behavior: 'smooth' });
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
    <Head :title="__('complete_profile.title')" />
    <div class="profile-setup" :dir="isRtl ? 'rtl' : 'ltr'">
        <div class="profile-setup__shell">
            <header class="profile-setup__header">
                <a href="/" class="profile-setup__brand" aria-label="Khotobah">
                    <span class="profile-setup__brand-mark">◎</span>
                    <span><strong>خطوبة</strong><small>KHOTOBAH</small></span>
                </a>
                <div class="profile-setup__language" :aria-label="isRtl ? 'اللغة' : 'Language'">
                    <button type="button" :class="{ active: !isRtl }" @click="switchLang('en')">EN</button>
                    <button type="button" :class="{ active: isRtl }" @click="switchLang('ar')">عربي</button>
                </div>
            </header>

            <div class="profile-setup__intro">
                <div>
                    <span class="profile-setup__eyebrow">{{ isRtl ? 'خطوة نحو حكايتك' : 'YOUR STORY STARTS HERE' }}</span>
                    <h1>{{ __('complete_profile.title') }}</h1>
                    <p>{{ __('complete_profile.subtitle') }}</p>
                </div>
                <div class="profile-setup__intro-art" aria-hidden="true"><span>✦</span><span>◎</span><span>✳</span></div>
            </div>

            <div class="profile-setup__body">
                <aside class="profile-setup__aside" aria-label="Progress">
                    <div class="profile-progress">
                        <div class="profile-progress__top">
                            <span>{{ isRtl ? 'رحلتك' : 'YOUR JOURNEY' }}</span
                            ><strong>{{ String(currentStep).padStart(2, '0') }} / 04</strong>
                        </div>
                        <div class="profile-progress__bar" role="progressbar" :aria-valuenow="progressPercent" aria-valuemin="0" aria-valuemax="100">
                            <span :style="{ width: progressPercent + '%' }"></span>
                        </div>
                        <ol class="profile-progress__steps">
                            <li
                                v-for="(step, index) in stepTitles"
                                :key="index"
                                :class="{ active: currentStep === index + 1, done: currentStep > index + 1 }"
                            >
                                <span class="profile-progress__number">{{ currentStep > index + 1 ? '✓' : String(index + 1).padStart(2, '0') }}</span>
                                <span
                                    ><strong>{{ step.title }}</strong
                                    ><small>{{ step.subtitle }}</small></span
                                >
                            </li>
                        </ol>
                        <div class="profile-progress__note">
                            <span>✦</span>{{ isRtl ? 'كل تفصيل يقرّبك من الشخص المناسب.' : 'Every detail helps us bring the right people closer.' }}
                        </div>
                    </div>
                </aside>
                <main class="profile-setup__main">
                    <div class="profile-setup__card">
                        <div class="profile-setup__mobile-progress">
                            {{ isRtl ? 'الخطوة' : 'STEP' }} {{ currentStep }} / {{ totalSteps }}
                            <div class="profile-progress__bar"><span :style="{ width: progressPercent + '%' }"></span></div>
                        </div>
                        <form class="form" @submit.prevent="handleSubmit">
                            <section v-show="currentStep === 1" class="profile-step-panel" aria-labelledby="profile-step-title-1">
                                <div class="w-100">
                                    <div class="profile-step-heading">
                                        <h2 id="profile-step-title-1" class="profile-step-title">{{ __('complete_profile.step-1-title') }}</h2>
                                        <div class="profile-step-description">{{ __('complete_profile.step-1-subtitle') }}</div>
                                    </div>

                                    <div class="fv-row mb-5">
                                        <label class="required form-label">{{ __('complete_profile.marriage-status') }}</label>
                                        <Select2Input
                                            v-model="form.marriage_status"
                                            :options="marriage_statuses"
                                            :placeholder="__('complete_profile.select-placeholder')"
                                        />
                                    </div>

                                    <div class="fv-row mb-5">
                                        <label class="required form-label">{{ __('complete_profile.marriage-type') }}</label>
                                        <Select2Input
                                            v-model="form.marriage_type"
                                            :options="filteredMarriageTypes"
                                            :placeholder="__('complete_profile.select-placeholder')"
                                        />
                                    </div>

                                    <div v-if="showChildCount" class="fv-row mb-5">
                                        <label class="required form-label">{{ __('complete_profile.child-count') }}</label>
                                        <select class="form-select form-select-lg themed-select" v-model="form.child_count">
                                            <option v-for="count in childCount" :key="count" :value="count">{{ count }}</option>
                                        </select>
                                    </div>
                                </div>
                            </section>
                            <section v-show="currentStep === 2" class="profile-step-panel" aria-labelledby="profile-step-title-2">
                                <div class="w-100">
                                    <div class="profile-step-heading">
                                        <h2 id="profile-step-title-2" class="profile-step-title">{{ __('complete_profile.step-2-title') }}</h2>
                                        <div class="profile-step-description">{{ __('complete_profile.step-2-subtitle') }}</div>
                                    </div>

                                    <div class="fv-row mb-5">
                                        <label class="required form-label">{{ __('complete_profile.residence') }}</label>
                                        <Select2Input
                                            v-model="form.residence"
                                            :options="countries.map((c) => ({ value: c.id, label: c.name }))"
                                            :placeholder="__('complete_profile.select-placeholder')"
                                        />
                                        <div v-if="form.errors.residence" class="fv-plugins-message-container">
                                            <div class="fv-help-block">
                                                <span role="alert">{{ form.errors.residence }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="fv-row mb-5">
                                        <label class="required form-label">{{ __('complete_profile.nationality') }}</label>
                                        <Select2Input
                                            v-model="form.nationality"
                                            :options="countries.map((c) => ({ value: c.id, label: c.name }))"
                                            :placeholder="__('complete_profile.select-placeholder')"
                                        />
                                        <div v-if="form.errors.nationality" class="fv-plugins-message-container">
                                            <div class="fv-help-block">
                                                <span role="alert">{{ form.errors.nationality }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="fv-row mb-5">
                                        <label class="required form-label">{{ __('complete_profile.city') }}</label>
                                        <Select2Input
                                            v-model="form.city"
                                            :options="cityOptions"
                                            :disabled="!form.residence"
                                            :placeholder="
                                                form.residence
                                                    ? __('complete_profile.select-placeholder')
                                                    : __('complete_profile.city-select-residence-first')
                                            "
                                        />
                                        <div v-if="form.errors.city" class="fv-plugins-message-container">
                                            <div class="fv-help-block">
                                                <span role="alert">{{ form.errors.city }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="fv-row mb-5">
                                        <label class="required form-label">{{ __('complete_profile.religion') }}</label>
                                        <select class="form-select form-select-lg themed-select" v-model="form.religion" disabled>
                                            <option value="1">{{ __('complete_profile.religion-option') }}</option>
                                        </select>
                                    </div>

                                    <div class="row g-4 mb-0">
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

                                    <div class="row g-4 mb-0">
                                        <div class="col-6">
                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.skin-color') }}</label>
                                                <Select2Input
                                                    v-model="form.skin_color"
                                                    :options="skin_colors"
                                                    :placeholder="__('complete_profile.select-placeholder')"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.body-shape') }}</label>
                                                <Select2Input
                                                    v-model="form.body_shape"
                                                    :options="body_shapes"
                                                    :placeholder="__('complete_profile.select-placeholder')"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            <section v-show="currentStep === 3" class="profile-step-panel" aria-labelledby="profile-step-title-3">
                                <div class="w-100">
                                    <div class="profile-step-heading">
                                        <h2 id="profile-step-title-3" class="profile-step-title">{{ __('complete_profile.step-3-title') }}</h2>
                                        <div class="profile-step-description">{{ __('complete_profile.step-3-subtitle') }}</div>
                                    </div>

                                    <div class="row g-4 mb-0">
                                        <div class="col-6">
                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.devotion') }}</label>
                                                <Select2Input
                                                    v-model="form.devotion"
                                                    :options="devotions"
                                                    :placeholder="__('complete_profile.select-placeholder')"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.prayer') }}</label>
                                                <Select2Input
                                                    v-model="form.prayer"
                                                    :options="prayer_commitments"
                                                    :placeholder="__('complete_profile.select-placeholder')"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-4 mb-0">
                                        <div class="col-6">
                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.smoking') }}</label>
                                                <Select2Input
                                                    v-model="form.smoking"
                                                    :options="yes_no_list"
                                                    :placeholder="__('complete_profile.select-placeholder')"
                                                />
                                            </div>
                                        </div>
                                        <div v-if="showBeardField" class="col-6">
                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.beard') }}</label>
                                                <Select2Input
                                                    v-model="form.beard"
                                                    :options="yes_no_list"
                                                    :placeholder="__('complete_profile.select-placeholder')"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-4 mb-0">
                                        <div class="col-6">
                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.education-level') }}</label>
                                                <Select2Input
                                                    v-model="form.education_level"
                                                    :options="education_levels"
                                                    :placeholder="__('complete_profile.select-placeholder')"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.financial-status') }}</label>
                                                <Select2Input
                                                    v-model="form.financial_status"
                                                    :options="financial_statuses"
                                                    :placeholder="__('complete_profile.select-placeholder')"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-4 mb-0">
                                        <div class="col-6">
                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.field-of-work') }}</label>
                                                <Select2Input
                                                    v-model="form.field_of_work"
                                                    :options="fields_of_work"
                                                    :placeholder="__('complete_profile.select-placeholder')"
                                                />
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
                            </section>
                            <section v-show="currentStep === 4" class="profile-step-panel" aria-labelledby="profile-step-title-4">
                                <div class="w-100">
                                    <div class="profile-step-heading">
                                        <h2 id="profile-step-title-4" class="profile-step-title">{{ __('complete_profile.step-4-title') }}</h2>
                                        <div class="profile-step-description">{{ __('complete_profile.step-4-subtitle') }}</div>
                                    </div>

                                    <div class="row g-4 mb-0">
                                        <div class="col-6">
                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.monthly-income') }}</label>
                                                <Select2Input
                                                    v-model="form.monthly_income"
                                                    :options="monthly_incomes"
                                                    :placeholder="__('complete_profile.select-placeholder')"
                                                />
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="fv-row mb-5">
                                                <label class="required form-label">{{ __('complete_profile.health-status') }}</label>
                                                <Select2Input
                                                    v-model="form.health_status"
                                                    :options="health_statuses"
                                                    :placeholder="__('complete_profile.select-placeholder')"
                                                />
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
                            </section>
                            <div class="profile-setup__actions">
                                <button v-if="currentStep > 1" type="button" class="profile-setup__back" @click="goPrev">
                                    <span aria-hidden="true">{{ isRtl ? '→' : '←' }}</span
                                    >{{ __('complete_profile.btn-back') }}
                                </button>
                                <span v-else></span>
                                <button v-if="currentStep < totalSteps" type="button" class="profile-setup__next" @click="goNext">
                                    {{ __('complete_profile.btn-continue') }} <span aria-hidden="true">{{ isRtl ? '←' : '→' }}</span>
                                </button>
                                <button v-else type="submit" class="profile-setup__next" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                    {{ __('complete_profile.btn-submit') }}
                                    <span v-if="!form.processing" aria-hidden="true">{{ isRtl ? '←' : '→' }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>
