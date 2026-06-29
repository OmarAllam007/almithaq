<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { echo } from '@laravel/echo-vue';
import axios from 'axios';
import { computed, nextTick, onUnmounted, ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import MessageItem from './MessageItem.vue';
import EmojiPicker from './EmojiPicker.vue';
import { usePresenceStore } from '@/stores/useUserStore';
import { useActiveChatStore } from '@/composables/useActiveChatStore';
import { useUnreadConversations } from '@/composables/useUnreadConversations';
import { useLang } from '@/composables/useLang';
import { route } from 'ziggy-js';
const { trans } = useLang();
const { activeConversationId } = useActiveChatStore();
const { markConversationRead } = useUnreadConversations();

interface Message {
    id: number;
    conversation_id: number;
    message: string;
    sender_id: number;
    created_at: string;
    is_read: boolean;
    sender?: {
        id: number;
        name: string;
        username: string;
    };
}

interface OtherUser {
    id: number;
    name: string;
    username: string;
    age?: number;
    nationality?: string | number;
    residence?: string | number;
    is_online: boolean;
    last_seen_at?: string;
    profile_image?: string | null;
    is_ignored?: boolean;
}

interface Props {
    isOpen: boolean;
    recipientId?: number;
    conversationId?: number;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
}>();

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

const messages = ref<Message[]>([]);
const newMessage = ref('');
const isLoading = ref(false);
const otherUserTyping = ref(false);
const isSending = ref(false);
const conversationData = ref<{
    id: number;
    other_user: OtherUser;
} | null>(null);
const pendingRecipientId = ref<number | null>(null);
const pendingOtherUser = ref<OtherUser | null>(null);

const messagesContainer = ref<HTMLElement | null>(null);
const messageInput = ref<HTMLTextAreaElement | null>(null);
const showEmojiPicker = ref(false);

const toggleEmojiPicker = () => {
    showEmojiPicker.value = !showEmojiPicker.value;
};

const insertEmoji = (emoji: string) => {
    const textarea = messageInput.value;
    if (!textarea) {
        newMessage.value += emoji;
        return;
    }
    const start = textarea.selectionStart ?? newMessage.value.length;
    const end = textarea.selectionEnd ?? newMessage.value.length;
    newMessage.value = newMessage.value.slice(0, start) + emoji + newMessage.value.slice(end);
    nextTick(() => {
        const pos = start + emoji.length;
        textarea.setSelectionRange(pos, pos);
        textarea.focus();
    });
};

const otherUser = computed(() => conversationData.value?.other_user ?? pendingOtherUser.value);

const presenceStore = usePresenceStore();
const isOnline = computed(() => {
    if (!otherUser.value) return false;
    return presenceStore.isUserOnline(otherUser.value.id) || (otherUser.value.is_online ?? false);
});

const lastSeenText = computed(() => {
    if (!otherUser.value?.last_seen_at) {
        return trans('chat.offline');
    }

    const lastSeen = new Date(otherUser.value.last_seen_at);
    const now = new Date();
    const diffMinutes = Math.floor((now.getTime() - lastSeen.getTime()) / 60000);

    if (diffMinutes < 1) {
        return trans('chat.just_now');
    }
    if (diffMinutes < 60) {
        return trans('chat.minutes_ago').replace(':count', String(diffMinutes));
    }
    if (diffMinutes < 1440) {
        return trans('chat.hours_ago').replace(':count', String(Math.floor(diffMinutes / 60)));
    }
    return trans('chat.days_ago').replace(':count', String(Math.floor(diffMinutes / 1440)));
});

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const loadConversation = async () => {
    if (!props.recipientId && !props.conversationId) {
        return;
    }

    isLoading.value = true;

    try {
        if (props.conversationId) {
            const response = await axios.get(`/conversations/${props.conversationId}`);
            conversationData.value = response.data.conversation;
            messages.value = response.data.messages;

            scrollToBottom();
            nextTick(() => messageInput.value?.focus());

            if (conversationData.value?.id) {
                const hasUnread = messages.value.some(
                    (msg) => !msg.is_read && msg.sender_id !== currentUser.value.id,
                );
                if (hasUnread) {
                    axios.post(`/conversations/${conversationData.value.id}/mark-as-read`).catch(() => {});
                    markConversationRead(conversationData.value.id);
                }
            }
        } else if (props.recipientId) {
            // Check if a conversation already exists before creating one
            const existingConvResponse = await axios.get('/conversations');
            const existing = existingConvResponse.data.find(
                (c: any) => c.other_user?.id === props.recipientId,
            );

            if (existing) {
                // Load the existing conversation
                const response = await axios.get(`/conversations/${existing.id}`);
                conversationData.value = response.data.conversation;
                messages.value = response.data.messages;

                scrollToBottom();
                nextTick(() => messageInput.value?.focus());

                if (conversationData.value?.id) {
                    const hasUnread = messages.value.some(
                        (msg) => !msg.is_read && msg.sender_id !== currentUser.value.id,
                    );
                    if (hasUnread) {
                        axios.post(`/conversations/${conversationData.value.id}/mark-as-read`).catch(() => {});
                        markConversationRead(conversationData.value.id);
                    }
                }
            } else {
                // No existing conversation — fetch user info and defer creation until first message
                const userResponse = await axios.get(`/users/${props.recipientId}/chat-info`);
                pendingOtherUser.value = userResponse.data;
                pendingRecipientId.value = props.recipientId;
                messages.value = [];
                nextTick(() => messageInput.value?.focus());
            }
        }
    } catch (error) {
        console.error('Failed to load conversation:', error);
    } finally {
        isLoading.value = false;
    }
};

const sendMessage = async () => {
    if (!newMessage.value.trim() || isSending.value) {
        return;
    }
    if (!conversationData.value && !pendingRecipientId.value) {
        return;
    }

    const messageText = newMessage.value.trim();
    newMessage.value = '';
    isSending.value = true;

    try {
        // Create conversation on first message if it doesn't exist yet
        if (!conversationData.value && pendingRecipientId.value) {
            const createResponse = await axios.post('/conversations', {
                recipient_id: pendingRecipientId.value,
            });
            const conversationId = createResponse.data.conversation_id;
            const convResponse = await axios.get(`/conversations/${conversationId}`);
            conversationData.value = convResponse.data.conversation;
            pendingRecipientId.value = null;
            pendingOtherUser.value = null;
            subscribeToChannel(conversationData.value.id);
            activeConversationId.value = conversationData.value.id;
        }

        const response = await axios.post(`/conversations/${conversationData.value!.id}/messages`, {
            message: messageText,
        });

        messages.value.push(response.data.message);
        scrollToBottom();
    } catch (error) {
        console.error('Failed to send message:', error);
        newMessage.value = messageText;
    } finally {
        isSending.value = false;
        nextTick(() => messageInput.value?.focus());
    }
};

const handleClose = () => {
    emit('close');
    messages.value = [];
    conversationData.value = null;
    pendingRecipientId.value = null;
    pendingOtherUser.value = null;
    newMessage.value = '';
    showEmojiPicker.value = false;
};

// Track current Echo channel for cleanup
let currentChannel: any = null;
let typingTimeout: ReturnType<typeof setTimeout> | null = null;

const subscribeToChannel = (conversationId: number) => {
    // Leave previous channel if exists
    if (currentChannel) {
        echo().leave(`conversation.${currentChannel}`);
        echo().leave(`conversation.${currentChannel}.typing`);
    }

    currentChannel = conversationId;
    console.log('Subscribing to channel:', `conversation.${conversationId}`);

    const channel = echo().private(`conversation.${conversationId}`);
    const typingChannel = echo().private(`conversation.${conversationId}.typing`);

    // Listen for custom broadcastAs name (with leading dot)
    channel.listen('.MessageSent', (e: any) => {
        console.log('New message received via Echo (.MessageSent):', e);
        handleNewMessage(e);
    });
    // Also listen for fully qualified class name (fallback for debugging)
    channel.listen('MessageSent', (e: any) => {
        console.log('New message received via Echo (MessageSent without dot):', e);
        handleNewMessage(e);
    });

    channel.listen('.MessagesRead', handleMessagesRead);
    channel.listen('MessagesRead', handleMessagesRead);

    const handleTypingEvent = (e: any) => {
        if (e.typerId === currentUser.value.id) {
            return;
        }
        // Clear existing timeout to reset the timer
        if (typingTimeout) {
            clearTimeout(typingTimeout);
        }
        otherUserTyping.value = true;
        typingTimeout = setTimeout(() => {
            otherUserTyping.value = false;
        }, 3000);
    };

    typingChannel.listen('.UserTyping', handleTypingEvent);
    typingChannel.listen('UserTyping', handleTypingEvent);
};

const handleMessagesRead = (e: any) => {
    if (e.readerId === currentUser.value.id) {
        return;
    }
    messages.value.forEach((msg) => {
        if (msg.sender_id === currentUser.value.id && !msg.is_read) {
            msg.is_read = true;
        }
    });
};

const handleNewMessage = (e: any) => {
    const messageExists = messages.value.some((msg) => msg.id === e.message.id);
    if (!messageExists) {
        messages.value.push(e.message);
        scrollToBottom();
        // Auto-mark as read since the drawer is open
        if (conversationData.value?.id && e.message.sender_id !== currentUser.value.id) {
            axios.post(`/conversations/${conversationData.value.id}/mark-as-read`).catch(() => {});
        }
    }
};

const unsubscribeFromChannel = () => {
    if (currentChannel) {
        echo().leave(`conversation.${currentChannel}`);
        echo().leave(`conversation.${currentChannel}.typing`);
        currentChannel = null;
    }
    if (typingTimeout) {
        clearTimeout(typingTimeout);
        typingTimeout = null;
    }
    otherUserTyping.value = false;
};

const broadcastTyping = async () => {
    if (newMessage.value && conversationData.value?.id) {
        await axios.post(`/conversations/${conversationData.value.id}/typing`, {
            conversationId: conversationData.value.id,
            senderId: currentUser.value.id,
        });
    }
};

// Debounce typing broadcasts to avoid flooding the server
const debouncedBroadcastTyping = useDebounceFn(broadcastTyping, 500);

// Clean up on component unmount
onUnmounted(() => {
    unsubscribeFromChannel();
});

watch(
    () => props.isOpen,
    (isOpen) => {
        if (isOpen) {
            loadConversation().then(() => {
                if (conversationData.value?.id) {
                    subscribeToChannel(conversationData.value.id);
                    activeConversationId.value = conversationData.value.id;
                }
            });
        } else {
            unsubscribeFromChannel();
            activeConversationId.value = null;
        }
    },
);

watch(
    () => [props.recipientId, props.conversationId],
    () => {
        if (props.isOpen) {
            loadConversation();
        }
    },
);

watch(
    () => newMessage.value,
    () => {
        debouncedBroadcastTyping();
    },
);
</script>

<template>
    <!-- Drawer Overlay -->
    <Transition name="fade">
        <div v-if="isOpen" @click="handleClose" class="position-fixed bg-dark bg-opacity-50 start-0 top-0 h-100 w-100" style="z-index: 1050"></div>
    </Transition>

    <!-- Drawer -->
    <Transition name="slide">
        <div v-if="isOpen" class="position-fixed d-flex flex-column end-0 top-0 h-100 bg-white shadow-lg" style="z-index: 1051; width: 450px">
            <!-- Header -->
            <div class="border-bottom px-6 py-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div v-if="otherUser" class="d-flex align-items-center flex-grow-1 gap-3">
                        <div class="position-relative">
                            <div class="symbol symbol-45px">
                                <img
                                    :src="otherUser.profile_image || '/assets/media/auth/no-image-for-user.png'"
                                    :alt="otherUser.username"
                                    class="rounded-circle"
                                    @error="($event.target as HTMLImageElement).src = '/assets/media/auth/no-image-for-user.png'"
                                />
                            </div>
                            <div
                                :class="['position-absolute rounded-circle border border-2 border-white h-10px w-10px', isOnline ? 'bg-success' : 'bg-secondary']"
                                style="bottom: 0; right: 0"
                            ></div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-0 text-gray-900">{{ otherUser.name }}</h5>
                            <Transition name="typing-status" mode="out-in">
                                <span v-if="otherUserTyping" class="text-primary fs-7 fw-semibold">
                                    {{ trans('chat.typing') }}<span class="typing-dots"><span>.</span><span>.</span><span>.</span></span>
                                </span>
                                <span v-else class="text-muted fs-7">
                                    {{ isOnline ? trans('chat.online') : lastSeenText }}
                                </span>
                            </Transition>
                        </div>
                    </div>
                    <div v-else class="flex-grow-1">
                        <div class="placeholder-glow">
                            <span class="placeholder col-6"></span>
                        </div>
                    </div>
                    <button @click="handleClose" class="btn btn-sm btn-icon btn-light btn-active-light-primary">
                        <i class="ki-outline ki-cross fs-2"></i>
                    </button>
                </div>
            </div>

            <!-- Blocked state when other user is ignored -->
            <template v-if="otherUser?.is_ignored">
                <div class="flex-grow-1 d-flex flex-column align-items-center justify-content-center px-6 py-10 text-center" style="background-color: #f9f9f9; gap: 16px;">
                    <div class="cd-blocked-icon">
                        <i class="ki-outline ki-eye-slash"></i>
                    </div>
                    <div>
                        <p class="fw-bold text-gray-800 fs-5 mb-1">{{ trans('chat.you_blocked_this_user') }}</p>
                        <p class="text-muted fs-7 mb-0">{{ trans('chat.unblock_to_message') }}</p>
                    </div>
                    <a
                        :href="route('my-interactions') + '?tab=ignored'"
                        class="cd-unblock-btn"
                    >
                        <i class="ki-outline ki-eye fs-6"></i>
                        {{ trans('chat.go_to_ignored_list') }}
                    </a>
                </div>
            </template>

            <!-- Normal chat when not ignored -->
            <template v-else>
            <!-- Pinned safety notice -->
            <div class="cd-safety-notice d-flex align-items-start gap-2 px-4 py-3 border-bottom">
                <i class="ki-outline ki-shield-tick fs-5 text-warning mt-1 flex-shrink-0"></i>
                <p class="mb-0 fs-7 text-gray-600 lh-sm font-bold">
                    {{ trans('chat.safety_notice') }}
                </p>
            </div>

            <!-- Messages Container -->
            <div ref="messagesContainer" class="flex-grow-1 overflow-auto px-6 py-4" style="background-color: #f9f9f9">
                <div v-if="isLoading" class="d-flex justify-content-center align-items-center h-100">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">{{ trans('chat.loading') }}</span>
                    </div>
                </div>

                <div v-else-if="messages.length === 0" class="d-flex justify-content-center align-items-center h-100">
                    <div class="text-muted text-center">
                        <i class="ki-outline ki-message-text fs-3x mb-3"></i>
                        <p>{{ trans('chat.no_messages_yet') }}</p>
                    </div>
                </div>

                <div v-else>
                    <MessageItem v-for="message in messages" :key="message.id" :message="message" :current-user-id="currentUser.id" />

                    <!-- Typing Indicator Bubble -->
                    <Transition name="typing-bubble">
                        <div v-if="otherUserTyping" class="d-flex align-items-start gap-2 mt-3">
                            <div class="typing-indicator">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- Message Input -->
            <div class="border-top bg-white px-6 py-4">
                <form @submit.prevent="sendMessage" class="d-flex gap-2 align-items-end">
                    <div class="cd-emoji-wrap">
                        <button
                            type="button"
                            class="cd-emoji-btn"
                            :class="{ 'cd-emoji-btn--active': showEmojiPicker }"
                            @click="toggleEmojiPicker"
                            :title="'Emoji'"
                        >😊</button>
                        <Transition name="ep-pop">
                            <EmojiPicker
                                v-if="showEmojiPicker"
                                @select="(e) => { insertEmoji(e); showEmojiPicker = false; }"
                                @close="showEmojiPicker = false"
                            />
                        </Transition>
                    </div>
                    <div class="flex-grow-1">
                        <textarea
                            ref="messageInput"
                            v-model="newMessage"
                            @keydown.enter.exact.prevent="sendMessage"
                            :placeholder="trans('chat.type_a_message')"
                            rows="1"
                            class="form-control form-control-flush resize-none"
                            :disabled="isSending || isLoading"
                            style="min-height: 40px; max-height: 120px"
                        ></textarea>
                    </div>
                    <button
                        type="submit"
                        :disabled="!newMessage.trim() || isSending || isLoading"
                        class="btn btn-primary btn-icon"
                        :title="trans('chat.send_message')"
                    >
                        <i v-if="!isSending" class="ki-outline ki-send fs-2"></i>
                        <span v-else class="spinner-border spinner-border-sm" role="status"></span>
                    </button>
                </form>
            </div>
            </template>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
    transform: translateX(100%);
}

.resize-none {
    resize: none;
}

.form-control-flush {
    border: 1px solid #e4e6ef;
    border-radius: 0.475rem;
}

.form-control-flush:focus {
    border-color: #009ef7;
    box-shadow: 0 0 0 0.25rem rgba(0, 158, 247, 0.1);
}

/* Typing indicator bubble */
.typing-indicator {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 12px 16px;
    background-color: #e9ecef;
    border-radius: 18px;
    border-bottom-left-radius: 4px;
}

.typing-indicator span {
    width: 8px;
    height: 8px;
    background-color: #6c757d;
    border-radius: 50%;
    animation: typing-bounce 1.4s ease-in-out infinite;
}

.typing-indicator span:nth-child(1) {
    animation-delay: 0s;
}

.typing-indicator span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing-bounce {
    0%,
    60%,
    100% {
        transform: translateY(0);
        opacity: 0.6;
    }
    30% {
        transform: translateY(-6px);
        opacity: 1;
    }
}

/* Typing dots in header */
.typing-dots span {
    animation: typing-dot 1.4s ease-in-out infinite;
}

.typing-dots span:nth-child(1) {
    animation-delay: 0s;
}

.typing-dots span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-dots span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing-dot {
    0%,
    60%,
    100% {
        opacity: 0.3;
    }
    30% {
        opacity: 1;
    }
}

/* Typing status transition */
.typing-status-enter-active,
.typing-status-leave-active {
    transition: opacity 0.2s ease;
}

.typing-status-enter-from,
.typing-status-leave-to {
    opacity: 0;
}

/* ─── Safety notice ─────────────────────────────────────── */
.cd-safety-notice {
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    font-size: 0.76rem;
}

/* ─── Blocked state ──────────────────────────────────────── */
.cd-blocked-icon {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: rgba(255, 64, 64, 0.09);
    border: 2px solid rgba(255, 64, 64, 0.22);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    color: #e03333;
    box-shadow: 0 0 0 8px rgba(255, 64, 64, 0.05);
    animation: cd-pulse-ring 2.4s ease-in-out infinite;
}

@keyframes cd-pulse-ring {
    0%, 100% { box-shadow: 0 0 0 8px rgba(255, 64, 64, 0.05); }
    50%       { box-shadow: 0 0 0 14px rgba(255, 64, 64, 0.0); }
}

.cd-unblock-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 10px 22px;
    border-radius: 12px;
    background: rgba(255, 64, 64, 0.08);
    border: 1px solid rgba(255, 64, 64, 0.24);
    color: #cc2020;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}

.cd-unblock-btn:hover {
    background: rgba(255, 64, 64, 0.15);
    border-color: rgba(255, 64, 64, 0.45);
    box-shadow: 0 4px 14px rgba(200, 30, 30, 0.18);
    color: #aa1010;
}

/* ─── Emoji button & wrapper ─────────────────────────────── */
.cd-emoji-wrap {
    position: relative;
    flex-shrink: 0;
}

.cd-emoji-btn {
    width: 38px;
    height: 38px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: #f9fafb;
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.18s, border-color 0.18s, transform 0.18s;
    padding: 0;
}

.cd-emoji-btn:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
    transform: scale(1.1);
}

.cd-emoji-btn--active {
    background: #ede9fe;
    border-color: #a78bfa;
}

/* Picker pop-in transition */
.ep-pop-enter-active {
    transition: opacity 0.18s ease, transform 0.18s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.ep-pop-leave-active {
    transition: opacity 0.14s ease, transform 0.14s ease;
}

.ep-pop-enter-from,
.ep-pop-leave-to {
    opacity: 0;
    transform: scale(0.92) translateY(6px);
}

/* ─── Typing bubble transition ───────────────────────────── */
.typing-bubble-enter-active {
    transition: all 0.3s ease-out;
}

.typing-bubble-leave-active {
    transition: all 0.2s ease-in;
}

.typing-bubble-enter-from {
    opacity: 0;
    transform: translateY(10px) scale(0.9);
}

.typing-bubble-leave-to {
    opacity: 0;
    transform: scale(0.9);
}
</style>
