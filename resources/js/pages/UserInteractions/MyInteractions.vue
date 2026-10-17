<script setup lang="ts">
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';
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
declare const Swal: any;

interface Props {
    users: PaginationData;
    activeTab: 'favorites' | 'ignored';
}

const props = defineProps<Props>();

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

const handleMessageFromModal = async (userId: number) => {
    closeProfileModal();
    try {
        const response = await axios.post('/conversations', { recipient_id: userId });
        openChatDrawer(response.data.conversation_id);
    } catch (error) {
        console.error('Failed to get or create conversation:', error);
    }
};

const changeTab = (tab: 'favorites' | 'ignored') => {
    router.get('/my-interactions', { tab }, { preserveState: true, preserveScroll: true });
};

const removeInteraction = async (userId: number) => {
    const result = await Swal.fire({
        title: trans(props.activeTab === 'favorites' ? 'user_interactions.unfavorite_confirm' : 'user_interactions.unignore_confirm'),
        showDenyButton: true,
        customClass: {
            confirmButton: 'btn btn-primary',
            denyButton: 'btn btn-secondary',
        },
        buttonsStyling: false,
        confirmButtonText: trans('user_interactions.yes'),
        denyButtonText: trans('user_interactions.no'),
    });

    if (result.isConfirmed) {
        try {
            if (props.activeTab === 'favorites') {
                await axios.post(`/users/${userId}/favorite`);
            } else {
                await axios.post(`/users/${userId}/ignore`);
            }
            router.reload();
        } catch (error) {
            console.error('Failed to remove interaction:', error);
        }
    }
};

const mapUser = (user: any) => ({
    ...user,
    name: user.name || user.username,
    is_favorited: user.is_favourite ?? user.is_favorited ?? false,
});
</script>

<template>
    <div id="kt_app_content" class="app-content flex-column-fluid member-page interactions-page">
        <div id="kt_app_content_container" class="app-container container-xxl member-container">
            <section class="member-hero member-reveal">
                <div class="member-hero-art"></div>
                <div class="member-hero-copy">
                    <div class="member-eyebrow">{{ isRtl ? 'إدارة تفاعلاتك' : 'YOUR CONNECTIONS' }}</div>
                    <h1>{{ trans('user_interactions.my_interactions') }}<span>.</span></h1>
                    <p>
                        {{
                            isRtl
                                ? 'احتفظ بمن يهمك، وأدر تفضيلاتك في مكان واحد.'
                                : 'Keep the people who matter close and manage your preferences in one place.'
                        }}
                    </p>
                </div>
                <div class="member-hero-stat">
                    <strong>{{ users.total }}</strong
                    ><span>{{
                        activeTab === 'favorites' ? trans('user_interactions.total_favorites') : trans('user_interactions.total_ignored')
                    }}</span>
                </div>
            </section>
            <div class="member-section-head member-reveal">
                <div>
                    <small>{{ isRtl ? 'قوائمك' : 'YOUR LISTS' }}</small>
                    <h2>{{ trans('user_interactions.like_and_ignore_list') }}</h2>
                </div>
                <span class="member-count"
                    >{{ users.total }}
                    {{ activeTab === 'favorites' ? trans('user_interactions.total_favorites') : trans('user_interactions.total_ignored') }}</span
                >
            </div>
            <div class="interaction-tabs member-reveal" role="tablist" :aria-label="trans('user_interactions.my_interactions')">
                <button
                    type="button"
                    role="tab"
                    :aria-selected="activeTab === 'favorites'"
                    :class="{ active: activeTab === 'favorites' }"
                    @click="changeTab('favorites')"
                >
                    <i class="ki-outline ki-heart"></i>{{ trans('user_interactions.my_favorites') }}</button
                ><button
                    type="button"
                    role="tab"
                    :aria-selected="activeTab === 'ignored'"
                    :class="{ active: activeTab === 'ignored' }"
                    @click="changeTab('ignored')"
                >
                    <i class="ki-outline ki-eye-slash"></i>{{ trans('user_interactions.ignored_users') }}
                </button>
            </div>
            <div v-if="users.data.length" class="member-grid">
                <div v-for="user in users.data" :key="user.id" class="member-grid-item">
                    <UserCard :user="mapUser(user)" @view-profile="openProfileModal" @message="handleMessageFromModal" />
                    <div class="member-liked-date">
                        <i :class="['ki-outline', activeTab === 'favorites' ? 'ki-heart' : 'ki-eye-slash']"></i>{{ user.created_at }}
                    </div>
                    <button type="button" class="interaction-remove" @click="removeInteraction(user.id)">
                        <i :class="['ki-outline', activeTab === 'favorites' ? 'ki-trash' : 'ki-eye']"></i
                        >{{ activeTab === 'favorites' ? trans('user_interactions.remove_from_favorites') : trans('user_interactions.unignore_user') }}
                    </button>
                </div>
            </div>
            <div v-else class="member-empty">
                <div class="member-empty-icon"><i :class="['ki-outline', activeTab === 'favorites' ? 'ki-heart' : 'ki-eye-slash']"></i></div>
                <h3>{{ trans('user_interactions.no_users_found') }}</h3>
                <p>{{ activeTab === 'favorites' ? trans('user_interactions.no_favorites_yet') : trans('user_interactions.no_ignored_yet') }}</p>
            </div>
            <Pagination :data="users" />
        </div>
        <ChatDrawer :is-open="isChatDrawerOpen" :conversation-id="selectedConversationId" @close="closeChatDrawer" />
        <UserProfileModal :is-open="isProfileModalOpen" :user-id="selectedUserId" @close="closeProfileModal" @message="handleMessageFromModal" />
    </div>
</template>
