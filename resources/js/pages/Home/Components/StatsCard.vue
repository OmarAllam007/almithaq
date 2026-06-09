<script setup lang="ts">
import { useLang } from '@/composables/useLang';
const { trans } = useLang();
interface Props {
    profileViews: number;
    likes: number;
    conversations: number;
    daysSinceJoined: number;
}

defineProps<Props>();
</script>

<template>
    <div class="col-xl-12 mb-8">
        <p class="fs-2 fw-bold mb-5">{{trans('home.stats')}}</p>
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="stat-card rounded-3 bg-white px-6 py-5 shadow-sm d-flex align-items-center gap-4">
                    <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#fff0f7;">
                        <i class="ki-outline ki-eye fs-2" style="color:#d02e79;"></i>
                    </div>
                    <div>
                        <span class="fw-bolder d-block fs-2x lh-1 text-gray-800">{{ profileViews }}</span>
                        <span class="fw-bold fs-7 text-gray-500 mt-1 d-block">{{trans('home.profile_views')}}</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="stat-card rounded-3 bg-white px-6 py-5 shadow-sm d-flex align-items-center gap-4">
                    <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#fff0f7;">
                        <i class="ki-solid ki-heart-circle fs-2" style="color:#d02e79;"></i>
                    </div>
                    <div>
                        <span class="fw-bolder d-block fs-2x lh-1 text-gray-800">{{ likes }}</span>
                        <span class="fw-semibold fs-7 text-gray-500 mt-1 d-block">{{trans('home.liked_you')}}</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="stat-card rounded-3 bg-white px-6 py-5 shadow-sm d-flex align-items-center gap-4">
                    <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#f0f4ff;">
                        <i class="ki-outline ki-message-text-2 fs-2 text-primary"></i>
                    </div>
                    <div>
                        <span class="fw-bolder d-block fs-2x lh-1 text-gray-800">{{ conversations }}</span>
                        <span class="fw-semibold fs-7 text-gray-500 mt-1 d-block">{{trans('home.conversations')}}</span>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="stat-card rounded-3 bg-white px-6 py-5 shadow-sm d-flex align-items-center gap-4">
                    <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#fffbf0;">
                        <i class="ki-outline ki-calendar fs-2 text-warning"></i>
                    </div>
                    <div>
                        <span class="fw-bolder d-block fs-2x lh-1 text-gray-800">{{ daysSinceJoined }}</span>
                        <span class="fw-semibold fs-7 text-gray-500 mt-1 d-block">{{trans('home.days_since_joined')}}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.stat-card {
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    cursor: default;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(208, 46, 121, 0.15) !important;
}

.stat-card:hover .stat-icon {
    transform: scale(1.12);
    transition: transform 0.25s ease;
}

.stat-icon {
    transition: transform 0.25s ease;
}
</style>
