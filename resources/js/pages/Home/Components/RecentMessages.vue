<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useLang } from '@/composables/useLang';
const { trans } = useLang();

interface Message {
    id: number;
    message: string;
    sender_id: number;
    created_at: string;
    is_read: boolean;
}

interface ConversationUser {
    id: number;
    name: string;
    username: string;
    is_online?: boolean;
    profile_image?: string | null;
}

interface Conversation {
    id: number;
    other_user: ConversationUser;
    latest_message?: Message;
    unread_count: number;
    last_message_at: string;
}

interface Props {
    conversations?: Conversation[];
}

const props = withDefaults(defineProps<Props>(), {
    conversations: () => [],
});

const emit = defineEmits<{
    openChat: [conversationId: number];
}>();

const formatTime = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMinutes = Math.floor((now.getTime() - date.getTime()) / 60000);

    if (diffMinutes < 1) { return 'Just now'; }
    if (diffMinutes < 60) { return `${diffMinutes}m ago`; }
    if (diffMinutes < 1440) { return `${Math.floor(diffMinutes / 60)}h ago`; }
    if (diffMinutes < 10080) { return `${Math.floor(diffMinutes / 1440)}d ago`; }

    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const truncateMessage = (message: string, maxLength = 45): string => {
    if (message.length <= maxLength) { return message; }
    return message.substring(0, maxLength) + '…';
};

const getInitials = (name: string): string => {
    return name
        .split(' ')
        .slice(0, 2)
        .map((w) => w[0])
        .join('')
        .toUpperCase();
};
</script>

<template>
    <div class="col-xl-6 mb-8">
        <div class="card card-flush h-xl-100">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900">{{ trans('home.recent_messages') }}</span>
                    <span class="fw-semibold fs-7 mt-1 text-gray-500">{{ trans('home.your_latest_conversations') }}</span>
                </h3>
            </div>

            <div class="card-body pt-4 px-5">
                <template v-if="conversations.length > 0">
                    <div
                        v-for="(conversation, index) in conversations"
                        :key="conversation.id"
                        class="conversation-item d-flex align-items-center gap-4 p-3 rounded-3 cursor-pointer"
                        :class="{ 'mt-2': index > 0 }"
                        @click="emit('openChat', conversation.id)"
                    >
                        <div class="position-relative flex-shrink-0">
                            <div v-if="conversation.other_user.profile_image" class="symbol symbol-50px">
                                <img
                                    :src="conversation.other_user.profile_image"
                                    :alt="conversation.other_user.username"
                                    class="rounded-circle object-fit-cover"
                                    style="width:50px;height:50px;"
                                />
                            </div>
                            <div
                                v-else
                                class="rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5 text-white flex-shrink-0"
                                style="width:50px;height:50px;background:linear-gradient(135deg,#d02e79,#f472b6);"
                            >
                                {{ getInitials(conversation.other_user.name || conversation.other_user.username) }}
                            </div>
                            <span
                                v-if="conversation.other_user.is_online"
                                class="position-absolute bottom-0 end-0 rounded-circle border border-2 border-white bg-success"
                                style="width:13px;height:13px;"
                            ></span>
                        </div>

                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span
                                    class="fw-bold text-gray-800 fs-6"
                                    :class="{ 'text-gray-900': conversation.unread_count > 0 }"
                                >
                                    {{ conversation.other_user.name || conversation.other_user.username }}
                                </span>
                                <span class="fs-8 text-gray-400 text-nowrap ms-2">
                                    {{ formatTime(conversation.last_message_at) }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span
                                    class="fs-7 text-truncate"
                                    :class="conversation.unread_count > 0 ? 'fw-semibold text-gray-700' : 'text-gray-500'"
                                    style="max-width:220px;"
                                >
                                    <template v-if="conversation.latest_message">
                                        {{ truncateMessage(conversation.latest_message.message) }}
                                    </template>
                                    <span v-else class="fst-italic text-gray-400">No messages yet</span>
                                </span>
                                <span
                                    v-if="conversation.unread_count > 0"
                                    class="badge rounded-pill text-white fs-9 ms-2 flex-shrink-0"
                                    style="background:#d02e79;min-width:20px;"
                                >
                                    {{ conversation.unread_count }}
                                </span>
                            </div>
                        </div>
                    </div>
                </template>

                <div v-else class="d-flex flex-column align-items-center justify-content-center py-12 text-center">
                    <div
                        class="rounded-circle d-flex align-items-center justify-content-center mb-4"
                        style="width:70px;height:70px;background:#fff0f7;"
                    >
                        <i class="ki-outline ki-message-text fs-2x" style="color:#d02e79;"></i>
                    </div>
                    <p class="fw-bold fs-5 text-gray-800 mb-1">{{ trans('home.no_messages_yet') }}</p>
                    <p class="fs-7 text-gray-500">{{ trans('home.start_a_conversation_with_someone') }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
.conversation-item {
    transition: background 0.15s ease;
}
.conversation-item:hover {
    background: #fdf4f8;
}
</style>
