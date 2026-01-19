<script setup lang="ts">
import Header from '@/pages/_shared/Header.vue';
import Sidebar from '@/pages/_shared/Sidebar.vue';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { usePresenceStore } from '@/stores/useUserStore';

const page = usePage();
const isAuth = computed(() => page.props.auth?.user !== null);

const presence = usePresenceStore();

onMounted(() => {
    // Only initialize presence for authenticated users
    if (isAuth.value) {
        presence.initializePresence();
    }
});
</script>

<template>
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <!--begin::Page-->
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            <Header />

            <div class="flex-column flex-row-fluid" :class="{ 'app-wrapper': isAuth }" id="kt_app_wrapper">
                <Sidebar v-if="isAuth" />
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped></style>
