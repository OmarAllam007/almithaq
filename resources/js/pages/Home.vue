<script setup lang="ts">
import { ref } from 'vue';
import AdvancedSearch from '@/pages/Home/Components/AdvancedSearch.vue';
import RecentMessages from '@/pages/Home/Components/RecentMessages.vue';
import LatestMembers from '@/pages/Home/Components/LatestMembers.vue';
import StatsCard from '@/pages/Home/Components/StatsCard.vue';
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';

interface Props {
    conversations?: Array<any>;
}

const props = withDefaults(defineProps<Props>(), {
    conversations: () => [],
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
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="row gx-5 gx-xl-10">
                    <StatsCard />
                    <AdvancedSearch />
                    <RecentMessages
                        :conversations="conversations"
                        @open-chat="handleOpenChat"
                    />
                    <LatestMembers />
                </div>
            </div>
        </div>

        <!-- Chat Drawer -->
        <ChatDrawer
            :is-open="isChatOpen"
            :conversation-id="selectedConversationId"
            @close="handleCloseChat"
        />
    </div>
</template>

<style scoped></style>
