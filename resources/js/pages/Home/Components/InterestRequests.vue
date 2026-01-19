<script setup lang="ts">
// Dummy data - will be replaced with actual data later
const interestRequests = [
    {
        id: 1,
        name: 'Ahmed Mohammed',
        age: 28,
        city: 'Riyadh',
        avatar: '/metronic8/demo23/assets/media/avatars/blank.png',
        time: '2 hours ago',
    },
    {
        id: 2,
        name: 'Fatima Ali',
        age: 25,
        city: 'Jeddah',
        avatar: '/metronic8/demo23/assets/media/avatars/blank.png',
        time: '5 hours ago',
    },
    {
        id: 3,
        name: 'Omar Hassan',
        age: 30,
        city: 'Mecca',
        avatar: '/metronic8/demo23/assets/media/avatars/blank.png',
        time: '1 day ago',
    },
];
</script>

<template>
    <div class="col-xl-6">
        <!--begin::Interest Requests Card-->
        <div class="card card-flush h-xl-100">
            <!--begin::Header-->
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900">Who Added You to Interest</span>
                    <span class="fw-semibold fs-7 mt-1 text-gray-500">People interested in your profile</span>
                </h3>

                <!--begin::Toolbar-->
                <div class="card-toolbar">
                    <a href="#" class="btn btn-sm btn-light">View All</a>
                </div>
                <!--end::Toolbar-->
            </div>
            <!--end::Header-->

            <!--begin::Body-->
            <div class="card-body pt-5">
                <template v-for="(request, index) in interestRequests" :key="request.id">
                    <!--begin::Item-->
                    <div class="d-flex flex-stack">
                        <!--begin::Wrapper-->
                        <div class="d-flex align-items-center me-3">
                            <!--begin::Avatar-->
                            <div class="symbol symbol-50px me-5">
                                <div class="symbol-label fs-3 bg-light-primary text-primary">
                                    {{ request.name.charAt(0) }}
                                </div>
                            </div>
                            <!--end::Avatar-->

                            <!--begin::Section-->
                            <div class="flex-grow-1">
                                <!--begin::Text-->
                                <a href="#" class="text-hover-primary fs-5 fw-bold lh-0 text-gray-800">
                                    {{ request.name }}
                                </a>
                                <!--end::Text-->

                                <!--begin::Description-->
                                <div class="fw-semibold d-flex align-items-center mt-1 gap-2 text-gray-500">
                                    <span class="fs-6">{{ request.age }} years</span>
                                    <span class="bullet bullet-dot h-6px w-6px bg-gray-500"></span>
                                    <span class="fs-6">{{ request.city }}</span>
                                </div>
                                <!--end::Description--->
                            </div>
                            <!--end::Section-->
                        </div>
                        <!--end::Wrapper-->

                        <!--begin::Actions-->
                        <div class="d-flex flex-column align-items-end">
                            <span class="fw-semibold fs-7 mb-2 text-gray-500">{{ request.time }}</span>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-light-primary btn-icon">
                                    <i class="ki-outline ki-eye fs-4"></i>
                                </button>
                                <button class="btn btn-sm btn-light-success btn-icon">
                                    <i class="ki-solid ki-heart fs-4"></i>
                                </button>
                            </div>
                        </div>
                        <!--end::Actions-->
                    </div>
                    <!--end::Item-->

                    <!--begin::Separator-->
                    <div v-if="index < interestRequests.length - 1" class="separator separator-dashed my-4"></div>
                    <!--end::Separator-->
                </template>

                <!--begin::Empty State-->
                <div v-if="interestRequests.length === 0" class="py-10 text-center">
                    <div class="symbol symbol-100px mx-auto mb-5">
                        <div class="symbol-label fs-1 bg-light-info text-info">
                            <i class="ki-outline ki-heart-circle fs-3x"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-2 text-gray-800">No Interest Requests Yet</h3>
                    <p class="fw-semibold fs-6 mb-0 text-gray-500">You haven't received any interest requests yet.</p>
                </div>
                <!--end::Empty State-->
            </div>
            <!--end::Body-->
        </div>
        <!--end::Interest Requests Card-->
    </div>
</template>

<style scoped></style>
