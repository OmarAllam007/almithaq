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
    totalOnline: number;
    filters: {
        nationality?: number;
        residence?: number;
    };
}

const props = defineProps<Props>();

const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');
const countryName = (c: Country) => (isRtl.value ? c.ar_name || c.name : c.name);

const selectedNationality = ref<number | undefined>(props.filters.nationality);
const selectedResidence = ref<number | undefined>(props.filters.residence);
const isChatOpen = ref(false);
const selectedUserId = ref<number | undefined>(undefined);
const isProfileModalOpen = ref(false);
const selectedProfileUserId = ref<number | undefined>(undefined);

const applyFilters = () => {
    router.get(
        '/online-members',
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
                    <div class="member-eyebrow">{{ isRtl ? 'اللقاء يبدأ الآن' : 'HERE RIGHT NOW' }}</div>
                    <h1>{{ trans('online.online_members') }}<span>.</span></h1>
                    <p>{{ trans('online.members_currently_active_on_the_platform') }}</p>
                </div>
                <div class="member-hero-stat">
                    <strong>{{ totalOnline }}</strong
                    ><span>{{ trans('online.online') }}</span>
                </div>
            </section>
            <div class="member-section-head member-reveal">
                <div>
                    <small>{{ isRtl ? 'تواصل الآن' : 'CONNECT IN THE MOMENT' }}</small>
                    <h2>{{ isRtl ? 'الأعضاء المتصلون' : 'Members online' }}</h2>
                </div>
                <span class="member-count">{{ users.total }} {{ trans('online.online_members') }}</span>
            </div>
            <div class="member-filters member-reveal">
                <div class="member-filter">
                    <label for="online-nationality">{{ trans('online.nationality') }}</label
                    ><select id="online-nationality" v-model="selectedNationality" @change="applyFilters">
                        <option value="">{{ trans('online.all') }}</option>
                        <option v-for="country in countries" :key="country.id" :value="country.id">{{ countryName(country) }}</option>
                    </select>
                </div>
                <div class="member-filter">
                    <label for="online-residence">{{ trans('online.residence') }}</label
                    ><select id="online-residence" v-model="selectedResidence" @change="applyFilters">
                        <option value="">{{ trans('online.all_countries') }}</option>
                        <option v-for="country in countries" :key="country.id" :value="country.id">{{ countryName(country) }}</option>
                    </select>
                </div>
                <button v-if="selectedNationality || selectedResidence" type="button" class="member-clear" @click="resetFilters">
                    <i class="ki-outline ki-cross"></i>{{ trans('online.clear_filters') }}
                </button>
            </div>
            <!-- <div class="member-ad"><GoogleAd slot-id="ONLINE_MEMBERS_TOP" format="horizontal" /></div> -->
            <div v-if="users.data.length" class="member-grid mt-10">
                <div v-for="user in users.data" :key="user.id" class="member-grid-item">
                    <UserCard :user="user" :countries="countries" @message="handleMessage" @view-profile="handleViewProfile" />
                </div>
            </div>
            <div v-else class="member-empty mt-10">
                <div class="member-empty-icon"><i class="ki-outline ki-wifi"></i></div>
                <h3>{{ trans('online.no_members_found') }}</h3>
                <p>
                    {{
                        selectedNationality || selectedResidence
                            ? trans('online.no_members_matching_your_filters_are_currently_online')
                            : trans('online.there_are_no_other_members_online_at_the_moment_check_back_later')
                    }}
                </p>
                <button v-if="selectedNationality || selectedResidence" type="button" class="member-clear" @click="resetFilters">
                    {{ trans('online.clear_filters') }}
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
