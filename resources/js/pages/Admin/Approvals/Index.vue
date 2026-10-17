<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import type { AdminPagination } from '@/pages/Admin/types';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';
interface ImageRow {
    id: number;
    thumbnail_url: string;
    is_main: boolean;
    created_at: string;
    user: { id: number; name: string; username: string } | null;
}
const props = defineProps<{ images: AdminPagination<ImageRow> }>();
const selected = ref<{ image: ImageRow; action: 'approve' | 'reject' } | null>(null);
const busy = ref(false);
const confirm = () => {
    if (!selected.value) return;
    busy.value = true;
    router.patch(
        route(`admin.approvals.${selected.value.action}`, selected.value.image.id),
        {},
        { preserveScroll: true, onSuccess: () => (selected.value = null), onFinish: () => (busy.value = false) },
    );
};
</script>
<template>
    <main class="admin-page">
        <div class="admin-shell">
            <header class="admin-hero">
                <div>
                    <small>CONTROL CENTER / MODERATION</small>
                    <h1>Image approvals</h1>
                    <p>Review uploaded member photos before they appear to others.</p>
                </div>
                <Link :href="route('admin.verifications.index')" class="admin-hero-action">Video verifications</Link>
            </header>
            <div class="admin-panel">
                <div class="admin-panel-head">
                    <h2>Awaiting review</h2>
                    <span class="admin-pill warn">{{ images.total }} images</span>
                </div>
                <div v-if="images.data.length" class="approval-grid">
                    <article v-for="image in images.data" :key="image.id" class="approval-card">
                        <img :src="image.thumbnail_url" :alt="`Uploaded by ${image.user?.name || image.user?.username}`" />
                        <div>
                            <strong>{{ image.user?.name || image.user?.username || 'Member' }}</strong
                            ><small>@{{ image.user?.username }} · {{ image.created_at?.slice(0, 10) }}</small
                            ><span v-if="image.is_main" class="admin-pill">Main photo</span>
                        </div>
                        <div class="approval-actions">
                            <button class="admin-button primary" @click="selected = { image, action: 'approve' }">Approve</button
                            ><button class="admin-button danger" @click="selected = { image, action: 'reject' }">Reject</button>
                        </div>
                    </article>
                </div>
                <div v-else class="admin-empty"><i class="ki-outline ki-check-circle"></i>All images have been reviewed.</div>
                <div v-if="images.last_page > 1" class="admin-panel-body"><Pagination :data="images" /></div>
            </div>
        </div>
    </main>
    <div v-if="selected" class="admin-dialog-backdrop" @click.self="selected = null">
        <div class="admin-dialog" role="alertdialog" aria-modal="true">
            <h2>{{ selected.action === 'approve' ? 'Approve' : 'Reject' }} image?</h2>
            <p>{{ selected.action === 'reject' ? 'Rejecting deletes this upload.' : 'The image will become visible to members.' }}</p>
            <div class="admin-dialog-actions">
                <button class="admin-button" @click="selected = null">Cancel</button
                ><button :class="['admin-button', selected.action === 'reject' ? 'danger' : 'primary']" :disabled="busy" @click="confirm">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</template>
<style scoped>
.approval-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    padding: 20px;
}
.approval-card {
    border: 1px solid #efdee5;
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
}
.approval-card img {
    display: block;
    width: 100%;
    height: 180px;
    object-fit: cover;
}
.approval-card > div:not(.approval-actions) {
    padding: 13px;
}
.approval-card strong,
.approval-card small {
    display: block;
}
.approval-card strong {
    color: #422b38;
}
.approval-card small {
    color: #9a7e8c;
    font-size: 10px;
    margin: 4px 0 9px;
}
.approval-actions {
    display: flex;
    gap: 7px;
    padding: 0 13px 13px;
}
.approval-actions button {
    flex: 1;
}
</style>
