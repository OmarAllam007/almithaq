<script setup lang="ts">
defineProps({
    title: String,
})
</script>

<template>
    <div class="d-flex align-items-center mb-7">
        <span class="fw-semibold fs-5 flex-grow-1 text-gray-700"> {{ title }}   </span>
        <i class="ki-outline ki-check-circle fs-1 text-success"></i>
    </div>
</template>

<style scoped>

</style>
