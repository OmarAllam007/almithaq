<script setup lang="ts">
import { check } from '@/actions/App/Http/Controllers/Api/UsernameCheckController';
import { store } from '@/actions/App/Http/Controllers/Auth/RegisterController';
import { showAlertError } from '@/lib/utils';
import PasswordInput from '@/components/Inputs/PasswordInput.vue';
import SelectInput from '@/components/Inputs/SelectInput.vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

const usernameAvailable = ref<boolean | null>(null);
const checkingUsername = ref(false);

const form = useForm({
    registration_type: '',
    username: '',
    password: '',
    confirm_password: '',
    age: null,
    phone_number: '',
});

// Age range from 18 to 90
const ageRange = Array.from({ length: 90 - 18 + 1 }, (_, i) => (18 + i).toString());

function isUsernameLengthValid(): boolean {
    if (!form.username) return true;
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

watch(
    () => form.username,
    () => {
        usernameAvailable.value = null;
        if (form.username && form.username.length > 20) {
            showAlertError('Username must be 20 characters or less', 'Invalid Username Length');
        }
    },
);

const canSubmit = computed(() => {
    return !!(
        form.registration_type &&
        form.username &&
        isUsernameLengthValid() &&
        form.password &&
        form.confirm_password &&
        form.password === form.confirm_password &&
        form.age &&
        form.phone_number &&
        !form.processing
    );
});

function getValidationErrors(): string[] {
    const errors: string[] = [];
    if (!form.registration_type) errors.push('Please select your registration type (Wife or Husband)');
    if (!form.username) errors.push('Username is required');
    if (form.username && !isUsernameLengthValid()) errors.push('Username must be 20 characters or less');
    if (usernameAvailable.value === false) errors.push('Username is already taken');
    if (!form.password) errors.push('Password is required');
    if (!form.confirm_password) errors.push('Password confirmation is required');
    if (form.password && form.confirm_password && form.password !== form.confirm_password) errors.push('Passwords do not match');
    if (!form.age) errors.push('Age is required');
    if (!form.phone_number) errors.push('Phone number is required');
    return errors;
}

function handleSubmit() {
    if (canSubmit.value) {
        form.submit(store());
    } else {
        const errors = getValidationErrors();
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
                    <div class="mw-600px w-100">
                        <div class="pb-lg-10 pb-5 text-center">
                            <h2 class="fw-bold fs-2 text-gray-900">Create Account</h2>
                            <div class="text-muted fw-semibold fs-6">Quick registration — you'll complete your profile
                                after signing up</div>
                        </div>

                        <form class="form w-100" @submit.prevent="handleSubmit">
                            <!-- Account Type -->
                            <div class="pb-5">
                                <h4 class="text-primary py-2">Account Type</h4>
                                <div class="fv-row">
                                    <input type="radio" class="btn-check" name="registration_type" value="husband"
                                        :checked="form.registration_type === 'husband'" id="reg_type_husband"
                                        @click="form.registration_type = 'husband'" />
                                    <label
                                        class="btn btn-outline-primary btn-outline-dashed btn-active-light-primary d-flex align-items-center mb-3 p-5"
                                        for="reg_type_husband">
                                        <i class="ki-outline ki-user fs-2x me-3"></i>
                                        <span class="d-block fw-semibold text-start">
                                            <span class="fw-bold d-block fs-4 text-active-primary">Husband</span>
                                            <span class="text-muted fw-semibold fs-7">Register as a husband</span>
                                        </span>
                                    </label>

                                    <input type="radio" class="btn-check" name="registration_type" value="wife"
                                        :checked="form.registration_type === 'wife'" id="reg_type_wife"
                                        @click="form.registration_type = 'wife'" />
                                    <label
                                        class="btn btn-outline-danger btn-outline-dashed btn-active-light-danger d-flex align-items-center mb-3 p-5"
                                        for="reg_type_wife">
                                        <i class="ki-outline ki-user fs-2x me-3"></i>
                                        <span class="d-block fw-semibold text-start">
                                            <span class="fw-bold d-block fs-4 text-active-danger">Wife</span>
                                            <span class="text-muted fw-semibold fs-7">Register as a wife</span>
                                        </span>
                                    </label>

                                    <div v-if="form.errors.registration_type" class="fv-plugins-message-container">
                                        <div class="fv-help-block">
                                            <span role="alert">{{ form.errors.registration_type }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Login Information -->
                            <h4 class="text-primary py-2">Login Information</h4>

                            <div class="fv-row mb-5">
                                <label class="required form-label">Username (max 20 characters)</label>
                                <div class="position-relative">
                                    <input type="text" class="form-control form-control-lg rounded-2"
                                        v-model="form.username" maxlength="20" @blur="checkUsernameAvailability" />
                                    <span v-if="checkingUsername"
                                        class="position-absolute translate-middle-y end-0 top-50 me-3">
                                        <span class="spinner-border spinner-border-sm text-primary"></span>
                                    </span>
                                    <span v-else-if="usernameAvailable === true"
                                        class="position-absolute translate-middle-y end-0 top-50 me-3">
                                        <i class="ki-outline ki-check fs-2 text-success"></i>
                                    </span>
                                    <span v-else-if="usernameAvailable === false"
                                        class="position-absolute translate-middle-y end-0 top-50 me-3">
                                        <i class="ki-outline ki-cross fs-2 text-danger"></i>
                                    </span>
                                </div>
                                <div v-if="form.errors.username" class="fv-plugins-message-container">
                                    <div class="fv-help-block">
                                        <span role="alert">{{ form.errors.username }}</span>
                                    </div>
                                </div>
                            </div>

                            <PasswordInput v-model="form.password" />
                            <PasswordInput v-model="form.confirm_password" label="Confirm Password" />

                            <!-- Basic Info -->
                            <h4 class="text-primary py-2">Basic Information</h4>

                            <SelectInput v-model="form.age" :options="ageRange" label="Age" />

                            <div class="fv-row mb-5">
                                <label class="required form-label">Phone Number</label>
                                <input type="tel" class="form-control form-control-lg rounded-2"
                                    v-model="form.phone_number" />
                                <div v-if="form.errors.phone_number" class="fv-plugins-message-container">
                                    <div class="fv-help-block">
                                        <span role="alert">{{ form.errors.phone_number }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="d-flex flex-stack pt-5">
                                <div></div>
                                <button type="submit" class="btn btn-lg btn-primary" :disabled="!canSubmit">
                                    <span v-if="!form.processing">
                                        Create Account
                                        <i class="ki-outline ki-arrow-right fs-3 ms-1"></i>
                                    </span>
                                    <span v-else>
                                        <span class="spinner-border spinner-border-sm align-middle"></span>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
