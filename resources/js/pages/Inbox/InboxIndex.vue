<script setup lang="ts">
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';
import Pagination from '@/components/Pagination.vue';
import UserProfileModal from '@/components/UserProfileModal.vue';
import { router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { usePresenceStore } from '@/stores/useUserStore';
import { markAllAsRead } from '@/actions/App/Http/Controllers/MessageController';
import { bulkDestroy, destroy } from '@/actions/App/Http/Controllers/ConversationController';
import { useLang } from '@/composables/useLang';

const { trans } = useLang();

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

const formatMessageDate = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMinutes = Math.floor((now.getTime() - date.getTime()) / 60000);

    if (diffMinutes < 1) {
        return trans('inbox.just_now');
    }
    if (diffMinutes < 60) {
        return trans('inbox.m_ago', { minutes: diffMinutes });
    }
    if (diffMinutes < 1440) {
        return trans('inbox.h_ago', { hours: Math.floor(diffMinutes / 60) });
    }
    if (diffMinutes < 10080) {
        return trans('inbox.d_ago', { days: Math.floor(diffMinutes / 1440) });
    }

    return date.toLocaleDateString();
};

const truncateMessage = (message: string, maxLength: number = 50) => {
    if (message.length <= maxLength) {
        return message;
    }
    return message.substring(0, maxLength) + '...';
};

// Selection state
const selectedConversations = ref<number[]>([]);
const selectAll = ref(false);

const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedConversations.value = props.conversations.data.map((c) => c.id);
    } else {
        selectedConversations.value = [];
    }
};

const toggleConversationSelection = (conversationId: number) => {
    const index = selectedConversations.value.indexOf(conversationId);
    if (index > -1) {
        selectedConversations.value.splice(index, 1);
    } else {
        selectedConversations.value.push(conversationId);
    }
    selectAll.value = selectedConversations.value.length === props.conversations.data.length;
};

const hasSelectedConversations = computed(() => selectedConversations.value.length > 0);

// Delete modal state
const showDeleteModal = ref(false);
const deleteTarget = ref<'single' | 'bulk'>('single');
const conversationToDelete = ref<number | null>(null);
const isDeleting = ref(false);

const openDeleteModal = (conversationId: number) => {
    conversationToDelete.value = conversationId;
    deleteTarget.value = 'single';
    showDeleteModal.value = true;
};

const openBulkDeleteModal = () => {
    if (selectedConversations.value.length === 0) return;
    deleteTarget.value = 'bulk';
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    conversationToDelete.value = null;
};

const confirmDelete = async () => {
    isDeleting.value = true;

    if (deleteTarget.value === 'single' && conversationToDelete.value) {
        router.delete(destroy(conversationToDelete.value).url, {
            preserveScroll: true,
            onSuccess: () => {
                closeDeleteModal();
            },
            onFinish: () => {
                isDeleting.value = false;
                router.reload({ only: ['conversations'] });
            },
        });
    } else if (deleteTarget.value === 'bulk') {
        router.delete(bulkDestroy().url, {
            data: { conversation_ids: selectedConversations.value },
            preserveScroll: true,
            onSuccess: () => {
                selectedConversations.value = [];
                selectAll.value = false;
                closeDeleteModal();
            },
            onFinish: () => {
                isDeleting.value = false;
                router.reload({ only: ['conversations'] });
            },
        });
    }
};

// Mark all as read
const isMarkingAllAsRead = ref(false);

const handleMarkAllAsRead = async () => {
    isMarkingAllAsRead.value = true;
    router.post(markAllAsRead().url, {}, {
        preserveScroll: true,
        onFinish: () => {
            isMarkingAllAsRead.value = false;
        },
    });
};

const deleteModalTitle = computed(() => {
    if (deleteTarget.value === 'single') {
        return trans('inbox.delete_conversation');
    }
    return trans('inbox.delete_conversations', { count: selectedConversations.value.length });
});

const deleteModalMessage = computed(() => {
    if (deleteTarget.value === 'single') {
        return trans('inbox.delete_conversation_confirm');
    }
    return trans('inbox.delete_conversations_confirm', { count: selectedConversations.value.length });
});
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
                        <h1 class="page-heading d-flex fw-bold fs-3 flex-column justify-content-center my-0 text-gray-900">{{ trans('inbox.inbox') }}</h1>
                        <!--end::Title-->

                        <!--begin::Breadcrumb-->
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <!--begin::Item-->
                            <li class="breadcrumb-item text-muted">
                                <a href="/metronic8/demo23/?page=index" class="text-muted text-hover-primary">{{ trans('inbox.home') }}</a>
                            </li>
                            <!--end::Item-->
                            <!--begin::Item-->
                            <li class="breadcrumb-item">
                                <span class="bullet w-5px h-2px bg-gray-500"></span>
                            </li>
                            <!--end::Item-->

                            <!--begin::Item-->
                            <li class="breadcrumb-item text-muted">{{ trans('inbox.inbox') }}</li>
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
                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                        <!--begin::Checkbox-->
                                        <div class="form-check form-check-sm form-check-custom form-check-solid me-lg-4 me-2">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                v-model="selectAll"
                                                @change="toggleSelectAll"
                                            />
                                        </div>
                                        <!--end::Checkbox-->

                                        <!--begin::Delete Selected-->
                                        <button
                                            @click="openBulkDeleteModal"
                                            class="btn btn-sm btn-icon btn-light btn-active-light-danger"
                                            :class="{ 'opacity-50': !hasSelectedConversations }"
                                            :disabled="!hasSelectedConversations"
                                            :title="trans('inbox.delete_selected')"
                                        >
                                            <i class="ki-outline ki-trash fs-2"></i>
                                        </button>
                                        <!--end::Delete Selected-->

                                        <!--begin::Mark All Read-->
                                        <button
                                            @click="handleMarkAllAsRead"
                                            class="btn btn-sm btn-light btn-active-light-primary"
                                            :disabled="isMarkingAllAsRead"
                                            :title="trans('inbox.mark_all_read')"
                                        >
                                            <span v-if="isMarkingAllAsRead" class="spinner-border spinner-border-sm me-1"></span>
                                            <i v-else class="ki-outline ki-double-check fs-2 me-1"></i>
                                            {{ trans('inbox.mark_all_read') }}
                                        </button>
                                        <!--end::Mark All Read-->

                                        <span v-if="hasSelectedConversations" class="text-muted fs-7 ms-2">
                                            {{ selectedConversations.length }} {{ trans('inbox.selected') }}
                                        </span>
                                    </div>

                                    <p class="mb-0">{{ trans('inbox.conversations') }}: {{ conversations.data.length }}</p>
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
                                                                <span class="dt-column-title">{{ trans('inbox.checkbox') }}</span
                                                                ><span
                                                                    class="dt-column-order"
                                                                    role="button"
                                                                    :aria-label="trans('inbox.checkbox_sort_label')"
                                                                    tabindex="0"
                                                                ></span>
                                                            </div>
                                                        </th>
                                                        <th data-dt-column="1" rowspan="1" colspan="1" class="dt-orderable-asc dt-orderable-desc">
                                                            <div class="dt-column-header">
                                                                <span class="dt-column-title">{{ trans('inbox.actions') }}</span
                                                                ><span
                                                                    class="dt-column-order"
                                                                    role="button"
                                                                    :aria-label="trans('inbox.actions_sort_label')"
                                                                    tabindex="0"
                                                                ></span>
                                                            </div>
                                                        </th>
                                                        <th data-dt-column="2" rowspan="1" colspan="1" class="dt-orderable-asc dt-orderable-desc">
                                                            <div class="dt-column-header">
                                                                <span class="dt-column-title">{{ trans('inbox.author') }}</span
                                                                ><span
                                                                    class="dt-column-order"
                                                                    role="button"
                                                                    :aria-label="trans('inbox.author_sort_label')"
                                                                    tabindex="0"
                                                                ></span>
                                                            </div>
                                                        </th>
                                                        <th data-dt-column="3" rowspan="1" colspan="1" class="dt-orderable-asc dt-orderable-desc">
                                                            <div class="dt-column-header">
                                                                <span class="dt-column-title">{{ trans('inbox.title') }}</span
                                                                ><span
                                                                    class="dt-column-order"
                                                                    role="button"
                                                                    :aria-label="trans('inbox.title_sort_label')"
                                                                    tabindex="0"
                                                                ></span>
                                                            </div>
                                                        </th>
                                                        <th data-dt-column="4" rowspan="1" colspan="1" class="dt-orderable-asc dt-orderable-desc">
                                                            <div class="dt-column-header">
                                                                <span class="dt-column-title">{{ trans('inbox.date') }}</span
                                                                ><span
                                                                    class="dt-column-order"
                                                                    role="button"
                                                                    :aria-label="trans('inbox.date_sort_label')"
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
                                                                <input
                                                                    class="form-check-input"
                                                                    type="checkbox"
                                                                    :checked="selectedConversations.includes(conversation.id)"
                                                                    @click.stop="toggleConversationSelection(conversation.id)"
                                                                />
                                                            </div>
                                                        </td>
                                                        <td class="min-w-80px">
                                                            <!--begin::Delete-->
                                                            <button
                                                                @click.stop="openDeleteModal(conversation.id)"
                                                                class="btn btn-icon btn-color-gray-500 btn-active-color-danger w-35px h-35px"
                                                                :title="trans('inbox.delete_conversation')"
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
                                                                        :title="trans('inbox.favorited')"
                                                                    >
                                                                        <i class="fa fa-heart text-white" style="font-size: 10px"></i>
                                                                    </div>

                                                                    <!-- Ignore Badge -->
                                                                    <div
                                                                        v-if="conversation.otherUser.isIgnored"
                                                                        class="position-absolute translate-middle d-flex align-items-center justify-content-center rounded-circle bg-warning start-0 top-0 border border-2 border-white"
                                                                        style="width: 20px; height: 20px; z-index: 1"
                                                                        :title="trans('inbox.ignored')"
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
                                                                    <span class="fw-bold mb-1 text-gray-900 me-10">{{
                                                                        conversation.otherUser.username
                                                                    }}</span>
                                                                    <div class="d-flex align-items-center gap-2">
                                                                        <span class="badge badge-light-primary fs-8">{{
                                                                            conversation.otherUser.marriage_status
                                                                        }}</span>
                                                                        <span class="text-muted fs-7">{{ conversation.otherUser.age }} {{ trans('inbox.yrs') }}</span>
                                                                        <span class="text-muted fs-7">{{ conversation.otherUser.nationality }}</span>
                                                                    </div>
                                                                </div>
                                                                <!--end::User Info-->
                                                            </div>
                                                        </td>
                                                        <td @click="openChatDrawer(conversation.id)">
                                                            <div v-if="conversation.lastMessage" class="gap-1 pt-2 text-gray-900">

                                                                <div class="fs-6  badge overflow-ellipsis" :class="conversation.lastMessage.isRead ? 'text-gray-700' : 'badge-primary text-white'">
                                                                    {{ truncateMessage(conversation.lastMessage.message) }}
                                                                </div>
                                                            </div>
                                                            <div v-else class="text-muted gap-1 pt-2">
                                                                <span class="fst-italic">{{ trans('inbox.no_messages_yet') }}</span>
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

        <!-- Delete Confirmation Modal -->
        <div
            v-if="showDeleteModal"
            class="modal fade show d-block"
            tabindex="-1"
            style="background-color: rgba(0, 0, 0, 0.5)"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">
                            <i class="ki-outline ki-trash fs-2 text-danger me-2"></i>
                            {{ deleteModalTitle }}
                        </h5>
                        <button type="button" class="btn-close" @click="closeDeleteModal" :disabled="isDeleting"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger d-flex align-items-center p-5">
                            <i class="ki-outline ki-information-5 fs-2hx text-danger me-4"></i>
                            <div class="d-flex flex-column">
                                <span>{{ deleteModalMessage }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" @click="closeDeleteModal" :disabled="isDeleting">
                            {{ trans('inbox.cancel') }}
                        </button>
                        <button type="button" class="btn btn-danger" @click="confirmDelete" :disabled="isDeleting">
                            <span v-if="isDeleting" class="spinner-border spinner-border-sm me-2"></span>
                            {{ trans('inbox.delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
</style>
