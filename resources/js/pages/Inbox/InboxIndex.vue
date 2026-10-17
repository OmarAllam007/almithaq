<script setup lang="ts">
import { bulkDestroy, destroy } from '@/actions/App/Http/Controllers/ConversationController';
import { markAllAsRead } from '@/actions/App/Http/Controllers/MessageController';
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';
import Pagination from '@/components/Pagination.vue';
import UserProfileModal from '@/components/UserProfileModal.vue';
import { useLang } from '@/composables/useLang';
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const { trans } = useLang();

interface Message {
    id: number;
    message: string;
    created_at: string;
    isRead: boolean;
}

interface User {
    id: number;
    name: string;
    username: string;
    age: number;
    marriage_status: string;
    mainProfileImage?: string;
    marriage_status_label?: string;
    is_favorited?: boolean;
    is_ignored?: boolean;
    is_online?: boolean;
    registration_type?: number;
}

interface Conversation {
    id: number;
    lastMessage: Message | null;
    otherUser: User;
}

interface Props {
    conversations: {
        data: Conversation[];
        links: any[];
        total: number;
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        first_page_url: string;
        last_page_url: string;
        next_page_url: string | null;
        prev_page_url: string | null;
        path: string;
        per_page: number;
    };
}

const props = defineProps<Props>();

const isChatDrawerOpen = ref(false);
const isProfileModalOpen = ref(false);
const selectedConversationId = ref<number | undefined>(undefined);
const selectedUserId = ref<number | undefined>(undefined);
const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');
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
watch(
    () => props.conversations.current_page,
    () => {
        selectedConversations.value = [];
        selectAll.value = false;
    },
);

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
    router.post(
        markAllAsRead().url,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isMarkingAllAsRead.value = false;
            },
        },
    );
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
    <div id="kt_app_content" class="app-content flex-column-fluid member-page inbox-page">
        <div id="kt_app_content_container" class="app-container container-xxl member-container">
            <section class="member-hero member-reveal">
                <div class="member-hero-art"></div>
                <div class="member-hero-copy">
                    <div class="member-eyebrow">{{ isRtl ? 'ابقَ على تواصل' : 'STAY CONNECTED' }}</div>
                    <h1>{{ trans('inbox.inbox') }}<span>.</span></h1>
                    <p>
                        {{
                            isRtl
                                ? 'كل محادثة فرصة للتعرّف أكثر. تابع رسائلك وابدأ من حيث توقفت.'
                                : 'Every conversation is a chance to connect. Pick up where you left off.'
                        }}
                    </p>
                </div>
                <div class="member-hero-stat">
                    <strong>{{ conversations.total }}</strong
                    ><span>{{ trans('inbox.conversations') }}</span>
                </div>
            </section>
            <div class="member-section-head member-reveal">
                <div>
                    <small>{{ isRtl ? 'رسائلك' : 'YOUR MESSAGES' }}</small>
                    <h2>{{ trans('inbox.conversations') }}</h2>
                </div>
                <span class="member-count">{{ conversations.total }} {{ trans('inbox.conversations') }}</span>
            </div>
            <section class="inbox-panel member-reveal" :aria-label="trans('inbox.conversations')">
                <div class="inbox-toolbar">
                    <div class="inbox-toolbar-left">
                        <label class="inbox-select-all"
                            ><input
                                v-model="selectAll"
                                type="checkbox"
                                @change="toggleSelectAll"
                                :aria-label="isRtl ? 'تحديد الكل' : 'Select all conversations'"
                            /><span>{{
                                hasSelectedConversations
                                    ? `${selectedConversations.length} ${trans('inbox.selected')}`
                                    : isRtl
                                      ? 'تحديد الكل'
                                      : 'Select all'
                            }}</span></label
                        ><span class="toolbar-divider"></span
                        ><button type="button" class="inbox-tool-button" :disabled="!hasSelectedConversations" @click="openBulkDeleteModal">
                            <i class="ki-outline ki-trash"></i>{{ trans('inbox.delete_selected') }}
                        </button>
                    </div>
                    <button type="button" class="inbox-tool-button mark-read" :disabled="isMarkingAllAsRead" @click="handleMarkAllAsRead">
                        <span v-if="isMarkingAllAsRead" class="spinner-border spinner-border-sm"></span
                        ><i v-else class="ki-outline ki-double-check"></i>{{ trans('inbox.mark_all_read') }}
                    </button>
                </div>
                <div v-if="conversations.data.length" class="conversation-list">
                    <article
                        v-for="conversation in conversations.data"
                        :key="conversation.id"
                        class="conversation-row"
                        :class="{
                            unread: conversation.lastMessage && !conversation.lastMessage.isRead,
                            selected: selectedConversations.includes(conversation.id),
                        }"
                        tabindex="0"
                        @click="openChatDrawer(conversation.id)"
                        @keydown.enter.self="openChatDrawer(conversation.id)"
                    >
                        <div class="conversation-select" @click.stop>
                            <input
                                type="checkbox"
                                :checked="selectedConversations.includes(conversation.id)"
                                :aria-label="isRtl ? 'تحديد المحادثة' : 'Select conversation'"
                                @change="toggleConversationSelection(conversation.id)"
                            />
                        </div>
                        <button
                            type="button"
                            class="conversation-avatar"
                            :aria-label="conversation.otherUser.username"
                            @click.stop="openProfileModal(conversation.otherUser.id)"
                        >
                            <img
                                :src="
                                    conversation.otherUser.mainProfileImage ||
                                    (conversation.otherUser.registration_type === 2
                                        ? '/assets/media/auth/female_bgd.png'
                                        : '/assets/media/auth/male_bgd.png')
                                "
                                :alt="conversation.otherUser.username"
                            /><span v-if="conversation.otherUser.is_online" class="avatar-online"></span>
                        </button>
                        <div class="conversation-main">
                            <div class="conversation-name-line">
                                <strong>{{ conversation.otherUser.name || conversation.otherUser.username }}</strong
                                ><span v-if="conversation.otherUser.is_favorited" class="conversation-mini-icon" :title="trans('inbox.favorited')"
                                    ><i class="ki-solid ki-heart"></i></span
                                ><span v-if="conversation.otherUser.is_ignored" class="conversation-mini-icon" :title="trans('inbox.ignored')"
                                    ><i class="ki-outline ki-eye-slash"></i
                                ></span>
                            </div>
                            <div class="conversation-subline">
                                <span>@{{ conversation.otherUser.username }}</span
                                ><span v-if="conversation.otherUser.age">· {{ conversation.otherUser.age }} {{ trans('inbox.yrs') }}</span
                                ><span v-if="conversation.otherUser.marriage_status_label">· {{ conversation.otherUser.marriage_status_label }}</span>
                            </div>
                            <p class="conversation-preview">
                                {{
                                    conversation.lastMessage ? truncateMessage(conversation.lastMessage.message, 100) : trans('inbox.no_messages_yet')
                                }}
                            </p>
                        </div>
                        <div class="conversation-end">
                            <time v-if="conversation.lastMessage" :datetime="conversation.lastMessage.created_at">{{
                                formatMessageDate(conversation.lastMessage.created_at)
                            }}</time
                            ><span v-if="conversation.lastMessage && !conversation.lastMessage.isRead" class="message-unread-dot"></span
                            ><button
                                type="button"
                                class="conversation-delete"
                                :title="trans('inbox.delete_conversation')"
                                :aria-label="trans('inbox.delete_conversation')"
                                @click.stop="openDeleteModal(conversation.id)"
                            >
                                <i class="ki-outline ki-trash"></i>
                            </button>
                        </div>
                    </article>
                </div>
                <div v-else class="member-empty inbox-empty">
                    <div class="member-empty-icon"><i class="ki-outline ki-messages"></i></div>
                    <h3>{{ trans('inbox.no_messages_yet') }}</h3>
                    <p>{{ isRtl ? 'تصفح الأعضاء وابدأ محادثة جديدة.' : 'Explore members and start a new conversation.' }}</p>
                </div>
            </section>
            <Pagination :data="conversations" />
        </div>
        <ChatDrawer :is-open="isChatDrawerOpen" :conversation-id="selectedConversationId" @close="closeChatDrawer" />
        <UserProfileModal :is-open="isProfileModalOpen" :user-id="selectedUserId" @close="closeProfileModal" @message="handleMessageFromModal" />
        <div
            v-if="showDeleteModal"
            class="modal fade show d-block inbox-modal"
            tabindex="-1"
            role="dialog"
            aria-modal="true"
            :aria-label="deleteModalTitle"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ki-outline ki-trash"></i>{{ deleteModalTitle }}</h5>
                        <button type="button" class="btn-close" :disabled="isDeleting" @click="closeDeleteModal"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ deleteModalMessage }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="inbox-modal-cancel" :disabled="isDeleting" @click="closeDeleteModal">
                            {{ trans('inbox.cancel') }}</button
                        ><button type="button" class="inbox-modal-delete" :disabled="isDeleting" @click="confirmDelete">
                            <span v-if="isDeleting" class="spinner-border spinner-border-sm"></span>{{ trans('inbox.delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
