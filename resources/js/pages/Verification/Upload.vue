<script setup lang="ts">
import { useForm, usePage, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';
import { useLang } from '@/composables/useLang';

interface Props {
    hasVideo: boolean;
    isVerified: boolean;
}

const props = defineProps<Props>();
const page = usePage();
const flash = computed(() => (page.props as any).flash);
const { trans } = useLang();
const isRtl = computed(() => (page.props as any).locale === 'ar');
const dragActive = ref(false);
const previewUrl = ref<string | null>(null);

const form = useForm({
    video: null as File | null,
});

const allowedTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/webm', 'video/x-matroska'];
const maxSizeMB = 10;

const fileError = ref<string | null>(null);

const validateFile = (file: File): boolean => {
    fileError.value = null;

    if (!allowedTypes.includes(file.type)) {
        fileError.value = 'Invalid file type. Only MP4, MOV, AVI, WebM, and MKV are allowed.';
        return false;
    }

    if (file.size > maxSizeMB * 1024 * 1024) {
        fileError.value = `File is too large. Maximum size is ${maxSizeMB}MB.`;
        return false;
    }

    return true;
};

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file && validateFile(file)) {
        form.video = file;
        previewUrl.value = URL.createObjectURL(file);
    } else {
        form.video = null;
        previewUrl.value = null;
    }
};

const handleDrop = (event: DragEvent) => {
    dragActive.value = false;
    const file = event.dataTransfer?.files?.[0];
    if (file && validateFile(file)) {
        form.video = file;
        previewUrl.value = URL.createObjectURL(file);
    }
};

const handleDragOver = (event: DragEvent) => {
    dragActive.value = true;
};

const handleDragLeave = () => {
    dragActive.value = false;
};

const removeFile = () => {
    form.video = null;
    previewUrl.value = null;
    fileError.value = null;
};

const formatFileSize = (bytes: number): string => {
    if (bytes < 1024 * 1024) {
        return (bytes / 1024).toFixed(1) + ' KB';
    }
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const submitForm = () => {
    form.post(route('verification.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            previewUrl.value = null;
        },
    });
};
</script>

<template>
    <div class="verification-page d-flex flex-column flex-root" id="kt_app_root" :dir="isRtl ? 'rtl' : 'ltr'">
        <div class="d-flex flex-column flex-column-fluid">
            <div class="d-flex flex-column flex-lg-row-fluid p-lg-10 w-100 p-5">
                <div class="d-flex flex-center flex-column w-100">
                    <div class="mw-700px w-100">

                        <!-- Logo / Branding -->
                        <div class="text-center mb-10">
                            <div class="verification-icon-wrapper mb-5">
                                <i class="ki-outline ki-shield-tick fs-3x text-primary"></i>
                            </div>
                            <h1 class="fw-bold fs-2x text-gray-900 mb-2">{{ trans('verification.title') }}</h1>
                            <p class="text-muted fw-semibold fs-5">{{ trans('verification.subtitle') }}</p>
                        </div>

                        <!-- Alert: Why Verification is Needed -->
                        <div class="alert alert-dismissible bg-light-warning border border-warning d-flex flex-column flex-sm-row p-5 mb-8">
                            <i class="ki-outline ki-information-4 fs-2hx text-warning me-4 mb-5 mb-sm-0"></i>
                            <div class="d-flex flex-column pe-0 pe-sm-10">
                                <h4 class="fw-semibold text-gray-900">{{ trans('verification.why_title') }}</h4>
                                <span class="text-gray-700 fs-6">
                                    {{ trans('verification.why_body') }}
                                    <strong>{{ trans('verification.why_note') }}</strong>{{ trans('verification.why_note_suffix') }}
                                </span>
                            </div>
                        </div>

                        <!-- State: Pending Approval -->
                        <div v-if="props.hasVideo && !props.isVerified" class="card shadow-sm border-0">
                            <div class="card-body text-center py-15">
                                <div class="pending-icon-wrapper mb-8">
                                    <div class="pending-pulse">
                                        <i class="ki-outline ki-time fs-4x text-warning"></i>
                                    </div>
                                </div>
                                <h2 class="fw-bold text-gray-900 mb-3">{{ trans('verification.pending_title') }}</h2>
                                <p class="text-gray-600 fs-5 mb-8 mw-500px mx-auto">
                                    {{ trans('verification.pending_body') }}
                                </p>
                                <div class="d-flex flex-center gap-4">
                                    <span class="badge badge-light-warning fs-6 py-3 px-5">
                                        <i class="ki-outline ki-timer fs-4 me-1"></i>
                                        {{ trans('verification.under_review') }}
                                    </span>
                                </div>
                                <div class="separator my-8"></div>
                                <p class="text-gray-500 fs-7 mb-4">
                                    {{ trans('verification.rejected_note') }}
                                </p>
                                <Link :href="route('logout')" method="post"
                                    class="btn btn-sm btn-light-danger">
                                    <i class="ki-outline ki-exit-right fs-4 me-1"></i>
                                    {{ trans('verification.logout') }}
                                </Link>
                            </div>
                        </div>

                        <!-- State: Upload Form -->
                        <div v-else class="card shadow-sm border-0">
                            <div class="card-body p-lg-10 p-6">
                                <form @submit.prevent="submitForm">
                                    <!-- Upload Requirements -->
                                    <div class="d-flex flex-column mb-8">
                                        <h3 class="fw-bold text-gray-900 fs-3 mb-4">{{ trans('verification.requirements_title') }}</h3>
                                        <div class="d-flex flex-wrap gap-4">
                                            <div class="d-flex align-items-center bg-light-primary rounded p-3 flex-grow-1">
                                                <i class="ki-outline ki-film fs-2 text-primary me-3"></i>
                                                <div>
                                                    <span class="fw-bold text-gray-800 d-block fs-7">{{ trans('verification.video_formats_label') }}</span>
                                                    <span class="text-gray-600 fs-8">{{ trans('verification.video_formats_value') }}</span>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center bg-light-info rounded p-3 flex-grow-1">
                                                <i class="ki-outline ki-data fs-2 text-info me-3"></i>
                                                <div>
                                                    <span class="fw-bold text-gray-800 d-block fs-7">{{ trans('verification.max_size_label') }}</span>
                                                    <span class="text-gray-600 fs-8">{{ trans('verification.max_size_value') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Drag & Drop Zone -->
                                    <div
                                        class="dropzone-area mb-6"
                                        :class="{
                                            'dropzone-active': dragActive,
                                            'dropzone-has-file': form.video,
                                            'dropzone-error': fileError || form.errors.video,
                                        }"
                                        @dragover.prevent="handleDragOver"
                                        @dragleave.prevent="handleDragLeave"
                                        @drop.prevent="handleDrop"
                                    >
                                        <!-- No File Selected -->
                                        <div v-if="!form.video" class="text-center py-10">
                                            <i class="ki-outline ki-cloud-add fs-4x mb-5 text-gray-400 d-block"></i>
                                            <h4 class="fw-semibold text-gray-800 mb-2">{{ trans('verification.drop_title') }}</h4>
                                            <p class="text-gray-500 fs-7 mb-5">{{ trans('verification.drop_subtitle') }}</p>
                                            <label class="btn btn-primary btn-sm cursor-pointer">
                                                <i class="ki-outline ki-folder-added fs-4 me-1"></i>
                                                {{ trans('verification.choose_file') }}
                                                <input
                                                    id="verification_video_input"
                                                    type="file"
                                                    class="d-none"
                                                    accept="video/mp4,video/quicktime,video/x-msvideo,video/webm,video/x-matroska"
                                                    @change="handleFileSelect"
                                                />
                                            </label>
                                        </div>

                                        <!-- File Selected - Preview -->
                                        <div v-else class="p-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <!-- Video Preview -->
                                                <div v-if="previewUrl" class="w-100 mb-4 video-preview-wrapper">
                                                    <video
                                                        :src="previewUrl"
                                                        controls
                                                        class="w-100 rounded-3"
                                                        style="max-height: 300px; object-fit: contain; background: #000"
                                                    ></video>
                                                </div>

                                                <!-- File Info -->
                                                <div class="d-flex align-items-center justify-content-between w-100">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ki-outline ki-film fs-2 text-primary me-3"></i>
                                                        <div>
                                                            <span class="fw-bold text-gray-800 d-block fs-7">{{ form.video.name }}</span>
                                                            <span class="text-gray-500 fs-8">{{ formatFileSize(form.video.size) }}</span>
                                                        </div>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        class="btn btn-icon btn-sm btn-light-danger"
                                                        @click="removeFile"
                                                        :title="trans('verification.remove_file')"
                                                    >
                                                        <i class="ki-outline ki-cross fs-3"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Error Messages -->
                                    <div v-if="fileError" class="alert alert-danger d-flex align-items-center p-4 mb-5">
                                        <i class="ki-outline ki-information-5 fs-2 text-danger me-3"></i>
                                        <span class="text-danger fw-semibold">{{ fileError }}</span>
                                    </div>
                                    <div v-if="form.errors.video" class="alert alert-danger d-flex align-items-center p-4 mb-5">
                                        <i class="ki-outline ki-information-5 fs-2 text-danger me-3"></i>
                                        <span class="text-danger fw-semibold">{{ form.errors.video }}</span>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="d-flex justify-content-between align-items-center">
                                        <Link :href="route('logout')" method="post"
                                            class="btn btn-light-danger">
                                            <i class="ki-outline ki-exit-right fs-4 me-1"></i>
                                            {{ trans('verification.logout') }}
                                        </Link>

                                        <button
                                            id="submit_verification_btn"
                                            type="submit"
                                            class="btn btn-primary"
                                            :disabled="!form.video || form.processing"
                                        >
                                            <span v-if="!form.processing">
                                                <i class="ki-outline ki-shield-tick fs-3 me-1"></i>
                                                {{ trans('verification.submit_btn') }}
                                            </span>
                                            <span v-else class="d-flex align-items-center">
                                                <span class="spinner-border spinner-border-sm me-2"></span>
                                                {{ trans('verification.uploading') }}
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Progress Bar (during upload) -->
                        <div v-if="form.processing" class="mt-5">
                            <div class="progress h-6px w-100">
                                <div
                                    class="progress-bar bg-primary progress-bar-striped progress-bar-animated"
                                    role="progressbar"
                                    :style="{ width: (form.progress?.percentage || 0) + '%' }"
                                ></div>
                            </div>
                            <span class="text-gray-500 fs-8 mt-2 d-block text-center">
                                {{ form.progress?.percentage || 0 }}{{ trans('verification.uploaded_percent') }}
                            </span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.verification-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #f5f8fa 0%, #eef2f7 100%);
}

.verification-icon-wrapper {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(0, 158, 247, 0.1) 0%, rgba(0, 158, 247, 0.05) 100%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    animation: float 3s ease-in-out infinite;
}

.pending-icon-wrapper {
    display: inline-block;
}

.pending-pulse {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(255, 199, 0, 0.15) 0%, rgba(255, 199, 0, 0.05) 100%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    animation: pulse 2s ease-in-out infinite;
}

.dropzone-area {
    border: 2px dashed var(--bs-gray-300, #e1e3ea);
    border-radius: 0.75rem;
    transition: all 0.3s ease;
    cursor: pointer;
    background: var(--bs-gray-100, #f9f9f9);
}

.dropzone-area:hover {
    border-color: var(--bs-primary, #009ef7);
    background: rgba(0, 158, 247, 0.02);
}

.dropzone-active {
    border-color: var(--bs-primary, #009ef7);
    background: rgba(0, 158, 247, 0.05);
    transform: scale(1.01);
}

.dropzone-has-file {
    border-color: var(--bs-success, #50cd89);
    border-style: solid;
    background: rgba(80, 205, 137, 0.03);
}

.dropzone-error {
    border-color: var(--bs-danger, #f1416c) !important;
    background: rgba(241, 65, 108, 0.03) !important;
}

.video-preview-wrapper {
    border-radius: 0.75rem;
    overflow: hidden;
    background: #000;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-6px); }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.05); opacity: 0.85; }
}

[data-bs-theme="dark"] .verification-page {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
}

[data-bs-theme="dark"] .dropzone-area {
    border-color: var(--bs-gray-600, #474761);
    background: var(--bs-gray-200, #1b1b29);
}

[data-bs-theme="dark"] .dropzone-area:hover {
    border-color: var(--bs-primary, #009ef7);
    background: rgba(0, 158, 247, 0.08);
}
</style>
