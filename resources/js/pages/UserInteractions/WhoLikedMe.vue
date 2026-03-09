<script setup lang="ts">
import UserProfileModal from '@/components/UserProfileModal.vue';
import ChatDrawer from '@/components/Chat/ChatDrawer.vue';
import { ref } from 'vue';
import axios from 'axios';
import Pagination from '@/components/Pagination.vue';
import { PaginationData } from '@/types/pagination';
import { useLang } from '@/composables/useLang';

const { trans } = useLang();

const props = defineProps<Props>();
interface Props {
    users: PaginationData;
}

const isChatDrawerOpen = ref(false);
const isProfileModalOpen = ref(false);
const selectedConversationId = ref<number | undefined>(undefined);
const selectedUserId = ref<number | undefined>(undefined);

const openChatDrawer = (conversationId: number) => {
    selectedConversationId.value = conversationId;
    isChatDrawerOpen.value = true;
};

const closeChatDrawer = () => {
    isChatDrawerOpen.value = false;
    selectedConversationId.value = undefined;
};

const openProfileModal = (userId: number) => {
    selectedUserId.value = userId;
    isProfileModalOpen.value = true;
};

const closeProfileModal = () => {
    isProfileModalOpen.value = false;
    selectedUserId.value = undefined;
};

const handleMessageFromModal = async (userId: number) => {
    closeProfileModal();

    try {
        // Get or create conversation with this user
        const response = await axios.post('/conversations', {
            recipient_id: userId,
        });

        const conversationId = response.data.conversation_id;
        openChatDrawer(conversationId);
    } catch (error) {
        console.error('Failed to get or create conversation:', error);
    }
};

</script>

<template>
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <!--begin::Content wrapper-->
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-lg-0 py-3">
                <!--begin::Toolbar container-->
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <!--begin::Page title-->
                    <div class="page-title d-flex flex-column justify-content-center me-3">
                        <!--begin::Title-->
                        <h1 class="page-heading d-flex fw-bold fs-3 flex-column justify-content-center my-0 text-gray-900">{{ trans('user_interactions.who_liked_me') }}</h1>
                        <!--end::Title-->

                        <!--begin::Breadcrumb-->
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <!--begin::Item-->
                            <li class="breadcrumb-item text-muted">
                                <a href="/metronic8/demo23/?page=index" class="text-muted text-hover-primary">{{ trans('user_interactions.home') }}</a>
                            </li>
                            <!--end::Item-->
                            <!--begin::Item-->
                            <li class="breadcrumb-item">
                                <span class="bullet w-5px h-2px bg-gray-500"></span>
                            </li>
                            <!--end::Item-->

                            <!--begin::Item-->
                            <li class="breadcrumb-item text-muted">{{ trans('user_interactions.who_liked_me') }}</li>
                            <!--end::Item-->
                        </ul>
                        <!--end::Breadcrumb-->
                    </div>
                    <!--end::Page title-->
                </div>
                <!--end::Toolbar container-->
            </div>
            <!--end::Toolbar-->

            <!--begin::Content-->
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <!--begin::Content container-->
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <!--begin::Inbox App - Messages -->
                    <div class="d-flex flex-column flex-lg-row">
                        <!--begin::Content-->
                        <div class="flex-lg-row-fluid">
                            <!--begin::Card-->
                            <div class="card">
                                <div class="card-header align-items-center gap-md-5 gap-2 py-5">
                                    <div class="d-flex flex-wrap gap-2"></div>
                                    <p>{{ trans('user_interactions.total_likes') }}:  {{ users.data.length }}</p>
                                </div>

                                <div class="card-body p-0">
                                    <!--begin::Table-->
                                    <div id="kt_inbox_listing_wrapper" class="dt-container dt-bootstrap5 dt-empty-footer">
                                        <div id="" class="table-responsive">
                                            <table
                                                class="table-hover table-row-dashed fs-6 gy-5 dataTable my-0 table"
                                                id="kt_inbox_listing"
                                                style="width: 100%"
                                            >

                                                <thead >
                                                    <tr>
                                                        <th data-dt-column="0" rowspan="1" colspan="1" class="dt-orderable-asc dt-orderable-desc p-5">
                                                            <div>
                                                                <span class="dt-column-title">{{ trans('user_interactions.member') }}</span
                                                                ><span
                                                                    class="dt-column-order"
                                                                    role="button"
                                                                    aria-label="Checkbox: Activate to sort"
                                                                    tabindex="0"
                                                                ></span>
                                                            </div>
                                                        </th>
                                                        <th data-dt-column="1" rowspan="1" colspan="1" class="dt-orderable-asc dt-orderable-desc">
                                                            <div >
                                                                <span class="dt-column-title">{{ trans('user_interactions.added_date') }}</span
                                                                ><span
                                                                    class="dt-column-order"
                                                                    role="button"
                                                                    aria-label="Actions: Activate to sort"
                                                                    tabindex="0"
                                                                ></span>
                                                            </div>
                                                        </th>
                                                  </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="user in users.data" :key="user.id" class="cursor-pointer"  @click.stop="openProfileModal(user.id)">

                                                        <td class="p-5">
                                                            <div class="d-flex align-items-center cursor-pointer text-gray-900">
                                                                <!--begin::Avatar-->
                                                                <div class="symbol symbol-45px me-3 position-relative">
                                                                    <!-- Favorite Badge -->
                                                                    <div
                                                                        v-if="user.is_favourite"
                                                                        class="position-absolute top-0 start-0 translate-middle d-flex align-items-center justify-content-center rounded-circle bg-danger border border-2 border-white"
                                                                        style="width: 20px; height: 20px; z-index: 1"
                                                                        :title="trans('user_interactions.they_favorited_you')"
                                                                    >
                                                                        <i class="fa fa-heart text-white" style="font-size: 10px"></i>
                                                                    </div>

                                                                    <img
                                                                        :src="user?.mainProfileImage || '/assets/media/avatars/300-1.jpg'"
                                                                        :alt="user?.username"
                                                                        class="rounded-circle"
                                                                    />
                                                                </div>
                                                                <!--end::Avatar-->

                                                                <!--begin::User Info-->
                                                                <div class="d-flex flex-column">
                                                                    <span class="fw-bold mb-1 text-gray-900">{{ user.username }}</span>
                                                                    <div class="d-flex align-items-center gap-2">
                                                                        <span class="badge badge-light-primary fs-8">{{
                                                                            user?.marriage_status
                                                                        }}</span>
                                                                        <span class="text-muted fs-5" :title="user?.nationality?.name">{{ user?.nationality?.flag }}</span>
                                                                        <span class="text-muted fs-7">{{ user?.age }} {{ trans('user_interactions.yrs') }}</span>
                                                                    </div>
                                                                </div>
                                                                <!--end::User Info-->
                                                            </div>
                                                        </td>

                                                        <td >
                                                            <div class="text-muted gap-1 pt-2">
                                                                <span class="fst-italic">{{user.created_at}}</span>
                                                            </div>
                                                        </td>
<!--                                                        <td @click="openChatDrawer(conversation.id)" class="w-100px fs-7 pe-9 text-end">-->
<!--                                                            <span v-if="conversation.lastMessage" class="fw-semibold text-gray-600">-->
<!--                                                                {{ formatMessageDate(conversation.lastMessage.created_at) }}-->
<!--                                                            </span>-->
<!--                                                            <span v-else class="text-muted">-</span>-->
<!--                                                        </td>-->
                                                    </tr>
                                                </tbody>
                                                <tfoot></tfoot>
                                            </table>
                                        </div>
                                        <div id="" class="row px-9 pt-3 pb-5">
                                            <!-- Pagination -->
                                            <Pagination :data="users" />
                                        </div>
                                        <div class="dt-autosize" style="width: 100%; height: 0px"></div>
                                    </div>
                                    <!--end::Table-->
                                </div>
                            </div>
                            <!--end::Card-->
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Inbox App - Messages -->
                </div>
                <!--end::Content container-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Content wrapper-->

        <!-- Chat Drawer -->
        <ChatDrawer :is-open="isChatDrawerOpen" :conversation-id="selectedConversationId" @close="closeChatDrawer" />

        <!-- User Profile Modal -->
        <UserProfileModal :is-open="isProfileModalOpen"
                          :user-id="selectedUserId"
                          @close="closeProfileModal"
                          @message="handleMessageFromModal" />
    </div>
</template>
