import { ref } from 'vue';

const activeConversationId = ref<number | null>(null);

export function useActiveChatStore() {
    return { activeConversationId };
}
