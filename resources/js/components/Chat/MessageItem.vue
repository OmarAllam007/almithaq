<script setup lang="ts">
import { computed } from 'vue';

interface Message {
    id: number;
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

interface Props {
    message: Message;
    currentUserId: number;
}

const props = defineProps<Props>();

const isOwnMessage = computed(() => props.message.sender_id === props.currentUserId);

const formattedTime = computed(() => {
    const date = new Date(props.message.created_at);
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
    });
});
</script>

<template>
    <div
        :class="[
            'd-flex mb-4',
            isOwnMessage ? 'justify-content-end' : 'justify-content-start',
        ]"
    >
        <div
            :class="[
                'd-flex flex-column',
                'max-w-75',
                isOwnMessage ? 'align-items-end' : 'align-items-start',
            ]"
        >
            <!-- Message Bubble -->
            <div
                :class="[
                    'px-4 py-3 rounded-3',
                    isOwnMessage
                        ? 'bg-primary text-white'
                        : 'bg-light-primary text-gray-900',
                ]"
                style="word-break: break-word"
            >
                <div class="fw-normal fs-6">{{ message.message }}</div>
            </div>

            <!-- Time & Read Status -->
            <div class="d-flex align-items-center gap-2 mt-1 px-2">
                <span class="text-muted fs-8">{{ formattedTime }}</span>
                <span v-if="isOwnMessage" class="text-muted fs-9">
                    <i
                        :class="[
                            'ki-outline',
                            message.is_read ? 'ki-double-check text-primary' : 'ki-check',
                        ]"
                    ></i>
                </span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.max-w-75 {
    max-width: 75%;
}
</style>
