<script setup lang="ts">
import axios from 'axios';
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useLang } from '@/composables/useLang';

const { trans } = useLang();
const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');

interface Country {
    id: number;
    name: string;
    ar_name: string;
    flag?: string;
}

interface GalleryImage {
    id: number;
    thumbnail_url: string;
    original_url: string | null;
    is_main: boolean;
}

interface User {
    id: number;
    name: string;
    username: string;
    age: number;
    registration_type?: number;
    nationality: Country | null;
    residence?: Country | null;
    city?: Country | null;
    marriage_status: string | number;
    marriage_status_label?: string;
    marriage_type_label?: string;
    mainProfileImage?: string;
    child_count?: number;
    religion?: string;
    height?: number;
    weight?: number;
    skin_color?: string;
    skin_color_label?: string;
    body_shape?: string;
    body_shape_label?: string;
    education_level?: string;
    education_level_label?: string;
    financial_status?: string;
    field_of_work?: string;
    field_of_work_label?: string;
    job?: string;
    about_self?: string;
    about_partner?: string;
    is_online?: boolean;
    is_verified?: boolean;
    is_subscriber?: boolean;
    is_favourited?: boolean;
    is_favorited?: boolean;
    is_ignored?: boolean;
    image_request_status?: 'pending' | 'approved' | 'rejected' | null;
    can_view_images?: boolean;
    gallery_images?: GalleryImage[];
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
const imageRequestStatus = ref<'pending' | 'approved' | 'rejected' | null>(null);
const isRequestingImages = ref(false);
const lightboxImage = ref<string | null>(null);
const isReportModalOpen = ref(false);
const reportReason = ref('');
const reportDescription = ref('');
const isSubmittingReport = ref(false);
const loginLocation = ref<{ country: string | null; city: string | null } | null>(null);
const isLoadingLocation = ref(false);
const showLoginLocation = ref(false);

// Gender-aware marriage status label (matches user's own gender)
const isFemale = computed(() => user.value?.registration_type === 2);
const genderLabel = computed(() => isFemale.value ? trans('home.female') : trans('home.male'));
const showMarriageTypeBadge = computed(() => isFemale.value && !!user.value?.marriage_type_label);

const marriageStatusKeyMap: Record<number, string> = { 1: 'single', 2: 'married', 3: 'divorced', 4: 'widowed' };

const marriageStatusLabel = computed(() => {
    if (!user.value) return null;
    const status = Number(user.value.marriage_status);
    const key = marriageStatusKeyMap[status];
    const genderSuffix = isFemale.value ? 'female' : 'male';
    return key
        ? trans(`enums.marriage_status_${key}_${genderSuffix}`)
        : (user.value.marriage_status_label || String(user.value.marriage_status));
});

const getCountryName = (country: Country | null | undefined): string => {
    if (!country) return trans('profile.not_specified');
    return isRtl.value ? (country.ar_name || country.name) : country.name;
};

const loadUserProfile = async () => {
    if (!props.userId) return;
    isLoading.value = true;
    try {
        const response = await axios.get(`/users/${props.userId}`);
        user.value = response.data.user;
        isFavorited.value = response.data.user.is_favorited ?? response.data.user.is_favourited ?? false;
        isIgnored.value = response.data.user.is_ignored ?? false;
        imageRequestStatus.value = response.data.user.image_request_status ?? null;
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
    imageRequestStatus.value = null;
    lightboxImage.value = null;
    loginLocation.value = null;
    showLoginLocation.value = false;
};

const handleRequestImages = async () => {
    if (!user.value || isRequestingImages.value) return;
    isRequestingImages.value = true;
    try {
        const response = await axios.post(`/users/${user.value.id}/image-request`);
        imageRequestStatus.value = response.data.status;
    } catch (error: any) {
        if (error.response?.data?.status) {
            imageRequestStatus.value = error.response.data.status;
        }
    } finally {
        isRequestingImages.value = false;
    }
};

const fetchLoginLocation = async () => {
    if (!user.value || showLoginLocation.value) return;
    isLoadingLocation.value = true;
    try {
        const response = await axios.get(`/users/${user.value.id}/login-location`);
        loginLocation.value = response.data;
        showLoginLocation.value = true;
    } catch (error) {
        console.error('Failed to fetch login location:', error);
    } finally {
        isLoadingLocation.value = false;
    }
};

const handleMessage = () => {
    if (user.value) {
        emit('message', user.value.id);
        handleClose();
    }
};

const handleFavorite = async () => {
    const action = isFavorited.value ? trans('user_interactions.unfavorite_confirm') : trans('profile.favorite_confirm');
    Swal.fire({
        title: action,
        showDenyButton: true,
        customClass: { confirmButton: 'btn btn-primary', denyButton: 'btn btn-secondary' },
        buttonsStyling: false,
        confirmButtonText: trans('user_interactions.yes'),
        denyButtonText: trans('user_interactions.no'),
    }).then(async (result) => {
        if (result.isConfirmed && user.value) {
            try {
                const response = await axios.post(`/users/${user.value.id}/favorite`);
                isFavorited.value = response.data.is_favorited;
            } catch (error) {
                console.error('Failed to toggle favorite:', error);
            }
        }
    });
};

const handleIgnore = async () => {
    if (!user.value) return;
    const action = isIgnored.value ? trans('user_interactions.unignore_confirm') : trans('profile.ignore_confirm');
    Swal.fire({
        title: action,
        showDenyButton: true,
        customClass: { confirmButton: 'btn btn-primary', denyButton: 'btn btn-secondary' },
        buttonsStyling: false,
        confirmButtonText: trans('user_interactions.yes'),
        denyButtonText: trans('user_interactions.no'),
    }).then(async (result) => {
        if (result.isConfirmed && user.value) {
            try {
                const response = await axios.post(`/users/${user.value.id}/ignore`);
                isIgnored.value = response.data.is_ignored;
            } catch (error) {
                console.error('Failed to toggle ignore:', error);
            }
        }
    });
};

const openReportModal = () => { isReportModalOpen.value = true; };

const closeReportModal = () => {
    isReportModalOpen.value = false;
    reportReason.value = '';
    reportDescription.value = '';
};

const submitReport = async () => {
    if (!user.value || !reportReason.value) return;
    isSubmittingReport.value = true;
    try {
        await axios.post(`/users/${user.value.id}/report`, {
            reason: reportReason.value,
            description: reportDescription.value,
        });
        closeReportModal();
        alert(trans('profile.report_submitted_successfully'));
    } catch (error) {
        console.error('Failed to submit report:', error);
        alert(trans('profile.failed_to_submit_report'));
    } finally {
        isSubmittingReport.value = false;
    }
};

watch(
    () => [props.isOpen, props.userId] as const,
    ([isOpen, userId], [wasOpen, prevUserId]) => {
        if (isOpen && userId && (!wasOpen || userId !== prevUserId)) {
            loadUserProfile();
        }
    },
);
</script>

<template>
    <!-- Backdrop -->
    <Transition name="fade">
        <div
            v-if="isOpen"
            class="modal-backdrop fade show position-fixed start-0 top-0 h-100 w-100"
            style="z-index: 1055; background-color: rgba(0,0,0,0.5)"
            @click="handleClose"
        ></div>
    </Transition>

    <!-- Modal -->
    <Transition name="modal">
        <div v-if="isOpen" class="modal fade show d-block" tabindex="-1" style="z-index: 1056">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content">

                    <!-- Header -->
                    <div class="modal-header">
                        <h3 class="modal-title">{{ trans('profile.user_profile') }}</h3>
                        <button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-primary" @click="handleClose">
                            <i class="ki-outline ki-cross fs-2"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body">
                        <!-- Loading -->
                        <div v-if="isLoading" class="d-flex justify-content-center align-items-center py-10">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">{{ trans('profile.uploading') }}</span>
                            </div>
                        </div>

                        <div v-else-if="user">
                            <!-- Profile Header -->
                            <div class="d-flex flex-column align-items-center mb-7">
                                <!-- Avatar + online dot -->
                                <div class="position-relative mb-4">
                                    <div class="symbol symbol-100px symbol-circle" :class="{ 'modal-subscriber-avatar': user.is_subscriber }">
                                        <img
                                            :src="user.mainProfileImage || (isFemale ? '/assets/media/auth/female_bgd.png' : '/assets/media/auth/male_bgd.png')"
                                            :alt="user.username"
                                            class="rounded-circle"
                                            style="width:100px;height:100px;object-fit:cover;"
                                        />
                                    </div>
                                    <!-- Online dot — physical right so it stays bottom-right in both LTR and RTL -->
                                    <div
                                        class="position-absolute rounded-circle border border-4 border-white h-20px w-20px"
                                        style="bottom: 0; right: 0; transform: translate(25%, 25%);"
                                        :class="user.is_online ? 'bg-success' : 'bg-secondary'"
                                        :title="user.is_online ? trans('profile.online') : trans('profile.offline')"
                                    ></div>
                                </div>

                                <!-- Name + flags -->
                                <h4 class="fw-bold mb-1" :class="user.is_subscriber ? 'modal-subscriber-name' : 'text-gray-900'">
                                    {{ user.username }}
                                    <span v-if="user.nationality?.flag">{{ user.nationality.flag }}</span>
                                    <i v-if="user.is_subscriber" class="ki-outline ki-verify fs-3" style="color:#f6a723;"></i>
                                    <i :class="user.is_verified ? 'ki-solid ki-shield-tick fs-3 modal-verified-icon' : 'ki-outline ki-shield-tick fs-3 modal-unverified-icon'"></i>
                                </h4>

                                <!-- Premium badge -->
                                <div v-if="user.is_subscriber" class="mb-2">
                                    <span class="modal-premium-badge">
                                        <i class="ki-outline ki-crown fs-9"></i>
                                        <span>{{ trans('profile.premium_member') }}</span>
                                    </span>
                                </div>

                                <!-- Verified badge -->
                                <div v-if="user.is_verified" class="mb-2">
                                    <span class="modal-verified-badge">
                                        <i class="ki-outline ki-shield-tick fs-9"></i>
                                        <span>{{ trans('profile.verified_member') }}</span>
                                    </span>
                                </div>

                                <!-- Age -->
                                <p class="text-muted mb-2">{{ user.age }} {{ trans('home.years_old') }}</p>

                                <!-- Gender-aware status badges -->
                                <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
                                    <span class="uc-badge" :class="isFemale ? 'uc-badge--female' : 'uc-badge--male'">
                                        <span class="uc-badge__gender">{{ isFemale ? '♀' : '♂' }}</span>
                                        {{ genderLabel }}
                                    </span>
                                    <span v-if="marriageStatusLabel" class="uc-badge uc-badge--status">
                                        {{ marriageStatusLabel }}
                                    </span>
                                    <span v-if="showMarriageTypeBadge" class="uc-badge uc-badge--type">
                                        {{ user.marriage_type_label }}
                                    </span>
                                </div>

                                <!-- Login location -->
                                <div class="col-12 text-center">
                                    <div v-if="!showLoginLocation">
                                        <button class="btn btn-sm btn-light-primary" :disabled="isLoadingLocation" @click="fetchLoginLocation">
                                            <span v-if="isLoadingLocation">
                                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                                {{ trans('profile.uploading') }}
                                            </span>
                                            <span v-else>
                                                <i class="ki-outline ki-geolocation-home fs-4 me-1"></i>
                                                {{ trans('profile.show_login_location') }}
                                            </span>
                                        </button>
                                    </div>
                                    <div v-else class="d-flex align-items-center justify-content-center gap-2">
                                        <i class="ki-outline ki-geolocation-home fs-3 text-gray-600"></i>
                                        <div>
                                            <div class="text-muted fs-7">{{ trans('profile.login_location') }}</div>
                                            <div class="fw-semibold text-gray-800">
                                                <span v-if="loginLocation?.city || loginLocation?.country">
                                                    {{ [loginLocation?.city, loginLocation?.country].filter(Boolean).join(', ') }}
                                                </span>
                                                <span v-else class="text-muted">{{ trans('profile.not_available') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Details -->
                            <div class="row g-5">

                                <!-- Basic Information -->
                                <div class="col-12">
                                    <h5 class="fw-bold mb-4 text-gray-900">{{ trans('profile.basic_information') }}</h5>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-flag fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">{{ trans('profile.nationality') }}</div>
                                                    <div class="fw-semibold text-gray-800">{{ getCountryName(user.nationality) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="user.residence" class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-geolocation fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">{{ trans('profile.residence') }}</div>
                                                    <div class="fw-semibold text-gray-800">{{ getCountryName(user.residence) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="user.city" class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-home-2 fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">{{ trans('profile.city') }}</div>
                                                    <div class="fw-semibold text-gray-800">{{ getCountryName(user.city) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="user.child_count !== null && user.child_count !== undefined" class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-people fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">{{ trans('profile.child_count') }}</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.child_count }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="user.religion" class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-book fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">{{ trans('profile.religion') }}</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.religion }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Physical Attributes -->
                                <div v-if="user.height || user.weight || user.skin_color || user.body_shape" class="col-12">
                                    <h5 class="fw-bold mb-4 text-gray-900">{{ trans('profile.physical_attributes') }}</h5>
                                    <div class="row g-3">
                                        <div v-if="user.height" class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-size fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">{{ trans('profile.height') }}</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.height }} {{ trans('profile.cm') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="user.weight" class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-weight fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">{{ trans('profile.weight') }}</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.weight }} {{ trans('profile.kg') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="user.skin_color" class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-color-swatch fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">{{ trans('profile.skin_color') }}</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.skin_color_label }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="user.body_shape" class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-profile-user fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">{{ trans('profile.body_shape') }}</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.body_shape_label }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Professional Information -->
                                <div v-if="user.education_level || user.job || user.field_of_work" class="col-12">
                                    <h5 class="fw-bold mb-4 text-gray-900">{{ trans('profile.professional_details') }}</h5>
                                    <div class="row g-3">
                                        <div v-if="user.education_level" class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-graduation-cap fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">{{ trans('profile.education_level') }}</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.education_level_label }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="user.job" class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-briefcase fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">{{ trans('profile.job') }}</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.job }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="user.field_of_work" class="col-12">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ki-outline ki-chart-simple fs-3 text-gray-600"></i>
                                                <div>
                                                    <div class="text-muted fs-7">{{ trans('profile.field_of_work') }}</div>
                                                    <div class="fw-semibold text-gray-800">{{ user.field_of_work_label }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- About Self -->
                                <div v-if="user.about_self" class="col-12">
                                    <h5 class="fw-bold mb-3 text-gray-900">{{ trans('profile.about') }}</h5>
                                    <div class="fs-6 text-gray-700">{{ user.about_self }}</div>
                                </div>

                                <!-- About Partner -->
                                <div v-if="user.about_partner" class="col-12">
                                    <h5 class="fw-bold mb-3 text-gray-900">{{ trans('profile.looking_for') }}</h5>
                                    <div class="fs-6 text-gray-700">{{ user.about_partner }}</div>
                                </div>

                                <!-- Gallery Section -->
                                <div class="col-12">
                                    <h5 class="fw-bold mb-3 text-gray-900">{{ trans('image_requests.gallery') }}</h5>

                                    <!-- Approved: show original images directly (right-click opens original) -->
                                    <div v-if="user.can_view_images && user.gallery_images && user.gallery_images.length > 0" class="modal-gallery">
                                        <div
                                            v-for="img in user.gallery_images"
                                            :key="img.id"
                                            class="modal-gallery__item"
                                            @click="lightboxImage = img.original_url || img.thumbnail_url"
                                        >
                                            <img :src="img.original_url || img.thumbnail_url" :alt="user.username" class="modal-gallery__thumb" />
                                            <div class="modal-gallery__overlay">
                                                <i class="ki-outline ki-picture fs-3"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Approved but no images -->
                                    <div v-else-if="user.can_view_images && (!user.gallery_images || user.gallery_images.length === 0)" class="modal-gallery-locked">
                                        <i class="ki-outline ki-picture fs-2x text-gray-400 mb-2"></i>
                                        <p class="text-muted fs-7 mb-0">{{ trans('profile.not_specified') }}</p>
                                    </div>

                                    <!-- Locked: blurred thumbnails as background + lock UI on top -->
                                    <div
                                        v-else
                                        class="modal-gallery-locked"
                                        :class="{ 'modal-gallery-locked--has-preview': user.gallery_images && user.gallery_images.length > 0 }"
                                    >
                                        <!-- Blurred preview grid (shown when backend sends thumbnails even before approval) -->
                                        <template v-if="user.gallery_images && user.gallery_images.length > 0">
                                            <div class="modal-gallery modal-gallery--locked" aria-hidden="true">
                                                <div v-for="img in user.gallery_images" :key="img.id" class="modal-gallery__item">
                                                    <!-- src = thumbnail so right-click reveals only the low-res thumbnail -->
                                                    <img :src="img.thumbnail_url" :alt="user.username" class="modal-gallery__thumb modal-gallery__thumb--blurred" />
                                                </div>
                                            </div>
                                            <div class="modal-gallery-lock-overlay"></div>
                                        </template>

                                        <!-- Lock UI content (sits above the blurred grid) -->
                                        <div class="modal-gallery-lock-content">
                                            <i class="ki-outline ki-lock fs-2x mb-3" :class="imageRequestStatus === 'rejected' ? 'text-danger' : 'text-gray-500'"></i>

                                            <template v-if="imageRequestStatus === null">
                                                <p class="text-muted fs-7 mb-3">{{ trans('image_requests.gallery_locked_desc') }}</p>
                                                <button
                                                    class="btn btn-sm btn-primary"
                                                    :disabled="isRequestingImages"
                                                    @click="handleRequestImages"
                                                >
                                                    <span v-if="isRequestingImages" class="spinner-border spinner-border-sm me-2"></span>
                                                    <i v-else class="ki-outline ki-picture fs-6 me-1"></i>
                                                    {{ trans('image_requests.request_images') }}
                                                </button>
                                            </template>

                                            <template v-else-if="imageRequestStatus === 'pending'">
                                                <p class="text-muted fs-7 mb-2">{{ trans('image_requests.gallery_pending_desc') }}</p>
                                                <span class="badge badge-warning fs-8 px-3 py-2">
                                                    <i class="ki-outline ki-time fs-8 me-1"></i>
                                                    {{ trans('image_requests.request_pending') }}
                                                </span>
                                            </template>

                                            <template v-else-if="imageRequestStatus === 'rejected'">
                                                <p class="text-danger fs-7 mb-2">{{ trans('image_requests.gallery_rejected_desc') }}</p>
                                                <span class="badge badge-danger fs-8 px-3 py-2">
                                                    <i class="ki-outline ki-cross-circle fs-8 me-1"></i>
                                                    {{ trans('image_requests.cannot_re_request') }}
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div v-if="user" class="modal-footer d-flex justify-content-between">
                        <div class="d-flex gap-2">
                            <button
                                :class="['btn', isFavorited ? 'btn-danger' : 'btn-light-danger']"
                                :title="isFavorited ? trans('user_interactions.remove_from_favorites') : trans('profile.add_to_favorites')"
                                @click="handleFavorite"
                            >
                                <i :class="['fs-3', isFavorited ? 'ki-solid ki-heart' : 'ki-outline ki-heart']"></i>
                            </button>
                            <button
                                :class="['btn', isIgnored ? 'btn-warning' : 'btn-light-warning']"
                                :title="isIgnored ? trans('profile.unignore') : trans('profile.ignore')"
                                @click="handleIgnore"
                            >
                                <i class="ki-outline ki-cross-circle fs-3"></i>
                            </button>
                            <button class="btn btn-light-danger" :title="trans('profile.report_user')" @click="openReportModal">
                                <i class="ki-outline ki-information fs-3"></i>
                            </button>
                        </div>
                        <button class="btn btn-primary" @click="handleMessage">
                            <i class="ki-outline ki-message-text fs-3"></i>
                            <span class="ms-2">{{ trans('profile.send_message') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Lightbox -->
    <Transition name="fade">
        <div
            v-if="lightboxImage"
            class="modal-lightbox"
            @click="lightboxImage = null"
        >
            <button class="modal-lightbox__close" @click.stop="lightboxImage = null">
                <i class="ki-outline ki-cross fs-2"></i>
            </button>
            <img :src="lightboxImage" class="modal-lightbox__img" alt="Full image" @click.stop />
        </div>
    </Transition>

    <!-- Report backdrop -->
    <Transition name="fade">
        <div
            v-if="isReportModalOpen"
            class="modal-backdrop fade show position-fixed start-0 top-0 h-100 w-100"
            style="z-index: 1057; background-color: rgba(0,0,0,0.5)"
            @click="closeReportModal"
        ></div>
    </Transition>

    <!-- Report Modal -->
    <Transition name="modal">
        <div v-if="isReportModalOpen" class="modal fade show d-block" tabindex="-1" style="z-index: 1058">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">{{ trans('profile.report_user') }}</h3>
                        <button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-primary" @click="closeReportModal">
                            <i class="ki-outline ki-cross fs-2"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitReport">
                            <div class="mb-5">
                                <label class="form-label required">{{ trans('profile.reason') }}</label>
                                <select v-model="reportReason" class="form-select" required>
                                    <option value="">{{ trans('profile.select_a_reason') }}</option>
                                    <option value="inappropriate_content">{{ trans('profile.report_reason_inappropriate_content') }}</option>
                                    <option value="harassment">{{ trans('profile.report_reason_harassment') }}</option>
                                    <option value="fake_profile">{{ trans('profile.report_reason_fake_profile') }}</option>
                                    <option value="spam">{{ trans('profile.report_reason_spam') }}</option>
                                    <option value="sexual_content">{{ trans('profile.report_reason_sexual_content') }}</option>
                                    <option value="offensive_language">{{ trans('profile.report_reason_offensive_language') }}</option>
                                    <option value="other">{{ trans('profile.report_reason_other') }}</option>
                                </select>
                            </div>
                            <div class="mb-5">
                                <label class="form-label">{{ trans('profile.additional_feedback_optional') }}</label>
                                <textarea
                                    v-model="reportDescription"
                                    class="form-control"
                                    rows="4"
                                    maxlength="1000"
                                    :placeholder="trans('profile.provide_additional_information')"
                                ></textarea>
                                <div class="form-text">{{ reportDescription.length }}/1000 {{ trans('profile.characters') }}</div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" @click="closeReportModal">{{ trans('profile.cancel') }}</button>
                        <button
                            type="button"
                            class="btn btn-danger"
                            :disabled="!reportReason || isSubmittingReport"
                            @click="submitReport"
                        >
                            <span v-if="isSubmittingReport">{{ trans('profile.submitting') }}</span>
                            <span v-else>{{ trans('profile.submit_report') }}</span>
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

/* Gender-aware status badges (same as UserCard) */
.uc-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 4px 11px;
    border-radius: 20px;
    line-height: 1.3;
    white-space: nowrap;
}
.uc-badge__gender {
    font-size: 0.8rem;
    line-height: 1;
}
.uc-badge--male {
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid rgba(37, 99, 235, 0.22);
}
.uc-badge--female {
    background: #fdf0f6;
    color: rgb(208, 46, 121);
    border: 1px solid rgba(208, 46, 121, 0.25);
}
.uc-badge--status {
    background: #f0fdf4;
    color: #16a34a;
    border: 1px solid rgba(22, 163, 74, 0.22);
}
.uc-badge--type {
    background: #fff7ed;
    color: #c2410c;
    border: 1px solid rgba(194, 65, 12, 0.22);
}

/* Subscriber avatar ring */
.modal-subscriber-avatar {
    position: relative;
}
.modal-subscriber-avatar::after {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    border: 3px solid transparent;
    background-image: linear-gradient(white, white), linear-gradient(135deg, #f6d365, #fda085, #f6d365);
    background-origin: border-box;
    background-clip: content-box, border-box;
    z-index: -1;
}
.modal-subscriber-name {
    color: #c8811a;
}
.modal-premium-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: linear-gradient(135deg, #f6d365, #fda085);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 20px;
    box-shadow: 0 2px 8px rgba(246, 211, 101, 0.4);
}

.modal-verified-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: linear-gradient(135deg, #38bdf8, #0ea5e9);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 20px;
    box-shadow: 0 2px 8px rgba(14, 165, 233, 0.35);
}

.modal-verified-icon {
    color: #38bdf8;
    filter: drop-shadow(0 0 3px rgba(56, 189, 248, 0.6));
}

.modal-unverified-icon {
    color: #9ca3af;
}

/* Gallery */
.modal-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
    gap: 8px;
}

.modal-gallery__item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    background: #e5e7eb;
}

.modal-gallery__thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.3s ease;
}

.modal-gallery__overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    opacity: 0;
    transition: all 0.2s ease;
}

.modal-gallery__item:hover .modal-gallery__thumb { transform: scale(1.06); }
.modal-gallery__item:hover .modal-gallery__overlay {
    background: rgba(0, 0, 0, 0.35);
    opacity: 1;
}

/* Locked gallery */
.modal-gallery-locked {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 32px 20px;
    border-radius: 14px;
    background: #f9fafb;
    border: 1px dashed #d1d5db;
    text-align: center;
    overflow: hidden;
}

.modal-gallery-locked--has-preview {
    min-height: 220px;
}

/* Blurred thumbnail grid inside locked container */
.modal-gallery--locked {
    position: absolute;
    inset: 0;
    border-radius: 14px;
    pointer-events: none;
    z-index: 0;
    gap: 4px;
    padding: 4px;
}

.modal-gallery__thumb--blurred {
    filter: blur(10px) brightness(0.72);
    transform: scale(1.08);
    transition: none;
}

/* Semi-transparent overlay between blurred grid and lock UI */
.modal-gallery-lock-overlay {
    position: absolute;
    inset: 0;
    background: rgba(249, 250, 251, 0.68);
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
    border-radius: 14px;
    z-index: 1;
    pointer-events: none;
}

/* Lock UI sits above the overlay */
.modal-gallery-lock-content {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Lightbox */
.modal-lightbox {
    position: fixed;
    inset: 0;
    z-index: 2000;
    background: rgba(0, 0, 0, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal-lightbox__close {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
}

.modal-lightbox__close:hover { background: rgba(255, 255, 255, 0.3); }

.modal-lightbox__img {
    max-width: 100%;
    max-height: 90vh;
    border-radius: 12px;
    object-fit: contain;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}
</style>
