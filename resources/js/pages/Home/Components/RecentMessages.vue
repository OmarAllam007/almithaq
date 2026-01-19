<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

interface Message {
    id: number;
    message: string;
    sender_id: number;
    created_at: string;
    is_read: boolean;
}

interface User {
    id: number;
    name: string;
    username: string;
    is_online?: boolean;
}

interface Conversation {
    id: number;
    other_user: User;
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

    if (diffMinutes < 1) return 'Just now';
    if (diffMinutes < 60) return `${diffMinutes}m ago`;
    if (diffMinutes < 1440) return `${Math.floor(diffMinutes / 60)}h ago`;
    if (diffMinutes < 10080) return `${Math.floor(diffMinutes / 1440)}d ago`;

    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const truncateMessage = (message: string, maxLength: number = 50): string => {
    if (message.length <= maxLength) return message;
    return message.substring(0, maxLength) + '...';
};

const handleConversationClick = (conversationId: number) => {
    emit('openChat', conversationId);
};

const handleViewAll = () => {
    router.visit('/messages');
};
</script>

<template>
    <div class="col-xl-6">
        <!--begin::Recent Messages Card-->
        <div class="card card-flush h-xl-100">
            <!--begin::Header-->
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900">Recent Messages</span>
                    <span class="fw-semibold fs-7 mt-1 text-gray-500">Your latest conversations</span>
                </h3>

                <!--begin::Toolbar-->
                <div class="card-toolbar">
                    <button @click="handleViewAll" class="btn btn-sm btn-light">View All</button>
                </div>
                <!--end::Toolbar-->
            </div>
            <!--end::Header-->

            <!--begin::Body-->
            <div class="card-body pt-5">
                <template v-for="(conversation, index) in conversations" :key="conversation.id">
                    <!--begin::Item-->
                    <div
                        @click="handleConversationClick(conversation.id)"
                        class="d-flex flex-stack cursor-pointer hover-bg-light-primary rounded p-3 transition"
                        style="margin: -12px; margin-bottom: 0"
                    >
                        <!--begin::Wrapper-->
                        <div class="d-flex align-items-center me-3 flex-grow-1 overflow-hidden">
                            <!--begin::Avatar-->
                            <div class="symbol symbol-50px me-5">
                                <div class="position-relative">
                                    <img
                                        src="/assets/media/avatars/300-1.jpg"
                                        :alt="conversation.other_user.username"
                                        class="rounded-circle"
                                    />
                                    <!--begin::Online Indicator-->
                                    <div
                                        v-if="conversation.other_user.is_online"
                                        class="position-absolute rounded-circle border border-2 border-white h-12px w-12px translate-middle top-100 start-100 bg-success"
                                    ></div>
                                    <!--end::Online Indicator-->
                                </div>
                            </div>
                            <!--end::Avatar-->

                            <!--begin::Section-->
                            <div class="flex-grow-1 overflow-hidden">
                                <!--begin::User Name-->
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <a
                                        href="#"
                                        @click.stop
                                        class="text-hover-primary fs-6 fw-bold lh-1 text-gray-800"
                                    >
                                        {{ conversation.other_user.username }}
                                    </a>
                                </div>
                                <!--end::User Name-->

                                <!--begin::Message Preview-->
                                <div
                                    class="fw-semibold text-gray-500 text-truncate"
                                    :class="{
                                        'fw-bold text-gray-900':
                                            conversation.unread_count > 0,
                                    }"
                                    style="max-width: 100%"
                                >
                                    <template v-if="conversation.latest_message">
                                        {{
                                            truncateMessage(
                                                conversation.latest_message.message
                                            )
                                        }}
                                    </template>
                                    <template v-else>
                                        <span class="fst-italic">No messages yet</span>
                                    </template>
                                </div>
                                <!--end::Message Preview-->
                            </div>
                            <!--end::Section--></div>
                        <!--end::Wrapper-->

                        <!--begin::Actions-->
                        <div class="d-flex flex-column align-items-end ms-2">
                            <span class="fw-semibold fs-7 mb-2 text-gray-500 text-nowrap">
                                {{ formatTime(conversation.last_message_at) }}
                            </span>
                            <!--begin::Unread Badge-->
                            <span
                                v-if="conversation.unread_count > 0"
                                class="badge badge-circle badge-primary"
                            >
                                {{ conversation.unread_count }}
                            </span>
                            <!--end::Unread Badge-->
                        </div>
                        <!--end::Actions-->
                    </div>
                    <!--end::Item-->

                    <!--begin::Separator-->
                    <div
                        v-if="index < conversations.length - 1"
                        class="separator separator-dashed my-4"
                    ></div>
                    <!--end::Separator-->
                </template>

                <!--begin::Empty State-->
                <div v-if="conversations.length === 0" class="py-10 text-center">
                    <div class="symbol symbol-100px mx-auto mb-5">
                        <div class="symbol-label fs-1 bg-light-primary text-primary">
                            <i class="ki-outline ki-message-text fs-3x"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-2 text-gray-800">No Messages Yet</h3>
                    <p class="fw-semibold fs-6 mb-0 text-gray-500">
                        Start a conversation with someone!
                    </p>
                </div>
                <!--end::Empty State-->
            </div>
            <!--end::Body-->
        </div>
        <!--end::Recent Messages Card-->
    </div>
</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}

.hover-bg-light-primary:hover {
    background-color: #f1faff;
}

.transition {
    transition: all 0.2s ease;
}

.badge-circle {
    min-width: 20px;
    height: 20px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 11px;
    font-weight: 600;
}
</style>
