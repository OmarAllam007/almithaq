<script setup lang="ts">
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';
import GoogleAd from '@/components/GoogleAd.vue';
import Pagination from '@/components/Pagination.vue';
import UserCard from '@/components/UserCard.vue';
import UserProfileModal from '@/components/UserProfileModal.vue';
import { useLang } from '@/composables/useLang';
import { PaginationData } from '@/types/pagination';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';
const { trans } = useLang();
const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');
const countryName = (country: Country) => (isRtl.value ? country.ar_name || country.name : country.name);

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
        },
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

        selectedUserId.value = userId;
        isChatOpen.value = true;
    } catch (error) {
        console.error('Failed to get or create conversation:', error);
    }
};
</script>

<template>
    <div id="kt_app_content" class="app-content flex-column-fluid member-page">
        <div id="kt_app_content_container" class="app-container container-xxl member-container">
            <section class="member-hero member-reveal">
                <div class="member-hero-art"></div>
                <div class="member-hero-copy">
                    <div class="member-eyebrow">{{ isRtl ? 'وجوه جديدة، فرص جديدة' : 'FRESH CONNECTIONS' }}</div>
                    <h1>{{ trans('new_members.new_members') }}<span>.</span></h1>
                    <p>{{ trans('new_members.latest_registered_members') }}</p>
                </div>
                <div class="member-hero-stat">
                    <strong>{{ users.total }}</strong
                    ><span>{{ isRtl ? 'عضو جديد' : 'new members' }}</span>
                </div>
            </section>
            <div class="member-section-head member-reveal">
                <div>
                    <small>{{ isRtl ? 'تصفح واكتشف' : 'BROWSE & DISCOVER' }}</small>
                    <h2>{{ isRtl ? 'اكتشف الأعضاء' : 'Discover members' }}</h2>
                </div>
                <span class="member-count">{{ users.total }} {{ trans('new_members.new_members') }}</span>
            </div>
            <div class="member-filters member-reveal">
                <div class="member-filter">
                    <label for="new-nationality">{{ trans('new_members.nationality') }}</label
                    ><select id="new-nationality" v-model="selectedNationality" @change="applyFilters">
                        <option value="">{{ trans('new_members.all') }}</option>
                        <option v-for="country in countries" :key="country.id" :value="country.id">{{ countryName(country) }}</option>
                    </select>
                </div>
                <div class="member-filter">
                    <label for="new-residence">{{ trans('new_members.residence') }}</label
                    ><select id="new-residence" v-model="selectedResidence" @change="applyFilters">
                        <option value="">{{ trans('new_members.all_countries') }}</option>
                        <option v-for="country in countries" :key="country.id" :value="country.id">{{ countryName(country) }}</option>
                    </select>
                </div>
                <button v-if="selectedNationality || selectedResidence" type="button" class="member-clear" @click="resetFilters">
                    <i class="ki-outline ki-cross"></i>{{ trans('new_members.clear_filters') }}
                </button>
            </div>
            <!-- <div class="member-ad"><GoogleAd slot-id="NEW_MEMBERS_TOP" format="horizontal" /></div> -->
            <div v-if="users.data.length" class="member-grid mt-10">
                <div v-for="user in users.data" :key="user.id" class="member-grid-item">
                    <UserCard :user="user" :countries="countries" @message="handleMessage" @view-profile="handleViewProfile" />
                </div>
            </div>
            <div v-else class="member-empty">
                <div class="member-empty-icon"><i class="ki-outline ki-people"></i></div>
                <h3>{{ trans('new_members.no_members_found') }}</h3>
                <p>{{ trans('new_members.try_adjusting_your_filters_to_see_more_results') }}</p>
                <button v-if="selectedNationality || selectedResidence" type="button" class="member-clear" @click="resetFilters">
                    {{ trans('new_members.clear_filters') }}
                </button>
            </div>
            <Pagination :data="users" />
        </div>
        <ChatDrawer :is-open="isChatOpen" :recipient-id="selectedUserId" @close="handleCloseChat" />
        <UserProfileModal
            :is-open="isProfileModalOpen"
            :user-id="selectedProfileUserId"
            @close="handleCloseProfile"
            @message="handleMessageFromModal"
        />
    </div>
</template>
