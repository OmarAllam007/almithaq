import { ref } from 'vue';

export interface ToastItem {
    id: string;
    type: string;
    actor_id: number;
    actor_username: string;
    actor_avatar: string | null;
    data: Record<string, any> | null;
}

const toasts = ref<ToastItem[]>([]);

function addToast(payload: Omit<ToastItem, 'id'>) {
    const id = `${Date.now()}-${Math.random()}`;
    toasts.value.push({ id, ...payload });
}

function removeToast(id: string) {
    toasts.value = toasts.value.filter((t) => t.id !== id);
}

export function useToastStore() {
    return { toasts, addToast, removeToast };
}
