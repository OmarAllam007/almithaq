<script setup lang="ts">
defineProps({
    devotions: { type: Array, required: true, },
    prayer_commitments: { type: Array, required: true, },
    yes_no_list: { type: Array, required: true, },
    education_levels: { type: Array, required: true, },
    financial_statuses: { type: Array, required: true, },
    fields_of_work: { type: Array, required: true, },
    showBeardField: { type: Boolean, required: true, },
    form: {
        type: Object,
        required: true,
    },
});

</script>

<template>
    <!-- Step 4 -->
    <div data-kt-stepper-element="content">
        <div class="w-100">
            <div class="pb-lg-10 pb-5">
                <h2 class="fw-bold fs-3 fs-lg-2 text-gray-900">Lifestyle & Work</h2>
                <div class="text-muted fw-semibold fs-6">Share your lifestyle and career information</div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Devotion</label>
                <select class="form-select form-select-lg" v-model="form.devotion">
                    <option value="">Select...</option>
                    <option v-for="(devotion,index) in devotions" :key="index" :value="index">
                        {{ devotion }}
                    </option>
                </select>
                <div v-if="form.errors.devotion" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.devotion }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Prayer</label>
                <select class="form-select form-select-lg" v-model="form.prayer">
                    <option value="">Select...</option>
                    <option v-for="(prayer,index) in prayer_commitments" :key="index" :value="index">
                        {{ prayer }}
                    </option>
                </select>
                <div v-if="form.errors.prayer" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.prayer }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Smoking</label>
                <select class="form-select form-select-lg" v-model="form.smoking">
                    <option value="">Select...</option>
                    <option v-for="(smoking,index) in yes_no_list" :key="index" :value="index">
                        {{ smoking }}
                    </option>
                </select>
                <div v-if="form.errors.smoking" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.smoking }}</span>
                    </div>
                </div>
            </div>

            <div v-if="showBeardField" class="fv-row mb-5">
                <label class="required form-label">Beard</label>
                <select class="form-select form-select-lg rounded-2" v-model="form.beard">
                    <option value="">Select...</option>
                    <option v-for="(beard,index) in yes_no_list" :key="index" :value="index">
                        {{ beard }}
                    </option>
                </select>
                <div v-if="form.errors.beard" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.beard }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Education Level</label>
                <select class="form-select form-select-lg rounded-2" v-model="form.education_level">
                    <option value="">Select...</option>
                    <option v-for="(education_level,index) in education_levels" :key="index" :value="index">
                        {{ education_level }}
                    </option>
                </select>
                <div v-if="form.errors.education_level" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.education_level }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Financial Status</label>
                <select class="form-select form-select-lg rounded-2" v-model="form.financial_status">
                    <option value="">Select...</option>
                    <option v-for="(financial_status,index) in financial_statuses" :key="index" :value="index">
                        {{ financial_status }}
                    </option>
                </select>
                <div v-if="form.errors.financial_status" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.financial_status }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Field of Work</label>
                <select class="form-select form-select-lg rounded-2" v-model="form.field_of_work">
                    <option value="">Select...</option>
                    <option v-for="(field_of_work,index) in fields_of_work" :key="index" :value="index">
                        {{ field_of_work }}
                    </option>
                </select>
                <div v-if="form.errors.field_of_work" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.field_of_work }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Job</label>
                <input type="text" class="form-control form-control-lg rounded-2" v-model="form.job" />
                <div v-if="form.errors.job" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.job }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<style scoped></style>
