<script setup lang="ts">
import Header from '@/pages/_shared/Header.vue';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, watch } from 'vue';
import { usePresenceStore } from '@/stores/useUserStore';
import AdminSidebar from '@/pages/Admin/AdminSidebar.vue';

const page = usePage();
const isAuth = computed(() => page.props.auth && page.props.auth?.user !== null);
const isRtl = computed(() => page.props.locale === 'ar');
const direction = computed(() => isRtl.value ? 'rtl' : 'ltr');

const presence = usePresenceStore();

// Update document direction for full RTL support
const updateDocumentDirection = () => {
    document.documentElement.setAttribute('dir', direction.value);
    document.documentElement.setAttribute('lang', page.props.locale as string || 'en');
    document.body.setAttribute('dir', direction.value);
};

onMounted(() => {
    // Only initialize presence for authenticated users
    if (isAuth.value) {
        presence.initializePresence();
    }

    // Set initial document direction
    updateDocumentDirection();
});

// Watch for locale changes
watch(() => page.props.locale, () => {
    updateDocumentDirection();
});
</script>

<template>
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root" :dir="$page.props.locale === 'ar' ? 'rtl' : 'ltr'">
        <!--begin::Page-->
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            <Header />
            <div class="flex-column flex-row-fluid" :class="{ 'app-wrapper': isAuth }" id="kt_app_wrapper">
                <AdminSidebar v-if="isAuth" />
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped></style>
