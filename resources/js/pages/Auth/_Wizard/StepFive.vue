<script setup lang="ts">
defineProps({
    form: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <!-- Step 5 -->
    <div data-kt-stepper-element="content">
        <div class="w-100">
            <div class="pb-lg-10 pb-5">
                <h2 class="fw-bold fs-3 fs-lg-2 text-gray-900">Additional Information</h2>
                <div class="text-muted fw-semibold fs-6">Final details to complete your profile</div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Monthly Income</label>
                <input type="number" step="0.01" class="form-control form-control-lg rounded-2" v-model="form.monthly_income" />
                <div v-if="form.errors.monthly_income" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.monthly_income }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Health Status</label>
                <select class="form-select form-select-lg rounded-2" v-model="form.health_status">
                    <option value="">Select...</option>
                </select>
                <div v-if="form.errors.health_status" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.health_status }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">About Your Partner</label>
                <textarea class="form-control form-control-lg" rows="4" v-model="form.about_partner"></textarea>
                <div v-if="form.errors.about_partner" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.about_partner }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">About Yourself</label>
                <textarea class="form-control form-control-lg" rows="4" v-model="form.about_self"></textarea>
                <div v-if="form.errors.about_self" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.about_self }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Full Name</label>
                <input type="text" class="form-control form-control-lg" v-model="form.full_name" />
                <div v-if="form.errors.full_name" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.full_name }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Phone Number</label>
                <input type="tel" class="form-control form-control-lg" v-model="form.phone_number" />
                <div v-if="form.errors.phone_number" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.phone_number }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
