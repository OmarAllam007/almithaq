<script setup lang="ts">
defineProps({
    countries: { type: Array, required: true },

    form: {
        type: Object,
        required: true,
    },
});
const weightRange = Array.from({ length: 126 }, (_, i) => i + 45);
const heightRange = Array.from({ length: 100 }, (_, i) => i + 120);
const skinColors = ['White', 'Light Brown', 'Brown', 'Dark Brown', 'Black'];
const bodyShapes = ['Slim', 'Sporty', 'Average', 'Muscular', 'Overweight'];
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
                <label class="required form-label">Country of residence</label>
                <select class="form-select form-select-lg" v-model="form.residence">
                    <option value="">Select...</option>
                    <option v-for="country in countries" :key="country.id" :value="country.id">
                        {{ country.name }}
                    </option>
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
                    <option v-for="country in countries" :key="country.id" :value="country.id">
                        {{ country.name }}
                    </option>
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
                <select class="form-select form-select-lg" v-model="form.religion" disabled>
                    <option value="">Select...</option>
                    <option value="1">Islamic only</option>
                </select>
                <div v-if="form.errors.religion" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.religion }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Weight (kg)</label>
                <select class="form-select form-select-lg" v-model="form.weight">
                    <option value="">Select...</option>
                    <option v-for="weight in weightRange" :key="weight" :value="weight">
                        {{ weight }}
                    </option>
                </select>

                <div v-if="form.errors.weight" class="fv-plugins-message-container">
                    <div class="fv-help-block">
                        <span role="alert">{{ form.errors.weight }}</span>
                    </div>
                </div>
            </div>

            <div class="fv-row mb-5">
                <label class="required form-label">Height (cm)</label>
                <select class="form-select form-select-lg" v-model="form.height">
                    <option value="">Select...</option>
                    <option v-for="height in heightRange" :key="height" :value="height">
                        {{ height }}
                    </option>
                </select>
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
                    <option v-for="skinColor in skinColors" :key="skinColor" :value="skinColor">
                        {{ skinColor }}
                    </option>
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
                    <option v-for="bodyShape in bodyShapes" :key="bodyShape" :value="bodyShape">
                        {{ bodyShape }}
                    </option>
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
