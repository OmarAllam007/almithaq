import { ref } from 'vue';

const unreadConversations = ref(0);
const trackedIds = new Set<number>();

export function useUnreadConversations() {
    function markConversationUnread(id: number) {
        if (!trackedIds.has(id)) {
            trackedIds.add(id);
            unreadConversations.value += 1;
        }
    }

    function markConversationRead(id: number) {
        trackedIds.delete(id);
        unreadConversations.value = Math.max(0, unreadConversations.value - 1);
    }

    function syncFromServer(count: number) {
        unreadConversations.value = count;
        trackedIds.clear();
    }

    return { unreadConversations, markConversationUnread, markConversationRead, syncFromServer };
}
