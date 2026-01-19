<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import UserCard from '@/components/UserCard.vue';
import UserProfileModal from '@/components/UserProfileModal.vue';
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';
import Pagination from '@/components/Pagination.vue';
import { PaginationData } from '@/types/pagination';
import axios from 'axios';
import noUiSlider from 'nouislider';
import 'nouislider/dist/nouislider.css';

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
        if (ageSlider.value && !ageSlider.value.noUiSlider) {
            noUiSlider.create(ageSlider.value, {
                start: [filters.value.age_min, filters.value.age_max],
                connect: true,
                range: {
                    'min': 18,
                    'max': 100
                },
                step: 1,
                tooltips: true,
                format: {
                    to: (value) => Math.round(value),
                    from: (value) => Number(value)
                }
            });

            ageSlider.value.noUiSlider.on('update', (values: any) => {
                filters.value.age_min = parseInt(values[0]);
                filters.value.age_max = parseInt(values[1]);
            });
        }

        // Weight Slider
        if (weightSlider.value && !weightSlider.value.noUiSlider) {
            noUiSlider.create(weightSlider.value, {
                start: [filters.value.weight_min, filters.value.weight_max],
                connect: true,
                range: {
                    'min': 30,
                    'max': 200
                },
                step: 1,
                tooltips: true,
                format: {
                    to: (value) => Math.round(value) + ' kg',
                    from: (value) => Number(String(value).replace(' kg', ''))
                }
            });

            weightSlider.value.noUiSlider.on('update', (values: any) => {
                filters.value.weight_min = parseInt(String(values[0]).replace(' kg', ''));
                filters.value.weight_max = parseInt(String(values[1]).replace(' kg', ''));
            });
        }

        // Height Slider
        if (heightSlider.value && !heightSlider.value.noUiSlider) {
            noUiSlider.create(heightSlider.value, {
                start: [filters.value.height_min, filters.value.height_max],
                connect: true,
                range: {
                    'min': 120,
                    'max': 230
                },
                step: 1,
                tooltips: true,
                format: {
                    to: (value) => Math.round(value) + ' cm',
                    from: (value) => Number(String(value).replace(' cm', ''))
                }
            });

            heightSlider.value.noUiSlider.on('update', (values: any) => {
                filters.value.height_min = parseInt(String(values[0]).replace(' cm', ''));
                filters.value.height_max = parseInt(String(values[1]).replace(' cm', ''));
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
        const response = await axios.post('/conversations', {
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
                            Smart Search
                        </h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="/" class="text-muted text-hover-primary">Home</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet w-5px h-2px bg-gray-500"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Smart Search</li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2" v-if="users">
                        <button @click="toggleFilters" class="btn btn-sm btn-primary">
                            <i class="ki-outline ki-filter fs-3 me-2"></i>
                            {{ showFilters ? 'Hide Filters' : 'Edit Criteria' }}
                        </button>
                        <button @click="clearAllFilters" class="btn btn-sm btn-light-danger">
                            <i class="ki-outline ki-trash fs-3 me-2"></i>
                            Clear Filters
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
                                    <h3 class="card-title">Location & Nationality</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row g-5">
                                        <!-- Residence -->
                                        <div class="col-md-6">
                                            <label class="form-label">Residence</label>
                                            <select v-model="filters.residence" class="form-select" multiple size="5">
                                                <option v-for="country in countries" :key="country.id" :value="country.id">
                                                    {{ country.name }}
                                                </option>
                                            </select>
                                            <div class="form-text">Hold Ctrl/Cmd to select multiple</div>
                                        </div>

                                        <!-- Nationality -->
                                        <div class="col-md-6">
                                            <label class="form-label">Nationality</label>
                                            <select v-model="filters.nationality" class="form-select" multiple size="5">
                                                <option v-for="country in countries" :key="country.id" :value="country.id">
                                                    {{ country.name }}
                                                </option>
                                            </select>
                                            <div class="form-text">Hold Ctrl/Cmd to select multiple</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Information Card -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Basic Information</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row g-5">
                                        <!-- Marriage Status -->
                                        <div class="col-md-6">
                                            <label class="form-label">Marriage Status</label>
                                            <div class="d-flex flex-column gap-3">
                                                <div v-for="status in filterOptions.marriage_statuses" :key="status.value" class="form-check">
                                                    <input
                                                        v-model="filters.marriage_status"
                                                        :value="status.value"
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        :id="'marriage_' + status.value"
                                                    />
                                                    <label class="form-check-label" :for="'marriage_' + status.value">
                                                        {{ status.label }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Age Range -->
                                        <div class="col-md-6">
                                            <label class="form-label">Age Range</label>
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
                                    <h3 class="card-title">Physical Attributes</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row g-5">
                                        <!-- Body Shape -->
                                        <div class="col-md-6">
                                            <label class="form-label">Body Shape</label>
                                            <select v-model="filters.body_shape" class="form-select" multiple size="5">
                                                <option v-for="shape in filterOptions.body_shapes" :key="shape.value" :value="shape.value">
                                                    {{ shape.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Skin Color -->
                                        <div class="col-md-6">
                                            <label class="form-label">Skin Color</label>
                                            <select v-model="filters.skin_color" class="form-select" multiple size="5">
                                                <option v-for="color in filterOptions.skin_colors" :key="color.value" :value="color.value">
                                                    {{ color.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Weight Range -->
                                        <div class="col-md-6">
                                            <label class="form-label">Weight Range</label>
                                            <div ref="weightSlider" class="mt-5"></div>
                                        </div>

                                        <!-- Height Range -->
                                        <div class="col-md-6">
                                            <label class="form-label">Height Range</label>
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
                                    <h3 class="card-title">Education & Career</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row g-5">
                                        <!-- Education Level -->
                                        <div class="col-md-6">
                                            <label class="form-label">Education Level</label>
                                            <select v-model="filters.education_level" class="form-select" multiple size="6">
                                                <option v-for="level in filterOptions.education_levels" :key="level.value" :value="level.value">
                                                    {{ level.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Financial Status -->
                                        <div class="col-md-6">
                                            <label class="form-label">Financial Status</label>
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
                                    <h3 class="card-title">Religious Practices</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row g-5">
                                        <!-- Devotion -->
                                        <div class="col-md-4">
                                            <label class="form-label">Devotion Level</label>
                                            <select v-model="filters.devotion" class="form-select" multiple size="5">
                                                <option v-for="level in filterOptions.devotion_levels" :key="level.value" :value="level.value">
                                                    {{ level.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Prayer -->
                                        <div class="col-md-4">
                                            <label class="form-label">Prayer Frequency</label>
                                            <select v-model="filters.prayer" class="form-select" multiple size="5">
                                                <option v-for="freq in filterOptions.prayer_frequencies" :key="freq.value" :value="freq.value">
                                                    {{ freq.label }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Smoking -->
                                        <div class="col-md-4">
                                            <label class="form-label">Smoking</label>
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
                                    Search
                                </button>
                                <button @click="clearAllFilters" class="btn btn-lg btn-light-danger">
                                    <i class="ki-outline ki-trash fs-2 me-2"></i>
                                    Clear All
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Results -->
                    <div v-if="users && !showFilters" class="row g-6 g-xl-9 mb-6 mb-xl-9">
                        <div v-if="users.data.length > 0" class="col-12">
                            <div class="row g-6 g-xl-9">
                                <div v-for="user in users.data" :key="user.id" class="col-md-6 col-xl-4">
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
                                    <h3 class="text-gray-800 mb-3">No Results Found</h3>
                                    <p class="text-gray-600 fs-5">
                                        Try adjusting your search criteria to see more results.
                                    </p>
                                    <button @click="toggleFilters" class="btn btn-primary mt-5">
                                        Edit Search Criteria
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
/* noUiSlider customization for Metronic theme */
.noUi-connect {
    background: var(--bs-primary);
}

.noUi-handle {
    border: 1px solid var(--bs-primary);
    border-radius: 50%;
    background: #fff;
    cursor: pointer;
    box-shadow: 0 0.5rem 1rem 0.25rem rgba(0, 0, 0, 0.1);
}

.noUi-handle:before,
.noUi-handle:after {
    display: none;
}

.noUi-tooltip {
    background: var(--bs-primary);
    border: none;
    color: #fff;
    font-size: 0.85rem;
    padding: 4px 8px;
    border-radius: 4px;
}

.noUi-horizontal {
    height: 6px;
}

.noUi-horizontal .noUi-handle {
    width: 20px;
    height: 20px;
    right: -10px;
    top: -7px;
}

.noUi-target {
    background: #e4e6ef;
    border: none;
    box-shadow: none;
}
</style>
