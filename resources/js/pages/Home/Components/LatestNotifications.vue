<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useLang } from '@/composables/useLang';
const { trans } = useLang();
const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');

interface NotificationActor {
    id: number;
    name: string;
    username: string;
    profile_image?: string | null;
}

interface Notification {
    id: number;
    type: string;
    data: Record<string, any> | null;
    is_read: boolean;
    created_at: string;
    actor: NotificationActor | null;
}

interface Props {
    notifications?: Notification[];
}

const props = withDefaults(defineProps<Props>(), {
    notifications: () => [],
});

const typeConfig: Record<string, { icon: string; color: string; bg: string; labelKey: string }> = {
    like: { icon: 'ki-solid ki-heart-circle', color: '#d02e79', bg: '#fff0f7', labelKey: 'liked your profile' },
    profile_visit: { icon: 'ki-outline ki-eye', color: '#7239ea', bg: '#f8f5ff', labelKey: 'visited your profile' },
    new_message: { icon: 'ki-outline ki-message-text-2', color: '#009ef7', bg: '#f0faff', labelKey: 'sent you a message' },
    subscription_renewed: { icon: 'ki-outline ki-verify', color: '#50cd89', bg: '#f0fff8', labelKey: 'subscription renewed' },
    ignore: { icon: 'ki-outline ki-cross-circle', color: '#f1416c', bg: '#fff5f8', labelKey: 'ignored your profile' },
};

const getConfig = (type: string) => {
    return typeConfig[type] ?? { icon: 'ki-outline ki-notification', color: '#7e8299', bg: '#f9f9f9', labelKey: type };
};

const getInitials = (name: string): string => {
    return name
        .split(' ')
        .slice(0, 2)
        .map((w) => w[0])
        .join('')
        .toUpperCase();
};

const formatTime = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMinutes = Math.floor((now.getTime() - date.getTime()) / 60000);

    if (diffMinutes < 1) { return trans('home.just_now'); }
    if (diffMinutes < 60) { return trans('home.ago_minutes', { count: diffMinutes }); }
    if (diffMinutes < 1440) { return trans('home.ago_hours', { count: Math.floor(diffMinutes / 60) }); }
    if (diffMinutes < 10080) { return trans('home.ago_days', { count: Math.floor(diffMinutes / 1440) }); }

    return date.toLocaleDateString(isRtl.value ? 'ar-SA' : 'en-US', { month: 'short', day: 'numeric' });
};
</script>

<template>
    <div class="col-xl-6 mb-8">
        <div class="card card-flush h-xl-100">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900">{{ trans('home.notifications') }}</span>
                    <span class="fw-semibold fs-7 mt-1 text-gray-500">{{ trans('home.your_latest_activity') }}</span>
                </h3>
            </div>

            <div class="card-body pt-4 px-5">
                <template v-if="notifications.length > 0">
                    <div
                        v-for="(notification, index) in notifications"
                        :key="notification.id"
                        class="notification-item d-flex align-items-center gap-4 p-3 rounded-3"
                        :class="{ 'mt-2': index > 0, 'unread': !notification.is_read }"
                    >
                        <div class="position-relative flex-shrink-0">
                            <div v-if="notification.actor?.profile_image" class="symbol symbol-45px">
                                <img
                                    :src="notification.actor.profile_image"
                                    :alt="notification.actor.username"
                                    class="rounded-circle object-fit-cover"
                                    style="width:45px;height:45px;"
                                />
                            </div>
                            <div
                                v-else-if="notification.actor"
                                class="rounded-circle d-flex align-items-center justify-content-center fw-bold fs-6 text-white flex-shrink-0"
                                style="width:45px;height:45px;background:linear-gradient(135deg,#d02e79,#f472b6);"
                            >
                                {{ getInitials(notification.actor.name || notification.actor.username) }}
                            </div>
                            <div
                                v-else
                                class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                :style="`width:45px;height:45px;background:${getConfig(notification.type).bg};`"
                            >
                                <i :class="getConfig(notification.type).icon" :style="`color:${getConfig(notification.type).color};font-size:1.4rem;`"></i>
                            </div>

                            <div
                                class="position-absolute bottom-0 end-0 rounded-circle d-flex align-items-center justify-content-center border border-2 border-white"
                                :style="`width:18px;height:18px;background:${getConfig(notification.type).bg};`"
                            >
                                <i :class="[getConfig(notification.type).icon, 'fs-9']" :style="`color:${getConfig(notification.type).color};`"></i>
                            </div>
                        </div>

                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold text-gray-800 fs-7">
                                    <template v-if="notification.actor">
                                        {{ notification.actor.name || notification.actor.username }}
                                    </template>
                                    <template v-else>{{ trans('home.system') }}</template>
                                </span>
                                <span class="fs-9 text-gray-600 text-nowrap ms-2">
                                    {{ formatTime(notification.created_at) }}
                                </span>
                            </div>
                            <span class="fs-7 text-gray-600">
                                {{ trans('home.' + getConfig(notification.type).labelKey) }}
                            </span>
                        </div>

                        <div v-if="!notification.is_read" class="flex-shrink-0">
                            <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#d02e79;"></span>
                        </div>
                    </div>
                </template>

                <div v-else class="d-flex flex-column align-items-center justify-content-center py-12 text-center">
                    <div
                        class="rounded-circle d-flex align-items-center justify-content-center mb-4"
                        style="width:70px;height:70px;background:#fff0f7;"
                    >
                        <i class="ki-outline ki-notification-on fs-2x" style="color:#d02e79;"></i>
                    </div>
                    <p class="fw-bold fs-5 text-gray-800 mb-1">{{ trans('home.no_notifications') }}</p>
                    <p class="fs-7 text-gray-700">{{ trans('home.you_re_all_caught_up') }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.notification-item {
    transition: background 0.15s ease;
}
.notification-item:hover {
    background: #fdf4f8;
    cursor: pointer;
}
.notification-item.unread {
    background: #fffbfd;
}
</style>
