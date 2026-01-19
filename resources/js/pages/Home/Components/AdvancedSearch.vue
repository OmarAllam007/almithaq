<script setup lang="ts">
import noUiSlider from 'nouislider';
import 'nouislider/dist/nouislider.css';
import { onMounted, ref } from 'vue';

const filters = ref({
    nationality: '',
    residence: '',
    city: '',
    ageMin: 18,
    ageMax: 60,
    marriage_status: '',
});

const ageSlider = ref<HTMLElement | null>(null);

onMounted(() => {
    if (ageSlider.value) {
        noUiSlider.create(ageSlider.value, {
            start: [filters.value.ageMin, filters.value.ageMax],
            connect: true,
            range: {
                min: 18,
                max: 80,
            },
        });

        ageSlider.value.noUiSlider?.on('update', (values: (string | number)[]) => {
            filters.value.ageMin = Math.round(Number(values[0]));
            filters.value.ageMax = Math.round(Number(values[1]));
        });
    }
});

const handleSearch = () => {
    // Will be implemented when actual data is provided
    console.log('Search filters:', filters.value);
};

const resetFilters = () => {
    filters.value = {
        nationality: '',
        residence: '',
        city: '',
        ageMin: 18,
        ageMax: 60,
        marriage_status: '',
    };

    if (ageSlider.value?.noUiSlider) {
        ageSlider.value.noUiSlider.set([18, 60]);
    }
};
</script>

<template>
    <div class="col-xl-12 mb-10">
        <!--begin::Advanced Search Card-->
        <div class="card card-flush h-xl-100">
            <!--begin::Header-->
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900">Advanced Search</span>
                    <span class="fw-semibold fs-7 mt-1 text-gray-500">Find your perfect match</span>
                </h3>
            </div>
            <!--end::Header-->

            <!--begin::Body-->
            <div class="card-body pt-5">
                <div class="row g-5">
                    <!--begin::Nationality-->
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label fw-semibold text-gray-700">Nationality</label>
                        <select v-model="filters.nationality" class="form-select form-select-solid">
                            <option value="">Select Nationality</option>
                            <option value="saudi">Saudi Arabia</option>
                            <option value="egypt">Egypt</option>
                            <option value="jordan">Jordan</option>
                            <option value="uae">United Arab Emirates</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <!--end::Nationality-->

                    <!--begin::Residence-->
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label fw-semibold text-gray-700">Residence</label>
                        <select v-model="filters.residence" class="form-select form-select-solid">
                            <option value="">Select Residence</option>
                            <option value="saudi">Saudi Arabia</option>
                            <option value="egypt">Egypt</option>
                            <option value="jordan">Jordan</option>
                            <option value="uae">United Arab Emirates</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <!--end::Residence-->

                    <!--begin::City-->
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label fw-semibold text-gray-700">City</label>
                        <select v-model="filters.city" class="form-select form-select-solid">
                            <option value="">Select City</option>
                            <option value="riyadh">Riyadh</option>
                            <option value="jeddah">Jeddah</option>
                            <option value="mecca">Mecca</option>
                            <option value="medina">Medina</option>
                            <option value="cairo">Cairo</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <!--end::City-->

                    <!--begin::Marriage Status-->
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label fw-semibold text-gray-700">Marriage Status</label>
                        <select v-model="filters.marriage_status" class="form-select form-select-solid">
                            <option value="">Select Status</option>
                            <option value="single">Single</option>
                            <option value="divorced">Divorced</option>
                            <option value="widowed">Widowed</option>
                        </select>
                    </div>
                    <!--end::Marriage Status-->

                    <!--begin::Age Range-->
                    <div class="col-md-12 col-lg-8">
                        <label class="form-label fw-semibold text-gray-700">Age Range</label>
                        <div ref="ageSlider" class="mb-5"></div>
                        <div class="d-flex justify-content-between">
                            <div class="fw-semibold text-gray-700">
                                Min: <span class="text-gray-900">{{ filters.ageMin }}</span> years
                            </div>
                            <div class="fw-semibold text-gray-700">
                                Max: <span class="text-gray-900">{{ filters.ageMax }}</span> years
                            </div>
                        </div>
                    </div>
                    <!--end::Age Range-->

                    <!--begin::Search Button-->
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-3">
                            <button type="button" class="btn btn-light" @click="resetFilters">
                                <i class="ki-outline ki-arrows-circle fs-2"></i>
                                Reset Filters
                            </button>
                            <button type="button" class="btn btn-primary" @click="handleSearch">
                                <i class="ki-outline ki-magnifier fs-2"></i>
                                Search
                            </button>
                        </div>
                    </div>
                    <!--end::Search Button-->
                </div>
            </div>
            <!--end::Body-->
        </div>
        <!--end::Advanced Search Card-->
    </div>
</template>

<style scoped></style>
