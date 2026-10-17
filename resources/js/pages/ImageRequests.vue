<script setup lang="ts">
import UserProfileModal from '@/components/UserProfileModal.vue';
import { useLang } from '@/composables/useLang';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref, watch } from 'vue';

const { trans } = useLang();
const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');

interface Requester {
    id: number;
    username: string;
    age: number;
    mainProfileImage?: string;
    is_online?: boolean;
}

interface ImageRequest {
    id: number;
    status: 'pending' | 'approved' | 'rejected';
    created_at: string;
    requester: Requester;
}

interface Props {
    requests: ImageRequest[];
}

const props = defineProps<Props>();
const selectedProfileUserId = ref<number | undefined>();
const showProfile = ref(false);
const openProfile = (userId: number) => {
    selectedProfileUserId.value = userId;
    showProfile.value = true;
};
const closeProfile = () => {
    showProfile.value = false;
    selectedProfileUserId.value = undefined;
};

const requests = ref<ImageRequest[]>(props.requests);
watch(
    () => props.requests,
    (value) => {
        requests.value = value;
    },
);
const loadingId = ref<number | null>(null);

const pendingRequests = computed(() => requests.value.filter((r) => r.status === 'pending'));
const resolvedRequests = computed(() => requests.value.filter((r) => r.status !== 'pending'));

const formatDate = (dateStr: string) => {
    const d = new Date(dateStr);
    return d.toLocaleDateString(isRtl.value ? 'ar-SA' : 'en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const approve = async (request: ImageRequest) => {
    loadingId.value = request.id;
    try {
        await axios.patch(`/image-requests/${request.id}/approve`);
        const found = requests.value.find((r) => r.id === request.id);
        if (found) {
            found.status = 'approved';
        }
    } catch (e) {
        console.error(e);
    } finally {
        loadingId.value = null;
    }
};

const reject = async (request: ImageRequest) => {
    loadingId.value = request.id;
    try {
        await axios.patch(`/image-requests/${request.id}/reject`);
        const found = requests.value.find((r) => r.id === request.id);
        if (found) {
            found.status = 'rejected';
        }
    } catch (e) {
        console.error(e);
    } finally {
        loadingId.value = null;
    }
};

const deleteRequest = async (request: ImageRequest) => {
    loadingId.value = request.id;
    try {
        await axios.delete(`/image-requests/${request.id}`);
        requests.value = requests.value.filter((r) => r.id !== request.id);
    } catch (e) {
        console.error(e);
    } finally {
        loadingId.value = null;
    }
};
</script>

<template>
    <div id="kt_app_content" class="app-content flex-column-fluid member-page requests-page">
        <div id="kt_app_content_container" class="app-container container-xxl member-container">
            <section class="member-hero member-reveal">
                <div class="member-hero-art"></div>
                <div class="member-hero-copy">
                    <div class="member-eyebrow">{{ isRtl ? 'خصوصيتك أولاً' : 'YOUR PRIVACY, YOUR CHOICE' }}</div>
                    <h1>{{ trans('image_requests.page_title') }}<span>.</span></h1>
                    <p>
                        {{
                            isRtl
                                ? 'راجع طلبات مشاهدة صورك واختر ما يناسبك بكل راحة.'
                                : 'Review requests to view your photos and choose what feels right for you.'
                        }}
                    </p>
                </div>
                <div class="member-hero-stat">
                    <strong>{{ pendingRequests.length }}</strong
                    ><span>{{ trans('image_requests.pending') }}</span>
                </div>
            </section>
            <section v-if="pendingRequests.length" class="request-section member-reveal">
                <div class="member-section-head">
                    <div>
                        <small>{{ isRtl ? 'بانتظار قرارك' : 'NEEDS YOUR ATTENTION' }}</small>
                        <h2>{{ trans('image_requests.pending') }}</h2>
                    </div>
                    <span class="member-count">{{ pendingRequests.length }}</span>
                </div>
                <div class="request-list">
                    <article v-for="req in pendingRequests" :key="req.id" class="request-row">
                        <button type="button" class="request-avatar" :aria-label="req.requester.username" @click="openProfile(req.requester.id)">
                            <img
                                :src="req.requester.mainProfileImage || '/assets/media/auth/no-image-for-user.png'"
                                :alt="req.requester.username"
                            /><span v-if="req.requester.is_online" class="request-online"></span>
                        </button>
                        <div class="request-info">
                            <strong>{{ req.requester.username }}</strong
                            ><span>{{ req.requester.age }} {{ trans('image_requests.years_old') }} · {{ formatDate(req.created_at) }}</span
                            ><small class="request-status pending">{{ trans('image_requests.pending') }}</small>
                        </div>
                        <div class="request-actions">
                            <button type="button" class="request-button approve" :disabled="loadingId === req.id" @click="approve(req)">
                                <i class="ki-outline ki-check"></i>{{ trans('image_requests.approve') }}</button
                            ><button type="button" class="request-button reject" :disabled="loadingId === req.id" @click="reject(req)">
                                <i class="ki-outline ki-cross"></i>{{ trans('image_requests.reject') }}
                            </button>
                        </div>
                    </article>
                </div>
            </section>
            <section v-if="resolvedRequests.length" class="request-section member-reveal">
                <div class="member-section-head">
                    <div>
                        <small>{{ isRtl ? 'الطلبات السابقة' : 'REQUEST HISTORY' }}</small>
                        <h2>{{ trans('image_requests.approved') }} / {{ trans('image_requests.rejected') }}</h2>
                    </div>
                    <span class="member-count">{{ resolvedRequests.length }}</span>
                </div>
                <div class="request-list">
                    <article v-for="req in resolvedRequests" :key="req.id" class="request-row">
                        <button type="button" class="request-avatar" :aria-label="req.requester.username" @click="openProfile(req.requester.id)">
                            <img :src="req.requester.mainProfileImage || '/assets/media/auth/no-image-for-user.png'" :alt="req.requester.username" />
                        </button>
                        <div class="request-info">
                            <strong>{{ req.requester.username }}</strong
                            ><span>{{ req.requester.age }} {{ trans('image_requests.years_old') }} · {{ formatDate(req.created_at) }}</span
                            ><small class="request-status" :class="req.status">{{
                                req.status === 'approved' ? trans('image_requests.approved') : trans('image_requests.rejected')
                            }}</small>
                        </div>
                        <div class="request-actions">
                            <button
                                type="button"
                                class="request-button delete"
                                :disabled="loadingId === req.id"
                                :title="trans('image_requests.delete_confirm')"
                                @click="deleteRequest(req)"
                            >
                                <i class="ki-outline ki-trash"></i>{{ trans('image_requests.delete') }}
                            </button>
                        </div>
                    </article>
                </div>
            </section>
            <div v-if="!requests.length" class="member-empty request-empty">
                <div class="member-empty-icon"><i class="ki-outline ki-picture"></i></div>
                <h3>{{ trans('image_requests.no_requests') }}</h3>
                <p>{{ isRtl ? 'ستظهر طلبات مشاهدة الصور هنا عند وصولها.' : 'Requests to view your photos will appear here.' }}</p>
            </div>
        </div>
        <UserProfileModal :is-open="showProfile" :user-id="selectedProfileUserId" @close="closeProfile" />
    </div>
</template>
