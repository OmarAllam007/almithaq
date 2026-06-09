<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import UserCard from '@/components/UserCard.vue';
import UserProfileModal from '@/components/UserProfileModal.vue';
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';
import Pagination from '@/components/Pagination.vue';
import GoogleAd from '@/components/GoogleAd.vue';
import Select2MultiInput from '@/components/Inputs/Select2MultiInput.vue';
import { PaginationData } from '@/types/pagination';
import axios from 'axios';
import noUiSlider from 'nouislider';
import 'nouislider/dist/nouislider.css';
import { useLang } from '@/composables/useLang';
const { trans } = useLang();
const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');

interface Props {
    users: PaginationData | null;
    cachedFilters: any;
    filterOptions: any;
    countries: Array<{ id: number; name: string; ar_name: string }>;
}

const props = defineProps<Props>();

// State - Always show filters initially unless we have users (from pagination)
const showFilters = ref(!props.users);
const filters = ref({
    residence: props.cachedFilters?.residence || [],
    nationality: props.cachedFilters?.nationality || [],
    marriage_status: props.cachedFilters?.marriage_status || [],
    marriage_type: props.cachedFilters?.marriage_type || [],
    age_min: props.cachedFilters?.age_min || 18,
    age_max: props.cachedFilters?.age_max || 60,
    body_shape: props.cachedFilters?.body_shape || [],
    skin_color: props.cachedFilters?.skin_color || [],
    weight_min: props.cachedFilters?.weight_min || 40,
    weight_max: props.cachedFilters?.weight_max || 150,
    height_min: props.cachedFilters?.height_min || 140,
    height_max: props.cachedFilters?.height_max || 220,
    education_level: props.cachedFilters?.education_level || [],
    financial_status: props.cachedFilters?.financial_status || [],
    devotion: props.cachedFilters?.devotion || [],
    prayer: props.cachedFilters?.prayer || [],
    smoking: props.cachedFilters?.smoking || [],
});

// Chat and Profile Modal state
const isChatDrawerOpen = ref(false);
const isProfileModalOpen = ref(false);
const selectedUserId = ref<number | undefined>(undefined);
const selectedProfileUserId = ref<number | undefined>(undefined);

// Slider refs
const ageSlider = ref<HTMLElement | null>(null);
const weightSlider = ref<HTMLElement | null>(null);
const heightSlider = ref<HTMLElement | null>(null);

// Initialize sliders
onMounted(() => {
    initializeSliders();
});

const initializeSliders = () => {
    nextTick(() => {
        // Age Slider
        const ageEl = ageSlider.value as any;
        if (ageEl && !ageEl.noUiSlider) {
            noUiSlider.create(ageEl, {
                start: [filters.value.age_min, filters.value.age_max],
                connect: true,
                direction: isRtl.value ? 'rtl' : 'ltr',
                range: { min: 18, max: 100 },
                step: 1,
                tooltips: true,
                format: {
                    to: (value) => Math.round(value),
                    from: (value) => Number(value),
                },
            });

            ageEl.noUiSlider.on('update', (values: any) => {
                filters.value.age_min = parseInt(values[0]);
                filters.value.age_max = parseInt(values[1]);
            });
        }

        // Weight Slider
        const kgLabel = trans('smart.kg');
        const weightEl = weightSlider.value as any;
        if (weightEl && !weightEl.noUiSlider) {
            noUiSlider.create(weightEl, {
                start: [filters.value.weight_min, filters.value.weight_max],
                connect: true,
                direction: isRtl.value ? 'rtl' : 'ltr',
                range: { min: 30, max: 200 },
                step: 1,
                tooltips: true,
                format: {
                    to: (value) => Math.round(value) + ' ' + kgLabel,
                    from: (value) => Number(String(value).replace(' ' + kgLabel, '')),
                },
            });

            weightEl.noUiSlider.on('update', (values: any) => {
                filters.value.weight_min = parseInt(String(values[0]));
                filters.value.weight_max = parseInt(String(values[1]));
            });
        }

        // Height Slider
        const cmLabel = trans('smart.cm');
        const heightEl = heightSlider.value as any;
        if (heightEl && !heightEl.noUiSlider) {
            noUiSlider.create(heightEl, {
                start: [filters.value.height_min, filters.value.height_max],
                connect: true,
                direction: isRtl.value ? 'rtl' : 'ltr',
                range: { min: 120, max: 230 },
                step: 1,
                tooltips: true,
                format: {
                    to: (value) => Math.round(value) + ' ' + cmLabel,
                    from: (value) => Number(String(value).replace(' ' + cmLabel, '')),
                },
            });

            heightEl.noUiSlider.on('update', (values: any) => {
                filters.value.height_min = parseInt(String(values[0]));
                filters.value.height_max = parseInt(String(values[1]));
            });
        }
    });
};

// Watch for filter panel toggle to reinitialize sliders
watch(showFilters, (newValue) => {
    if (newValue) {
        nextTick(() => {
            initializeSliders();
        });
    }
});

// Watch for users prop changes (from pagination) and hide filters
watch(() => props.users, (newUsers) => {
    if (newUsers) {
        showFilters.value = false;
    }
});

const submitFilters = () => {
    router.post('/smart-search/search', filters.value, {
        preserveState: false,
        preserveScroll: false,
        onSuccess: () => {
            showFilters.value = false;
        }
    });
};

const clearAllFilters = () => {
    router.post('/smart-search/clear', {}, {
        preserveState: false,
        onSuccess: () => {
            showFilters.value = true;
        }
    });
};

const toggleFilters = () => {
    showFilters.value = !showFilters.value;
};

const handleMessage = (userId: number) => {
    selectedUserId.value = userId;
    isChatDrawerOpen.value = true;
};

const handleCloseChat = () => {
    isChatDrawerOpen.value = false;
    selectedUserId.value = undefined;
};

const handleViewProfile = (userId: number) => {
    selectedProfileUserId.value = userId;
    isProfileModalOpen.value = true;
};

const handleCloseProfile = () => {
    isProfileModalOpen.value = false;
    selectedProfileUserId.value = undefined;
};

const handleMessageFromModal = async (userId: number) => {
    handleCloseProfile();

    try {
        await axios.post('/conversations', {
            recipient_id: userId,
        });

        selectedUserId.value = userId;
        isChatDrawerOpen.value = true;
    } catch (error) {
        console.error('Failed to get or create conversation:', error);
    }
};
</script>

<template>
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!-- Toolbar -->
            <div id="kt_app_toolbar" class="app-toolbar py-lg-0 py-3">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center me-3">
                        <h1 class="page-heading d-flex fw-bold fs-3 flex-column justify-content-center my-0 text-gray-900">
                            {{ trans('smart.smart_search') }}
                        </h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="/" class="text-muted text-hover-primary">{{ trans('home.home') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet w-5px h-2px bg-gray-500"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">{{ trans('smart.smart_search') }}</li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2" v-if="users">
                        <button @click="toggleFilters" class="btn btn-sm btn-primary">
                            <i class="ki-outline ki-filter fs-3 me-2"></i>
                            {{ showFilters ? trans('smart.hide_filters') : trans('smart.edit_criteria') }}
                        </button>
                        <button @click="clearAllFilters" class="btn btn-sm btn-light-danger">
                            <i class="ki-outline ki-trash fs-3 me-2"></i>
                            {{ trans('smart.clear_filters') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <!-- Filter Form -->
                    <div v-if="showFilters" class="row g-6 g-xl-9 mb-6 mb-xl-9">
                        <!-- Location Card -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">{{ trans('smart.location_and_nationality') }}</h3>
                                </div>
                                <div class="card-body py-7">
                                    <div class="row g-5 align-items-stretch">
                                        <!-- Residence -->
                                        <div class="col-12 col-md pe-md-6">
                                            <label class="form-label">{{ trans('smart.residence') }}</label>
                                            <Select2MultiInput
                                                v-model="filters.residence"
                                                :options="countries.map(c => ({ value: c.id, label: isRtl ? c.ar_name : c.name }))"
                                                :placeholder="trans('smart.select_country')"
                                            />
                                        </div>

                                        <div class="col-auto d-none d-md-flex align-items-stretch px-0">
                                            <div class="border-start border-dashed border-gray-300"></div>
                                        </div>

                                        <!-- Nationality -->
                                        <div class="col-12 col-md ps-md-6">
                                            <label class="form-label">{{ trans('smart.nationality') }}</label>
                                            <Select2MultiInput
                                                v-model="filters.nationality"
                                                :options="countries.map(c => ({ value: c.id, label: isRtl ? c.ar_name : c.name }))"
                                                :placeholder="trans('smart.select_country')"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Information Card -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">{{ trans('smart.basic_information') }}</h3>
                                </div>
                                <div class="card-body py-7">
                                    <div class="row g-5 align-items-stretch">
                                        <!-- Marriage Status -->
                                        <div class="col-12 col-md pe-md-6">
                                            <label class="form-label">{{ trans('smart.marriage_status') }}</label>
                                            <div class="d-flex flex-column gap-3">
                                                <div v-for="status in filterOptions.marriage_statuses" :key="status.value" class="form-check">
                                                    <input
                                                        v-model="filters.marriage_status"
                                                        :value="status.value"
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        :id="'marriage_' + status.value"
                                                    />
                                                    <label class="form-check-label fw-semibold text-gray-800" :for="'marriage_' + status.value">
                                                        {{ status.label }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-auto d-none d-md-flex align-items-stretch px-0">
                                            <div class="border-start border-dashed border-gray-300"></div>
                                        </div>

                                        <!-- Marriage Type -->
                                        <div class="col-12 col-md ps-md-6">
                                            <label class="form-label">{{ trans('smart.marriage_type') }}</label>
                                            <div class="d-flex flex-column gap-3">
                                                <div v-for="type in filterOptions.marriage_types" :key="type.value" class="form-check">
                                                    <input
                                                        v-model="filters.marriage_type"
                                                        :value="type.value"
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        :id="'marriage_type_' + type.value"
                                                    />
                                                    <label class="form-check-label fw-semibold text-gray-800" :for="'marriage_type_' + type.value">
                                                        {{ type.label }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Separator -->
                                        <div class="col-12"><div class="separator separator-dashed"></div></div>

                                        <!-- Age Range -->
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">{{ trans('smart.age_range') }}</label>
                                            <div ref="ageSlider" class="mt-5"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Physical Attributes Card -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">{{ trans('smart.physical_attributes') }}</h3>
                                </div>
                                <div class="card-body py-7">
                                    <div class="row g-5 align-items-stretch">
                                        <!-- Body Shape -->
                                        <div class="col-12 col-md pe-md-6">
                                            <label class="form-label">{{ trans('smart.body_shape') }}</label>
                                            <select v-model="filters.body_shape" class="form-select" multiple size="5">
                                                <option v-for="shape in filterOptions.body_shapes" :key="shape.value" :value="shape.value">
                                                    {{ shape.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <div class="col-auto d-none d-md-flex align-items-stretch px-0">
                                            <div class="border-start border-dashed border-gray-300"></div>
                                        </div>

                                        <!-- Skin Color -->
                                        <div class="col-12 col-md ps-md-6">
                                            <label class="form-label">{{ trans('smart.skin_color') }}</label>
                                            <select v-model="filters.skin_color" class="form-select" multiple size="5">
                                                <option v-for="color in filterOptions.skin_colors" :key="color.value" :value="color.value">
                                                    {{ color.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Separator -->
                                        <div class="col-12"><div class="separator separator-dashed"></div></div>

                                        <!-- Weight Range -->
                                        <div class="col-12 col-md pe-md-6">
                                            <label class="form-label">{{ trans('smart.weight_range') }}</label>
                                            <div ref="weightSlider" class="mt-5"></div>
                                        </div>

                                        <div class="col-auto d-none d-md-flex align-items-stretch px-0">
                                            <div class="border-start border-dashed border-gray-300"></div>
                                        </div>

                                        <!-- Height Range -->
                                        <div class="col-12 col-md ps-md-6">
                                            <label class="form-label">{{ trans('smart.height_range') }}</label>
                                            <div ref="heightSlider" class="mt-5"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Education & Career Card -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">{{ trans('smart.education_and_career') }}</h3>
                                </div>
                                <div class="card-body py-7">
                                    <div class="row g-5 align-items-stretch">
                                        <!-- Education Level -->
                                        <div class="col-12 col-md pe-md-6">
                                            <label class="form-label">{{ trans('smart.education_level') }}</label>
                                            <select v-model="filters.education_level" class="form-select" multiple size="6">
                                                <option v-for="level in filterOptions.education_levels" :key="level.value" :value="level.value">
                                                    {{ level.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <div class="col-auto d-none d-md-flex align-items-stretch px-0">
                                            <div class="border-start border-dashed border-gray-300"></div>
                                        </div>

                                        <!-- Financial Status -->
                                        <div class="col-12 col-md ps-md-6">
                                            <label class="form-label">{{ trans('smart.financial_status') }}</label>
                                            <select v-model="filters.financial_status" class="form-select" multiple size="6">
                                                <option v-for="status in filterOptions.financial_statuses" :key="status.value" :value="status.value">
                                                    {{ status.label }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Religious Practices Card -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">{{ trans('smart.religious_practices') }}</h3>
                                </div>
                                <div class="card-body py-7">
                                    <div class="row g-5 align-items-stretch">
                                        <!-- Devotion -->
                                        <div class="col-12 col-md pe-md-6">
                                            <label class="form-label">{{ trans('smart.devotion_level') }}</label>
                                            <select v-model="filters.devotion" class="form-select" multiple size="5">
                                                <option v-for="level in filterOptions.devotion_levels" :key="level.value" :value="level.value">
                                                    {{ level.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <div class="col-auto d-none d-md-flex align-items-stretch px-0">
                                            <div class="border-start border-dashed border-gray-300"></div>
                                        </div>

                                        <!-- Prayer -->
                                        <div class="col-12 col-md px-md-6">
                                            <label class="form-label">{{ trans('smart.prayer_frequency') }}</label>
                                            <select v-model="filters.prayer" class="form-select" multiple size="5">
                                                <option v-for="freq in filterOptions.prayer_frequencies" :key="freq.value" :value="freq.value">
                                                    {{ freq.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <div class="col-auto d-none d-md-flex align-items-stretch px-0">
                                            <div class="border-start border-dashed border-gray-300"></div>
                                        </div>

                                        <!-- Smoking -->
                                        <div class="col-12 col-md ps-md-6">
                                            <label class="form-label">{{ trans('smart.smoking') }}</label>
                                            <div class="d-flex flex-column gap-3">
                                                <div v-for="option in filterOptions.smoking_options" :key="option.value" class="form-check">
                                                    <input
                                                        v-model="filters.smoking"
                                                        :value="option.value"
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        :id="'smoking_' + option.value"
                                                    />
                                                    <label class="form-check-label" :for="'smoking_' + option.value">
                                                        {{ option.label }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12">
                            <div class="d-flex justify-content-center gap-3">
                                <button @click="submitFilters" class="btn btn-lg btn-primary">
                                    <i class="ki-outline ki-magnifier fs-2 me-2"></i>
                                    {{ trans('smart.search') }}
                                </button>
                                <button @click="clearAllFilters" class="btn btn-lg btn-light-danger">
                                    <i class="ki-outline ki-trash fs-2 me-2"></i>
                                    {{ trans('smart.clear_all') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Ad: above results, shown after search is submitted -->
                    <div v-if="users && !showFilters" class="mb-6">
                        <GoogleAd slot-id="SMART_SEARCH_RESULTS" format="horizontal" />
                    </div>

                    <!-- Results -->
                    <div v-if="users && !showFilters" class="row g-6 g-xl-9 mb-6 mb-xl-9">
                        <div v-if="users.data.length > 0" class="col-12">
                            <div class="row g-6 g-xl-9">
                                <div v-for="user in users.data" :key="user.id" class="col-md-6 col-xl-3">
                                    <UserCard
                                        :user="user"
                                        :countries="countries"
                                        @message="handleMessage"
                                        @view-profile="handleViewProfile"
                                    />
                                </div>
                            </div>
                            <Pagination :data="users" />
                        </div>

                        <!-- Empty State -->
                        <div v-else class="col-12">
                            <div class="card">
                                <div class="card-body text-center py-20">
                                    <div class="mb-10">
                                        <i class="ki-outline ki-user fs-5x text-gray-400"></i>
                                    </div>
                                    <h3 class="text-gray-800 mb-3">{{ trans('smart.no_results_found') }}</h3>
                                    <p class="text-gray-600 fs-5">
                                        {{ trans('smart.try_adjusting_your_search_criteria_to_see_more_results') }}
                                    </p>
                                    <button @click="toggleFilters" class="btn btn-primary mt-5">
                                        {{ trans('smart.edit_search_criteria') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Drawer -->
        <ChatDrawer :is-open="isChatDrawerOpen" :recipient-id="selectedUserId" @close="handleCloseChat" />

        <!-- User Profile Modal -->
        <UserProfileModal
            :is-open="isProfileModalOpen"
            :user-id="selectedProfileUserId"
            @close="handleCloseProfile"
            @message="handleMessageFromModal"
        />
    </div>
</template>

<style>
.noUi-target {
    border: none;
    box-shadow: none;
    background: var(--bs-gray-200, #f1f1f4);
    height: 6px;
    border-radius: 3px;
}

.noUi-connect {
    background: rgb(208, 46, 121);
    border-radius: 3px;
}

.noUi-handle {
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

.noUi-handle:active {
    cursor: grabbing;
    transform: scale(1.15);
}

[dir='rtl'] .noUi-handle {
    right: auto !important;
    left: -9px !important;
}

.noUi-handle::before,
.noUi-handle::after {
    display: none;
}

.noUi-tooltip {
    background: rgb(208, 46, 121);
    border: none;
    color: #fff;
    font-size: 0.85rem;
    padding: 4px 8px;
    border-radius: 4px;
}
</style>
