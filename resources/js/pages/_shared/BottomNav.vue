<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, computed } from 'vue';
import { useLang } from '@/composables/useLang';

const { trans } = useLang();
const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');
const membersOpen = ref(false);
const pendingImageRequests = computed(() => (page.props as any).pending_image_requests ?? 0);

const toggleMembers = () => {
    membersOpen.value = !membersOpen.value;
};

const closeMembers = () => {
    membersOpen.value = false;
};
</script>

<template>
    <!--begin::Mobile Bottom Nav-->
    <div class="d-flex d-lg-none bottom-nav" @click.self="closeMembers">
        <!--begin::Members Popup-->
        <Transition name="slide-up">
            <div v-if="membersOpen" class="members-popup">
                <div class="members-popup-header">
                    <span class="fw-bold fs-6 text-gray-800">{{ trans('sidebar.Members') }}</span>
                    <button class="btn btn-sm btn-icon btn-light-danger" @click="closeMembers">
                        <i class="ki-outline ki-cross fs-4"></i>
                    </button>
                </div>
                <div class="members-popup-grid">
                    <Link :href="route('home')" class="members-popup-item" @click="closeMembers">
                        <div class="members-popup-icon bg-light-primary">
                            <i class="ki-outline ki-people fs-2 text-primary"></i>
                        </div>
                        <span class="fs-8 fw-semibold text-gray-700 mt-2">{{ trans('sidebar.All Members') }}</span>
                    </Link>
                    <Link :href="route('members-online')" class="members-popup-item" @click="closeMembers">
                        <div class="members-popup-icon bg-light-success">
                            <i class="ki-outline ki-wifi fs-2 text-success"></i>
                        </div>
                        <span class="fs-8 fw-semibold text-gray-700 mt-2">{{ trans('sidebar.Online') }}</span>
                    </Link>
                    <Link :href="route('new-members')" class="members-popup-item" @click="closeMembers">
                        <div class="members-popup-icon bg-light-info">
                            <i class="ki-outline ki-user-tick fs-2 text-info"></i>
                        </div>
                        <span class="fs-8 fw-semibold text-gray-700 mt-2">{{ trans('sidebar.New Members') }}</span>
                    </Link>
                    <Link :href="route('smart-search.index')" class="members-popup-item" @click="closeMembers">
                        <div class="members-popup-icon" style="background: rgba(246,211,101,0.15);">
                            <i class="ki-outline ki-crown fs-2" style="color: #f6a623;"></i>
                        </div>
                        <span class="fs-8 fw-semibold text-gray-700 mt-2">{{ trans('sidebar.VIP Search') }}</span>
                    </Link>
                    <Link :href="route('image-requests.index')" class="members-popup-item position-relative" @click="closeMembers">
                        <div class="members-popup-icon bg-light-danger" style="position: relative;">
                            <i class="ki-outline ki-picture fs-2 text-danger"></i>
                            <span
                                v-if="pendingImageRequests > 0"
                                class="position-absolute top-0 end-0 translate-middle badge badge-circle badge-danger"
                                style="font-size: 8px; min-width: 16px; height: 16px; padding: 0 3px;"
                            >
                                {{ pendingImageRequests > 99 ? '99+' : pendingImageRequests }}
                            </span>
                        </div>
                        <span class="fs-8 fw-semibold text-gray-700 mt-2">{{ trans('sidebar.Image Requests') }}</span>
                    </Link>
                </div>
            </div>
        </Transition>

        <!--begin::Nav Items-->
        <nav class="bottom-nav-bar">
            <Link :href="route('home')" class="bottom-nav-item">
                <i class="ki-outline ki-home-2 fs-2"></i>
                <span>{{ trans('sidebar.Home') }}</span>
            </Link>

            <Link :href="route('inbox.index')" class="bottom-nav-item">
                <i class="ki-outline ki-messages fs-2"></i>
                <span>{{ trans('sidebar.inbox') }}</span>
            </Link>

            <button class="bottom-nav-item bottom-nav-center-btn" :class="{ active: membersOpen }" @click="toggleMembers">
                <div class="center-icon-wrap">
                    <i class="ki-outline ki-people fs-1"></i>
                </div>
                <span>{{ trans('sidebar.Members') }}</span>
            </button>

            <Link :href="route('smart-search.index')" class="bottom-nav-item">
                <i class="ki-outline ki-filter-search fs-2"></i>
                <span>{{ trans('sidebar.Search') }}</span>
            </Link>

            <Link :href="route('profile')" class="bottom-nav-item">
                <i class="ki-outline ki-user fs-2"></i>
                <span>{{ trans('sidebar.Profile') }}</span>
            </Link>
        </nav>
        <!--end::Nav Items-->
    </div>
    <!--end::Mobile Bottom Nav-->
</template>

<style scoped>
.bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    flex-direction: column;
}

.bottom-nav-bar {
    display: flex;
    align-items: center;
    justify-content: space-around;
    background: #fff;
    border-top: 1px solid #e4e6ef;
    padding: 8px 0 calc(8px + env(safe-area-inset-bottom));
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08);
}

.bottom-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    color: #a1a5b7;
    text-decoration: none;
    font-size: 10px;
    font-weight: 600;
    background: none;
    border: none;
    padding: 4px 8px;
    cursor: pointer;
    transition: color 0.2s;
    min-width: 56px;
}

.bottom-nav-item:hover,
.bottom-nav-item.active {
    color: var(--bs-primary, #d02e79);
}

.bottom-nav-item span {
    font-size: 10px;
    font-weight: 600;
    line-height: 1;
}

.bottom-nav-center-btn {
    position: relative;
    margin-top: -22px;
}

.center-icon-wrap {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: linear-gradient(135deg, #d02e79, #b8266a);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 16px rgba(208, 46, 121, 0.4);
    transition: transform 0.2s, box-shadow 0.2s;
}

.bottom-nav-center-btn .ki-outline {
    color: #fff;
}

.bottom-nav-center-btn.active .center-icon-wrap {
    transform: scale(1.08);
    box-shadow: 0 6px 20px rgba(208, 46, 121, 0.5);
    background: linear-gradient(135deg, #b8266a, #8f1d52);
}

/* Members popup */
.members-popup {
    background: #fff;
    border-top-left-radius: 20px;
    border-top-right-radius: 20px;
    padding: 20px 16px 12px;
    box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.12);
    border-top: 1px solid #e4e6ef;
}

.members-popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.members-popup-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

.members-popup-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    gap: 0;
}

.members-popup-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Slide-up transition */
.slide-up-enter-active,
.slide-up-leave-active {
    transition: transform 0.25s ease, opacity 0.25s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
    transform: translateY(100%);
    opacity: 0;
}

/* Dark mode */
[data-bs-theme="dark"] .bottom-nav-bar,
[data-bs-theme="dark"] .members-popup {
    background: #1e1e2d;
    border-color: #2d2d3f;
}

[data-bs-theme="dark"] .bottom-nav-item {
    color: #5e6278;
}

[data-bs-theme="dark"] .members-popup-header .fw-bold {
    color: #cdcfda;
}
</style>
