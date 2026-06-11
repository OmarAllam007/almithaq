<script setup lang="ts">
import { check } from '@/actions/App/Http/Controllers/Api/UsernameCheckController';
import { store } from '@/actions/App/Http/Controllers/Auth/RegisterController';
import { switchLanguage } from '@/actions/App/Http/Controllers/LanguageController';
import { showAlertError } from '@/lib/utils';
import PasswordInput from '@/components/Inputs/PasswordInput.vue';
import { vueLang } from '@erag/lang-sync-inertia';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
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
    showLangDropdown.value = false;
    router.visit(switchLanguage.url(lang), { preserveScroll: true });
}
</script>

<template>
    <div class="auth-root" :dir="isRtl ? 'rtl' : 'ltr'">
        <!-- Glows -->
        <div class="auth-glow auth-glow--1"></div>
        <div class="auth-glow auth-glow--2"></div>

        <div class="auth-wrap">
            <div class="auth-card">

                <!-- Brand -->
                <a href="/" class="auth-brand">
                    <span class="auth-brand__mark">
                        <svg viewBox="0 0 40 28" fill="none">
                            <circle cx="14" cy="14" r="9" stroke="currentColor" stroke-width="2.2" />
                            <circle cx="26" cy="14" r="9" stroke="currentColor" stroke-width="2.2" opacity="0.55" />
                        </svg>
                    </span>
                    <span class="auth-brand__text">
                        <span class="auth-brand__name">Khotobah</span>
                        <span class="auth-brand__sub">خطوبة</span>
                    </span>
                </a>

                <!-- Heading -->
                <div class="auth-head">
                    <h1 class="auth-head__title">{{ __('signup.title') }}</h1>
                    <p class="auth-head__desc">{{ __('signup.subtitle') }}</p>
                    <div class="auth-head__sub">
                        <span>{{ __('signup.head-desc') }}</span>
                        <Link :href="route('login')" class="auth-link">{{ __('signup.head-link') }}</Link>
                    </div>
                </div>

                <form @submit.prevent="handleSubmit">

                    <!-- Account type -->
                    <div class="field">
                        <label class="field__label">{{ __('signup.account-type') }}</label>
                        <div class="reg-type-group">
                            <button
                                type="button"
                                class="reg-type-card"
                                :class="{ active: form.registration_type === 'husband' }"
                                @click="form.registration_type = 'husband'"
                            >
                                <span class="reg-type-card__icon">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="1.6"/>
                                        <path d="M4.5 20c0-3.866 3.358-7 7.5-7s7.5 3.134 7.5 7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <span>{{ __('signup.husband-label') }}</span>
                            </button>
                            <button
                                type="button"
                                class="reg-type-card reg-type-card--rose"
                                :class="{ active: form.registration_type === 'wife' }"
                                @click="form.registration_type = 'wife'"
                            >
                                <span class="reg-type-card__icon">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="1.6"/>
                                        <path d="M4.5 20c0-3.866 3.358-7 7.5-7s7.5 3.134 7.5 7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <span>{{ __('signup.wife-label') }}</span>
                            </button>
                        </div>
                        <p v-if="form.errors.registration_type" class="field__error">{{ form.errors.registration_type }}</p>
                    </div>

                    <!-- Username + Age row -->
                    <div class="row g-3 mb-3">
                        <div class="col-8">
                            <label class="field__label">{{ __('signup.username-label') }}</label>
                            <div class="username-wrap">
                                <input
                                    type="text"
                                    class="field__input"
                                    v-model="form.username"
                                    maxlength="20"
                                    @blur="checkUsernameAvailability"
                                />
                                <span class="username-status">
                                    <span v-if="checkingUsername" class="auth-spinner auth-spinner--plum"></span>
                                    <svg v-else-if="usernameAvailable === true" viewBox="0 0 24 24" fill="none" class="status-icon status-icon--ok">
                                        <path d="m5 13 4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <svg v-else-if="usernameAvailable === false" viewBox="0 0 24 24" fill="none" class="status-icon status-icon--err">
                                        <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </span>
                            </div>
                            <p v-if="form.errors.username" class="field__error">{{ form.errors.username }}</p>
                        </div>
                        <div class="col-4">
                            <label class="field__label">{{ __('signup.age-label') }}</label>
                            <select
                                class="field__input"
                                :value="form.age"
                                @input="form.age = ($event.target as HTMLSelectElement).value"
                            >
                                <option value="">—</option>
                                <option v-for="a in ageRange" :key="a" :value="a">{{ a }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Password + Confirm row -->
                    <div class="row g-3 mb-2">
                        <div class="col-6">
                            <PasswordInput v-model="form.password" :label="__('signup.password-label')" />
                        </div>
                        <div class="col-6">
                            <PasswordInput v-model="form.confirm_password" :label="__('signup.confirm-password-label')" />
                        </div>
                    </div>

                    <!-- Password strength chips -->
                    <div v-if="form.password || form.confirm_password" class="pw-hints">
                        <template v-if="form.password">
                            <span class="pw-chip" :class="isPasswordLengthValid() ? 'pw-chip--ok' : 'pw-chip--err'">
                                <svg viewBox="0 0 16 16" fill="none">
                                    <path v-if="isPasswordLengthValid()" d="m3 8 3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path v-else d="M12 4 4 12M4 4l8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                                8+
                            </span>
                            <span class="pw-chip" :class="isPasswordHasUppercase() ? 'pw-chip--ok' : 'pw-chip--err'">
                                <svg viewBox="0 0 16 16" fill="none">
                                    <path v-if="isPasswordHasUppercase()" d="m3 8 3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path v-else d="M12 4 4 12M4 4l8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                                A–Z
                            </span>
                            <span class="pw-chip" :class="isPasswordHasNumber() ? 'pw-chip--ok' : 'pw-chip--err'">
                                <svg viewBox="0 0 16 16" fill="none">
                                    <path v-if="isPasswordHasNumber()" d="m3 8 3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path v-else d="M12 4 4 12M4 4l8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                                0–9
                            </span>
                        </template>
                        <span v-if="form.confirm_password" class="pw-chip" :class="form.password === form.confirm_password ? 'pw-chip--ok' : 'pw-chip--err'">
                            <svg viewBox="0 0 16 16" fill="none">
                                <path v-if="form.password === form.confirm_password" d="m3 8 3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path v-else d="M12 4 4 12M4 4l8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                            {{ __('signup.validation-passwords-match') }}
                        </span>
                    </div>

                    <!-- Phone -->
                    <div class="field">
                        <label class="field__label">{{ __('signup.phone-label') }}</label>
                        <div class="phone-row">
                            <select class="field__input phone-code" v-model="form.country_code">
                                <option v-for="c in phoneCountryCodes" :key="c.dial" :value="c.dial">
                                    {{ c.dial }} {{ isRtl ? c.ar : c.en }}
                                </option>
                            </select>
                            <input
                                type="tel"
                                class="field__input phone-number"
                                v-model="form.phone_number"
                                :placeholder="__('signup.phone-label')"
                            />
                        </div>
                        <p v-if="form.errors.phone_number || form.errors.country_code" class="field__error">
                            {{ form.errors.country_code || form.errors.phone_number }}
                        </p>
                    </div>

                    <!-- Validation hints -->
                    <div v-if="!canSubmit && validationHints.length > 0" class="validation-hints">
                        <svg class="validation-hints__icon" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/>
                            <path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <ul>
                            <li v-for="hint in validationHints" :key="hint">{{ hint }}</li>
                        </ul>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="auth-submit" :disabled="form.processing">
                        <span v-if="!form.processing" style="display:inline-flex;align-items:center;gap:0.5rem;white-space:nowrap">
                            {{ __('signup.submit') }}
                            <svg viewBox="0 0 20 20" fill="none" style="width:16px;height:16px" :style="isRtl ? 'transform:scaleX(-1)' : ''">
                                <path d="M4 10h12M11 5l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span v-else class="auth-submit__loading">
                            {{ __('signup.processing') }}
                            <span class="auth-spinner"></span>
                        </span>
                    </button>
                </form>

                <!-- Language switcher -->
                <div class="auth-lang">
                    <div class="lang">
                        <button type="button" class="lang__toggle" @click="showLangDropdown = !showLangDropdown">
                            <img :src="currentLocale === 'ar' ? 'assets/media/flags/saudi-arabia.svg' : 'assets/media/flags/united-states.svg'" alt="" />
                            <span>{{ currentLocale === 'ar' ? 'العربية' : 'English' }}</span>
                        </button>
                        <transition name="fade">
                            <div v-if="showLangDropdown" class="lang__menu">
                                <button type="button" :class="{ active: currentLocale === 'en' }" @click="switchLang('en')">
                                    <img src="assets/media/flags/united-states.svg" alt="" /> English
                                </button>
                                <button type="button" :class="{ active: currentLocale === 'ar' }" @click="switchLang('ar')">
                                    <img src="assets/media/flags/saudi-arabia.svg" alt="" /> العربية
                                </button>
                            </div>
                        </transition>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
/* ===== Design tokens (matching landing) ===== */
.auth-root {
    --ink: #2c0a18;
    --plum: #45112a;
    --plum-deep: #2a0a1a;
    --rose: #d02e79;
    --rose-soft: #e85fa0;
    --gold: #c79a3f;
    --gold-soft: #e6c878;
    --cream: #fbf5ef;
    --cream-2: #f5ebe1;
    --blush: #fce8f3;
    --line: rgba(69, 17, 42, 0.12);

    position: relative;
    min-height: 100vh;
    background: var(--cream);
    font-family: 'Cairo', sans-serif;
    overflow-x: hidden;
}

/* Dot texture */
.auth-root::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image: radial-gradient(circle at 1px 1px, rgba(69, 17, 42, 0.05) 1px, transparent 0);
    background-size: 26px 26px;
    pointer-events: none;
    z-index: 0;
    opacity: 0.7;
}

/* Ambient glows */
.auth-glow {
    position: fixed;
    border-radius: 50%;
    filter: blur(90px);
    pointer-events: none;
    z-index: 0;
}
.auth-glow--1 { width: 500px; height: 500px; background: rgba(208, 46, 121, 0.15); top: -160px; inset-inline-end: -120px; }
.auth-glow--2 { width: 400px; height: 400px; background: rgba(199, 154, 63, 0.13); bottom: -120px; inset-inline-start: -100px; }

/* Layout */
.auth-wrap {
    position: relative;
    z-index: 1;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
}

/* Card */
.auth-card {
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(12px);
    border: 1px solid var(--line);
    border-radius: 1.5rem;
    padding: 2.5rem 2rem;
    width: 100%;
    max-width: 580px;
    box-shadow: 0 24px 64px -24px rgba(69, 17, 42, 0.28);
}

/* Brand */
.auth-brand {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    text-decoration: none;
    margin-bottom: 2rem;
}
.auth-brand__mark { color: var(--rose); display: inline-flex; }
.auth-brand__mark svg { width: 36px; height: 25px; }
.auth-brand__text { display: flex; flex-direction: column; line-height: 1; }
.auth-brand__name {
    font-family: 'Cormorant Garamond', 'Cairo', serif;
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--plum);
}
.auth-brand__sub { font-size: 0.68rem; color: var(--rose); font-weight: 600; margin-top: 2px; }

/* Heading */
.auth-head { margin-bottom: 1.8rem; }
.auth-head__title {
    font-family: 'Cormorant Garamond', 'Cairo', serif;
    font-size: 2rem;
    font-weight: 600;
    color: var(--plum);
    margin: 0 0 0.35rem;
    line-height: 1.15;
}
.auth-head__desc { font-size: 0.9rem; color: rgba(44, 10, 24, 0.62); margin: 0 0 0.4rem; }
.auth-head__sub { font-size: 0.88rem; color: rgba(44, 10, 24, 0.6); }

/* Fields */
.field { margin-bottom: 1rem; }
.field__label { display: block; font-size: 0.82rem; font-weight: 700; color: var(--plum); margin-bottom: 0.4rem; }
.field__input {
    width: 100%;
    box-sizing: border-box;
    padding: 0.72rem 0.9rem;
    border: 1.5px solid var(--line);
    border-radius: 0.65rem;
    background: #fff;
    color: var(--ink);
    font-family: 'Cairo', sans-serif;
    font-size: 0.92rem;
    outline: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    appearance: auto;
}
.field__input:focus { border-color: var(--rose); box-shadow: 0 0 0 3px rgba(208, 46, 121, 0.1); }
.field__error { font-size: 0.8rem; color: #dc2626; margin: 0.3rem 0 0; }

/* Account type */
.reg-type-group { display: flex; gap: 0.75rem; }
.reg-type-card {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    border: 1.5px solid var(--line);
    border-radius: 0.85rem;
    background: #fff;
    font-family: 'Cairo', sans-serif;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--plum);
    cursor: pointer;
    transition: border-color 0.2s ease, background 0.2s ease, color 0.2s ease;
}
.reg-type-card__icon { display: flex; }
.reg-type-card__icon svg { width: 24px; height: 24px; }
.reg-type-card:hover { background: var(--cream-2); }
.reg-type-card.active { border-color: var(--gold); background: rgba(199, 154, 63, 0.08); color: var(--gold); }
.reg-type-card--rose.active { border-color: var(--rose); background: var(--blush); color: var(--rose); }

/* Username with status */
.username-wrap { position: relative; }
.username-wrap .field__input { padding-inline-end: 2.5rem; }
.username-status {
    position: absolute;
    top: 50%; inset-inline-end: 0.75rem;
    transform: translateY(-50%);
    display: flex; align-items: center;
    pointer-events: none;
}
.status-icon { width: 18px; height: 18px; }
.status-icon--ok { color: #16a34a; }
.status-icon--err { color: #dc2626; }

/* Phone */
.phone-row { display: flex; gap: 0.5rem; }
.phone-code { width: auto; min-width: 155px; flex-shrink: 0; }
.phone-number { flex: 1; }

/* Password chips */
.pw-hints { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem; }
.pw-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 0.25rem 0.6rem;
    border-radius: 999px;
}
.pw-chip svg { width: 12px; height: 12px; }
.pw-chip--ok { color: #166534; background: #dcfce7; }
.pw-chip--err { color: #991b1b; background: #fee2e2; }

/* Validation hints */
.validation-hints {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    padding: 0.8rem 1rem;
    background: rgba(199, 154, 63, 0.08);
    border: 1px solid rgba(199, 154, 63, 0.4);
    border-radius: 0.75rem;
    margin-bottom: 1rem;
    color: var(--ink);
}
.validation-hints__icon { width: 18px; height: 18px; flex-shrink: 0; color: var(--gold); margin-top: 1px; }
.validation-hints ul { margin: 0; padding: 0 1rem; font-size: 0.82rem; line-height: 1.7; }

/* Submit */
.auth-submit {
    width: 100%;
    margin-top: 0.5rem;
    padding: 0.9rem 1.5rem;
    background: linear-gradient(135deg, var(--rose), var(--rose-soft));
    color: #fff;
    border: none;
    border-radius: 999px;
    font-family: 'Cairo', sans-serif;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 8px 24px -8px rgba(208, 46, 121, 0.8);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}
.auth-submit:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 14px 32px -8px rgba(208, 46, 121, 0.9); }
.auth-submit:disabled { opacity: 0.55; cursor: not-allowed; }
.auth-submit__loading { display: inline-flex; align-items: center; gap: 0.5rem; }
.auth-spinner {
    display: inline-block;
    width: 14px; height: 14px;
    border: 2px solid rgba(255, 255, 255, 0.35);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}
.auth-spinner--plum {
    border-color: rgba(69, 17, 42, 0.2);
    border-top-color: var(--plum);
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Links */
.auth-link {
    color: var(--rose);
    font-weight: 700;
    font-size: 0.88rem;
    text-decoration: none;
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
    font-family: 'Cairo', sans-serif;
    transition: color 0.2s ease;
    margin-inline-start: 0.25rem;
}
.auth-link:hover { color: var(--plum); }

/* Lang */
.auth-lang { display: flex; justify-content: center; margin-top: 1.6rem; padding-top: 1.4rem; border-top: 1px solid var(--line); }
.lang { position: relative; }
.lang__toggle {
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: rgba(255, 255, 255, 0.6);
    border: 1px solid var(--line);
    border-radius: 999px;
    padding: 0.4rem 0.75rem;
    font-size: 0.82rem; font-weight: 700; color: var(--plum);
    cursor: pointer;
    font-family: 'Cairo', sans-serif;
}
.lang__toggle img { width: 16px; height: 16px; border-radius: 3px; }
.lang__menu {
    position: absolute;
    bottom: calc(100% + 8px);
    left: 50%; transform: translateX(-50%);
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 0.75rem;
    box-shadow: 0 18px 40px -18px rgba(69, 17, 42, 0.4);
    padding: 0.3rem;
    min-width: 160px;
    z-index: 60;
    white-space: nowrap;
}
.lang__menu button {
    display: flex; align-items: center; gap: 0.55rem;
    width: 100%;
    padding: 0.55rem 0.7rem;
    border: none; background: none; cursor: pointer;
    border-radius: 0.5rem;
    font-size: 0.88rem; font-weight: 600; color: var(--plum);
    text-align: start;
    font-family: 'Cairo', sans-serif;
}
.lang__menu button img { width: 18px; height: 18px; border-radius: 3px; }
.lang__menu button:hover { background: var(--cream-2); }
.lang__menu button.active { color: var(--rose); background: var(--blush); }

/* Transitions */
.fade-enter-active, .fade-leave-active { transition: opacity 0.18s ease, transform 0.18s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(6px); }

/* PasswordInput override */
:deep(.fv-row) { margin-bottom: 0; }
:deep(.form-label) { display: block; font-size: 0.82rem; font-weight: 700; color: var(--plum); margin-bottom: 0.4rem; }
:deep(.form-control) {
    width: 100%;
    box-sizing: border-box;
    padding: 0.72rem 0.9rem !important;
    border: 1.5px solid var(--line) !important;
    border-radius: 0.65rem !important;
    background: #fff !important;
    color: var(--ink) !important;
    font-family: 'Cairo', sans-serif !important;
    font-size: 0.92rem !important;
    box-shadow: none !important;
}
:deep(.form-control:focus) {
    border-color: var(--rose) !important;
    box-shadow: 0 0 0 3px rgba(208, 46, 121, 0.1) !important;
}
:deep(.input-group) { border-radius: 0.65rem; overflow: hidden; }
:deep(.input-group-text) {
    background: #fff !important;
    border: 1.5px solid var(--line) !important;
    border-inline-start: none !important;
    color: rgba(44, 10, 24, 0.45) !important;
}
</style>
