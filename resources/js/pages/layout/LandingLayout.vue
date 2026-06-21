<script setup lang="ts">
import AdBlockDetector from '@/components/AdBlockDetector.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link
            href="https://fonts.bunny.net/css?family=cormorant-garamond:400,500,600,600i,700"
            rel="stylesheet"
        />
    </Head>

    <AdBlockDetector />
    <slot />
</template>
