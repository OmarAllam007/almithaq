<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface Props {
    slotId: string;
    format?: 'auto' | 'rectangle' | 'horizontal' | 'vertical';
    fullWidthResponsive?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    format: 'auto',
    fullWidthResponsive: true,
});

const page = usePage();
const adsenseClient = page.props.adsense_client as string | null;
const adRef = ref<HTMLElement | null>(null);

onMounted(() => {
    if (!adsenseClient || !adRef.value) {
        return;
    }

    try {
        ((window as any).adsbygoogle = (window as any).adsbygoogle || []).push({});
    } catch {
        // adsbygoogle not yet loaded — will be pushed once script initialises
    }
});
</script>

<template>
    <!-- Live ad (adsense_client is set) -->
    <div v-if="adsenseClient" class="google-ad-wrapper">
        <ins
            ref="adRef"
            class="adsbygoogle"
            style="display:block"
            :data-ad-client="adsenseClient"
            :data-ad-slot="slotId"
            :data-ad-format="format"
            :data-full-width-responsive="fullWidthResponsive ? 'true' : 'false'"
        ></ins>
    </div>

    <!-- Dev placeholder (no adsense_client in env) -->
    <div
        v-else
        class="google-ad-placeholder d-flex align-items-center justify-content-center text-muted border border-dashed border-gray-300 rounded bg-light-subtle"
    >
        <div class="text-center py-2">
            <i class="ki-outline ki-picture fs-2 text-gray-400 mb-1 d-block"></i>
            <span class="fs-8 text-gray-400">Ad</span>
        </div>
    </div>
</template>

<style scoped>
.google-ad-wrapper {
    width: 100%;
    overflow: hidden;
}

.google-ad-placeholder {
    width: 100%;
    min-height: 90px;
}
</style>
