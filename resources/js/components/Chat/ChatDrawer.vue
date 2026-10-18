<script setup lang="ts">
import { useActiveChatStore } from '@/composables/useActiveChatStore';
import { useLang } from '@/composables/useLang';
import { useUnreadConversations } from '@/composables/useUnreadConversations';
import { usePresenceStore } from '@/stores/useUserStore';
import { usePage } from '@inertiajs/vue3';
import { echo } from '@laravel/echo-vue';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import { computed, nextTick, onUnmounted, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import EmojiPicker from './EmojiPicker.vue';
import MessageItem from './MessageItem.vue';
const { trans } = useLang();
const { activeConversationId } = useActiveChatStore();
const { markConversationRead } = useUnreadConversations();
const needsEmailVerification = ref(false);

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
    registration_type?: number;
    age?: number;
    nationality?: string | number;
    residence?: string | number;
    is_online: boolean;
    last_seen_at?: string;
    profile_image?: string | null;
    can_view_images?: boolean;
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
const isFemale = computed(() => otherUser.value?.registration_type === 2);
const defaultAvatar = computed(() => (isFemale.value ? '/assets/media/auth/female_bgd.png' : '/assets/media/auth/male_bgd.png'));

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
                const hasUnread = messages.value.some((msg) => !msg.is_read && msg.sender_id !== currentUser.value.id);
                if (hasUnread) {
                    axios.post(`/conversations/${conversationData.value.id}/mark-as-read`).catch(() => {});
                    markConversationRead(conversationData.value.id);
                }
            }
        } else if (props.recipientId) {
            // Check if a conversation already exists before creating one
            const existingConvResponse = await axios.get('/conversations');
            const existing = existingConvResponse.data.find((c: any) => c.other_user?.id === props.recipientId);

            if (existing) {
                // Load the existing conversation
                const response = await axios.get(`/conversations/${existing.id}`);
                conversationData.value = response.data.conversation;
                messages.value = response.data.messages;

                scrollToBottom();
                nextTick(() => messageInput.value?.focus());

                if (conversationData.value?.id) {
                    const hasUnread = messages.value.some((msg) => !msg.is_read && msg.sender_id !== currentUser.value.id);
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
        if (axios.isAxiosError(error) && error.response?.data?.reason === 'email_unverified') {
            needsEmailVerification.value = true;
        } else {
            console.error('Failed to send message:', error);
        }
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
    <Transition name="fade">
        <div v-if="isOpen" class="chat-scrim" @click="handleClose"></div>
    </Transition>
    <Transition name="slide">
        <section v-if="isOpen" class="chat-drawer" role="dialog" aria-modal="true" :aria-label="trans('chat.send_message')">
            <header class="chat-header">
                <div class="chat-header-top">
                    <span class="chat-kicker"><span class="chat-kicker-dot"></span>{{ trans('chat.send_message') }}</span>
                    <button type="button" class="chat-close" :aria-label="trans('profile.cancel')" @click="handleClose">
                        <i class="ki-outline ki-cross"></i>
                    </button>
                </div>
                <div v-if="otherUser" class="chat-person">
                    <div class="chat-avatar">
                        <img
                            :src="otherUser.profile_image || defaultAvatar"
                            :alt="otherUser.name"
                            :class="{ 'cd-avatar--blurred': otherUser.can_view_images === false && !!otherUser.profile_image }"
                            @error="($event.target as HTMLImageElement).src = defaultAvatar"
                        />
                        <span class="chat-presence" :class="{ online: isOnline }"></span>
                    </div>
                    <div class="chat-person-copy">
                        <h2>{{ otherUser.name }}</h2>
                        <p>@{{ otherUser.username }}</p>
                        <Transition name="typing-status" mode="out-in">
                            <span v-if="otherUserTyping" class="chat-status typing"
                                >{{ trans('chat.typing') }}<span class="typing-dots"><span>.</span><span>.</span><span>.</span></span></span
                            >
                            <span v-else class="chat-status"
                                ><span class="chat-status-dot" :class="{ online: isOnline }"></span
                                >{{ isOnline ? trans('chat.online') : lastSeenText }}</span
                            >
                        </Transition>
                    </div>
                </div>
                <div v-else class="chat-person chat-person-placeholder">
                    <div class="chat-avatar"></div>
                    <div class="chat-person-copy"><span></span><span></span></div>
                </div>
            </header>

            <div v-if="otherUser?.is_ignored" class="chat-blocked">
                <div class="chat-empty-mark"><i class="ki-outline ki-eye-slash"></i></div>
                <h3>{{ trans('chat.you_blocked_this_user') }}</h3>
                <p>{{ trans('chat.unblock_to_message') }}</p>
                <a :href="route('my-interactions') + '?tab=ignored'" class="chat-blocked-link"
                    ><i class="ki-outline ki-eye"></i>{{ trans('chat.go_to_ignored_list') }}</a
                >
            </div>
            <template v-else>
                <div class="chat-safety">
                    <i class="ki-outline ki-shield-tick"></i>
                    <p>{{ trans('chat.safety_notice') }}</p>
                </div>
                <div ref="messagesContainer" class="chat-messages">
                    <div v-if="isLoading" class="chat-empty">
                        <span class="spinner-border" role="status"></span><span class="visually-hidden">{{ trans('chat.loading') }}</span>
                    </div>
                    <div v-else-if="messages.length === 0" class="chat-empty">
                        <div class="chat-empty-mark"><i class="ki-outline ki-message-text"></i></div>
                        <h3>{{ trans('chat.no_messages_yet') }}</h3>
                        <p>{{ trans('chat.type_a_message') }}</p>
                    </div>
                    <div v-else class="chat-thread">
                        <MessageItem v-for="message in messages" :key="message.id" :message="message" :current-user-id="currentUser.id" />
                        <Transition name="typing-bubble"
                            ><div v-if="otherUserTyping" class="typing-indicator"><span></span><span></span><span></span></div
                        ></Transition>
                    </div>
                </div>
                <div v-if="needsEmailVerification" class="chat-verify-notice">
                    <i class="ki-outline ki-sms"></i>
                    <span>{{ trans('email_verification.verify_to_message') }}</span>
                    <a :href="route('verification.notice')">{{ trans('email_verification.verify_cta') }}</a>
                </div>
                <div class="chat-composer">
                    <form @submit.prevent="sendMessage" class="chat-compose-form">
                        <div class="cd-emoji-wrap">
                            <button
                                type="button"
                                class="cd-emoji-btn"
                                :class="{ 'cd-emoji-btn--active': showEmojiPicker }"
                                :aria-label="'Emoji'"
                                @click="toggleEmojiPicker"
                            >
                                😊</button
                            ><Transition name="ep-pop"
                                ><EmojiPicker
                                    v-if="showEmojiPicker"
                                    @select="
                                        (e) => {
                                            insertEmoji(e);
                                            showEmojiPicker = false;
                                        }
                                    "
                                    @close="showEmojiPicker = false"
                            /></Transition>
                        </div>
                        <textarea
                            ref="messageInput"
                            v-model="newMessage"
                            @keydown.enter.exact.prevent="sendMessage"
                            :placeholder="trans('chat.type_a_message')"
                            rows="1"
                            :disabled="isSending || isLoading"
                            class="chat-input"
                        ></textarea>
                        <button
                            type="submit"
                            class="chat-send"
                            :disabled="!newMessage.trim() || isSending || isLoading"
                            :title="trans('chat.send_message')"
                        >
                            <i v-if="!isSending" class="ki-outline ki-send"></i
                            ><span v-else class="spinner-border spinner-border-sm" role="status"></span>
                        </button>
                    </form>
                </div>
            </template>
        </section>
    </Transition>
</template>
<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.26s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
.slide-enter-active,
.slide-leave-active {
    transition: transform 0.36s cubic-bezier(0.22, 1, 0.36, 1);
}
.slide-enter-from,
.slide-leave-to {
    transform: translateX(105%);
}
.slide-enter-from:dir(rtl),
.slide-leave-to:dir(rtl) {
    transform: translateX(-105%);
}
.chat-scrim {
    position: fixed;
    inset: 0;
    z-index: 1050;
    background: rgba(33, 18, 28, 0.48);
    backdrop-filter: blur(4px);
}
.chat-drawer {
    position: fixed;
    inset-block: 0;
    inset-inline-end: 0;
    z-index: 1051;
    display: flex;
    flex-direction: column;
    width: min(100vw, 500px);
    background: #fcf8f6;
    box-shadow: 0 0 70px #2f182c33;
    overflow: hidden;
    color: #4b3040;
}
.chat-header {
    position: relative;
    flex: none;
    padding: 24px 28px 25px;
    background: linear-gradient(116deg, #482138, #823b5b);
    color: #fff;
    overflow: hidden;
}
.chat-header::before {
    content: '';
    position: absolute;
    width: 210px;
    height: 210px;
    border: 1px solid #ffffff25;
    border-radius: 50%;
    inset-inline-end: -75px;
    top: -115px;
    pointer-events: none;
}
.chat-header::after {
    content: '';
    position: absolute;
    width: 150px;
    height: 150px;
    border: 1px solid #ffffff1a;
    border-radius: 50%;
    inset-inline-end: -32px;
    top: -85px;
    pointer-events: none;
}
.chat-header-top,
.chat-person {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
}
.chat-header-top {
    justify-content: space-between;
    margin-bottom: 24px;
}
.chat-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.04em;
    color: #f4d5de;
}
.chat-kicker-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #efad9e;
    box-shadow: 0 0 0 5px #efad9e30;
}
.chat-close {
    width: 34px;
    height: 34px;
    border: 1px solid #ffffff40;
    border-radius: 10px;
    background: #ffffff15;
    color: #fff;
    font-size: 17px;
    display: grid;
    place-items: center;
    transition: 0.2s;
}
.chat-close:hover {
    background: #ffffff30;
    transform: rotate(90deg);
}
.chat-person {
    gap: 15px;
    min-height: 76px;
}
.chat-avatar {
    position: relative;
    flex: none;
    width: 72px;
    height: 72px;
    padding: 3px;
    border: 1px solid #ffffff70;
    border-radius: 19px;
    background: #ffffff20;
}
.chat-avatar img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 15px;
}
.chat-presence {
    position: absolute;
    bottom: -3px;
    inset-inline-end: -3px;
    width: 15px;
    height: 15px;
    border: 3px solid #743551;
    border-radius: 50%;
    background: #b8a4b0;
}
.chat-presence.online,
.chat-status-dot.online {
    background: #7ed3a6;
}
.chat-person-copy {
    min-width: 0;
}
.chat-person h2 {
    margin: 0;
    color: #fff;
    font-size: 22px;
    line-height: 1.35;
    font-weight: 900;
}
.chat-person p {
    margin: 1px 0 7px;
    color: #eed0db;
    font-size: 11px;
}
.chat-status {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #ead2db;
    font-size: 11px;
    font-weight: 700;
}
.chat-status.typing {
    color: #ffd6bc;
}
.chat-status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #bea8b2;
}
.chat-person-placeholder .chat-avatar,
.chat-person-placeholder span {
    background: #ffffff30;
    animation: chat-shimmer 1.4s ease-in-out infinite;
}
.chat-person-placeholder span {
    display: block;
    width: 130px;
    height: 12px;
    margin: 8px 0;
    border-radius: 5px;
}
.chat-person-placeholder span + span {
    width: 78px;
}
.chat-safety {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    flex: none;
    padding: 13px 26px;
    background: #fff6e9;
    border-bottom: 1px solid #f2e3d6;
    color: #865b4d;
}
.chat-safety i {
    font-size: 18px;
    color: #bb8053;
}
.chat-safety p {
    margin: 0;
    font-size: 11px;
    line-height: 1.65;
    font-weight: 700;
}
.chat-messages {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding: 25px 25px 18px;
    background: radial-gradient(circle at 10% 15%, #f7eef0 0, transparent 35%), #fcf8f6;
    scrollbar-color: #e5cbd5 transparent;
    scrollbar-width: thin;
}
.chat-thread {
    max-width: 100%;
}
.chat-empty,
.chat-blocked {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex: 1;
    text-align: center;
    padding: 36px 26px;
    color: #8b7380;
}
.chat-empty {
    min-height: 100%;
}
.chat-empty-mark {
    display: grid;
    place-items: center;
    width: 76px;
    height: 76px;
    margin-bottom: 18px;
    border-radius: 22px;
    background: #f4e5e9;
    color: #a85072;
    font-size: 31px;
    box-shadow: 0 12px 30px #6b30421a;
}
.chat-empty h3,
.chat-blocked h3 {
    margin: 0;
    color: #4f3542;
    font-size: 16px;
    font-weight: 900;
}
.chat-empty p,
.chat-blocked p {
    margin: 7px 0 0;
    font-size: 12px;
    line-height: 1.7;
}
.chat-blocked {
    background: #fcf8f6;
}
.chat-blocked-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 21px;
    padding: 11px 16px;
    border-radius: 9px;
    background: #8c3e60;
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    text-decoration: none;
}
.chat-blocked-link:hover {
    color: #fff;
    background: #71324e;
}
.chat-verify-notice {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    padding: 10px 14px;
    background: #fef0f7;
    border-top: 1px solid #f9a8d4;
    color: #9d174d;
    font-size: 13px;
}
.chat-verify-notice a {
    color: #d02e79;
    font-weight: 700;
    text-decoration: underline;
}
.chat-composer {
    flex: none;
    padding: 15px 21px 20px;
    border-top: 1px solid #ebdfe2;
    background: #fff;
}
.chat-compose-form {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    padding: 7px;
    border: 1px solid #eadbe0;
    border-radius: 14px;
    background: #fdfaf9;
    box-shadow: 0 4px 16px #6336490a;
}
.chat-compose-form:focus-within {
    border-color: #bd829a;
    box-shadow: 0 0 0 3px #a850721c;
}
.cd-emoji-wrap {
    position: relative;
    flex: none;
}
.cd-emoji-btn {
    width: 37px;
    height: 37px;
    border: 0;
    border-radius: 9px;
    background: transparent;
    font-size: 19px;
}
.cd-emoji-btn:hover,
.cd-emoji-btn--active {
    background: #f5e8ec;
}
.chat-input {
    flex: 1;
    min-width: 0;
    min-height: 37px;
    max-height: 120px;
    padding: 8px 3px;
    resize: none;
    border: 0;
    outline: 0;
    background: transparent;
    color: #4b3040;
    font: inherit;
    font-size: 12px;
    line-height: 1.5;
}
.chat-input::placeholder {
    color: #b09da6;
}
.chat-send {
    flex: none;
    display: grid;
    place-items: center;
    width: 38px;
    height: 38px;
    border: 0;
    border-radius: 9px;
    background: #a24469;
    color: white;
    font-size: 18px;
    transition: 0.2s;
}
.chat-send:hover:not(:disabled) {
    background: #843250;
    transform: translateY(-2px);
}
.chat-send:disabled {
    opacity: 0.45;
}
.cd-avatar--blurred {
    filter: blur(6px) brightness(0.88);
}
.typing-indicator {
    display: inline-flex;
    gap: 4px;
    padding: 12px 16px;
    border-radius: 15px;
    background: #f1e5e9;
}
.typing-indicator span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #a45270;
    animation: typing-bounce 1.4s ease-in-out infinite;
}
.typing-indicator span:nth-child(2) {
    animation-delay: 0.2s;
}
.typing-indicator span:nth-child(3) {
    animation-delay: 0.4s;
}
.typing-dots span {
    animation: typing-bounce 1.4s ease-in-out infinite;
}
.typing-dots span:nth-child(2) {
    animation-delay: 0.2s;
}
.typing-dots span:nth-child(3) {
    animation-delay: 0.4s;
}
@keyframes typing-bounce {
    0%,
    60%,
    100% {
        opacity: 0.45;
        transform: translateY(0);
    }
    30% {
        opacity: 1;
        transform: translateY(-4px);
    }
}
@keyframes chat-shimmer {
    50% {
        opacity: 0.55;
    }
}
.typing-status-enter-active,
.typing-status-leave-active {
    transition: opacity 0.2s;
}
.typing-status-enter-from,
.typing-status-leave-to {
    opacity: 0;
}
@media (max-width: 550px) {
    .chat-header {
        padding: 20px 19px;
    }
    .chat-messages {
        padding: 20px 15px;
    }
    .chat-composer {
        padding: 12px;
    }
    .chat-safety {
        padding: 12px 18px;
    }
}
@media (prefers-reduced-motion: reduce) {
    .slide-enter-active,
    .slide-leave-active,
    .fade-enter-active,
    .fade-leave-active {
        transition: none;
    }
    .typing-indicator span,
    .typing-dots span {
        animation: none;
    }
}
</style>
