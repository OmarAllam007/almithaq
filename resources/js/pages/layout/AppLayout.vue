<script setup lang="ts">
import AdBlockDetector from '@/components/AdBlockDetector.vue';
import Header from '@/pages/_shared/Header.vue';
import Sidebar from '@/pages/_shared/Sidebar.vue';
import BottomNav from '@/pages/_shared/BottomNav.vue';
import ToastNotification from '@/components/ToastNotification.vue';
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';
import UserProfileModal from '@/components/UserProfileModal.vue';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { usePresenceStore } from '@/stores/useUserStore';
import { echo } from '@laravel/echo-vue';
import { useToastStore } from '@/composables/useToastStore';
import { useNotificationCount } from '@/composables/useNotificationCount';
import { useActiveChatStore } from '@/composables/useActiveChatStore';
import { useUnreadConversations } from '@/composables/useUnreadConversations';
import axios from 'axios';

const page = usePage();
const isAuth = computed(() => page.props.auth && page.props.auth?.user !== null);
const isRtl = computed(() => page.props.locale === 'ar');
const direction = computed(() => isRtl.value ? 'rtl' : 'ltr');

const presence = usePresenceStore();
const { addToast } = useToastStore();
const { unreadCount } = useNotificationCount();
const { activeConversationId } = useActiveChatStore();
const { markConversationUnread, syncFromServer } = useUnreadConversations();

// Global modal/drawer state for toast-triggered actions
const toastChatConversationId = ref<number | undefined>(undefined);
const toastChatOpen = ref(false);
const toastProfileUserId = ref<number | undefined>(undefined);
const toastProfileOpen = ref(false);

function handleToastOpenChat(conversationId: number) {
    toastChatConversationId.value = conversationId;
    toastChatOpen.value = true;
}

function handleToastOpenProfile(actorId: number) {
    toastProfileUserId.value = actorId;
    toastProfileOpen.value = true;
}

async function handleToastMessageFromProfile(userId: number) {
    toastProfileOpen.value = false;
    try {
        const response = await axios.post('/conversations', { recipient_id: userId });
        toastChatConversationId.value = response.data.conversation_id;
        toastChatOpen.value = true;
    } catch (e) {
        console.error('Failed to open conversation:', e);
    }
}

const updateDocumentDirection = () => {
    document.documentElement.setAttribute('dir', direction.value);
    document.documentElement.setAttribute('lang', page.props.locale as string || 'en');
    document.body.setAttribute('dir', direction.value);
};

onMounted(() => {
    if (isAuth.value) {
        presence.initializePresence();

        syncFromServer((page.props as any).unread_conversations ?? 0);

        const userId = (page.props.auth as any)?.user?.id;
        if (userId) {
            echo().private(`user.${userId}`)
                .listen('.UserNotificationSent', (e: any) => {
                    const isViewingThisChat =
                        e.type === 'new_message' &&
                        e.data?.conversation_id === activeConversationId.value;
                    if (!isViewingThisChat) {
                        unreadCount.value += 1;
                        if (e.type === 'new_message' && e.data?.conversation_id) {
                            markConversationUnread(e.data.conversation_id);
                        }
                    }
                });
        }
    }

    updateDocumentDirection();
});

onUnmounted(() => {
    const userId = (page.props.auth as any)?.user?.id;
    if (userId) {
        echo().leave(`user.${userId}`);
    }
});

watch(() => page.props.locale, () => {
    updateDocumentDirection();
});

watch(
    () => (page.props as any).unread_conversations,
    (val: number | undefined) => {
        if (val !== undefined) {
            syncFromServer(val);
        }
    }
);
</script>

<template>
    <AdBlockDetector />
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root" :dir="$page.props.locale === 'ar' ? 'rtl' : 'ltr'">
        <!--begin::Page-->
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            <Header />
            <div class="flex-column flex-row-fluid" :class="{ 'app-wrapper': isAuth }" id="kt_app_wrapper">
                <Sidebar v-if="isAuth" />
                <slot />
            </div>
        </div>
        <BottomNav v-if="isAuth" />

        <!-- Global toast notifications -->
        <ToastNotification
            v-if="isAuth"
            @open-chat="handleToastOpenChat"
            @open-profile="handleToastOpenProfile"
        />

        <!-- Global chat drawer for toast actions -->
        <ChatDrawer
            v-if="isAuth"
            :is-open="toastChatOpen"
            :conversation-id="toastChatConversationId"
            @close="toastChatOpen = false; toastChatConversationId = undefined"
        />

        <!-- Global profile modal for toast actions -->
        <UserProfileModal
            v-if="isAuth"
            :is-open="toastProfileOpen"
            :user-id="toastProfileUserId"
            @close="toastProfileOpen = false; toastProfileUserId = undefined"
            @message="handleToastMessageFromProfile"
        />
    </div>
</template>

<style scoped></style>
