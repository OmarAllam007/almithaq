<script setup lang="ts">
import UserProfileModal from '@/components/UserProfileModal.vue';
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';
import { ref } from 'vue';
import axios from 'axios';
import Pagination from '@/components/Pagination.vue';
import { PaginationData } from '@/types/pagination';
import { router } from '@inertiajs/vue3';
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
        // Get or create conversation with this user
        const response = await axios.post('/conversations', {
            recipient_id: userId,
        });

        const conversationId = response.data.conversation_id;
        openChatDrawer(conversationId);
    } catch (error) {
        console.error('Failed to get or create conversation:', error);
    }
};

const changeTab = (tab: 'favorites' | 'ignored') => {
    router.get('/my-interactions', { tab }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const removeInteraction = async (userId: number) => {
    const actionText = props.activeTab === 'favorites' ? 'unfavorite' : 'unignore';

    const result = await Swal.fire({
        title: trans(props.activeTab === 'favorites' ? 'user_interactions.unfavorite_confirm' : 'user_interactions.unignore_confirm'),
        showDenyButton: true,
        customClass: {
            confirmButton: "btn btn-primary",
            denyButton: "btn btn-secondary",
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

            // Reload the page to refresh the list
            router.reload({
                preserveScroll: true,
            });
        } catch (error) {
            console.error('Failed to remove interaction:', error);
        }
    }
};
</script>

<template>
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <!--begin::Content wrapper-->
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-lg-0 py-3">
                <!--begin::Toolbar container-->
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <!--begin::Page title-->
                    <div class="page-title d-flex flex-column justify-content-center me-3">
                        <!--begin::Title-->
                        <h1 class="page-heading d-flex fw-bold fs-3 flex-column justify-content-center my-0 text-gray-900">
                            {{ trans('user_interactions.like_and_ignore_list') }}
                        </h1>
                        <!--end::Title-->

                        <!--begin::Breadcrumb-->
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <!--begin::Item-->
                            <li class="breadcrumb-item text-muted">
                                <a href="/" class="text-muted text-hover-primary">{{ trans('user_interactions.home') }}</a>
                            </li>
                            <!--end::Item-->
                            <!--begin::Item-->
                            <li class="breadcrumb-item">
                                <span class="bullet w-5px h-2px bg-gray-500"></span>
                            </li>
                            <!--end::Item-->

                            <!--begin::Item-->
                            <li class="breadcrumb-item text-muted">{{ trans('user_interactions.my_interactions') }}</li>
                            <!--end::Item-->
                        </ul>
                        <!--end::Breadcrumb-->
                    </div>
                    <!--end::Page title-->
                </div>
                <!--end::Toolbar container-->
            </div>
            <!--end::Toolbar-->

            <!--begin::Content-->
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <!--begin::Content container-->
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <!--begin::Card-->
                    <div class="card">
                        <!--begin::Card header-->
                        <div class="card-header border-0 pt-6">
                            <!--begin::Card toolbar-->
                            <div class="card-toolbar w-100">
                                <!--begin::Tabs-->
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
                                <!--end::Tabs-->
                            </div>
                            <!--end::Card toolbar-->
                        </div>
                        <!--end::Card header-->

                        <!--begin::Card body-->
                        <div class="card-body p-0">
                            <!--begin::Table-->
                            <div id="kt_inbox_listing_wrapper" class="dt-container dt-bootstrap5 dt-empty-footer">
                                <div id="" class="table-responsive">
                                    <table
                                        class="table-hover table-row-dashed fs-6 gy-5 dataTable my-0 table"
                                        id="kt_inbox_listing"
                                        style="width: 100%"
                                    >
                                        <thead>
                                            <tr>
                                                <th data-dt-column="0" rowspan="1" colspan="1" class="dt-orderable-asc dt-orderable-desc p-5">
                                                    <div>
                                                        <span class="dt-column-title">{{ trans('user_interactions.member') }}</span>
                                                    </div>
                                                </th>
                                                <th data-dt-column="1" rowspan="1" colspan="1" class="dt-orderable-asc dt-orderable-desc">
                                                    <div>
                                                        <span class="dt-column-title">{{ trans('user_interactions.added_date') }}</span>
                                                    </div>
                                                </th>
                                                <th data-dt-column="2" rowspan="1" colspan="1" class="text-end pe-5">
                                                    <div>
                                                        <span class="dt-column-title">{{ trans('user_interactions.actions') }}</span>
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="user in users.data"
                                                :key="user.id"
                                                class="cursor-pointer"
                                                @click.stop="openProfileModal(user.id)"
                                            >
                                                <td class="p-5">
                                                    <div class="d-flex align-items-center cursor-pointer text-gray-900">
                                                        <!--begin::Avatar-->
                                                        <div class="symbol symbol-45px me-3">
                                                            <img
                                                                :src="user?.mainProfileImage || '/assets/media/avatars/300-1.jpg'"
                                                                :alt="user?.username"
                                                                class="rounded-circle"
                                                            />
                                                        </div>
                                                        <!--end::Avatar-->

                                                        <!--begin::User Info-->
                                                        <div class="d-flex flex-column">
                                                            <span class="fw-bold mb-1 text-gray-900">{{ user.username }}</span>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="badge badge-light-primary fs-8">
                                                                    {{ user?.marriage_status }}
                                                                </span>
                                                                <span class="text-muted fs-5" :title="user?.nationality?.name">{{ user?.nationality?.flag }}</span>
                                                                <span class="text-muted fs-7">{{ user?.age }} {{ trans('user_interactions.yrs') }}</span>
                                                            </div>
                                                        </div>
                                                        <!--end::User Info-->
                                                    </div>
                                                </td>

                                                <td>
                                                    <div class="text-muted gap-1 pt-2">
                                                        <span class="fst-italic">{{ user.created_at }}</span>
                                                    </div>
                                                </td>

                                                <td class="text-end pe-5">
                                                    <button
                                                        @click.stop="removeInteraction(user.id)"
                                                        class="btn btn-sm btn-icon btn-light-danger"
                                                        :title="activeTab === 'favorites' ? trans('user_interactions.remove_from_favorites') : trans('user_interactions.unignore_user')"
                                                    >
                                                        <i class="ki-outline ki-trash fs-3"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>

                                <!--begin::Empty state-->
                                <div v-if="users.data.length === 0" class="text-center py-20">
                                    <div class="mb-10">
                                        <i class="ki-outline ki-user fs-5x text-gray-400"></i>
                                    </div>
                                    <h3 class="text-gray-800 mb-3">{{ trans('user_interactions.no_users_found') }}</h3>
                                    <p class="text-gray-600 fs-5">
                                        {{ activeTab === 'favorites' ? trans('user_interactions.no_favorites_yet') : trans('user_interactions.no_ignored_yet') }}
                                    </p>
                                </div>
                                <!--end::Empty state-->

                                <div id="" class="row px-9 pt-3 pb-5">
                                    <!-- Pagination -->
                                    <Pagination :data="users" />
                                </div>
                                <div class="dt-autosize" style="width: 100%; height: 0px"></div>
                            </div>
                            <!--end::Table-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Content container-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Content wrapper-->

        <!-- Chat Drawer -->
        <ChatDrawer :is-open="isChatDrawerOpen" :conversation-id="selectedConversationId" @close="closeChatDrawer" />

        <!-- User Profile Modal -->
        <UserProfileModal
            :is-open="isProfileModalOpen"
            :user-id="selectedUserId"
            @close="closeProfileModal"
            @message="handleMessageFromModal"
        />
    </div>
</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
</style>
