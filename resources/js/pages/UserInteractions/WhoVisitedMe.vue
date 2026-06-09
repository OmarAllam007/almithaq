<script setup lang="ts">
import UserCard from '@/components/UserCard.vue';
import UserProfileModal from '@/components/UserProfileModal.vue';
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';
import GoogleAd from '@/components/GoogleAd.vue';
import { ref } from 'vue';
import axios from 'axios';
import Pagination from '@/components/Pagination.vue';
import { PaginationData } from '@/types/pagination';
import { useLang } from '@/composables/useLang';

const { trans } = useLang();

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
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-lg-0 py-3">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center me-3">
                        <h1 class="page-heading d-flex fw-bold fs-3 flex-column justify-content-center my-0 text-gray-900">
                            {{ trans('user_interactions.who_visited_me') }}
                        </h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="/" class="text-muted text-hover-primary">{{ trans('user_interactions.home') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet w-5px h-2px bg-gray-500"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">{{ trans('user_interactions.who_visited_me') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--end::Toolbar-->

            <!--begin::Content-->
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <!-- Total count -->
                    <div class="d-flex align-items-center mb-5">
                        <span class="fw-semibold text-gray-700 fs-6">
                            {{ trans('user_interactions.total_visits') }}: {{ users.total }}
                        </span>
                    </div>

                    <!-- Cards grid -->
                    <div class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-3 mb-6">
                        <div
                            v-for="user in users.data"
                            :key="user.id"
                            class="col d-flex flex-column gap-2"
                        >
                            <UserCard
                                :user="mapUser(user)"
                                @view-profile="openProfileModal"
                                @message="handleMessage"
                            />
                            <div class="text-center text-muted fs-8">
                                <i class="ki-outline ki-eye fs-8 text-primary me-1"></i>
                                {{ user.visited_at }}
                            </div>
                        </div>
                    </div>

                    <!-- Ad -->
                    <div class="mb-6">
                        <GoogleAd slot-id="WHO_VISITED_ME" format="auto" />
                    </div>

                    <!-- Pagination -->
                    <Pagination :data="users" />
                </div>
            </div>
            <!--end::Content-->
        </div>

        <ChatDrawer :is-open="isChatDrawerOpen" :conversation-id="selectedConversationId" @close="closeChatDrawer" />

        <UserProfileModal
            :is-open="isProfileModalOpen"
            :user-id="selectedUserId"
            @close="closeProfileModal"
            @message="handleMessage"
        />
    </div>
</template>
