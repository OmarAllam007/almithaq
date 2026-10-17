<script setup lang="ts">
import Select2Input from '@/components/Inputs/Select2Input.vue';
import { useLang } from '@/composables/useLang';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import noUiSlider from 'nouislider';
import 'nouislider/dist/nouislider.css';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
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

const ageSlider = ref<(HTMLElement & { noUiSlider?: any }) | null>(null);
let resizeObserver: ResizeObserver | null = null;

const initializeAgeSlider = (): void => {
    if (!ageSlider.value || ageSlider.value.noUiSlider || ageSlider.value.offsetWidth === 0) {
        return;
    }

    noUiSlider.create(ageSlider.value, {
        start: [filters.value.age_min, filters.value.age_max],
        connect: true,
        direction: isRtl.value ? 'rtl' : 'ltr',
        range: { min: 18, max: 100 },
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
    <div class="search-panel">
        <div class="card card-flush search-card">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900">{{ trans('home.quick_search') }}</span>
                    <span class="fw-semibold fs-7 mt-1 text-gray-500">{{ trans('home.find_your_perfect_match') }}</span>
                </h3>
            </div>

            <div class="card-body pt-4">
                <div class="row g-4 quick-search-fields">
                    <div class="col-md-6 col-lg-3 quick-search-field">
                        <label class="form-label fw-semibold text-gray-700">{{ trans('home.nationality') }}</label>
                        <Select2Input
                            :model-value="filters.nationality"
                            :options="countryOptions"
                            :placeholder="trans('home.all_nationalities')"
                            @update:model-value="(v) => (filters.nationality = parseCountryValue(v))"
                        />
                    </div>

                    <div class="col-md-6 col-lg-3 quick-search-field">
                        <label class="form-label fw-semibold text-gray-700">{{ trans('home.residence') }}</label>
                        <Select2Input
                            :model-value="filters.residence"
                            :options="countryOptions"
                            :placeholder="trans('home.all_countries')"
                            @update:model-value="(v) => (filters.residence = parseCountryValue(v))"
                        />
                    </div>

                    <div class="col-md-6 col-lg-3 quick-search-field">
                        <label class="form-label fw-semibold text-gray-700">{{ trans('home.city') }}</label>
                        <Select2Input
                            :model-value="filters.city_id"
                            :options="cities"
                            :placeholder="trans('home.all_cities')"
                            :disabled="filters.residence === '' || isLoadingCities"
                            @update:model-value="(v) => (filters.city_id = parseCountryValue(v))"
                        />
                    </div>

                    <div class="col-md-6 col-lg-3 quick-search-field">
                        <label class="form-label fw-semibold text-gray-700">{{ trans('home.marriage_status') }}</label>
                        <Select2Input
                            :model-value="filters.marriage_status"
                            :options="marriageStatusOptions"
                            :placeholder="trans('home.any_status')"
                            @update:model-value="(v) => (filters.marriage_status = parseCountryValue(v))"
                        />
                    </div>

                    <div class="quick-search-age col-12">
                        <div class="quick-search-age__heading">
                            <label class="form-label">{{ trans('home.age_range') }}</label>
                            <span class="quick-search-age__values"
                                ><b>{{ filters.age_min }}</b
                                ><span>–</span><b>{{ filters.age_max }}</b></span
                            >
                        </div>
                        <div ref="ageSlider" class="advanced-search-slider"></div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-3">
                            <button type="button" class="btn btn-light search-reset" @click="resetFilters">
                                <i class="ki-outline ki-arrows-circle fs-2"></i>
                                {{ trans('home.reset') }}
                            </button>
                            <button type="button" class="btn search-submit text-white" @click="handleSearch">
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
    --bs-component-active-bg: #a34268;
}

.advanced-search-slider.noUi-target {
    border: none;
    box-shadow: none;
    background: #f0e6e9;
    height: 6px;
    border-radius: 3px;
}

.advanced-search-slider .noUi-connect {
    background: #a34268;
    border-radius: 3px;
}

.advanced-search-slider.noUi-horizontal .noUi-handle {
    border-radius: 50%;
    background: #a34268 !important;
    border: 2.5px solid #fff !important;
    box-shadow:
        0 0 0 2px #a34268,
        0 2px 6px rgba(163, 66, 104, 0.35) !important;
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

<style scoped>
.search-card {
    border-radius: 19px;
    border: 1px solid #f0e7e7;
    box-shadow: 0 8px 28px #6130480b;
    overflow: hidden;
}
.search-card :deep(.card-header) {
    padding: 27px 30px 0;
    min-height: 88px;
}
.search-card :deep(.card-label) {
    color: #3d2734 !important;
    font-size: 19px;
}
.search-card :deep(.card-body) {
    padding: 22px 30px 28px;
}
.search-card .form-label {
    display: block;
    color: #765b68 !important;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.02em;
    margin-bottom: 9px;
}
.search-card :deep(.form-control),
.search-card :deep(.form-select) {
    border-radius: 10px;
    min-height: 44px;
    border-color: #e9dce1;
    background-color: #fffcfb;
    color: #503845;
    font-size: 12px;
}
.search-card :deep(.select2-container) {
    width: 100% !important;
}
.search-card :deep(.select2-container--bootstrap5 .select2-selection) {
    min-height: 44px;
    border: 1px solid #e9dce1;
    border-radius: 10px;
    background: #fffcfb;
    box-shadow: none;
}
.search-card :deep(.select2-container--bootstrap5.select2-container--focus .select2-selection),
.search-card :deep(.select2-container--bootstrap5.select2-container--open .select2-selection) {
    border-color: #b66183;
    box-shadow: 0 0 0 3px #a342681c;
}
.search-card :deep(.select2-container--disabled .select2-selection) {
    background: #f7f1f3;
}
.quick-search-age {
    margin-top: 3px;
    padding-top: 20px;
    border-top: 1px solid #f2e8eb;
}
.quick-search-age__heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.quick-search-age__heading .form-label {
    margin: 0;
}
.quick-search-age__values {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #a34268;
    font-size: 11px;
}
.quick-search-age__values b {
    min-width: 30px;
    padding: 3px 7px;
    border-radius: 7px;
    background: #f8e9ef;
    text-align: center;
    font-weight: 850;
}
.quick-search-age .advanced-search-slider {
    margin: 21px 11px 11px;
}
.search-submit {
    background: #a34268;
    border-radius: 9px;
    min-height: 42px;
    padding-inline: 19px;
}
.search-submit:hover {
    background: #843153;
}
.search-reset {
    background: #f8e9ef;
    color: #88405f;
    border-radius: 9px;
    min-height: 42px;
}
@media (max-width: 767px) {
    .search-card :deep(.card-header) {
        padding: 20px 18px 0;
    }
    .search-card :deep(.card-body) {
        padding: 14px 18px 22px;
    }
    .quick-search-age {
        margin-top: 0;
    }
}
</style>
