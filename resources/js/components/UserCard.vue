<script setup lang="ts">
import { ref, computed } from 'vue';
import axios from 'axios';

interface User {
    id: number;
    name: string;
    username: string;
    age: number;
    nationality: string | number;
    residence: string | number;
    marriage_status: string;
    is_online?: boolean;
    is_favorited?: boolean;
}

interface Props {
    user: User;
    countries?: Array<{ id: number; name: string }>;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    message: [userId: number];
    viewProfile: [userId: number];
}>();

const isFavorited = ref(props.user.is_favorited ?? false);
const isTogglingFavorite = ref(false);

const getNationalityName = computed(() => {
    if (!props.countries) {
        return props.user.nationality;
    }
    const country = props.countries.find((c) => c.id === Number(props.user.nationality));
    return country?.name || props.user.nationality;
});

const getResidenceName = computed(() => {
    if (!props.countries) {
        return props.user.residence;
    }
    const country = props.countries.find((c) => c.id === Number(props.user.residence));
    return country?.name || props.user.residence;
});

const toggleFavorite = async () => {
    if (isTogglingFavorite.value) {
        return;
    }

    isTogglingFavorite.value = true;

    try {
        const response = await axios.post(`/users/${props.user.id}/favorite`);
        isFavorited.value = response.data.is_favorited;
    } catch (error) {
        console.error('Failed to toggle favorite:', error);
    } finally {
        isTogglingFavorite.value = false;
    }
};

const handleMessage = () => {
    emit('message', props.user.id);
};

const handleCardClick = () => {
    emit('viewProfile', props.user.id);
};
</script>

<template>
    <div class="card h-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="card-body d-flex flex-column cursor-pointer" @click="handleCardClick">
            <!-- Avatar Section -->
            <div class="d-flex justify-content-center mb-4">
                <div class="position-relative">
                    <div class="symbol symbol-100px symbol-circle">
                        <img
                            src="/assets/media/avatars/300-1.jpg"
                            :alt="user.username"
                            class="rounded-circle"
                        />
                    </div>
                    <!-- Online Indicator -->
                    <div
                        :class="[
                            'position-absolute rounded-circle border border-4 border-white',
                            'h-20px w-20px',
                            'translate-middle',
                            'top-100 start-100',
                            user.is_online ? 'bg-success' : 'bg-secondary',
                        ]"
                        :title="user.is_online ? 'Online' : 'Offline'"
                    ></div>
                </div>
            </div>

            <!-- User Info -->
            <div class="text-center mb-4">
                <h3 class="fs-4 fw-bold text-gray-900 mb-1">{{ user.username }}</h3>
                <p class="text-muted fs-6 mb-2">{{ user.age }} years old</p>
                <div class="d-flex align-items-center justify-content-center gap-2 text-muted fs-7">
                    <i class="ki-outline ki-flag fs-5"></i>
                    <span>{{ getNationalityName }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-center gap-2 text-muted fs-7 mt-1">
                    <i class="ki-outline ki-geolocation fs-5"></i>
                    <span>{{ getResidenceName }}</span>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-auto border-top pt-4">
                <div class="text-center mb-3">
                    <span class="badge badge-light-primary fs-7">{{ user.marriage_status }}</span>
                </div>

                <div class="d-flex gap-2">
                    <!-- Favorite Button -->
                    <button
                        @click.stop="toggleFavorite"
                        :disabled="isTogglingFavorite"
                        class="btn btn-sm flex-fill"
                        :class="[
                            isFavorited ? 'btn-light-danger' : 'btn-light',
                        ]"
                        :title="isFavorited ? 'Remove from favorites' : 'Add to favorites'"
                    >
                        <i
                            :class="[
                                'ki-outline fs-3',
                                isFavorited ? 'ki-heart text-danger' : 'ki-heart',
                            ]"
                        ></i>
                    </button>

                    <!-- Message Button -->
                    <button
                        @click.stop="handleMessage"
                        class="btn btn-sm btn-primary flex-fill"
                        title="Send message"
                    >
                        <i class="ki-outline ki-message-text fs-3"></i>
                        <span class="ms-2">Message</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-4px);
}

.hover\:shadow-md:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.cursor-pointer {
    cursor: pointer;
}
</style>
