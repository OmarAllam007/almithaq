<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
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
defineProps<{ pendingUsers: PendingUser[] }>();
const video = ref<PendingUser | null>(null);
const review = ref<{ user: PendingUser; action: 'approve' | 'reject' } | null>(null);
const note = ref('');
const error = ref('');
const busy = ref(false);
const openReview = (user: PendingUser, action: 'approve' | 'reject') => {
    review.value = { user, action };
    note.value = '';
    error.value = '';
};
const confirm = () => {
    if (!review.value) return;
    if (review.value.action === 'reject' && note.value.trim().length < 5) {
        error.value = 'Please add a review note of at least 5 characters.';
        return;
    }
    busy.value = true;
    router.post(
        route(`admin.verifications.${review.value.action}`, review.value.user.id),
        review.value.action === 'reject' ? { note: note.value.trim() } : {},
        {
            preserveScroll: true,
            onSuccess: () => {
                review.value = null;
                video.value = null;
            },
            onError: (errors) => {
                error.value = errors.note || 'The review could not be saved.';
            },
            onFinish: () => (busy.value = false),
        },
    );
};
</script>
<template>
    <main class="admin-page">
        <div class="admin-shell">
            <header class="admin-hero">
                <div>
                    <small>CONTROL CENTER / MODERATION</small>
                    <h1>Video verifications</h1>
                    <p>Review a member's verification video before approving their profile.</p>
                </div>
                <Link :href="route('admin.approvals.index')" class="admin-hero-action">Image approvals</Link>
            </header>
            <div class="admin-panel">
                <div class="admin-panel-head">
                    <h2>Waiting for review</h2>
                    <span class="admin-pill warn">{{ pendingUsers.length }} pending</span>
                </div>
                <div v-if="pendingUsers.length" class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Contact</th>
                                <th>Submitted</th>
                                <th>Video</th>
                                <th>Decision</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in pendingUsers" :key="user.id">
                                <td>
                                    <strong>{{ user.name || user.username }}</strong
                                    ><small>#{{ user.id }} · @{{ user.username }}</small>
                                </td>
                                <td>{{ user.email || user.phone_number || '—' }}</td>
                                <td>{{ user.updated_at }}</td>
                                <td>
                                    <button v-if="user.video_url" class="admin-button" @click="video = user">
                                        <i class="ki-outline ki-play"></i> Watch</button
                                    ><span v-else class="admin-pill bad">Unavailable</span>
                                </td>
                                <td>
                                    <div class="admin-actions">
                                        <button class="admin-button primary" :disabled="!user.video_url" @click="openReview(user, 'approve')">
                                            Approve</button
                                        ><button class="admin-button danger" @click="openReview(user, 'reject')">Reject</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="admin-empty"><i class="ki-outline ki-shield-tick"></i>No videos need review.</div>
            </div>
        </div>
    </main>
    <div v-if="video" class="admin-dialog-backdrop" @click.self="video = null">
        <div class="admin-dialog video-review" role="dialog" aria-modal="true">
            <div class="admin-panel-head">
                <h2>{{ video.name || video.username }} · verification video</h2>
                <button class="admin-button" @click="video = null">Close</button>
            </div>
            <video v-if="video.video_url" :src="video.video_url" controls autoplay playsinline></video>
            <div class="admin-dialog-actions">
                <button class="admin-button danger" @click="openReview(video, 'reject')">Reject</button
                ><button class="admin-button primary" @click="openReview(video, 'approve')">Approve</button>
            </div>
        </div>
    </div>
    <div v-if="review" class="admin-dialog-backdrop" @click.self="review = null">
        <div class="admin-dialog" role="alertdialog" aria-modal="true">
            <h2>{{ review.action === 'approve' ? 'Approve' : 'Reject' }} verification?</h2>
            <p>
                {{ review.user.name || review.user.username }} ·
                {{
                    review.action === 'reject'
                        ? 'The video will be removed so the member can submit a new one.'
                        : 'The member will receive verified status.'
                }}
            </p>
            <div v-if="review.action === 'reject'" class="admin-field">
                <label for="review-note">Review note for the member</label
                ><textarea id="review-note" v-model="note" rows="4" maxlength="1000" placeholder="Explain what needs to change"></textarea
                ><small v-if="error" class="text-danger">{{ error }}</small>
            </div>
            <div class="admin-dialog-actions">
                <button class="admin-button" @click="review = null">Cancel</button
                ><button :class="['admin-button', review.action === 'reject' ? 'danger' : 'primary']" :disabled="busy" @click="confirm">
                    {{ busy ? 'Saving…' : 'Confirm' }}
                </button>
            </div>
        </div>
    </div>
</template>
<style scoped>
.video-review {
    width: min(100%, 800px);
    padding: 0;
    overflow: hidden;
}
.video-review video {
    display: block;
    width: 100%;
    max-height: 62vh;
    background: #1e1720;
}
.video-review .admin-dialog-actions {
    padding: 12px 18px;
    margin: 0;
}
</style>
