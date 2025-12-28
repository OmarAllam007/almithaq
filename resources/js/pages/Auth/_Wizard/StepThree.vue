<script setup lang="ts">
defineProps({
    form: {
        type: Object,
        required: true,
    },
});

</script>

<template>
    <!-- Step 3 -->
    <div data-kt-stepper-element="content">
        <div class="w-100">
            <div class="pb-lg-10 pb-5">
                <h2 class="fw-bold fs-3 fs-lg-2 text-gray-900">Personal Information</h2>
                <div class="text-muted fw-semibold fs-6">Tell us more about yourself</div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Residence</label>
                <select class="form-select form-select-lg" v-model="form.residence">
                    <option value="">Select...</option>
                </select>
                <div v-if="form.errors.residence" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.residence }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Nationality</label>
                <select class="form-select form-select-lg" v-model="form.nationality">
                    <option value="">Select...</option>
                </select>
                <div v-if="form.errors.nationality" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.nationality }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">City</label>
                <input type="text" class="form-control form-control-lg rounded-2" v-model="form.city" />
                <div v-if="form.errors.city" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.city }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Religion</label>
                <select class="form-select form-select-lg" v-model="form.religion">
                    <option value="">Select...</option>
                </select>
                <div v-if="form.errors.religion" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.religion }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Ethnicity</label>
                <select class="form-select form-select-lg" v-model="form.ethnicity">
                    <option value="">Select...</option>
                </select>
                <div v-if="form.errors.ethnicity" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.ethnicity }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Weight (kg)</label>
                <input
                    type="number"
                    step="0.01"
                    class="form-control form-control-lg rounded-2"
                    v-model="form.weight"
                />
                <div v-if="form.errors.weight" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.weight }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Height (cm)</label>
                <input
                    type="number"
                    step="0.01"
                    class="form-control form-control-lg rounded-2"
                    v-model="form.height"
                />
                <div v-if="form.errors.height" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.height }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Skin Color</label>
                <select class="form-select form-select-lg" v-model="form.skin_color">
                    <option value="">Select...</option>
                </select>
                <div v-if="form.errors.skin_color" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.skin_color }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Body Shape</label>
                <select class="form-select form-select-lg" v-model="form.body_shape">
                    <option value="">Select...</option>
                </select>
                <div v-if="form.errors.body_shape" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.body_shape }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<style scoped></style>
