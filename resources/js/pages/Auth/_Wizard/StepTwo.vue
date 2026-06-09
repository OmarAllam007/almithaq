<script setup lang="ts">
import PasswordInput from '@/components/Inputs/PasswordInput.vue';
import SelectInput from '@/components/Inputs/SelectInput.vue';

defineProps({
    form: {
        type: Object,
        required: true,
    },
});

const marriageTypes = {
    husband: ['First wife', 'Second wife', 'Only one wife', 'Accept polygamy'],
    wife: ['Only one wife', 'Accept polygamy'],
};

const marriageStatuses = {
    husband: ['Single', 'Divorced', 'Widowed'],
    wife: ['Single', 'Divorced', 'Widowed'],
};

// range from 18 to 90
const ageRange = Array.from({ length: 90 - 18 + 1 }, (_, i) => 18 + i);

const childCount = [0, 1, 2, 3, 4, 5, 6, 7, 8];
</script>

<template>
    <!-- Step 2 -->
    <div data-kt-stepper-element="content">
        <div class="w-100">
            <div class="pb-lg-10 pb-5">
                <h2 class="fw-bold fs-3 fs-lg-2 text-gray-900">Basic Account Information</h2>
                <div class="text-muted fw-semibold fs-6">Provide your account details</div>
            </div>

            <h4 class="text-primary py-2">Login Information</h4>

            <div class="fv-row mb-5">
                <label class="required form-label">Username (max 20 characters)</label>
                <div class="position-relative">
                    <input type="text" class="form-control form-control-lg rounded-2" v-model="form.username" maxlength="20" />
                    <span v-if="checkingUsername" class="position-absolute translate-middle-y end-0 top-50 me-3">
                        <span class="spinner-border spinner-border-sm text-primary"></span>
                    </span>
                    <span v-else-if="usernameAvailable === true" class="position-absolute translate-middle-y end-0 top-50 me-3">
                        <i class="ki-outline ki-check fs-2 text-success"></i>
                    </span>
                    <span v-else-if="usernameAvailable === false" class="position-absolute translate-middle-y end-0 top-50 me-3">
                        <i class="ki-outline ki-cross fs-2 text-danger"></i>
                    </span>
                </div>
                <div v-if="form.errors.username" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.username }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="form-label">Email</label>
                <input type="email" class="form-control form-control-lg rounded-2" v-model="form.email" />
                <div v-if="form.errors.email" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.email }}</span>
                    </div>
                </div>
            </div>

            <PasswordInput v-model="form.password" />
            <PasswordInput v-model="form.confirm_password" />

            <h4 class="text-primary py-2">Familial status</h4>
            <SelectInput v-model="form.marriage_type" :options="marriageTypes[form.registration_type] || []" label="Marriage Type" />
            <SelectInput v-model="form.marriage_status" :options="marriageStatuses[form.registration_type] || []" label="Marriage Status" />
            <SelectInput v-model="form.age" :options="ageRange.map((age) => age.toString())" label="Age" />
            <SelectInput v-model="form.child_count" :options="childCount" label="Child Count" />
        </div>
    </div>
</template>

<style scoped></style>
