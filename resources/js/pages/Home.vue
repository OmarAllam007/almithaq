<script setup lang="ts">
import { ref } from 'vue';
import AdvancedSearch from '@/pages/Home/Components/AdvancedSearch.vue';
import RecentMessages from '@/pages/Home/Components/RecentMessages.vue';
import StatsCard from '@/pages/Home/Components/StatsCard.vue';
import LatestNotifications from '@/pages/Home/Components/LatestNotifications.vue';
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';
import GoogleAd from '@/components/GoogleAd.vue';

interface Stats {
    profile_views: number;
    likes: number;
    conversations: number;
    days_since_joined: number;
}

interface Props {
    stats?: Stats;
    conversations?: Array<any>;
    notifications?: Array<any>;
    countries?: Array<{ id: number; name: string; ar_name: string }>;
    marriageStatuses?: Array<{ value: number; label: string }>;
}

const props = withDefaults(defineProps<Props>(), {
    stats: () => ({ profile_views: 0, likes: 0, conversations: 0, days_since_joined: 0 }),
    conversations: () => [],
    notifications: () => [],
    countries: () => [],
    marriageStatuses: () => [],
});

const isChatOpen = ref(false);
const selectedConversationId = ref<number | undefined>(undefined);

const handleOpenChat = (conversationId: number) => {
    selectedConversationId.value = conversationId;
    isChatOpen.value = true;
};

const handleCloseChat = () => {
    isChatOpen.value = false;
    selectedConversationId.value = undefined;
};
</script>

<template>
    <div>
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="row gx-5 gx-xl-10">
                    <div class="animate-fade-up" style="animation-delay: 0ms">
                        <StatsCard
                            :profile-views="stats.profile_views"
                            :likes="stats.likes"
                            :conversations="stats.conversations"
                            :days-since-joined="stats.days_since_joined"
                        />
                    </div>

                    <!-- Ad: below stats, high-visibility top-of-page banner -->
                    <div class="col-xl-12 mb-6 animate-fade-up" style="animation-delay: 80ms">
                        <GoogleAd slot-id="HOME_TOP_BANNER" format="horizontal" />
                    </div>

                    <div class="animate-fade-up" style="animation-delay: 160ms; width: 100%">
                        <AdvancedSearch :countries="countries" :marriage-statuses="marriageStatuses" />
                    </div>
                    <div class="animate-fade-up" style="animation-delay: 240ms; width: 100%">
                        <RecentMessages
                            :conversations="conversations"
                            @open-chat="handleOpenChat"
                        />
                    </div>
                    <div class="animate-fade-up" style="animation-delay: 320ms; width: 100%">
                        <LatestNotifications :notifications="notifications" />
                    </div>

                    <!-- Ad: below all widgets, before page ends -->
                    <div class="col-xl-12 mt-2 mb-6 animate-fade-up" style="animation-delay: 400ms">
                        <GoogleAd slot-id="HOME_BOTTOM_BANNER" format="auto" />
                    </div>
                </div>
            </div>
        </div>

        <ChatDrawer
            :is-open="isChatOpen"
            :conversation-id="selectedConversationId"
            @close="handleCloseChat"
        />
    </div>
</template>

<style scoped>
.animate-fade-up {
    animation: fadeUp 0.5s ease both;
}
.animate-fade-up:hover {
    animation: fadeUp 0.5s ease both;
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
