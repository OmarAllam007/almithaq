<script setup lang="ts">
import UserCard from '@/components/UserCard.vue';
import UserProfileModal from '@/components/UserProfileModal.vue';
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';
import Pagination from '@/components/Pagination.vue';
import { ref } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { PaginationData } from '@/types/pagination';
import { useLang } from '@/composables/useLang';

const { trans } = useLang();

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
            router.reload({ preserveScroll: true });
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
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-lg-0 py-3">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center me-3">
                        <h1 class="page-heading d-flex fw-bold fs-3 flex-column justify-content-center my-0 text-gray-900">
                            {{ trans('user_interactions.like_and_ignore_list') }}
                        </h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="/" class="text-muted text-hover-primary">{{ trans('user_interactions.home') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet w-5px h-2px bg-gray-500 mx-2"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">{{ trans('user_interactions.my_interactions') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--end::Toolbar-->

            <!--begin::Content-->
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <!--begin::Card-->
                    <div class="card">
                        <!--begin::Card header (tabs)-->
                        <div class="card-header border-0 pt-6">
                            <div class="card-toolbar w-100">
                                <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold w-100">
                                    <li class="nav-item flex-fill">
                                        <button
                                            @click="changeTab('favorites')"
                                            :class="['nav-link text-center w-100', { active: activeTab === 'favorites' }]"
                                        >
                                            <i class="ki-outline ki-heart fs-3 me-2"></i>
                                            {{ trans('user_interactions.my_favorites') }}
                                        </button>
                                    </li>
                                    <li class="nav-item flex-fill">
                                        <button
                                            @click="changeTab('ignored')"
                                            :class="['nav-link text-center w-100', { active: activeTab === 'ignored' }]"
                                        >
                                            <i class="ki-outline ki-cross-circle fs-3 me-2"></i>
                                            {{ trans('user_interactions.ignored_users') }}
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <!--end::Card header-->

                        <!--begin::Card body-->
                        <div class="card-body">
                            <!-- Total count -->
                            <div class="d-flex align-items-center mb-5">
                                <span class="fw-semibold text-gray-700 fs-6">
                                    {{ activeTab === 'favorites' ? trans('user_interactions.total_favorites') : trans('user_interactions.total_ignored') }}: {{ users.total }}
                                </span>
                            </div>

                            <!-- Empty state -->
                            <div v-if="users.data.length === 0" class="text-center py-20">
                                <div class="mb-6">
                                    <i :class="['ki-outline fs-5x text-gray-400', activeTab === 'favorites' ? 'ki-heart' : 'ki-cross-circle']"></i>
                                </div>
                                <h3 class="text-gray-800 mb-3">{{ trans('user_interactions.no_users_found') }}</h3>
                                <p class="text-gray-600 fs-5">
                                    {{ activeTab === 'favorites' ? trans('user_interactions.no_favorites_yet') : trans('user_interactions.no_ignored_yet') }}
                                </p>
                            </div>

                            <!-- Cards grid -->
                            <div v-else class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-3 mb-6">
                                <div
                                    v-for="user in users.data"
                                    :key="user.id"
                                    class="col d-flex flex-column gap-2"
                                >
                                    <UserCard
                                        :user="mapUser(user)"
                                        @view-profile="openProfileModal"
                                        @message="handleMessageFromModal"
                                    />

                                    <!-- Action date -->
                                    <div class="text-center text-muted fs-8">
                                        <i
                                            :class="['ki-outline fs-8 me-1', activeTab === 'favorites' ? 'ki-heart text-danger' : 'ki-cross-circle text-warning']"
                                        ></i>
                                        {{ user.created_at }}
                                    </div>

                                    <!-- Remove button -->
                                    <button
                                        @click.stop="removeInteraction(user.id)"
                                        :class="['btn btn-sm w-100', activeTab === 'favorites' ? 'btn-light-danger' : 'mi-unignore-btn']"
                                    >
                                        <i :class="['ki-outline fs-7 me-1', activeTab === 'favorites' ? 'ki-trash' : 'ki-eye']"></i>
                                        {{ activeTab === 'favorites' ? trans('user_interactions.remove_from_favorites') : trans('user_interactions.unignore_user') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Pagination -->
                            <Pagination :data="users" />
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                </div>
            </div>
            <!--end::Content-->
        </div>

        <ChatDrawer :is-open="isChatDrawerOpen" :conversation-id="selectedConversationId" @close="closeChatDrawer" />

        <UserProfileModal
            :is-open="isProfileModalOpen"
            :user-id="selectedUserId"
            @close="closeProfileModal"
            @message="handleMessageFromModal"
        />
    </div>
</template>

<style scoped>
/* ─── Unignore button ──────────────────────────────────────── */
.mi-unignore-btn {
    background: rgba(255, 80, 80, 0.08);
    color: #cc2222;
    border: 1px solid rgba(255, 80, 80, 0.22);
    font-weight: 600;
    transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}

.mi-unignore-btn:hover {
    background: rgba(255, 80, 80, 0.15);
    border-color: rgba(255, 80, 80, 0.45);
    box-shadow: 0 3px 10px rgba(200, 30, 30, 0.18);
    color: #aa1111;
}
</style>
