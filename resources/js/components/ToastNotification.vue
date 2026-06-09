<script setup lang="ts">
import { computed, onUnmounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToastStore, type ToastItem } from '@/composables/useToastStore';
import { useLang } from '@/composables/useLang';

const { trans } = useLang();
const { toasts, removeToast } = useToastStore();
const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');

const emit = defineEmits<{
    openProfile: [actorId: number];
    openChat: [conversationId: number];
}>();

const timers = ref<Record<string, ReturnType<typeof setTimeout>>>({});
const progress = ref<Record<string, number>>({});
const progressTimers = ref<Record<string, ReturnType<typeof setInterval>>>({});

const DURATION = 5000;

function startTimer(id: string) {
    progress.value[id] = 100;

    progressTimers.value[id] = setInterval(() => {
        progress.value[id] = Math.max(0, (progress.value[id] ?? 100) - 100 / (DURATION / 100));
    }, 100);

    timers.value[id] = setTimeout(() => dismiss(id), DURATION);
}

function dismiss(id: string) {
    clearTimeout(timers.value[id]);
    clearInterval(progressTimers.value[id]);
    delete timers.value[id];
    delete progressTimers.value[id];
    delete progress.value[id];
    removeToast(id);
}

function handleClick(toast: ToastItem) {
    dismiss(toast.id);
    if (toast.type === 'new_message' && toast.data?.conversation_id) {
        emit('openChat', toast.data.conversation_id);
    } else {
        emit('openProfile', toast.actor_id);
    }
}

watch(
    () => toasts.value.length,
    (newLen, oldLen) => {
        if (newLen > oldLen) {
            const newest = toasts.value[toasts.value.length - 1];
            if (newest && !timers.value[newest.id]) {
                startTimer(newest.id);
            }
        }
    },
);

onUnmounted(() => {
    Object.values(timers.value).forEach(clearTimeout);
    Object.values(progressTimers.value).forEach(clearInterval);
});

const metaMap: Record<string, { icon: string; color: string; bg: string }> = {
    like: { icon: 'ki-solid ki-heart', color: '#e83e8c', bg: 'rgba(232,62,140,0.10)' },
    new_message: { icon: 'ki-outline ki-sms', color: '#5b8ff9', bg: 'rgba(91,143,249,0.10)' },
};

function getMeta(type: string) {
    return metaMap[type] ?? { icon: 'ki-outline ki-notification-on', color: '#6c757d', bg: 'rgba(108,117,125,0.10)' };
}

function getLabel(type: string) {
    const map: Record<string, string> = {
        like: trans('home.liked your profile'),
        new_message: trans('home.sent you a message'),
    };
    return map[type] ?? type;
}
</script>

<template>
    <Teleport to="body">
        <div
            class="tn-stack"
            :class="isRtl ? 'tn-stack--rtl' : 'tn-stack--ltr'"
        >
            <TransitionGroup name="tn">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="tn-toast"
                    @click="handleClick(toast)"
                >
                    <!-- Icon badge -->
                    <div
                        class="tn-icon"
                        :style="{ background: getMeta(toast.type).bg, color: getMeta(toast.type).color }"
                    >
                        <i :class="getMeta(toast.type).icon"></i>
                    </div>

                    <!-- Avatar -->
                    <img
                        :src="toast.actor_avatar || '/assets/media/auth/no-image-for-user.png'"
                        class="tn-avatar"
                        :alt="toast.actor_username"
                        @error="($event.target as HTMLImageElement).src = '/assets/media/auth/no-image-for-user.png'"
                    />

                    <!-- Text -->
                    <div class="tn-body">
                        <span class="tn-name">{{ toast.actor_username }}</span>
                        <span class="tn-msg">{{ getLabel(toast.type) }}</span>
                    </div>

                    <!-- Dismiss -->
                    <button class="tn-close" @click.stop="dismiss(toast.id)">
                        <i class="ki-outline ki-cross fs-6"></i>
                    </button>

                    <!-- Progress bar -->
                    <div
                        class="tn-progress"
                        :style="{
                            width: (progress[toast.id] ?? 100) + '%',
                            background: getMeta(toast.type).color,
                        }"
                    ></div>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
.tn-stack {
    position: fixed;
    bottom: 24px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    pointer-events: none;
    width: 320px;
}

.tn-stack--ltr { right: 24px; }
.tn-stack--rtl { left: 24px; }

.tn-toast {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fff;
    border-radius: 14px;
    padding: 12px 14px 18px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.13);
    cursor: pointer;
    pointer-events: all;
    overflow: hidden;
    transition: transform 0.18s ease, box-shadow 0.18s ease;
    border: 1px solid rgba(0,0,0,0.06);
}

.tn-toast:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 36px rgba(0,0,0,0.17);
}

.tn-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.tn-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    border: 2px solid #f3f4f6;
}

.tn-body {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 0;
    gap: 2px;
}

.tn-name {
    font-weight: 700;
    font-size: 0.82rem;
    color: #111827;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.tn-msg {
    font-size: 0.75rem;
    color: #6b7280;
}

.tn-close {
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 2px;
    flex-shrink: 0;
    line-height: 1;
    opacity: 0;
    transition: opacity 0.15s;
}

.tn-toast:hover .tn-close { opacity: 1; }

.tn-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    border-radius: 0 0 14px 14px;
    transition: width 0.1s linear;
}

/* Transition animations */
.tn-enter-active { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.tn-leave-active { transition: all 0.2s ease; }
.tn-enter-from { opacity: 0; transform: translateY(20px) scale(0.9); }
.tn-leave-to   { opacity: 0; transform: translateX(40px) scale(0.95); }
</style>
