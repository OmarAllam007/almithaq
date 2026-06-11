<script setup lang="ts">
import noUiSlider from 'nouislider';
import 'nouislider/dist/nouislider.css';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { useLang } from '@/composables/useLang';
import Select2Input from '@/components/Inputs/Select2Input.vue';
const { trans } = useLang();
const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');

interface Props {
    countries?: Array<{ id: number; name: string; ar_name: string }>;
    marriageStatuses?: Array<{ value: number; label: string }>;
}

const props = withDefaults(defineProps<Props>(), {
    countries: () => [],
    marriageStatuses: () => [],
});

const filters = ref({
    nationality: '' as number | '',
    residence: '' as number | '',
    city_id: '' as number | '',
    age_min: 18,
    age_max: 60,
    marriage_status: '' as number | '',
});

const cities = ref<Array<{ value: number; label: string }>>([]);
const isLoadingCities = ref(false);

watch(
    () => filters.value.residence,
    async (newResidence) => {
        filters.value.city_id = '';
        cities.value = [];

        if (newResidence === '') return;

        isLoadingCities.value = true;
        try {
            const response = await axios.get('/api/cities', { params: { country_id: newResidence } });
            cities.value = response.data.map((c: { id: number; name: string; ar_name: string }) => ({
                value: c.id,
                label: isRtl.value ? c.ar_name : c.name,
            }));
        } finally {
            isLoadingCities.value = false;
        }
    },
);

const countryOptions = computed(() =>
    props.countries.map((c) => ({
        value: c.id,
        label: isRtl.value ? c.ar_name : c.name,
    })),
);

const marriageStatusOptions = computed(() =>
    props.marriageStatuses.map((s) => ({
        value: s.value,
        label: s.label,
    })),
);

const parseCountryValue = (v: number | string): number | '' => (v === '' ? '' : Number(v));

const ageSlider = ref<HTMLElement | null>(null);
let resizeObserver: ResizeObserver | null = null;

const initializeAgeSlider = (): void => {
    if (!ageSlider.value || ageSlider.value.noUiSlider || ageSlider.value.offsetWidth === 0) {
        return;
    }

    noUiSlider.create(ageSlider.value, {
        start: [filters.value.age_min, filters.value.age_max],
        connect: true,
        direction: isRtl.value ? 'rtl' : 'ltr',
        range: { min: 18, max: 80 },
        step: 1,
    });

    ageSlider.value.noUiSlider.on('update', (values: (string | number)[]) => {
        filters.value.age_min = Math.round(Number(values[0]));
        filters.value.age_max = Math.round(Number(values[1]));
    });
};

const refreshAgeSlider = (): void => {
    ageSlider.value?.noUiSlider?.set([filters.value.age_min, filters.value.age_max]);
};

onMounted(() => {
    nextTick(() => {
        initializeAgeSlider();

        if (!ageSlider.value?.noUiSlider && ageSlider.value) {
            resizeObserver = new ResizeObserver(() => {
                initializeAgeSlider();

                if (ageSlider.value?.noUiSlider) {
                    resizeObserver?.disconnect();
                    resizeObserver = null;
                    refreshAgeSlider();
                }
            });
            resizeObserver.observe(ageSlider.value);
        }

        // Home page entrance animation is 500ms — recalculate handle positions once layout settles.
        window.setTimeout(refreshAgeSlider, 550);
    });
});

onUnmounted(() => {
    resizeObserver?.disconnect();
    ageSlider.value?.noUiSlider?.destroy();
});

const handleSearch = () => {
    const payload: Record<string, any> = {
        age_min: filters.value.age_min,
        age_max: filters.value.age_max,
    };

    if (filters.value.nationality !== '') {
        payload.nationality = filters.value.nationality;
    }
    if (filters.value.residence !== '') {
        payload.residence = filters.value.residence;
    }
    if (filters.value.city_id !== '') {
        payload.city_id = filters.value.city_id;
    }
    if (filters.value.marriage_status !== '') {
        payload.marriage_status = filters.value.marriage_status;
    }

    router.get('/quick-search', payload);
};

const resetFilters = () => {
    filters.value = {
        nationality: '',
        residence: '',
        city_id: '',
        age_min: 18,
        age_max: 60,
        marriage_status: '',
    };

    cities.value = [];

    if (ageSlider.value?.noUiSlider) {
        ageSlider.value.noUiSlider.set([18, 60]);
    }
};
</script>

<template>
    <div class="col-xl-12 mb-8">
        <div class="card card-flush">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900">{{ trans('home.quick_search') }}</span>
                    <span class="fw-semibold fs-7 mt-1 text-gray-500">{{ trans('home.find_your_perfect_match') }}</span>
                </h3>
            </div>

            <div class="card-body pt-4">
                <div class="row g-5">
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label fw-semibold text-gray-700">{{ trans('home.nationality') }}</label>
                        <Select2Input
                            :model-value="filters.nationality"
                            :options="countryOptions"
                            :placeholder="trans('home.all_nationalities')"
                            @update:model-value="(v) => (filters.nationality = parseCountryValue(v))"
                        />
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label class="form-label fw-semibold text-gray-700">{{ trans('home.residence') }}</label>
                        <Select2Input
                            :model-value="filters.residence"
                            :options="countryOptions"
                            :placeholder="trans('home.all_countries')"
                            @update:model-value="(v) => (filters.residence = parseCountryValue(v))"
                        />
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label class="form-label fw-semibold text-gray-700">{{ trans('home.city') }}</label>
                        <Select2Input
                            :model-value="filters.city_id"
                            :options="cities"
                            :placeholder="trans('home.all_cities')"
                            :disabled="filters.residence === '' || isLoadingCities"
                            @update:model-value="(v) => (filters.city_id = parseCountryValue(v))"
                        />
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label class="form-label fw-semibold text-gray-700">{{ trans('home.marriage_status') }}</label>
                        <Select2Input
                            :model-value="filters.marriage_status"
                            :options="marriageStatusOptions"
                            :placeholder="trans('home.any_status')"
                            @update:model-value="(v) => (filters.marriage_status = parseCountryValue(v))"
                        />
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-gray-700">
                            {{ trans('home.age_range') }}:
                            <span class="text-gray-800 fw-bold">{{ filters.age_min }} – {{ filters.age_max }}</span>
                        </label>
                        <div ref="ageSlider" class=" mt-3 mb-2"></div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-3">
                            <button type="button" class="btn btn-light" @click="resetFilters">
                                <i class="ki-outline ki-arrows-circle fs-2"></i>
                                {{ trans('home.reset') }}
                            </button>
                            <button type="button" class="btn text-white" style="background-color:#d02e79;" @click="handleSearch">
                                <i class="ki-outline ki-magnifier fs-2 text-white"></i>
                                {{ trans('home.search') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.advanced-search-slider {
    --bs-component-active-bg: rgb(208, 46, 121);
}

.advanced-search-slider.noUi-target {
    border: none;
    box-shadow: none;
    background: var(--bs-gray-200, #f1f1f4);
    height: 6px;
    border-radius: 3px;
}

.advanced-search-slider .noUi-connect {
    background: rgb(208, 46, 121);
    border-radius: 3px;
}

.advanced-search-slider.noUi-horizontal .noUi-handle {
    border-radius: 50%;
    background: rgb(208, 46, 121) !important;
    border: 2.5px solid #fff !important;
    box-shadow:
        0 0 0 2px rgb(208, 46, 121),
        0 2px 6px rgba(208, 46, 121, 0.35) !important;
    width: 18px !important;
    height: 18px !important;
    top: -7px !important;
    right: -9px !important;
    cursor: grab;
}

.advanced-search-slider.noUi-horizontal .noUi-handle:active {
    cursor: grabbing;
    transform: scale(1.15);
}

[dir='rtl'] .advanced-search-slider.noUi-horizontal .noUi-handle {
    right: auto !important;
    left: -9px !important;
}

.advanced-search-slider .noUi-handle::before,
.advanced-search-slider .noUi-handle::after {
    display: none;
}
</style>
