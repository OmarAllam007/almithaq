<script setup lang="ts">
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';
import GoogleAd from '@/components/GoogleAd.vue';
import Pagination from '@/components/Pagination.vue';
import UserCard from '@/components/UserCard.vue';
import UserProfileModal from '@/components/UserProfileModal.vue';
import { useLang } from '@/composables/useLang';
import { PaginationData } from '@/types/pagination';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

const { trans } = useLang();
const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');

const props = defineProps<Props>();
interface Props {
    users: PaginationData;
}

const isChatDrawerOpen = ref(false);
const isProfileModalOpen = ref(false);
const selectedConversationId = ref<number | undefined>(undefined);
const selectedUserId = ref<number | undefined>(undefined);

const openChatDrawer = (conversationId: number) => {
    selectedConversationId.value = conversationId;
    isChatDrawerOpen.value = true;
};

const closeChatDrawer = () => {
    isChatDrawerOpen.value = false;
    selectedConversationId.value = undefined;
};

const openProfileModal = (userId: number) => {
    selectedUserId.value = userId;
    isProfileModalOpen.value = true;
};

const closeProfileModal = () => {
    isProfileModalOpen.value = false;
    selectedUserId.value = undefined;
};

const handleMessage = async (userId: number) => {
    closeProfileModal();
    try {
        const response = await axios.post('/conversations', { recipient_id: userId });
        openChatDrawer(response.data.conversation_id);
    } catch (error) {
        console.error('Failed to get or create conversation:', error);
    }
};

const mapUser = (user: any) => ({
    ...user,
    name: user.name || user.username,
    is_favorited: user.is_favourite ?? user.is_favorited ?? false,
});
</script>

<template>
    <div id="kt_app_content" class="app-content flex-column-fluid member-page">
        <div id="kt_app_content_container" class="app-container container-xxl member-container">
            <section class="member-hero member-reveal">
                <div class="member-hero-art"></div>
                <div class="member-hero-copy">
                    <div class="member-eyebrow">{{ isRtl ? 'من مرّ بملفك' : 'PROFILE ACTIVITY' }}</div>
                    <h1>{{ trans('user_interactions.who_visited_me') }}<span>.</span></h1>
                    <p>
                        {{
                            isRtl
                                ? 'تعرّف على الأشخاص الذين زاروا ملفك مؤخراً، فقد تبدأ قصة من هنا.'
                                : 'See who has taken a closer look at your profile. A new connection may start here.'
                        }}
                    </p>
                </div>
                <div class="member-hero-stat">
                    <strong>{{ users.total }}</strong
                    ><span>{{ trans('user_interactions.total_visits') }}</span>
                </div>
            </section>
            <div class="member-section-head member-reveal">
                <div>
                    <small>{{ isRtl ? 'آخر النشاطات' : 'RECENT ACTIVITY' }}</small>
                    <h2>{{ trans('user_interactions.who_visited_me') }}</h2>
                </div>
                <span class="member-count">{{ users.total }} {{ trans('user_interactions.total_visits') }}</span>
            </div>
            <div v-if="users.data.length" class="member-grid">
                <div v-for="user in users.data" :key="user.id" class="member-grid-item">
                    <UserCard :user="mapUser(user)" @view-profile="openProfileModal" @message="handleMessage" />
                    <div class="member-liked-date"><i class="ki-outline ki-eye"></i>{{ user.visited_at }}</div>
                </div>
            </div>
            <div v-else class="member-empty">
                <div class="member-empty-icon"><i class="ki-outline ki-eye"></i></div>
                <h3>{{ trans('user_interactions.no_users_found') }}</h3>
                <p>{{ isRtl ? 'عندما يزور أحد ملفك، سيظهر هنا.' : 'When someone visits your profile, you’ll see them here.' }}</p>
            </div>
            <!-- <div class="member-ad"><GoogleAd slot-id="WHO_VISITED_ME" format="auto" /></div> -->
            <Pagination :data="users" />
        </div>
        <ChatDrawer :is-open="isChatDrawerOpen" :conversation-id="selectedConversationId" @close="closeChatDrawer" />
        <UserProfileModal :is-open="isProfileModalOpen" :user-id="selectedUserId" @close="closeProfileModal" @message="handleMessage" />
    </div>
</template>
