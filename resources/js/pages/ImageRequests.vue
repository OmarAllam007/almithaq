<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { useLang } from '@/composables/useLang';

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
const emit = defineEmits<{ viewProfile: [userId: number] }>();

const requests = ref<ImageRequest[]>(props.requests);
const loadingId = ref<number | null>(null);

const pendingRequests = computed(() => requests.value.filter(r => r.status === 'pending'));
const resolvedRequests = computed(() => requests.value.filter(r => r.status !== 'pending'));

const formatDate = (dateStr: string) => {
    const d = new Date(dateStr);
    return d.toLocaleDateString(isRtl.value ? 'ar-SA' : 'en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
    });
};

const approve = async (request: ImageRequest) => {
    loadingId.value = request.id;
    try {
        await axios.patch(`/image-requests/${request.id}/approve`);
        const found = requests.value.find(r => r.id === request.id);
        if (found) { found.status = 'approved'; }
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
        const found = requests.value.find(r => r.id === request.id);
        if (found) { found.status = 'rejected'; }
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
        requests.value = requests.value.filter(r => r.id !== request.id);
    } catch (e) {
        console.error(e);
    } finally {
        loadingId.value = null;
    }
};
</script>

<template>
    <div class="ir-page">
        <div class="ir-header">
            <h2 class="ir-title">
                <i class="ki-outline ki-picture fs-2 me-2"></i>
                {{ trans('image_requests.page_title') }}
            </h2>
            <span v-if="pendingRequests.length > 0" class="ir-pending-badge">
                {{ pendingRequests.length }}
            </span>
        </div>

        <!-- Pending Requests -->
        <section v-if="pendingRequests.length > 0" class="ir-section">
            <h4 class="ir-section-title">
                <span class="ir-dot ir-dot--pending"></span>
                {{ trans('image_requests.pending') }}
                <span class="ir-count">{{ pendingRequests.length }}</span>
            </h4>
            <div class="ir-list">
                <div
                    v-for="req in pendingRequests"
                    :key="req.id"
                    class="ir-card"
                >
                    <!-- Avatar -->
                    <div class="ir-card__avatar" @click="$emit('viewProfile', req.requester.id)">
                        <img
                            :src="req.requester.mainProfileImage || '/assets/media/auth/no-image-for-user.png'"
                            :alt="req.requester.username"
                            class="ir-card__img"
                        />
                        <span
                            class="ir-card__online"
                            :class="req.requester.is_online ? 'ir-card__online--on' : 'ir-card__online--off'"
                        ></span>
                    </div>

                    <!-- Info -->
                    <div class="ir-card__info">
                        <div class="ir-card__name">{{ req.requester.username }}</div>
                        <div class="ir-card__meta">
                            {{ req.requester.age }} {{ trans('image_requests.years_old') }}
                            <span class="ir-card__sep">·</span>
                            <i class="ki-outline ki-calendar fs-7"></i>
                            {{ formatDate(req.created_at) }}
                        </div>
                        <span class="ir-badge ir-badge--pending">
                            {{ trans('image_requests.pending') }}
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="ir-card__actions">
                        <button
                            class="ir-btn ir-btn--approve"
                            :disabled="loadingId === req.id"
                            @click="approve(req)"
                        >
                            <i class="ki-outline ki-check fs-5"></i>
                            {{ trans('image_requests.approve') }}
                        </button>
                        <button
                            class="ir-btn ir-btn--reject"
                            :disabled="loadingId === req.id"
                            @click="reject(req)"
                        >
                            <i class="ki-outline ki-cross fs-5"></i>
                            {{ trans('image_requests.reject') }}
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Resolved Requests -->
        <section v-if="resolvedRequests.length > 0" class="ir-section">
            <h4 class="ir-section-title">
                <span class="ir-dot ir-dot--resolved"></span>
                {{ trans('image_requests.approved') }} / {{ trans('image_requests.rejected') }}
                <span class="ir-count">{{ resolvedRequests.length }}</span>
            </h4>
            <div class="ir-list">
                <div
                    v-for="req in resolvedRequests"
                    :key="req.id"
                    class="ir-card ir-card--resolved"
                >
                    <div class="ir-card__avatar" @click="$emit('viewProfile', req.requester.id)">
                        <img
                            :src="req.requester.mainProfileImage || '/assets/media/auth/no-image-for-user.png'"
                            :alt="req.requester.username"
                            class="ir-card__img"
                        />
                    </div>

                    <div class="ir-card__info">
                        <div class="ir-card__name">{{ req.requester.username }}</div>
                        <div class="ir-card__meta">
                            {{ req.requester.age }} {{ trans('image_requests.years_old') }}
                            <span class="ir-card__sep">·</span>
                            {{ formatDate(req.created_at) }}
                        </div>
                        <span
                            class="ir-badge"
                            :class="req.status === 'approved' ? 'ir-badge--approved' : 'ir-badge--rejected'"
                        >
                            {{ req.status === 'approved' ? trans('image_requests.approved') : trans('image_requests.rejected') }}
                        </span>
                    </div>

                    <div class="ir-card__actions">
                        <button
                            class="ir-btn ir-btn--delete"
                            :disabled="loadingId === req.id"
                            :title="trans('image_requests.delete_confirm')"
                            @click="deleteRequest(req)"
                        >
                            <i class="ki-outline ki-trash fs-5"></i>
                            {{ trans('image_requests.delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Empty state -->
        <div v-if="requests.length === 0" class="ir-empty">
            <i class="ki-outline ki-picture fs-2x text-gray-400 mb-3"></i>
            <p class="text-muted">{{ trans('image_requests.no_requests') }}</p>
        </div>
    </div>
</template>

<style scoped>
.ir-page {
    padding: 24px 16px;
    max-width: 760px;
    margin: 0 auto;
}

.ir-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 28px;
}

.ir-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
    display: flex;
    align-items: center;
}

.ir-pending-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 24px;
    height: 24px;
    padding: 0 6px;
    border-radius: 12px;
    background: rgb(208, 46, 121);
    color: #fff;
    font-size: 0.75rem;
    font-weight: 700;
}

.ir-section {
    margin-bottom: 32px;
}

.ir-section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #6b7280;
    margin-bottom: 14px;
}

.ir-count {
    margin-inline-start: auto;
    font-size: 0.75rem;
    font-weight: 600;
    color: #9ca3af;
}

.ir-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.ir-dot--pending { background: #f59e0b; }
.ir-dot--resolved { background: #9ca3af; }

.ir-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ir-card {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #fff;
    border-radius: 14px;
    padding: 14px 16px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.07);
    border: 1px solid #f3f4f6;
    transition: box-shadow 0.2s ease;
}

.ir-card:hover {
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.1);
}

.ir-card--resolved {
    opacity: 0.82;
}

.ir-card__avatar {
    position: relative;
    flex-shrink: 0;
    cursor: pointer;
}

.ir-card__img {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e5e7eb;
    display: block;
}

.ir-card__online {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 11px;
    height: 11px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.ir-card__online--on { background: #22c55e; }
.ir-card__online--off { background: #9ca3af; }

.ir-card__info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.ir-card__name {
    font-weight: 700;
    font-size: 0.95rem;
    color: #111827;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ir-card__meta {
    font-size: 0.75rem;
    color: #6b7280;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
}

.ir-card__sep {
    color: #d1d5db;
}

.ir-card__actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}

.ir-badge {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 10px;
    width: fit-content;
}

.ir-badge--pending {
    background: #fef3c7;
    color: #d97706;
    border: 1px solid rgba(217, 119, 6, 0.3);
}

.ir-badge--approved {
    background: #f0fdf4;
    color: #16a34a;
    border: 1px solid rgba(22, 163, 74, 0.25);
}

.ir-badge--rejected {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid rgba(220, 38, 38, 0.25);
}

.ir-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 7px 14px;
    border-radius: 8px;
    font-size: 0.78rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.18s ease;
    white-space: nowrap;
}

.ir-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.ir-btn--approve {
    background: #f0fdf4;
    color: #16a34a;
    border: 1px solid rgba(22, 163, 74, 0.3);
}

.ir-btn--approve:hover:not(:disabled) {
    background: #16a34a;
    color: #fff;
}

.ir-btn--reject {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid rgba(220, 38, 38, 0.3);
}

.ir-btn--reject:hover:not(:disabled) {
    background: #dc2626;
    color: #fff;
}

.ir-btn--delete {
    background: #f9fafb;
    color: #6b7280;
    border: 1px solid #e5e7eb;
}

.ir-btn--delete:hover:not(:disabled) {
    background: #f3f4f6;
    color: #374151;
}

.ir-empty {
    text-align: center;
    padding: 60px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

@media (max-width: 480px) {
    .ir-card {
        flex-wrap: wrap;
    }

    .ir-card__actions {
        width: 100%;
        justify-content: flex-end;
    }
}

[data-bs-theme="dark"] .ir-card {
    background: #1e1e2d;
    border-color: #2d2d3f;
}

[data-bs-theme="dark"] .ir-title,
[data-bs-theme="dark"] .ir-card__name {
    color: #cdcfda;
}
</style>
