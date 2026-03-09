<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

interface PendingUser {
    id: number;
    name: string;
    username: string;
    email: string;
    phone_number: string;
    video_url: string | null;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    pendingUsers: PendingUser[];
}>();

const processing = ref(false);
const activeVideoUser = ref<PendingUser | null>(null);
const confirmingReject = ref<number | null>(null);

const showVideo = (user: PendingUser) => {
    activeVideoUser.value = user;
};

const closeVideo = () => {
    activeVideoUser.value = null;
};

const approveUser = (userId: number) => {
    processing.value = true;
    router.post(route('admin.verifications.approve', userId), {}, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
        },
    });
};

const rejectUser = (userId: number) => {
    processing.value = true;
    router.post(route('admin.verifications.reject', userId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            confirmingReject.value = null;
            if (activeVideoUser.value?.id === userId) {
                activeVideoUser.value = null;
            }
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>

<template>
    <div>
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                <!-- Page Header -->
                <div class="card mb-5 mb-xl-10">
                    <div class="card-body pt-9 pb-0">
                        <div class="d-flex flex-wrap flex-sm-nowrap mb-6">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="text-gray-900 fs-2 fw-bold me-3">
                                                <i class="ki-outline ki-shield-tick fs-2 me-2"></i>
                                                Video Verifications
                                            </span>
                                            <span class="badge badge-light-warning fs-7 fw-semibold">
                                                {{ pendingUsers.length }} pending
                                            </span>
                                        </div>
                                        <div class="d-flex flex-wrap fw-semibold mb-4 fs-5 text-gray-500">
                                            Review and approve user verification videos
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Users Table -->
                <div class="card" v-if="pendingUsers.length > 0">
                    <div class="card-body py-4">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="admin_verifications_table">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-50px">ID</th>
                                        <th class="min-w-150px">User</th>
                                        <th class="min-w-120px">Contact</th>
                                        <th class="min-w-100px">Submitted</th>
                                        <th class="min-w-100px">Video</th>
                                        <th class="min-w-120px text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 fw-semibold">
                                    <tr v-for="user in pendingUsers" :key="user.id">
                                        <!-- ID -->
                                        <td>
                                            <span class="text-gray-800 fw-bold">#{{ user.id }}</span>
                                        </td>

                                        <!-- User Info -->
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 fw-bold mb-1">
                                                    {{ user.name || user.username || '—' }}
                                                </span>
                                                <span class="text-gray-500 fs-7">@{{ user.username || '—' }}</span>
                                            </div>
                                        </td>

                                        <!-- Contact -->
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-700 fs-7">{{ user.email || '—' }}</span>
                                                <span class="text-gray-500 fs-8">{{ user.phone_number || '—' }}</span>
                                            </div>
                                        </td>

                                        <!-- Submitted At -->
                                        <td>
                                            <span class="text-gray-600 fs-7">{{ user.updated_at }}</span>
                                        </td>

                                        <!-- Video Preview -->
                                        <td>
                                            <button
                                                v-if="user.video_url"
                                                @click="showVideo(user)"
                                                class="btn btn-sm btn-light-primary d-flex align-items-center gap-2"
                                            >
                                                <i class="ki-outline ki-eye fs-5"></i>
                                                Watch
                                            </button>
                                            <span v-else class="badge badge-light-danger">No Video</span>
                                        </td>

                                        <!-- Actions -->
                                        <td class="text-end">
                                            <div v-if="confirmingReject !== user.id" class="d-flex justify-content-end gap-2">
                                                <button
                                                    @click="approveUser(user.id)"
                                                    class="btn btn-sm btn-light-success"
                                                    :disabled="processing"
                                                    title="Approve"
                                                >
                                                    <i class="ki-outline ki-check-circle fs-4 me-1"></i>
                                                    Approve
                                                </button>
                                                <button
                                                    @click="confirmingReject = user.id"
                                                    class="btn btn-sm btn-light-danger"
                                                    :disabled="processing"
                                                    title="Reject"
                                                >
                                                    <i class="ki-outline ki-cross-circle fs-4 me-1"></i>
                                                    Reject
                                                </button>
                                            </div>

                                            <!-- Confirm Reject -->
                                            <div v-else class="d-flex justify-content-end align-items-center gap-2">
                                                <span class="text-danger fs-8 fw-semibold">Reject?</span>
                                                <button
                                                    @click="rejectUser(user.id)"
                                                    class="btn btn-sm btn-danger py-1 px-3"
                                                    :disabled="processing"
                                                >
                                                    Yes
                                                </button>
                                                <button
                                                    @click="confirmingReject = null"
                                                    class="btn btn-sm btn-light py-1 px-3"
                                                >
                                                    No
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="card">
                    <div class="card-body text-center py-20">
                        <div class="mb-10">
                            <i class="ki-outline ki-shield-tick fs-5x text-gray-400"></i>
                        </div>
                        <h3 class="text-gray-800 mb-3">No Pending Verifications</h3>
                        <p class="text-gray-600 fs-5">
                            All users are either verified or haven't uploaded a verification video yet.
                        </p>
                    </div>
                </div>

                <!-- Video Player Modal -->
                <div v-if="activeVideoUser" class="video-modal-overlay" @click.self="closeVideo">
                    <div class="video-modal-content card shadow-lg border-0">
                        <div class="card-header d-flex justify-content-between align-items-center py-4">
                            <div>
                                <h3 class="card-title fw-bold mb-0">
                                    Verification Video — {{ activeVideoUser.name || activeVideoUser.username }}
                                </h3>
                                <span class="text-gray-500 fs-7">#{{ activeVideoUser.id }}</span>
                            </div>
                            <button @click="closeVideo" class="btn btn-icon btn-sm btn-light-danger">
                                <i class="ki-outline ki-cross fs-3"></i>
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <video
                                v-if="activeVideoUser.video_url"
                                :src="activeVideoUser.video_url"
                                controls
                                autoplay
                                class="w-100"
                                style="max-height: 60vh; background: #000"
                            ></video>
                        </div>
                        <div class="card-footer d-flex justify-content-end gap-3 py-4">
                            <button
                                @click="rejectUser(activeVideoUser.id)"
                                class="btn btn-danger"
                                :disabled="processing"
                            >
                                <i class="ki-outline ki-cross-circle fs-3 me-1"></i>
                                Reject
                            </button>
                            <button
                                @click="approveUser(activeVideoUser.id)"
                                class="btn btn-success"
                                :disabled="processing"
                            >
                                <i class="ki-outline ki-check-circle fs-3 me-1"></i>
                                Approve
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
.table td {
    vertical-align: middle;
}

/* Video Modal */
.video-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    backdrop-filter: blur(4px);
    animation: fadeIn 0.2s ease;
}

.video-modal-content {
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow: hidden;
    border-radius: 0.75rem;
    animation: slideUp 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
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
