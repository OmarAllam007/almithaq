<script setup lang="ts">
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import UserCard from '@/components/UserCard.vue';
import Pagination from '@/components/Pagination.vue';
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';
import UserProfileModal from '@/components/UserProfileModal.vue';
import GoogleAd from '@/components/GoogleAd.vue';
import { PaginationData } from '@/types/pagination';
import axios from 'axios';
import { useLang } from '@/composables/useLang';
const { trans } = useLang();

interface User {
    id: number;
    username: string;
    age: number;
    nationality: string | number;
    residence: string | number;
    marriage_status: string;
    is_online?: boolean;
    is_favorited?: boolean;
}

interface Country {
    id: number;
    name: string;
    ar_name: string;
}



interface Props {
    users: PaginationData;
    countries: Country[];
    filters: {
        nationality?: number;
        residence?: number;
    };
}

const props = defineProps<Props>();

const selectedNationality = ref<number | undefined>(props.filters.nationality);
const selectedResidence = ref<number | undefined>(props.filters.residence);
const isChatOpen = ref(false);
const selectedUserId = ref<number | undefined>(undefined);
const isProfileModalOpen = ref(false);
const selectedProfileUserId = ref<number | undefined>(undefined);

const applyFilters = () => {
    router.get(
        '/new-members',
        {
            nationality: selectedNationality.value || undefined,
            residence: selectedResidence.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const resetFilters = () => {
    selectedNationality.value = undefined;
    selectedResidence.value = undefined;
    applyFilters();
};

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
        // Get or create conversation with this user
        const response = await axios.post('/conversations', {
            recipient_id: userId,
        });

        const conversationId = response.data.conversation_id;
        // Open chat drawer with conversation
        selectedUserId.value = userId;
        isChatOpen.value = true;
    } catch (error) {
        console.error('Failed to get or create conversation:', error);
    }
};

watch([selectedNationality, selectedResidence], () => {
    applyFilters();
});
</script>

<template>
    <div>
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!-- Page Header -->
                <div class="card mb-5 mb-xl-10">
                    <div class="card-body pt-9 pb-0">
                        <div class="d-flex flex-wrap flex-sm-nowrap mb-6">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="text-gray-900 fs-2 fw-bold me-3">{{ trans('new_members.new_members') }}</span>
                                        </div>
                                        <div class="d-flex flex-wrap fw-semibold mb-4 fs-5 text-gray-500">
                                            {{ trans('new_members.latest_registered_members') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="d-flex overflow-auto h-55px">
                            <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold flex-nowrap">
                                <li class="nav-item">
                                    <div class="d-flex align-items-center gap-3 px-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <label class="form-label mb-0 text-nowrap">{{ trans('new_members.nationality') }}:</label>
                                            <select
                                                v-model="selectedNationality"
                                                class="form-select form-select-sm"
                                                style="min-width: 150px"
                                            >
                                                <option :value="undefined">{{ trans('new_members.all') }}</option>
                                                <option
                                                    v-for="country in countries"
                                                    :key="country.id"
                                                    :value="country.id"
                                                >
                                                    {{ country.name }}
                                                </option>
                                            </select>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <label class="form-label mb-0 text-nowrap">{{ trans('new_members.residence') }}:</label>
                                            <select
                                                v-model="selectedResidence"
                                                class="form-select form-select-sm"
                                                style="min-width: 150px"
                                            >
                                                <option :value="undefined">{{ trans('new_members.all') }}</option>
                                                <option
                                                    v-for="country in countries"
                                                    :key="country.id"
                                                    :value="country.id"
                                                >
                                                    {{ country.name }}
                                                </option>
                                            </select>
                                        </div>

                                        <button
                                            v-if="selectedNationality || selectedResidence"
                                            @click="resetFilters"
                                            class="btn btn-sm btn-light-primary"
                                        >
                                            <i class="ki-outline ki-cross fs-3"></i>
                                            {{ trans('new_members.clear_filters') }}
                                        </button>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Ad: between filter and user grid -->
                <div class="mb-6">
                    <GoogleAd slot-id="NEW_MEMBERS_TOP" format="horizontal" />
                </div>

                <!-- Users Grid -->
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

                <!-- Empty State -->
                <div v-else class="card">
                    <div class="card-body text-center py-20">
                        <div class="mb-10">
                            <i class="ki-outline ki-user fs-5x text-gray-400"></i>
                        </div>
                        <h3 class="text-gray-800 mb-3">{{ trans('new_members.no_members_found') }}</h3>
                        <p class="text-gray-600 fs-5">
                            {{ trans('new_members.try_adjusting_your_filters_to_see_more_results') }}
                        </p>
                        <button
                            v-if="selectedNationality || selectedResidence"
                            @click="resetFilters"
                            class="btn btn-primary mt-5"
                        >
                            {{ trans('new_members.clear_filters') }}
                        </button>
                    </div>
                </div>

                <!-- Pagination -->
                <Pagination :data="users" />
            </div>
        </div>

        <!-- Chat Drawer -->
        <ChatDrawer
            :is-open="isChatOpen"
            :recipient-id="selectedUserId"
            @close="handleCloseChat"
        />

        <!-- User Profile Modal -->
        <UserProfileModal
            :is-open="isProfileModalOpen"
            :user-id="selectedProfileUserId"
            @close="handleCloseProfile"
            @message="handleMessageFromModal"
        />
    </div>
</template>

<style scoped>
.form-select-sm {
    padding: 0.5rem 2rem 0.5rem 0.75rem;
}
</style>
