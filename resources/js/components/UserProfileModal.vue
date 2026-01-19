<script setup lang="ts">
import axios from 'axios';
import { ref, watch } from 'vue';

interface User {
    id: number;
    name: string;
    username: string;
    age: number;
    nationality: string | number;
    residence?: string | number;
    marriage_status: string;
    mainProfileImage?: string;
    child_count?: number;
    religion?: string;
    ethnicity?: string;
    height?: number;
    weight?: number;
    skin_color?: string;
    body_shape?: string;
    devotion?: string;
    prayer?: string;
    smoking?: string;
    beard?: string;
    education_level?: string;
    financial_status?: string;
    field_of_work?: string;
    job?: string;
    about_self?: string;
    about_partner?: string;
    is_online?: boolean;
}

interface Props {
    isOpen: boolean;
    userId?: number;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
    message: [userId: number];
}>();

const user = ref<User | null>(null);
const isLoading = ref(false);
const isFavorited = ref(false);
const isIgnored = ref(false);
const isReportModalOpen = ref(false);
const reportReason = ref('');
const reportDescription = ref('');
const isSubmittingReport = ref(false);

const loadUserProfile = async () => {
    if (!props.userId) {
        return;
    }

    isLoading.value = true;

    try {
        const response = await axios.get(`/users/${props.userId}`);
        console.log(response.data.user);
        user.value = response.data.user;
        isFavorited.value = response.data.user.is_favourite || false;
        isIgnored.value = response.data.user.is_ignored || false;
    } catch (error) {
        console.error('Failed to load user profile:', error);
    } finally {
        isLoading.value = false;
    }
};

const handleClose = () => {
    emit('close');
    user.value = null;
    isFavorited.value = false;
    isIgnored.value = false;
};

const handleMessage = () => {
    if (user.value) {
        emit('message', user.value.id);
        handleClose();
    }
};

const handleFavorite = async () => {
    let isFavorite = isFavorited.value ? 'unfavorite' : 'favorite';
    Swal.fire({
        title: `Do you want to ${isFavorite} this user?`,
        showDenyButton: true,
        customClass: {
            confirmButton: "btn btn-primary",
            denyButton: "btn btn-secondary",
        },
        buttonsStyling: false,
        confirmButtonText: 'Yes',
        denyButtonText: 'No',
    }).then(async (result) => {
        if (result.isConfirmed) {
            if (!user.value) {
                return;
            }

            try {
                const response = await axios.post(`/users/${user.value.id}/favorite`);

                isFavorited.value = response.data.is_favorited;
                // isIgnored.value = response.data.is_ignored;
            } catch (error) {
                console.error('Failed to toggle favorite:', error);
            }
        } else if (result.isDenied) {
            // optional logic
        }
    });
};

const handleIgnore = async () => {
    if (!user.value) {
        return;
    }

    const action = isIgnored.value ? 'unignore' : 'ignore';

    Swal.fire({
        title: `Do you want to ${action} this user?`,
        showDenyButton: true,
        customClass: {
            confirmButton: "btn btn-primary",
            denyButton: "btn btn-secondary",
        },
        buttonsStyling: false,
        confirmButtonText: 'Yes',
        denyButtonText: 'No',
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await axios.post(`/users/${user.value.id}/ignore`);
                isIgnored.value = response.data.is_ignored;
            } catch (error) {
                console.error('Failed to toggle ignore:', error);
            }
        }
    });
};

const openReportModal = () => {
    isReportModalOpen.value = true;
};

const closeReportModal = () => {
    isReportModalOpen.value = false;
    reportReason.value = '';
    reportDescription.value = '';
};

const submitReport = async () => {
    if (!user.value || !reportReason.value) {
        return;
    }

    isSubmittingReport.value = true;

    try {
        await axios.post(`/users/${user.value.id}/report`, {
            reason: reportReason.value,
            description: reportDescription.value,
        });

        closeReportModal();
        alert('Report submitted successfully');
    } catch (error) {
        console.error('Failed to submit report:', error);
        alert('Failed to submit report');
    } finally {
        isSubmittingReport.value = false;
    }
};

watch(
    () => props.isOpen,
    (isOpen) => {
        if (isOpen) {
            loadUserProfile();
        }
    },
);

watch(
    () => props.userId,
    () => {
        if (props.isOpen) {
            loadUserProfile();
        }
    },
);
</script>

<template>
    <!-- Modal Overlay -->
    <Transition name="fade">
        <div
            v-if="isOpen"
            @click="handleClose"
            class="modal-backdrop fade show position-fixed start-0 top-0 h-100 w-100"
            style="z-index: 1055; background-color: rgba(0, 0, 0, 0.5)"
        ></div>
    </Transition>

    <!-- Modal -->
    <Transition name="modal">
        <div v-if="isOpen" class="modal fade show d-block" tabindex="-1" style="z-index: 1056">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h3 class="modal-title">User Profile</h3>
                        <button @click="handleClose" type="button" class="btn btn-sm btn-icon btn-light btn-active-light-primary">
                            <i class="ki-outline ki-cross fs-2"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body">
                        <div v-if="isLoading" class="d-flex justify-content-center align-items-center py-10">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>

                        <div v-else-if="user">
                            <!-- Profile Header -->
                            <div class="d-flex flex-column align-items-center mb-7">
                                <div class="position-relative mb-4">
                                    <div class="symbol symbol-100px symbol-circle">
                                        <img
                                            :src="user.mainProfileImage || '/assets/media/avatars/300-1.jpg'"
                                            :alt="user.username"
                                            class="rounded-circle"
                                        />
                                    </div>
                                    <div
                                        :class="[
                                            'position-absolute rounded-circle border border-4 border-white',
                                            'h-20px w-20px',
                                            'translate-middle',
                                            'start-100 top-100',
                                            user.is_online ? 'bg-success' : 'bg-secondary',
                                        ]"
                                        :title="user.is_online ? 'Online' : 'Offline'"
                                    ></div>
                                </div>
                                <h4 class="fw-bold mb-1 text-gray-900">{{ user.username }} <span>
                                    {{ user.nationality.flag }}
                                </span></h4>
                                <p class="text-muted mb-2">{{ user.age }} years old</p>
                                <span class="badge badge-light-primary">{{ user.marriage_status }}</span>
                            </div>

                            <!-- Profile Details -->
                            <div class="row g-5">
                                <!-- Basic Information -->
                                <div class="col-12">
                                    <h5 class="fw-bold mb-4 text-gray-900">Basic Information</h5>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-flag fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">Nationality</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.nationality.name }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="user.residence" class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-geolocation fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">Residence</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.residence.name }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="user.child_count !== null && user.child_count !== undefined" class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-people fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">Children</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.child_count }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="user.religion" class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-book fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">Religion</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.religion }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Physical Attributes -->
                                <div v-if="user.height || user.weight || user.skin_color || user.body_shape" class="col-12">
                                    <h5 class="fw-bold mb-4 text-gray-900">Physical Attributes</h5>
                                    <div class="row g-3">
                                        <div v-if="user.height" class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-size fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">Height</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.height }} cm</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="user.weight" class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-weight fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">Weight</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.weight }} kg</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="user.skin_color" class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-color-swatch fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">Skin Color</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.skin_color }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="user.body_shape" class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-profile-user fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">Body Shape</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.body_shape }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Professional Information -->
                                <div v-if="user.education_level || user.job || user.field_of_work" class="col-12">
                                    <h5 class="fw-bold mb-4 text-gray-900">Professional Information</h5>
                                    <div class="row g-3">
                                        <div v-if="user.education_level" class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-graduation-cap fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">Education</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.education_level }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="user.job" class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-briefcase fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">Job</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.job }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="user.field_of_work" class="col-12">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-chart-simple fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">Field of Work</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.field_of_work }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- About Self -->
                                <div v-if="user.about_self" class="col-12">
                                    <h5 class="fw-bold mb-3 text-gray-900">About</h5>
                                    <div class="fs-6 text-gray-700">{{ user.about_self }}</div>
                                </div>

                                <!-- About Partner -->
                                <div v-if="user.about_partner" class="col-12">
                                    <h5 class="fw-bold mb-3 text-gray-900">Looking For</h5>
                                    <div class="fs-6 text-gray-700">{{ user.about_partner }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div v-if="user" class="modal-footer d-flex justify-content-between">
                        <div class="d-flex gap-2">
                            <button
                                @click="handleFavorite"
                                :class="['btn', isFavorited ? 'btn-danger' : 'btn-light-danger']"
                                :title="isFavorited ? 'Remove from favorites' : 'Add to favorites'"
                            >
                                <i :class="['ki-outline', isFavorited ? 'ki-heart' : 'ki-heart', 'fs-3']"></i>
                            </button>
                            <button
                                @click="handleIgnore"
                                :class="['btn', isIgnored ? 'btn-warning' : 'btn-light-warning']"
                                :title="isIgnored ? 'Unignore' : 'Ignore'"
                            >
                                <i class="ki-outline ki-cross-circle fs-3"></i>
                            </button>
                            <button @click="openReportModal" class="btn btn-light-danger" title="Report user">
                                <i class="ki-outline ki-information fs-3"></i>
                            </button>
                        </div>
                        <button @click="handleMessage" class="btn btn-primary">
                            <i class="ki-outline ki-message-text fs-3"></i>
                            <span class="ms-2">Send Message</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Report Modal -->
    <Transition name="fade">
        <div
            v-if="isReportModalOpen"
            @click="closeReportModal"
            class="modal-backdrop fade show position-fixed start-0 top-0 h-100 w-100"
            style="z-index: 1057; background-color: rgba(0, 0, 0, 0.5)"
        ></div>
    </Transition>

    <Transition name="modal">
        <div v-if="isReportModalOpen" class="modal fade show d-block" tabindex="-1" style="z-index: 1058">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h3 class="modal-title">Report User</h3>
                        <button @click="closeReportModal" type="button" class="btn btn-sm btn-icon btn-light btn-active-light-primary">
                            <i class="ki-outline ki-cross fs-2"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body">
                        <form @submit.prevent="submitReport">
                            <div class="mb-5">
                                <label class="form-label required">Reason</label>
                                <select v-model="reportReason" class="form-select" required>
                                    <option value="">Select a reason</option>
                                    <option value="inappropriate_content">Inappropriate Content</option>
                                    <option value="harassment">Harassment</option>
                                    <option value="fake_profile">Fake Profile</option>
                                    <option value="spam">Spam</option>
                                    <option value="sexual_content">Sexual Content</option>
                                    <option value="offensive_language">Offensive Language</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="mb-5">
                                <label class="form-label">Additional Details (Optional)</label>
                                <textarea
                                    v-model="reportDescription"
                                    class="form-control"
                                    rows="4"
                                    maxlength="1000"
                                    placeholder="Provide additional information about your report..."
                                ></textarea>
                                <div class="form-text">{{ reportDescription.length }}/1000 characters</div>
                            </div>
                        </form>
                    </div>
                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button @click="closeReportModal" type="button" class="btn btn-light">Cancel</button>
                        <button @click="submitReport" type="button" class="btn btn-danger" :disabled="!reportReason || isSubmittingReport">
                            <span v-if="isSubmittingReport">Submitting...</span>
                            <span v-else>Submit Report</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.modal-enter-active,
.modal-leave-active {
    transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
    transform: scale(0.9);
}
</style>
