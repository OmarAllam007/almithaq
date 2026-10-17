<script setup lang="ts">
import { computed } from 'vue';

interface Message {
    id: number;
    message: string;
    sender_id: number;
    created_at: string;
    is_read: boolean;
    sender?: { id: number; name: string; username: string };
}

const props = defineProps<{ message: Message; currentUserId: number }>();
const isOwnMessage = computed(() => props.message.sender_id === props.currentUserId);
const formattedTime = computed(() => new Date(props.message.created_at).toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' }));
</script>

<template>
    <div class="message-row" :class="{ 'message-row--own': isOwnMessage }">
        <div class="message-content">
            <div class="message-bubble">{{ message.message }}</div>
            <div class="message-meta">
                <time>{{ formattedTime }}</time>
                <i v-if="isOwnMessage" :class="['ki-outline', message.is_read ? 'ki-double-check' : 'ki-check']"></i>
            </div>
        </div>
    </div>
</template>

<style scoped>
.message-row {
    display: flex;
    justify-content: flex-start;
    margin-bottom: 15px;
}
.message-row--own {
    justify-content: flex-end;
}
.message-content {
    max-width: min(82%, 370px);
    min-width: 0;
}
.message-bubble {
    padding: 11px 14px;
    border: 1px solid #ebdfe3;
    border-radius: 14px 14px 14px 4px;
    background: #fff;
    color: #553c49;
    box-shadow: 0 4px 12px #4321330b;
    font-size: 12px;
    line-height: 1.75;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
}
.message-row--own .message-bubble {
    border-color: #964366;
    border-radius: 14px 14px 4px 14px;
    background: #9b4568;
    color: #fff;
}
.message-meta {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 5px;
    margin: 4px 5px 0;
    color: #a68e9a;
    font-size: 9px;
}
.message-row--own .message-meta i {
    color: #ac6380;
}
:global([dir='rtl']) .message-row:not(.message-row--own) .message-bubble {
    border-radius: 14px 14px 4px 14px;
}
:global([dir='rtl']) .message-row--own .message-bubble {
    border-radius: 14px 14px 14px 4px;
}
</style>
