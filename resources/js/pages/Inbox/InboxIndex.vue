<script setup lang="ts">
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';
import Pagination from '@/components/Pagination.vue';
import UserProfileModal from '@/components/UserProfileModal.vue';
import { router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { usePresenceStore } from '@/stores/useUserStore';

interface Message {
    id: number;
    message: string;
    created_at: string;
}

interface User {
    id: number;
    name: string;
    username: string;
    age: number;
    nationality: string | number;
    marriage_status: string;
    mainProfileImage?: string;
}

interface Conversation {
    id: number;
    lastMessage: Message | null;
    otherUser: User;
}

interface Props {
    conversations: {
        data: Conversation[];
        links: any;
        meta: any;
    };
}

const props = defineProps<Props>();

const isChatDrawerOpen = ref(false);
const isProfileModalOpen = ref(false);
const selectedConversationId = ref<number | undefined>(undefined);
const selectedUserId = ref<number | undefined>(undefined);
const currentUser = usePage().props.auth.user;
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

const handleMessageFromModal = (userId: number) => {
    closeProfileModal();
    // Find conversation with this user
    const conversation = props.conversations.data.find((c) => c.otherUser.id === userId);
    if (conversation) {
        openChatDrawer(conversation.id);
    }
};

const deleteConversation = async (conversationId: number) => {
    if (!confirm('Are you sure you want to delete this conversation?')) {
        return;
    }

    router.delete(`/conversations/${conversationId}`, {
        preserveScroll: true,
    });
};

const formatMessageDate = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMinutes = Math.floor((now.getTime() - date.getTime()) / 60000);

    if (diffMinutes < 1) {
        return 'Just now';
    }
    if (diffMinutes < 60) {
        return `${diffMinutes}m ago`;
    }
    if (diffMinutes < 1440) {
        return `${Math.floor(diffMinutes / 60)}h ago`;
    }
    if (diffMinutes < 10080) {
        return `${Math.floor(diffMinutes / 1440)}d ago`;
    }

    return date.toLocaleDateString();
};

const truncateMessage = (message: string, maxLength: number = 50) => {
    if (message.length <= maxLength) {
        return message;
    }
    return message.substring(0, maxLength) + '...';
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
                        <h1 class="page-heading d-flex fw-bold fs-3 flex-column justify-content-center my-0 text-gray-900">Inbox</h1>
                        <!--end::Title-->

                        <!--begin::Breadcrumb-->
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <!--begin::Item-->
                            <li class="breadcrumb-item text-muted">
                                <a href="/metronic8/demo23/?page=index" class="text-muted text-hover-primary"> Home </a>
                            </li>
                            <!--end::Item-->
                            <!--begin::Item-->
                            <li class="breadcrumb-item">
                                <span class="bullet w-5px h-2px bg-gray-500"></span>
                            </li>
                            <!--end::Item-->

                            <!--begin::Item-->
                            <li class="breadcrumb-item text-muted">Inbox</li>
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
                    <!--begin::Inbox App - Messages -->
                    <div class="d-flex flex-column flex-lg-row">
                        <!--begin::Content-->
                        <div class="flex-lg-row-fluid">
                            <!--begin::Card-->
                            <div class="card">
                                <div class="card-header align-items-center gap-md-5 gap-2 py-5">
                                    <!--begin::Actions-->
                                    <div class="d-flex flex-wrap gap-2">
                                        <!--begin::Checkbox-->
                                        <div class="form-check form-check-sm form-check-custom form-check-solid me-lg-7 me-4">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                data-kt-check="true"
                                                data-kt-check-target="#kt_inbox_listing .form-check-input"
                                                value="1"
                                            />
                                        </div>
                                        <!--end::Checkbox-->

                                        <!--begin::Delete-->
                                        <a
                                            href="#"
                                            class="btn btn-sm btn-icon btn-light btn-active-light-primary"
                                            data-bs-toggle="tooltip"
                                            data-bs-dismiss="click"
                                            data-bs-placement="top"
                                            aria-label="Delete"
                                            data-bs-original-title="Delete"
                                            data-kt-initialized="1"
                                        >
                                            <i class="ki-outline ki-trash fs-2"></i>
                                        </a>
                                        <!--end::Delete-->
                                    </div>

                                    <p>Current Conversations: {{ conversations.data.length }}</p>
                                    <!--end::Actions-->
                                </div>

                                <div class="card-body p-0">
                                    <!--begin::Table-->
                                    <div id="kt_inbox_listing_wrapper" class="dt-container dt-bootstrap5 dt-empty-footer">
                                        <div id="" class="table-responsive">
                                            <table
                                                class="table-hover table-row-dashed fs-6 gy-5 dataTable my-0 table"
                                                id="kt_inbox_listing"
                                                style="width: 100%"
                                            >
                                                <colgroup>
                                                    <col data-dt-column="0" style="width: 9.75px" />
                                                    <col data-dt-column="1" style="width: 19.5px" />
                                                    <col data-dt-column="2" style="width: 114.227px" />
                                                    <col data-dt-column="3" style="width: 714.805px" />
                                                    <col data-dt-column="4" style="width: 72.2188px" />
                                                </colgroup>
                                                <thead class="d-none">
                                                    <tr>
                                                        <th data-dt-column="0" rowspan="1" colspan="1" class="dt-orderable-asc dt-orderable-desc">
                                                            <div class="dt-column-header">
                                                                <span class="dt-column-title">Checkbox</span
                                                                ><span
                                                                    class="dt-column-order"
                                                                    role="button"
                                                                    aria-label="Checkbox: Activate to sort"
                                                                    tabindex="0"
                                                                ></span>
                                                            </div>
                                                        </th>
                                                        <th data-dt-column="1" rowspan="1" colspan="1" class="dt-orderable-asc dt-orderable-desc">
                                                            <div class="dt-column-header">
                                                                <span class="dt-column-title">Actions</span
                                                                ><span
                                                                    class="dt-column-order"
                                                                    role="button"
                                                                    aria-label="Actions: Activate to sort"
                                                                    tabindex="0"
                                                                ></span>
                                                            </div>
                                                        </th>
                                                        <th data-dt-column="2" rowspan="1" colspan="1" class="dt-orderable-asc dt-orderable-desc">
                                                            <div class="dt-column-header">
                                                                <span class="dt-column-title">Author</span
                                                                ><span
                                                                    class="dt-column-order"
                                                                    role="button"
                                                                    aria-label="Author: Activate to sort"
                                                                    tabindex="0"
                                                                ></span>
                                                            </div>
                                                        </th>
                                                        <th data-dt-column="3" rowspan="1" colspan="1" class="dt-orderable-asc dt-orderable-desc">
                                                            <div class="dt-column-header">
                                                                <span class="dt-column-title">Title</span
                                                                ><span
                                                                    class="dt-column-order"
                                                                    role="button"
                                                                    aria-label="Title: Activate to sort"
                                                                    tabindex="0"
                                                                ></span>
                                                            </div>
                                                        </th>
                                                        <th data-dt-column="4" rowspan="1" colspan="1" class="dt-orderable-asc dt-orderable-desc">
                                                            <div class="dt-column-header">
                                                                <span class="dt-column-title">Date</span
                                                                ><span
                                                                    class="dt-column-order"
                                                                    role="button"
                                                                    aria-label="Date: Activate to sort"
                                                                    tabindex="0"
                                                                ></span>
                                                            </div>
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="conversation in conversations.data" :key="conversation.id" class="cursor-pointer">
                                                        <td class="ps-9">
                                                            <div class="form-check form-check-sm form-check-custom form-check-solid mt-3">
                                                                <input class="form-check-input" type="checkbox" value="1" @click.stop />
                                                            </div>
                                                        </td>
                                                        <td class="min-w-80px">
                                                            <!--begin::Delete-->
                                                            <button
                                                                @click.stop="deleteConversation(conversation.id)"
                                                                class="btn btn-icon btn-color-gray-500 btn-active-color-danger w-35px h-35px"
                                                                title="Delete conversation"
                                                            >
                                                                <i class="ki-outline ki-trash fs-3"></i>
                                                            </button>
                                                            <!--end::Delete-->
                                                        </td>
                                                        <td class="w-150px w-md-175px">
                                                            <div
                                                                @click.stop="openProfileModal(conversation.otherUser.id)"
                                                                class="d-flex align-items-center cursor-pointer text-gray-900"
                                                            >
                                                                <!--begin::Avatar-->
                                                                <div class="symbol symbol-45px position-relative me-3">
                                                                    <!-- Favorite Badge -->
                                                                    <div
                                                                        v-if="conversation.otherUser.isFavourite"
                                                                        class="position-absolute translate-middle d-flex align-items-center justify-content-center rounded-circle bg-danger start-0 top-0 border border-2 border-white"
                                                                        style="width: 20px; height: 20px; z-index: 1"
                                                                        title="Favorited"
                                                                    >
                                                                        <i class="fa fa-heart text-white" style="font-size: 10px"></i>
                                                                    </div>

                                                                    <!-- Ignore Badge -->
                                                                    <div
                                                                        v-if="conversation.otherUser.isIgnored"
                                                                        class="position-absolute translate-middle d-flex align-items-center justify-content-center rounded-circle bg-warning start-0 top-0 border border-2 border-white"
                                                                        style="width: 20px; height: 20px; z-index: 1"
                                                                        title="Ignored"
                                                                    >
                                                                        <i class="fa fa-ban text-white" style="font-size: 10px"></i>
                                                                    </div>

                                                                    <img
                                                                        :src="
                                                                            conversation.otherUser.mainProfileImage || '/assets/media/avatars/300-1.jpg'
                                                                        "
                                                                        :alt="conversation.otherUser.username"
                                                                        class="rounded-circle"
                                                                    />
                                                                    <div
                                                                        data-v-94a0a411=""
                                                                        class="position-absolute rounded-circle h-10px w-10px translate-middle bg-success start-100 top-100 border border-2 border-white"
                                                                        v-if="conversation.otherUser.isOnline"
                                                                    ></div>
                                                                </div>
                                                                <!--end::Avatar-->

                                                                <!--begin::User Info-->
                                                                <div class="d-flex flex-column">
                                                                    <span class="fw-bold mb-1 text-gray-900">{{
                                                                        conversation.otherUser.username
                                                                    }}</span>
                                                                    <div class="d-flex align-items-center gap-2">
                                                                        <span class="badge badge-light-primary fs-8">{{
                                                                            conversation.otherUser.marriage_status
                                                                        }}</span>
                                                                        <span class="text-muted fs-7">{{ conversation.otherUser.age }} yrs</span>
                                                                        <span class="text-muted fs-7">{{ conversation.otherUser.nationality }}</span>
                                                                    </div>
                                                                </div>
                                                                <!--end::User Info-->
                                                            </div>
                                                        </td>
                                                        <td @click="openChatDrawer(conversation.id)">
                                                            <div v-if="conversation.lastMessage" class="gap-1 pt-2 text-gray-900">
                                                                <div class="fs-6 text-gray-700">
                                                                    {{ truncateMessage(conversation.lastMessage.message) }}
                                                                </div>
                                                            </div>
                                                            <div v-else class="text-muted gap-1 pt-2">
                                                                <span class="fst-italic">No messages yet</span>
                                                            </div>
                                                        </td>
                                                        <td @click="openChatDrawer(conversation.id)" class="w-100px fs-7 pe-9 text-end">
                                                            <span v-if="conversation.lastMessage" class="fw-semibold text-gray-600">
                                                                {{ formatMessageDate(conversation.lastMessage.created_at) }}
                                                            </span>
                                                            <span v-else class="text-muted">-</span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <tfoot></tfoot>
                                            </table>
                                        </div>
                                        <div id="" class="row px-9 pt-3 pb-5">
                                            <Pagination :data="conversations" />
                                        </div>
                                        <div class="dt-autosize" style="width: 100%; height: 0px"></div>
                                    </div>
                                    <!--end::Table-->
                                </div>
                            </div>
                            <!--end::Card-->
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Inbox App - Messages -->
                </div>
                <!--end::Content container-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Content wrapper-->

        <!-- Chat Drawer -->
        <ChatDrawer :is-open="isChatDrawerOpen" :conversation-id="selectedConversationId" @close="closeChatDrawer" />

        <!-- User Profile Modal -->
        <UserProfileModal :is-open="isProfileModalOpen" :user-id="selectedUserId" @close="closeProfileModal" @message="handleMessageFromModal" />
    </div>
</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
</style>
