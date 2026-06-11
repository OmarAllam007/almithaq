<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import UserCard from '@/components/UserCard.vue';
import Pagination from '@/components/Pagination.vue';
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';
import UserProfileModal from '@/components/UserProfileModal.vue';
import { useLang } from '@/composables/useLang';
import axios from 'axios';
import type { PaginationData } from '@/types/pagination';

const { trans } = useLang();
const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');

interface Country {
    id: number;
    name: string;
    ar_name: string;
}

interface City {
    id: number;
    name: string;
    ar_name: string;
}

interface Filters {
    nationality: number | null;
    residence: number | null;
    city: City | null;
    marriage_status: number | null;
    marriage_status_label: string | null;
    age_min: number;
    age_max: number;
}

interface Props {
    users: PaginationData;
    countries: Country[];
    filters: Filters;
}

const props = defineProps<Props>();

const isChatOpen = ref(false);
const selectedUserId = ref<number | undefined>(undefined);
const isProfileModalOpen = ref(false);
const selectedProfileUserId = ref<number | undefined>(undefined);

const getCountryName = (id: number | null): string | null => {
    if (!id) return null;
    const country = props.countries.find((c) => c.id === id);
    if (!country) return null;
    return isRtl.value ? country.ar_name : country.name;
};

const activeFilterChips = computed(() => {
    const chips: { label: string; value: string }[] = [];

    if (props.filters.nationality) {
        const name = getCountryName(props.filters.nationality);
        if (name) chips.push({ label: trans('quick_search.nationality'), value: name });
    }

    if (props.filters.residence) {
        const name = getCountryName(props.filters.residence);
        if (name) chips.push({ label: trans('quick_search.residence'), value: name });
    }

    if (props.filters.city) {
        const cityName = isRtl.value ? props.filters.city.ar_name : props.filters.city.name;
        chips.push({ label: trans('quick_search.city'), value: cityName });
    }

    if (props.filters.marriage_status && props.filters.marriage_status_label) {
        chips.push({ label: trans('quick_search.marriage_status'), value: props.filters.marriage_status_label });
    }

    const isDefaultAge = props.filters.age_min === 18 && props.filters.age_max === 60;
    if (!isDefaultAge) {
        chips.push({
            label: trans('quick_search.age'),
            value: trans('quick_search.age_range_label', { min: props.filters.age_min, max: props.filters.age_max }),
        });
    }

    return chips;
});

const handleMessage = (userId: number) => {
    selectedUserId.value = userId;
    isChatOpen.value = true;
};

const handleCloseChat = () => {
    isChatOpen.value = false;
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
        const response = await axios.post('/conversations', { recipient_id: userId });
        selectedUserId.value = response.data.conversation_id;
        isChatOpen.value = true;
    } catch (error) {
        console.error('Failed to get or create conversation:', error);
    }
};
</script>

<template>
    <div>
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                <!-- Header -->
                <div class="card mb-6">
                    <div class="card-body pt-7 pb-5">
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-4">
                            <div>
                                <div class="d-flex align-items-center gap-3 mb-1">
                                    <Link href="/" class="btn btn-sm btn-icon btn-light">
                                        <i :class="isRtl ? 'ki-outline ki-arrow-right' : 'ki-outline ki-arrow-left'" class="fs-3"></i>
                                    </Link>
                                    <h2 class="fs-2 fw-bold text-gray-900 mb-0">{{ trans('quick_search.title') }}</h2>
                                </div>
                                <p class="text-gray-500 fw-semibold fs-7 mb-0 ms-12">
                                    {{ trans('quick_search.results_count', { total: users.total }) }}
                                    &nbsp;·&nbsp;
                                    {{ trans('quick_search.subtitle') }}
                                </p>
                            </div>

                            <!-- Active filter chips -->
                            <div v-if="activeFilterChips.length > 0" class="d-flex flex-wrap gap-2 align-items-center">
                                <span
                                    v-for="chip in activeFilterChips"
                                    :key="chip.label"
                                    class="badge-chip"
                                >
                                    <span class="chip-label">{{ chip.label }}</span>
                                    <span class="chip-value">{{ chip.value }}</span>
                                </span>
                                <Link href="/" class="btn btn-sm btn-light-danger px-3 py-2 fs-8">
                                    <i class="ki-outline ki-cross fs-4 me-1"></i>
                                    {{ trans('quick_search.clear_filters') }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results grid -->
                <div v-if="users.data.length > 0" class="row g-6 g-xl-9 mb-6 mb-xl-9">
                    <div
                        v-for="user in users.data"
                        :key="user.id"
                        class="col-md-6 col-xl-3"
                    >
                        <UserCard
                            :user="user"
                            :countries="countries"
                            @message="handleMessage"
                            @view-profile="handleViewProfile"
                        />
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else class="card">
                    <div class="card-body text-center py-20">
                        <div
                            class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-6"
                            style="width: 80px; height: 80px; background: #fff0f7;"
                        >
                            <i class="ki-outline ki-magnifier fs-2x" style="color: #d02e79;"></i>
                        </div>
                        <h3 class="text-gray-800 fw-bold mb-2">{{ trans('quick_search.no_results') }}</h3>
                        <p class="text-gray-500 fs-6 mb-6">{{ trans('quick_search.no_results_hint') }}</p>
                        <Link href="/" class="btn text-white" style="background-color: #d02e79;">
                            <i class="ki-outline ki-arrow-left fs-4 me-2"></i>
                            {{ trans('quick_search.back_to_home') }}
                        </Link>
                    </div>
                </div>

                <!-- Pagination -->
                <Pagination :data="users" />
            </div>
        </div>

        <ChatDrawer
            :is-open="isChatOpen"
            :recipient-id="selectedUserId"
            @close="handleCloseChat"
        />

        <UserProfileModal
            :is-open="isProfileModalOpen"
            :user-id="selectedProfileUserId"
            @close="handleCloseProfile"
            @message="handleMessageFromModal"
        />
    </div>
</template>

<style scoped>
.badge-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px 4px 8px;
    border-radius: 20px;
    background: #fff0f7;
    border: 1px solid rgba(208, 46, 121, 0.2);
    font-size: 0.75rem;
    line-height: 1.3;
}

.chip-label {
    color: #9b2c65;
    font-weight: 600;
}

.chip-value {
    color: #d02e79;
    font-weight: 700;
}
</style>
