<script setup lang="ts">
import { ref } from 'vue';

interface Props {
    title: string;
    isEditing?: boolean;
}

defineProps<Props>();
</script>

<template>
    <div class="card mb-xl-10 rounded-1 mb-5">
        <div class="card-header cursor-pointer">
            <div class="card-title m-0">
                <h3 class="fw-bold m-0"> {{  title }} </h3>
            </div>
        </div>
        <div class="card-body p-9">
            <slot />
        </div>
    </div>
</template>
