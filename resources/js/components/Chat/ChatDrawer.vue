<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { echo } from '@laravel/echo-vue';
import axios from 'axios';
import { computed, nextTick, onUnmounted, ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import MessageItem from './MessageItem.vue';
import { usePresenceStore } from '@/stores/useUserStore';

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

const messagesContainer = ref<HTMLElement | null>(null);

const otherUser = computed(() => conversationData.value?.other_user);

const isOnline = computed(() => otherUser?.value?.id ? usePresenceStore().isUserOnline(otherUser.value.id) : false);

const lastSeenText = computed(() => {
    if (!otherUser.value?.last_seen_at) {
        return 'Offline';
    }

    const lastSeen = new Date(otherUser.value.last_seen_at);
    const now = new Date();
    const diffMinutes = Math.floor((now.getTime() - lastSeen.getTime()) / 60000);

    if (diffMinutes < 1) {
        return 'Just now';
    }
    if (diffMinutes < 60) {
        return `${diffMinutes}m ago`;
    }
    if (diffMinutes < 1440) {
        return `${Math.floor(diffMinutes / 60)}h ago`;
    }
    return `${Math.floor(diffMinutes / 1440)}d ago`;
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
            // Load existing conversation
            const response = await axios.get(`/conversations/${props.conversationId}`);
            conversationData.value = response.data.conversation;
            messages.value = response.data.messages;
        } else if (props.recipientId) {
            // Create or get conversation with recipient
            const createResponse = await axios.post('/conversations', {
                recipient_id: props.recipientId,
            });

            const conversationId = createResponse.data.conversation_id;

            // Load the conversation
            const response = await axios.get(`/conversations/${conversationId}`);
            conversationData.value = response.data.conversation;
            messages.value = response.data.messages;
        }

        scrollToBottom();
    } catch (error) {
        console.error('Failed to load conversation:', error);
    } finally {
        isLoading.value = false;
    }
};

const sendMessage = async () => {
    if (!newMessage.value.trim() || !conversationData.value || isSending.value) {
        return;
    }

    const messageText = newMessage.value.trim();
    newMessage.value = '';
    isSending.value = true;

    try {
        const response = await axios.post(`/conversations/${conversationData.value.id}/messages`, {
            message: messageText,
        });

        messages.value.push(response.data.message);
        scrollToBottom();
    } catch (error) {
        console.error('Failed to send message:', error);
        newMessage.value = messageText; // Restore message on error
    } finally {
        isSending.value = false;
    }
};

const handleClose = () => {
    emit('close');
    messages.value = [];
    conversationData.value = null;
    newMessage.value = '';
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

const handleNewMessage = (e: any) => {
    // Check if message already exists to avoid duplicates
    const messageExists = messages.value.some((msg) => msg.id === e.message.id);
    if (!messageExists) {
        messages.value.push(e.message);
        scrollToBottom();
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
                }
            });
        } else {
            unsubscribeFromChannel();
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
                                <img src="/assets/media/avatars/300-1.jpg" :alt="otherUser.username" class="rounded-circle" />
                            </div>
                            <div
                                :class="[
                                    'position-absolute rounded-circle border border-2 border-white',
                                    'h-10px w-10px',
                                    'translate-middle',
                                    'start-100 top-100',
                                    isOnline ? 'bg-success' : 'bg-secondary',
                                ]"
                            ></div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-0 text-gray-900">{{ otherUser.username }}</h5>
                            <Transition name="typing-status" mode="out-in">
                                <span v-if="otherUserTyping" class="text-primary fs-7 fw-semibold">
                                    Typing<span class="typing-dots"><span>.</span><span>.</span><span>.</span></span>
                                </span>
                                <span v-else class="text-muted fs-7">
                                    {{ isOnline ? 'Online' : lastSeenText }}
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

            <!-- Messages Container -->
            <div ref="messagesContainer" class="flex-grow-1 overflow-auto px-6 py-4" style="background-color: #f9f9f9">
                <div v-if="isLoading" class="d-flex justify-content-center align-items-center h-100">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div v-else-if="messages.length === 0" class="d-flex justify-content-center align-items-center h-100">
                    <div class="text-muted text-center">
                        <i class="ki-outline ki-message-text fs-3x mb-3"></i>
                        <p>No messages yet. Start the conversation!</p>
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
                <form @submit.prevent="sendMessage" class="d-flex gap-2">
                    <div class="flex-grow-1">
                        <textarea
                            v-model="newMessage"
                            @keydown.enter.exact.prevent="sendMessage"
                            placeholder="Type a message..."
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
                        title="Send message"
                    >
                        <i v-if="!isSending" class="ki-outline ki-send fs-2"></i>
                        <span v-else class="spinner-border spinner-border-sm" role="status"></span>
                    </button>
                </form>
            </div>
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

/* Typing bubble transition */
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
