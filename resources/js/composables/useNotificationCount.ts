import { ref } from 'vue';

const unreadCount = ref(0);

export function useNotificationCount() {
    return { unreadCount };
}
