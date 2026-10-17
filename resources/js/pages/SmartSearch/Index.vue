<script setup lang="ts">
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';
import Select2MultiInput from '@/components/Inputs/Select2MultiInput.vue';
import Pagination from '@/components/Pagination.vue';
import UserCard from '@/components/UserCard.vue';
import UserProfileModal from '@/components/UserProfileModal.vue';
import { useLang } from '@/composables/useLang';
import { PaginationData } from '@/types/pagination';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import noUiSlider from 'nouislider';
import 'nouislider/dist/nouislider.css';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
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
watch(
    () => props.users,
    (newUsers) => {
        if (newUsers) {
            showFilters.value = false;
        }
    },
);

const submitFilters = () => {
    router.post('/smart-search/search', filters.value, {
        preserveState: false,
        preserveScroll: false,
        onSuccess: () => {
            showFilters.value = false;
        },
    });
};

const clearAllFilters = () => {
    router.post(
        '/smart-search/clear',
        {},
        {
            preserveState: false,
            onSuccess: () => {
                showFilters.value = true;
            },
        },
    );
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
    <div class="app-main flex-column flex-row-fluid smart-page" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <section class="smart-hero smart-reveal" aria-labelledby="smart-heading">
                <div class="smart-hero-copy">
                    <div class="smart-eyebrow"><span></span>{{ isRtl ? 'ابدأ بقصة تناسبك' : 'DISCOVER WITH INTENTION' }}</div>
                    <h1 id="smart-heading">{{ trans('smart.smart_search') }}<span>.</span></h1>
                    <p>
                        {{
                            isRtl
                                ? 'معاييرك، اختياراتك. ابحث عن الشخص الذي يشاركك ما يهمك، بطريقتك الخاصة.'
                                : 'Your values, your criteria. Find someone who shares what matters most to you.'
                        }}
                    </p>
                    <div class="smart-hero-meta">
                        <span class="meta-mark">✦</span
                        >{{
                            users
                                ? isRtl
                                    ? `${users.total} نتيجة تناسب بحثك`
                                    : `${users.total} profiles match your search`
                                : isRtl
                                  ? 'بحث أكثر دقة، بداية أجمل'
                                  : 'A more thoughtful way to connect'
                        }}
                    </div>
                </div>
                <div class="smart-hero-art" aria-hidden="true">
                    <div class="smart-orbit"></div>
                    <div class="smart-orbit inner"></div>
                    <div class="smart-art-card back"></div>
                    <div class="smart-art-card front"><span>✳</span><i></i><i></i></div>
                    <b>✦</b>
                </div>
            </section>

            <div class="smart-page-actions smart-reveal">
                <div class="smart-page-label">
                    <span class="label-index">{{ showFilters ? '01' : '02' }}</span>
                    <div>
                        <small>{{ isRtl ? 'بحث مخصص' : 'PERSONALIZED SEARCH' }}</small>
                        <h2>{{ showFilters ? (isRtl ? 'حدد ما تبحث عنه' : 'Make it yours') : isRtl ? 'نتائج البحث' : 'Your matches' }}</h2>
                    </div>
                </div>
                <div class="smart-action-buttons" v-if="users">
                    <button type="button" @click="toggleFilters" class="smart-button smart-button-primary">
                        <i class="ki-outline ki-filter"></i>{{ showFilters ? trans('smart.hide_filters') : trans('smart.edit_criteria') }}</button
                    ><button type="button" @click="clearAllFilters" class="smart-button smart-button-quiet">
                        <i class="ki-outline ki-trash"></i>{{ trans('smart.clear_filters') }}
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl smart-container">
                    <!-- Filter Form -->
                    <div v-if="showFilters" class="smart-filter-grid">
                        <!-- Location Card -->
                        <div class="smart-filter-card smart-filter-card--location">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <span class="smart-card-icon"><i class="ki-outline ki-geolocation"></i></span
                                        >{{ trans('smart.location_and_nationality') }}
                                    </h3>
                                </div>
                                <div class="card-body py-7">
                                    <div class="smart-fields">
                                        <!-- Residence -->
                                        <div class="smart-field">
                                            <label class="form-label">{{ trans('smart.residence') }}</label>
                                            <Select2MultiInput
                                                v-model="filters.residence"
                                                :options="countries.map((c) => ({ value: c.id, label: isRtl ? c.ar_name : c.name }))"
                                                :placeholder="trans('smart.select_country')"
                                            />
                                        </div>

                                        <!-- Nationality -->
                                        <div class="smart-field">
                                            <label class="form-label">{{ trans('smart.nationality') }}</label>
                                            <Select2MultiInput
                                                v-model="filters.nationality"
                                                :options="countries.map((c) => ({ value: c.id, label: isRtl ? c.ar_name : c.name }))"
                                                :placeholder="trans('smart.select_country')"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Information Card -->
                        <div class="smart-filter-card smart-filter-card--basic">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <span class="smart-card-icon"><i class="ki-outline ki-profile-circle"></i></span
                                        >{{ trans('smart.basic_information') }}
                                    </h3>
                                </div>
                                <div class="card-body py-7">
                                    <div class="smart-fields">
                                        <!-- Marriage Status -->
                                        <div class="smart-field">
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

                                        <!-- Marriage Type -->
                                        <div class="smart-field">
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

                                        <!-- Age Range -->
                                        <div class="smart-field smart-range-field">
                                            <label class="form-label">{{ trans('smart.age_range') }}</label>
                                            <div ref="ageSlider" class="mt-5"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Physical Attributes Card -->
                        <div class="smart-filter-card smart-filter-card--physical">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <span class="smart-card-icon"><i class="ki-outline ki-ruler"></i></span
                                        >{{ trans('smart.physical_attributes') }}
                                    </h3>
                                </div>
                                <div class="card-body py-7">
                                    <div class="smart-fields">
                                        <!-- Body Shape -->
                                        <div class="smart-field">
                                            <label class="form-label">{{ trans('smart.body_shape') }}</label>
                                            <select v-model="filters.body_shape" class="form-select" multiple size="5">
                                                <option v-for="shape in filterOptions.body_shapes" :key="shape.value" :value="shape.value">
                                                    {{ shape.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Skin Color -->
                                        <div class="smart-field">
                                            <label class="form-label">{{ trans('smart.skin_color') }}</label>
                                            <select v-model="filters.skin_color" class="form-select" multiple size="5">
                                                <option v-for="color in filterOptions.skin_colors" :key="color.value" :value="color.value">
                                                    {{ color.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Weight Range -->
                                        <div class="smart-field smart-range-field">
                                            <label class="form-label">{{ trans('smart.weight_range') }}</label>
                                            <div ref="weightSlider" class="mt-5"></div>
                                        </div>

                                        <!-- Height Range -->
                                        <div class="smart-field smart-range-field">
                                            <label class="form-label">{{ trans('smart.height_range') }}</label>
                                            <div ref="heightSlider" class="mt-5"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Education & Career Card -->
                        <div class="smart-filter-card smart-filter-card--career">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <span class="smart-card-icon"><i class="ki-outline ki-book"></i></span
                                        >{{ trans('smart.education_and_career') }}
                                    </h3>
                                </div>
                                <div class="card-body py-7">
                                    <div class="smart-fields">
                                        <!-- Education Level -->
                                        <div class="smart-field">
                                            <label class="form-label">{{ trans('smart.education_level') }}</label>
                                            <select v-model="filters.education_level" class="form-select" multiple size="6">
                                                <option v-for="level in filterOptions.education_levels" :key="level.value" :value="level.value">
                                                    {{ level.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Financial Status -->
                                        <div class="smart-field">
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
                        <div class="smart-filter-card smart-filter-card--faith">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <span class="smart-card-icon"><i class="ki-outline ki-heart"></i></span
                                        >{{ trans('smart.religious_practices') }}
                                    </h3>
                                </div>
                                <div class="card-body py-7">
                                    <div class="smart-fields">
                                        <!-- Devotion -->
                                        <div class="smart-field">
                                            <label class="form-label">{{ trans('smart.devotion_level') }}</label>
                                            <select v-model="filters.devotion" class="form-select" multiple size="5">
                                                <option v-for="level in filterOptions.devotion_levels" :key="level.value" :value="level.value">
                                                    {{ level.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Prayer -->
                                        <div class="smart-field">
                                            <label class="form-label">{{ trans('smart.prayer_frequency') }}</label>
                                            <select v-model="filters.prayer" class="form-select" multiple size="5">
                                                <option v-for="freq in filterOptions.prayer_frequencies" :key="freq.value" :value="freq.value">
                                                    {{ freq.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Smoking -->
                                        <div class="smart-field smart-field--wide">
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
                        <div class="smart-submit-wrap">
                            <div class="smart-submit-actions">
                                <div class="smart-submit-copy">
                                    <span>{{ isRtl ? 'خطوتك التالية' : 'YOUR NEXT STEP' }}</span>
                                    <h3>{{ isRtl ? 'جاهز لاكتشاف من يناسبك؟' : 'Ready to meet your matches?' }}</h3>
                                    <p>{{ isRtl ? 'سنبحث بناءً على المعايير التي اخترتها.' : 'We’ll search using the criteria you selected.' }}</p>
                                </div>
                                <div class="smart-submit-buttons">
                                    <button type="button" @click="submitFilters" class="smart-button smart-button-primary smart-search-button">
                                        <i class="ki-outline ki-magnifier fs-2 me-2"></i>
                                        {{ trans('smart.search') }}
                                    </button>
                                    <button type="button" @click="clearAllFilters" class="smart-button smart-button-quiet">
                                        <i class="ki-outline ki-trash fs-2 me-2"></i>
                                        {{ trans('smart.clear_all') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ad: above results, shown after search is submitted -->
                    <!-- <div v-if="users && !showFilters" class="smart-results-ad">
                        <GoogleAd slot-id="SMART_SEARCH_RESULTS" format="horizontal" />
                    </div> -->

                    <!-- Results -->
                    <div v-if="users && !showFilters" class="smart-results smart-reveal">
                        <div v-if="users.data.length > 0" class="smart-results-content">
                            <div class="smart-results-grid">
                                <div v-for="user in users.data" :key="user.id" class="smart-result-card">
                                    <UserCard :user="user" :countries="countries" @message="handleMessage" @view-profile="handleViewProfile" />
                                </div>
                            </div>
                            <Pagination :data="users" />
                        </div>

                        <!-- Empty State -->
                        <div v-else class="smart-empty-wrap">
                            <div class="card smart-empty-card">
                                <div class="card-body py-20 text-center">
                                    <div class="mb-10">
                                        <i class="ki-outline ki-user fs-5x text-gray-400"></i>
                                    </div>
                                    <h3 class="mb-3 text-gray-800">{{ trans('smart.no_results_found') }}</h3>
                                    <p class="fs-5 text-gray-600">
                                        {{ trans('smart.try_adjusting_your_search_criteria_to_see_more_results') }}
                                    </p>
                                    <button type="button" @click="toggleFilters" class="smart-button smart-button-primary mt-5">
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
.smart-page .noUi-target {
    border: none;
    box-shadow: none;
    background: var(--bs-gray-200, #f1f1f4);
    height: 6px;
    border-radius: 3px;
}

.smart-page .noUi-connect {
    background: #a34268;
    border-radius: 3px;
}

.smart-page .noUi-handle {
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

.smart-page .noUi-handle:active {
    cursor: grabbing;
    transform: scale(1.15);
}

[dir='rtl'] .smart-page .noUi-handle {
    right: auto !important;
    left: -9px !important;
}

.smart-page .noUi-handle::before,
.smart-page .noUi-handle::after {
    display: none;
}

.smart-page .noUi-tooltip {
    background: #a34268;
    border: none;
    color: #fff;
    font-size: 0.85rem;
    padding: 4px 8px;
    border-radius: 4px;
}
</style>

<style scoped>
.smart-page {
    --smart-ink: #3b2432;
    --smart-berry: #973c60;
    --smart-line: #f0e3e6;
    width: 100%;
    min-width: 0;
    background: #fbf8f5;
    color: var(--smart-ink);
    padding-bottom: 36px;
}
.smart-container {
    max-width: 1680px !important;
    width: 100%;
    padding-inline: clamp(18px, 3vw, 52px) !important;
}
.smart-hero {
    position: relative;
    display: flex;
    align-items: center;
    min-height: 270px;
    overflow: hidden;
    width: calc(100% - clamp(18px, 3vw, 52px) - clamp(18px, 3vw, 52px));
    margin: 24px auto 26px;
    max-width: 1576px;
    border-radius: 26px;
    background: linear-gradient(112deg, #452137 0%, #642c49 58%, #a5506d 100%);
    box-shadow: 0 17px 40px #51243a24;
}
.smart-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(#fff 0.7px, transparent 0.7px);
    background-size: 21px 21px;
    opacity: 0.15;
    mask-image: linear-gradient(to right, transparent 25%, #000);
}
.smart-hero-copy {
    position: relative;
    z-index: 1;
    width: min(69%, 820px);
    padding: 38px clamp(26px, 4vw, 64px);
}
.smart-eyebrow {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #f4c3d3;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.16em;
    margin-bottom: 12px;
}
.smart-eyebrow span {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #f4b69c;
    box-shadow: 0 0 0 5px #ffffff20;
}
.smart-hero h1 {
    color: #fff;
    font-size: clamp(36px, 3.5vw, 58px);
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1.2;
    margin: 0 0 11px;
}
.smart-hero h1 span {
    color: #f2ad9c;
}
.smart-hero p {
    color: #f6dce5;
    max-width: 550px;
    line-height: 1.85;
    font-size: 14px;
    margin: 0 0 17px;
}
.smart-hero-meta {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    color: #ffd9ca;
    font-weight: 700;
}
.meta-mark {
    font-size: 17px;
}
.smart-hero-art {
    position: absolute;
    inset-inline-end: 0;
    top: 0;
    width: 40%;
    height: 100%;
}
.smart-orbit {
    position: absolute;
    width: 335px;
    aspect-ratio: 1;
    border: 1px solid #ffffff45;
    border-radius: 50%;
    top: -110px;
    inset-inline-end: -20px;
}
.smart-orbit.inner {
    width: 235px;
    top: -62px;
    inset-inline-end: 30px;
}
.smart-art-card {
    position: absolute;
    width: 150px;
    height: 185px;
    top: 50px;
    inset-inline-end: 125px;
    border-radius: 95px 95px 15px 15px;
    box-shadow: 0 18px 35px #32132361;
    transform: rotate(-13deg);
}
.smart-art-card.back {
    background: #cd8395;
    transform: rotate(14deg);
    inset-inline-end: 88px;
    top: 58px;
}
.smart-art-card.front {
    background: linear-gradient(155deg, #f9e7dc, #e7baa9);
    display: flex;
    flex-direction: column;
    align-items: center;
}
.smart-art-card.front span {
    font-size: 70px;
    line-height: 1;
    color: #c7737a;
    margin: 31px 0 16px;
}
.smart-art-card.front i {
    width: 73px;
    height: 1px;
    background: #a96873;
    margin-bottom: 10px;
}
.smart-art-card.front i:last-child {
    width: 49px;
}
.smart-hero-art b {
    position: absolute;
    top: 44px;
    inset-inline-end: 59px;
    color: #ffd8c9;
    font-size: 28px;
}
.smart-page-actions {
    width: 100%;
    max-width: 1680px;
    margin: 0 auto 18px;
    padding-inline: clamp(18px, 3vw, 52px);
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 16px;
}
.smart-page-label {
    display: flex;
    align-items: center;
    gap: 13px;
}
.label-index {
    display: grid;
    place-items: center;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #f5e4e9;
    color: #9b4265;
    font-size: 12px;
    font-weight: 800;
}
.smart-page-label small {
    display: block;
    color: #b2778d;
    letter-spacing: 0.12em;
    font-size: 9px;
    font-weight: 800;
}
.smart-page-label h2 {
    font-size: 23px;
    line-height: 1.3;
    margin: 3px 0 0;
    color: #3b2432;
    font-weight: 800;
}
.smart-action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.smart-button {
    min-height: 41px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: 0;
    border-radius: 9px;
    padding: 9px 16px;
    font-size: 12px;
    font-weight: 800;
    transition:
        transform 0.2s,
        background 0.2s,
        box-shadow 0.2s;
}
.smart-button:hover {
    transform: translateY(-2px);
}
.smart-button-primary {
    background: #973d60;
    color: #fff;
    box-shadow: 0 5px 15px #973d602a;
}
.smart-button-primary:hover {
    background: #7d2e50;
    color: #fff;
}
.smart-button-quiet {
    background: #f5e9ed;
    color: #8f4261;
}
.smart-button-quiet:hover {
    background: #ecdae1;
    color: #79354f;
}
.smart-button i {
    font-size: 16px;
}
.smart-filter-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
    align-items: stretch;
}
.smart-filter-card {
    min-width: 0;
}
.smart-filter-card--location,
.smart-filter-grid > .smart-submit-wrap {
    grid-column: 1 / -1;
}
.smart-filter-card > .card {
    width: 100%;
    height: 100%;
    min-width: 0;
    border: 1px solid var(--smart-line);
    border-radius: 17px;
    box-shadow: 0 9px 28px #6330440b;
    overflow: hidden;
    background: #fff;
    transition:
        border-color 0.25s ease,
        box-shadow 0.25s ease,
        transform 0.25s ease;
}
.smart-filter-card > .card:hover {
    border-color: #e9d1da;
    box-shadow: 0 15px 36px #63304415;
    transform: translateY(-2px);
}
.smart-filter-grid .card-header {
    min-height: 64px;
    padding: 14px 22px;
    border-bottom: 1px solid #f3eaec;
    background: #fffdfc;
}
.smart-filter-grid .card-title {
    display: flex;
    align-items: center;
    gap: 11px;
    color: #3e2935;
    font-size: 16px;
    font-weight: 800;
}
.smart-card-icon {
    width: 34px;
    height: 34px;
    display: grid;
    place-items: center;
    border-radius: 9px;
    background: #f7e9ed;
    color: #a34368;
    font-size: 18px;
}
.smart-filter-grid .card-body {
    padding: 22px 24px 24px !important;
}
.smart-fields {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
    align-items: start;
}
.smart-field {
    display: flex;
    flex-direction: column;
    gap: 9px;
    min-width: 0;
}
.smart-field--wide,
.smart-filter-card--basic .smart-range-field {
    grid-column: 1 / -1;
}
.smart-filter-card--basic .smart-range-field,
.smart-filter-card--physical .smart-range-field,
.smart-field--wide {
    padding-top: 18px;
    border-top: 1px solid #f2e8eb;
}
.smart-range-field .noUi-target {
    margin: 25px 12px 11px;
}
.smart-field--wide > .d-flex {
    flex-direction: row !important;
    flex-wrap: wrap;
    gap: 9px !important;
}
.smart-filter-grid .form-label {
    display: block;
    font-weight: 800;
    font-size: 11px;
    letter-spacing: 0.02em;
    color: #765b68;
    margin: 0;
}
.smart-filter-grid .form-select {
    border: 1px solid #e9dce1;
    border-radius: 10px;
    background-color: #fffcfb;
    color: #503845;
    padding: 10px;
    font-size: 12px;
}
.smart-filter-grid .form-select:focus {
    border-color: #b66183;
    box-shadow: 0 0 0 3px #a342681c;
}
.smart-filter-grid :deep(.select2-container--bootstrap5 .select2-selection) {
    min-height: 44px;
    border-color: #e9dce1;
    border-radius: 10px;
    background: #fffcfb;
}
.smart-field :deep(.select2-container) {
    width: 100% !important;
}
.smart-field :deep(.select2-selection--multiple) {
    padding: 4px 7px;
}
.smart-filter-grid :deep(.select2-container--bootstrap5 .select2-selection__choice) {
    background: #f8e9ef;
    border: 1px solid #ead3dd;
    border-radius: 7px;
    color: #88405f;
}
.smart-filter-grid .form-select option {
    padding: 6px 8px;
}
.smart-filter-grid .form-check {
    display: flex;
    align-items: center;
    gap: 9px;
    min-height: 36px;
    padding: 7px 10px;
    margin: 0;
    border: 1px solid #f0e4e8;
    border-radius: 8px;
    background: #fdfafb;
    transition:
        border-color 0.2s ease,
        background 0.2s ease;
}
.smart-filter-grid .form-check:hover {
    border-color: #dfbccb;
    background: #faf0f3;
}
.smart-filter-grid .form-check-input {
    float: none;
    flex: none;
    margin: 0;
    border-color: #d8bec9;
}
.smart-filter-grid .form-check-input:checked {
    background-color: #a34268;
    border-color: #a34268;
}
.smart-filter-grid .form-check-label {
    color: #614957 !important;
    font-size: 11px;
    font-weight: 700;
}
.smart-submit-wrap {
    padding: 0 !important;
}
.smart-submit-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 22px;
    padding: 23px 28px;
    background: #f8eef1;
    border: 1px solid #ead7df;
    border-radius: 16px;
}
.smart-submit-copy span {
    color: #ab6480;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.1em;
}
.smart-submit-copy h3 {
    margin: 4px 0 2px;
    color: #4a2c3c;
    font-size: 17px;
    font-weight: 800;
}
.smart-submit-copy p {
    margin: 0;
    color: #846b78;
    font-size: 11px;
}
.smart-submit-buttons {
    display: flex;
    flex: none;
    gap: 10px;
}
.smart-search-button {
    min-width: 195px;
}
.smart-results-ad {
    margin: 0 0 18px;
}
.smart-results-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
}
.smart-result-card {
    min-width: 0;
    animation: smart-rise 0.55s both;
}
.smart-result-card:nth-child(2) {
    animation-delay: 0.06s;
}
.smart-result-card:nth-child(3) {
    animation-delay: 0.12s;
}
.smart-result-card:nth-child(4) {
    animation-delay: 0.18s;
}
.smart-result-card:nth-child(n + 5) {
    animation-delay: 0.22s;
}
.smart-results :deep(.uc-card) {
    height: 100%;
}
.smart-results :deep(.pagination) {
    margin-top: 28px;
}
.smart-empty-wrap {
    width: 100%;
}
.smart-empty-card {
    border: 1px solid var(--smart-line);
    border-radius: 19px;
    box-shadow: 0 8px 25px #6330440d;
}
.smart-empty-card :deep(.card-body) {
    padding: 62px 22px !important;
}
.smart-empty-card h3 {
    color: #3b2432 !important;
    font-weight: 800;
}
.smart-reveal {
    animation: smart-rise 0.6s both;
}
.smart-page-actions {
    animation-delay: 0.08s;
}
.smart-filter-grid,
.smart-results {
    animation: smart-rise 0.62s 0.15s both;
}
@keyframes smart-rise {
    from {
        opacity: 0;
        transform: translateY(17px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
@media (min-width: 1680px) {
    .smart-hero {
        margin-inline: auto;
    }
}
@media (max-width: 1450px) {
    .smart-results-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
@media (max-width: 1200px) {
    .smart-filter-grid {
        grid-template-columns: 1fr;
    }
    .smart-filter-card--location,
    .smart-filter-grid > .smart-submit-wrap {
        grid-column: auto;
    }
    .smart-results-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 767px) {
    .smart-hero {
        width: calc(100% - 28px);
        min-height: 260px;
        margin: 14px auto 23px;
        border-radius: 20px;
    }
    .smart-hero-copy {
        width: 100%;
        padding: 30px 25px 70px;
    }
    .smart-hero h1 {
        font-size: 34px;
    }
    .smart-hero p {
        font-size: 12px;
        max-width: 330px;
    }
    .smart-hero-art {
        opacity: 0.35;
        transform: scale(0.68);
        transform-origin: bottom right;
        inset-inline-end: -110px;
    }
    .smart-page-actions {
        align-items: start;
        flex-direction: column;
        padding-inline: 17px;
    }
    .smart-page-label h2 {
        font-size: 20px;
    }
    .smart-container {
        padding-inline: 14px !important;
    }
    .smart-filter-grid {
        gap: 12px;
    }
    .smart-filter-grid .card-header {
        padding: 13px 18px;
        min-height: 64px;
    }
    .smart-filter-grid .card-body {
        padding: 20px 18px 24px !important;
    }
    .smart-fields {
        gap: 16px;
    }
    .smart-submit-actions {
        flex-wrap: wrap;
        padding: 16px;
    }
    .smart-submit-buttons {
        width: 100%;
    }
    .smart-submit-buttons .smart-button {
        flex: 1;
    }
    .smart-results-grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }
}
@media (max-width: 620px) {
    .smart-fields {
        grid-template-columns: 1fr;
    }
    .smart-field--wide,
    .smart-filter-card--basic .smart-range-field {
        grid-column: auto;
    }
    .smart-filter-card--physical .smart-range-field {
        padding-top: 0;
        border-top: 0;
    }
}
@media (prefers-reduced-motion: reduce) {
    .smart-reveal,
    .smart-filter-grid,
    .smart-results,
    .smart-result-card {
        animation: none;
    }
    .smart-button {
        transition: none;
    }
}
</style>
