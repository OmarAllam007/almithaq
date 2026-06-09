<script setup lang="ts">
import { check } from '@/actions/App/Http/Controllers/Api/UsernameCheckController';
import { store } from '@/actions/App/Http/Controllers/Auth/RegisterController';
import { switchLanguage } from '@/actions/App/Http/Controllers/LanguageController';
import { showAlertError } from '@/lib/utils';
import PasswordInput from '@/components/Inputs/PasswordInput.vue';
import { vueLang } from '@erag/lang-sync-inertia';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';

const { __ } = vueLang();
const page = usePage();
const currentLocale = computed(() => page.props.locale as string);
const isRtl = computed(() => currentLocale.value === 'ar');
const showLangDropdown = ref(false);

const usernameAvailable = ref<boolean | null>(null);
const checkingUsername = ref(false);

const phoneCountryCodes = [
    { dial: '+966', en: 'Saudi Arabia', ar: 'السعودية' },
    { dial: '+971', en: 'UAE', ar: 'الإمارات' },
    { dial: '+965', en: 'Kuwait', ar: 'الكويت' },
    { dial: '+974', en: 'Qatar', ar: 'قطر' },
    { dial: '+973', en: 'Bahrain', ar: 'البحرين' },
    { dial: '+968', en: 'Oman', ar: 'عُمان' },
    { dial: '+962', en: 'Jordan', ar: 'الأردن' },
    { dial: '+20', en: 'Egypt', ar: 'مصر' },
    { dial: '+212', en: 'Morocco', ar: 'المغرب' },
    { dial: '+216', en: 'Tunisia', ar: 'تونس' },
    { dial: '+213', en: 'Algeria', ar: 'الجزائر' },
    { dial: '+218', en: 'Libya', ar: 'ليبيا' },
    { dial: '+249', en: 'Sudan', ar: 'السودان' },
    { dial: '+967', en: 'Yemen', ar: 'اليمن' },
    { dial: '+963', en: 'Syria', ar: 'سوريا' },
    { dial: '+961', en: 'Lebanon', ar: 'لبنان' },
    { dial: '+964', en: 'Iraq', ar: 'العراق' },
    { dial: '+970', en: 'Palestine', ar: 'فلسطين' },
    { dial: '+60', en: 'Malaysia', ar: 'ماليزيا' },
    { dial: '+62', en: 'Indonesia', ar: 'إندونيسيا' },
    { dial: '+92', en: 'Pakistan', ar: 'باكستان' },
    { dial: '+880', en: 'Bangladesh', ar: 'بنغلاديش' },
    { dial: '+90', en: 'Turkey', ar: 'تركيا' },
    { dial: '+98', en: 'Iran', ar: 'إيران' },
    { dial: '+1', en: 'USA / Canada', ar: 'أمريكا / كندا' },
    { dial: '+44', en: 'UK', ar: 'بريطانيا' },
    { dial: '+49', en: 'Germany', ar: 'ألمانيا' },
    { dial: '+33', en: 'France', ar: 'فرنسا' },
    { dial: '+61', en: 'Australia', ar: 'أستراليا' },
];

const form = useForm({
    registration_type: '',
    username: '',
    password: '',
    confirm_password: '',
    age: null,
    country_code: '+966',
    phone_number: '',
});

const ageRange = Array.from({ length: 90 - 18 + 1 }, (_, i) => (18 + i).toString());

function isUsernameLengthValid(): boolean {
    if (!form.username) return true;
    return form.username.length <= 20;
}

function isUsernameCharsValid(): boolean {
    if (!form.username) return true;
    return /^[a-zA-Z]+$/.test(form.username);
}

function isPasswordLengthValid(): boolean {
    if (!form.password) return false;
    return form.password.length >= 8;
}

function isPasswordHasUppercase(): boolean {
    if (!form.password) return false;
    return /[A-Z]/.test(form.password);
}

function isPasswordHasNumber(): boolean {
    if (!form.password) return false;
    return /[0-9]/.test(form.password);
}

function isPasswordStrong(): boolean {
    return isPasswordLengthValid() && isPasswordHasUppercase() && isPasswordHasNumber();
}

async function checkUsernameAvailability() {
    if (!form.username || form.username.length === 0) {
        usernameAvailable.value = null;
        return;
    }

    if (!isUsernameLengthValid()) {
        showAlertError(__('signup.validation-username-length'), __('signup.validation-username-length'));
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
            showAlertError(response.data.message, __('signup.validation-username-taken'));
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

watch(
    () => form.username,
    () => {
        usernameAvailable.value = null;
    },
);


const validationHints = computed<string[]>(() => {
    const hints: string[] = [];
    if (!form.registration_type) hints.push(__('signup.validation-account-type'));
    if (!form.username) hints.push(__('signup.validation-username-required'));
    if (form.username && !isUsernameCharsValid()) hints.push(__('signup.validation-username-chars'));
    if (form.username && !isUsernameLengthValid()) hints.push(__('signup.validation-username-length'));
    if (usernameAvailable.value === false) hints.push(__('signup.validation-username-taken'));
    if (!form.password) {
        hints.push(__('signup.validation-password-required'));
    } else {
        if (!isPasswordLengthValid()) hints.push(__('signup.validation-password-length'));
        if (!isPasswordHasUppercase()) hints.push(__('signup.validation-password-uppercase'));
        if (!isPasswordHasNumber()) hints.push(__('signup.validation-password-number'));
    }
    if (!form.confirm_password) hints.push(__('signup.validation-confirm-required'));
    if (form.confirm_password && form.password !== form.confirm_password) hints.push(__('signup.validation-passwords-match'));
    if (!form.age) hints.push(__('signup.validation-age-required'));
    if (!form.country_code) hints.push(__('signup.validation-country-code-required'));
    if (!form.phone_number) hints.push(__('signup.validation-phone-required'));
    return hints;
});

const canSubmit = computed(() => {
    return !!(
        form.registration_type &&
        form.username &&
        isUsernameCharsValid() &&
        isUsernameLengthValid() &&
        usernameAvailable.value !== false &&
        isPasswordStrong() &&
        form.confirm_password &&
        form.password === form.confirm_password &&
        form.age &&
        form.country_code &&
        form.phone_number &&
        !form.processing
    );
});

function handleSubmit() {
    if (canSubmit.value) {
        form.submit(store());
    } else {
        if (validationHints.value.length > 0) {
            showAlertError(validationHints.value, __('signup.title'));
        }
    }
}

function switchLang(lang: string) {
    window.location.href = switchLanguage.url(lang);
}
</script>

<template>
    <!--begin::Root with full-bleed background-->
    <div class="signup-root" :dir="isRtl ? 'rtl' : 'ltr'">
        <!--begin::Background image + overlay-->
        <div class="signup-bg" style="background-image: url(assets/media/auth/background_logo.png)"></div>
        <div class="signup-overlay"></div>
        <!--end::Background-->

        <!--begin::Scrollable content layer-->
        <div class="signup-scroll">
            <!--begin::Form card-->
            <div class="signup-card">
                <!--begin::Heading-->
                <div class="mb-6 text-start">
                    <h1 class="fs-2x mb-1 text-gray-900">{{ __('signup.title') }}</h1>
                    <div class="fw-semibold fs-7 text-gray-500">{{ __('signup.subtitle') }}</div>
                    <div class="mt-2">
                        <span class="fw-semibold fs-7 text-gray-500 me-2">{{ __('signup.head-desc') }}</span>
                        <Link :href="route('login')" class="link-primary fw-bold fs-7">{{ __('signup.head-link') }}</Link>
                    </div>
                </div>
                <!--end::Heading-->

                <form class="form w-100" @submit.prevent="handleSubmit">
                    <!-- Account Type -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-2">{{ __('signup.account-type') }}</h6>
                        <div class="d-flex gap-3">
                            <input type="radio" class="btn-check" name="registration_type" value="husband"
                                :checked="form.registration_type === 'husband'" id="reg_type_husband"
                                @click="form.registration_type = 'husband'" />
                            <label class="btn btn-outline-primary btn-outline-dashed btn-active-light-primary d-flex align-items-center p-3 flex-grow-1"
                                for="reg_type_husband">
                                <i class="ki-outline ki-user fs-2 me-2"></i>
                                <span class="fw-bold fs-6 text-active-primary">{{ __('signup.husband-label') }}</span>
                            </label>

                            <input type="radio" class="btn-check" name="registration_type" value="wife"
                                :checked="form.registration_type === 'wife'" id="reg_type_wife"
                                @click="form.registration_type = 'wife'" />
                            <label class="btn btn-outline-danger btn-outline-dashed btn-active-light-danger d-flex align-items-center p-3 flex-grow-1"
                                for="reg_type_wife">
                                <i class="ki-outline ki-user fs-2 me-2"></i>
                                <span class="fw-bold fs-6 text-active-danger">{{ __('signup.wife-label') }}</span>
                            </label>
                        </div>
                        <div v-if="form.errors.registration_type" class="fv-plugins-message-container mt-1">
                            <div class="fv-help-block"><span role="alert">{{ form.errors.registration_type }}</span></div>
                        </div>
                    </div>

                    <!-- Two-column row: username + age -->
                    <div class="row g-3 mb-3">
                        <div class="col-8">
                            <label class="required form-label mb-1 fs-7">{{ __('signup.username-label') }}</label>
                            <div class="position-relative">
                                <input type="text" class="form-control rounded-2 pe-10" v-model="form.username"
                                    maxlength="20" @blur="checkUsernameAvailability" />
                                <span class="username-indicator">
                                    <span v-if="checkingUsername" class="spinner-border spinner-border-sm text-primary"></span>
                                    <i v-else-if="usernameAvailable === true" class="ki-outline ki-check fs-2 text-success"></i>
                                    <i v-else-if="usernameAvailable === false" class="ki-outline ki-cross fs-2 text-danger"></i>
                                </span>
                            </div>
                            <div v-if="form.errors.username" class="fv-plugins-message-container">
                                <div class="fv-help-block fs-8"><span role="alert">{{ form.errors.username }}</span></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <label class="required form-label mb-1 fs-7">{{ __('signup.age-label') }}</label>
                            <select class="form-select rounded-2" :value="form.age"
                                @input="form.age = ($event.target as HTMLSelectElement).value">
                                <option value="">—</option>
                                <option v-for="a in ageRange" :key="a" :value="a">{{ a }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Two-column row: password + confirm -->
                    <div class="row g-3 mb-1">
                        <div class="col-6">
                            <PasswordInput v-model="form.password" :label="__('signup.password-label')" />
                        </div>
                        <div class="col-6">
                            <PasswordInput v-model="form.confirm_password" :label="__('signup.confirm-password-label')" />
                        </div>
                    </div>

                    <!-- Password strength + match hints -->
                    <div v-if="form.password || form.confirm_password" class="d-flex flex-wrap gap-3 mb-3">
                        <template v-if="form.password">
                            <span class="d-flex align-items-center gap-1 fs-8" :class="isPasswordLengthValid() ? 'text-success' : 'text-danger'">
                                <i :class="isPasswordLengthValid() ? 'ki-outline ki-check' : 'ki-outline ki-cross'"></i>8+
                            </span>
                            <span class="d-flex align-items-center gap-1 fs-8" :class="isPasswordHasUppercase() ? 'text-success' : 'text-danger'">
                                <i :class="isPasswordHasUppercase() ? 'ki-outline ki-check' : 'ki-outline ki-cross'"></i>A–Z
                            </span>
                            <span class="d-flex align-items-center gap-1 fs-8" :class="isPasswordHasNumber() ? 'text-success' : 'text-danger'">
                                <i :class="isPasswordHasNumber() ? 'ki-outline ki-check' : 'ki-outline ki-cross'"></i>0–9
                            </span>
                        </template>
                        <span v-if="form.confirm_password" class="d-flex align-items-center gap-1 fs-8"
                            :class="form.password === form.confirm_password ? 'text-success' : 'text-danger'">
                            <i :class="form.password === form.confirm_password ? 'ki-outline ki-check' : 'ki-outline ki-cross'"></i>
                            {{ __('signup.validation-passwords-match') }}
                        </span>
                    </div>

                    <!-- Phone: country code + number -->
                    <div class="fv-row mb-3">
                        <label class="required form-label mb-1 fs-7">{{ __('signup.phone-label') }}</label>
                        <div class="d-flex gap-2">
                            <select class="form-select rounded-2 phone-code-select" v-model="form.country_code">
                                <option v-for="c in phoneCountryCodes" :key="c.dial" :value="c.dial">
                                    {{ c.dial }} {{ isRtl ? c.ar : c.en }}
                                </option>
                            </select>
                            <input type="tel" class="form-control rounded-2 flex-grow-1" v-model="form.phone_number"
                                :placeholder="__('signup.phone-label')" />
                        </div>
                        <div v-if="form.errors.phone_number || form.errors.country_code" class="fv-plugins-message-container">
                            <div class="fv-help-block fs-8">
                                <span role="alert">{{ form.errors.country_code || form.errors.phone_number }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Validation hints -->
                    <div v-if="!canSubmit && validationHints.length > 0" class="validation-hints mb-3">
                        <i class="ki-outline ki-information-5 fs-5 text-warning flex-shrink-0"></i>
                        <ul class="mb-0 ps-3 fs-8">
                            <li v-for="hint in validationHints" :key="hint">{{ hint }}</li>
                        </ul>
                    </div>

                    <!-- Submit -->
                    <div class="d-flex justify-content-end pt-2">
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                            <span v-if="!form.processing">
                                {{ __('signup.submit') }}
                                <i :class="['ki-outline', isRtl ? 'ki-arrow-left' : 'ki-arrow-right', 'fs-4 ms-1']"></i>
                            </span>
                            <span v-else>
                                {{ __('signup.processing') }}
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>

                <!--begin::Lang dropdown-->
                <div class="d-flex justify-content-center mt-6">
                    <div class="position-relative">
                        <button type="button" class="lang-toggle" @click="showLangDropdown = !showLangDropdown">
                            <img
                                :src="currentLocale === 'ar' ? 'assets/media/flags/saudi-arabia.svg' : 'assets/media/flags/united-states.svg'"
                                style="width:16px;height:16px;border-radius:3px;flex-shrink:0"
                                alt=""
                            />
                            <span>{{ currentLocale === 'ar' ? 'العربية' : 'English' }}</span>
                            <i class="ki-outline ki-down fs-8"></i>
                        </button>
                        <div v-if="showLangDropdown" class="lang-menu">
                            <button type="button" class="lang-option" :class="{ active: currentLocale === 'en' }"
                                @click="switchLang('en')">
                                <img src="assets/media/flags/united-states.svg" style="width:16px;height:16px;border-radius:3px;flex-shrink:0" alt="" />
                                <span>English</span>
                                <i v-if="currentLocale === 'en'" class="ki-outline ki-check fs-7 text-primary ms-auto"></i>
                            </button>
                            <button type="button" class="lang-option" :class="{ active: currentLocale === 'ar' }"
                                @click="switchLang('ar')">
                                <img src="assets/media/flags/saudi-arabia.svg" style="width:16px;height:16px;border-radius:3px;flex-shrink:0" alt="" />
                                <span>العربية</span>
                                <i v-if="currentLocale === 'ar'" class="ki-outline ki-check fs-7 text-primary ms-auto"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!--end::Lang dropdown-->
            </div>
            <!--end::Form card-->
        </div>
        <!--end::Scrollable content layer-->
    </div>
</template>

<style scoped>
.signup-root {
    position: relative;
    min-height: 100vh;
    overflow-x: hidden;
}

.signup-bg {
    position: fixed;
    inset: 0;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    z-index: 0;
}

.signup-overlay {
    position: fixed;
    inset: 0;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.20) 0%, rgba(0, 0, 0, 0.2) 100%);
    z-index: 1;
}

.signup-scroll {
    position: relative;
    z-index: 2;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
}

.signup-card {
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(8px);
    border-radius: 1rem;
    padding: 2rem;
    width: 100%;
    max-width: 580px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.username-indicator {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.5rem;
    pointer-events: none;
}

[dir='rtl'] .username-indicator {
    right: auto;
    left: 0.75rem;
}

.phone-code-select {
    width: auto;
    min-width: 160px;
    flex-shrink: 0;
}

.validation-hints {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    padding: 0.75rem;
    background: #fff8e1;
    border: 1px solid #f59e0b;
    border-radius: 0.5rem;
    color: #1f2937;
}

.lang-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: var(--bs-gray-600);
    border-radius: 2rem;
    padding: 0.35rem 0.9rem;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease;
    backdrop-filter: blur(4px);
}

.lang-toggle:hover {
    background: rgba(255, 255, 255, 0.25);
}

.lang-menu {
    position: absolute;
    bottom: calc(100% + 6px);
    left: 50%;
    transform: translateX(-50%);
    background: #fff;
    border: 1px solid var(--bs-border-color);
    border-radius: 0.5rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    min-width: 150px;
    padding: 0.25rem;
    z-index: 100;
}

.lang-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--bs-gray-700);
    text-decoration: none;
    transition: background 0.12s ease;
    width: 100%;
    background: none;
    border: none;
    cursor: pointer;
    text-align: start;
}

.lang-option:hover,
.lang-option.active {
    background: var(--bs-light);
    color: var(--bs-gray-900);
}
</style>
