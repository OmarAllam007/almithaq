<script setup lang="ts">
defineProps<{ title: string; isEditing?: boolean }>();
</script>

<template>
    <section class="profile-section">
        <div class="profile-section-head">
            <span class="section-accent" aria-hidden="true"></span>
            <h2>{{ title }}</h2>
        </div>
        <div class="profile-section-body"><slot /></div>
    </section>
</template>

<style scoped>
.profile-section {
    min-width: 0;
    margin-bottom: 17px;
    overflow: hidden;
    border: 1px solid #eee1e5;
    border-radius: 17px;
    background: #fff;
    box-shadow: 0 7px 23px #6330440d;
}
.profile-section-head {
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 63px;
    padding: 17px 22px;
    border-bottom: 1px solid #f2e9eb;
}
.section-accent {
    width: 7px;
    height: 24px;
    border-radius: 7px;
    background: #ae5275;
}
.profile-section-head h2 {
    margin: 0;
    color: #3f2936;
    font-size: 16px;
    font-weight: 800;
}
.profile-section-body {
    padding: 22px;
}
.profile-section-body :deep(.row) {
    --bs-gutter-x: 18px;
}
.profile-section-body :deep(.row:last-child .profile-field) {
    margin-bottom: 0;
}
@media (max-width: 767px) {
    .profile-section-head {
        padding: 15px 17px;
    }
    .profile-section-body {
        padding: 18px 17px;
    }
}
</style>
