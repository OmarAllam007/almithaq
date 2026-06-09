<script setup lang="ts">
import { ref, computed, onUnmounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { store, destroy, setMain } from '@/actions/App/Http/Controllers/ProfileImageController';
import { useLang } from '@/composables/useLang';
const { trans } = useLang();

interface Image {
    id: number;
    thumbnail_url: string;
    is_main: boolean;
    is_approved: boolean;
    order: number;
    created_at: string;
}

interface Props {
    images: Image[];
    canUploadMore: boolean;
}

const props = defineProps<Props>();

// Debug: Log images data

const selectedFiles = ref<File[]>([]);
const isDragging = ref(false);
const showDeleteModal = ref(false);
const imageToDelete = ref<number | null>(null);
const previewUrls = new Map<File, string>();

const form = useForm({
    images: [] as File[],
});

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        addFiles(Array.from(target.files));
    }
};

const handleDrop = (event: DragEvent) => {
    isDragging.value = false;
    if (event.dataTransfer?.files) {
        addFiles(Array.from(event.dataTransfer.files));
    }
};

const addFiles = (files: File[]) => {
    const imageFiles = files.filter((file) => file.type.startsWith('image/'));
    const remainingSlots = 5 - props.images.length - selectedFiles.value.length;
    const filesToAdd = imageFiles.slice(0, remainingSlots);

    selectedFiles.value = [...selectedFiles.value, ...filesToAdd];
};

const removeSelectedFile = (index: number) => {
    // Revoke the object URL for this file
    const file = selectedFiles.value[index];
    const url = previewUrls.get(file);
    if (url) {
        window.URL.revokeObjectURL(url);
        previewUrls.delete(file);
    }
    selectedFiles.value.splice(index, 1);
};

const uploadImages = () => {
    if (selectedFiles.value.length === 0) {
        return;
    }

    form.images = selectedFiles.value;
    form.submit(store(), {
        onSuccess: () => {
            // Clean up all preview URLs
            previewUrls.forEach((url) => {
                window.URL.revokeObjectURL(url);
            });
            previewUrls.clear();
            selectedFiles.value = [];
            form.reset();
        },
    });
};

const confirmDelete = (imageId: number) => {
    imageToDelete.value = imageId;
    showDeleteModal.value = true;
};

const deleteImage = () => {
    if (imageToDelete.value) {
        router.delete(route('profile.gallery.destroy', imageToDelete.value), {
            onSuccess: () => {
                showDeleteModal.value = false;
                imageToDelete.value = null;
            },
        });
    }
};

const setAsMainImage = (imageId: number) => {
    router.patch(route('profile.gallery.set-main', imageId));
};

const getPreviewUrl = (file: File): string => {
    // Return existing URL if already created
    if (previewUrls.has(file)) {
        return previewUrls.get(file)!;
    }

    // Create and cache new URL
    const url = window.URL.createObjectURL(file);
    previewUrls.set(file, url);
    return url;
};

const totalImages = computed(() => props.images.length + selectedFiles.value.length);

// Clean up all preview URLs when component unmounts
onUnmounted(() => {
    previewUrls.forEach((url) => {
        window.URL.revokeObjectURL(url);
    });
    previewUrls.clear();
});
</script>

<template>
    <div class="app-main flex-column flex-row-fluid overflow-y-auto" id="kt_app_main" scroll-region>
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-lg-0 py-3">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center me-3">
                        <h1 class="page-heading d-flex fw-bold fs-3 flex-column justify-content-center my-0 text-gray-900">{{ trans('profile.my_gallery') }}</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="/" class="text-muted text-hover-primary">{{ trans('home.home') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet w-5px h-2px bg-gray-500"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">{{ trans('profile.gallery') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <!-- Instructions Card -->
                    <div class="card mb-5">
                        <div class="card-body">
                            <h3 class="card-title mb-4">{{ trans('profile.profile_image_verification') }}</h3>
                            <div class="text-gray-700">
                                <p class="mb-2">{{ trans('profile.upload_your_profile_images_for_verification_please_note') }}</p>
                                <ul class="mb-0">
                                    <li>{{ trans('profile.you_can_upload_up_to_5_images') }}</li>
                                    <li>{{ trans('profile.only_jpeg_png_and_jpg_formats_are_supported') }}</li>
                                    <li>{{ trans('profile.maximum_file_size_is_5mb_per_image') }}</li>
                                    <li>{{ trans('profile.select_one_image_as_your_main_profile_picture') }}</li>
                                    <li>{{ trans('profile.all_images_require_admin_approval_before_being_visible_to_others') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Section -->
                    <div v-if="canUploadMore && totalImages < 5" class="card mb-5">
                        <div class="card-body">
                            <h3 class="card-title mb-4">{{ trans('profile.upload_images') }} ({{ totalImages }}/5)</h3>

                            <!-- Drag & Drop Zone -->
                            <div
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleDrop"
                                :class="[
                                    'cursor-pointer rounded border-dashed p-10 text-center',
                                    isDragging ? 'border-primary bg-light-primary' : 'border-gray-300',
                                ]"
                            >
                                <input
                                    type="file"
                                    id="imageUpload"
                                    multiple
                                    accept="image/jpeg,image/png,image/jpg"
                                    @change="handleFileSelect"
                                    class="d-none"
                                />
                                <label for="imageUpload" class="cursor-pointer">
                                    <i class="ki-outline ki-file-up fs-3x text-primary mb-3"></i>
                                    <p class="fs-5 fw-semibold mb-2 text-gray-700">{{ trans('profile.drop_images_here_or_click_to_upload') }}</p>
                                    <p class="fs-7 text-muted">{{ trans('profile.you_can_upload') }} {{ 5 - totalImages }} {{ trans('profile.more_image_s') }}</p>
                                </label>
                            </div>

                            <!-- Selected Files Preview -->
                            <div v-if="selectedFiles.length > 0" class="mt-5">
                                <h4 class="mb-3">{{ trans('profile.selected_images') }}</h4>
                                <div class="d-flex flex-wrap gap-3">
                                    <div v-for="(file, index) in selectedFiles" :key="index" class="position-relative">
                                        <img
                                            :src="getPreviewUrl(file)"
                                            class="rounded border"
                                            style="width: 120px; height: 120px; object-fit: cover"
                                        />
                                        <button
                                            @click="removeSelectedFile(index)"
                                            class="btn btn-sm btn-icon btn-danger position-absolute end-0 top-0 m-1"
                                        >
                                            <i class="ki-outline ki-cross fs-3"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Upload Button -->
                                <button @click="uploadImages" :disabled="form.processing" class="btn btn-primary mt-4">
                                    <i class="ki-outline ki-cloud-upload fs-3"></i>
                                        {{ form.processing ? trans('profile.uploading') : trans('profile.upload_images') }}
                                </button>
                            </div>

                            <!-- Validation Errors -->
                            <div v-if="form.errors.images" class="alert alert-danger mt-4">
                                {{ form.errors.images }}
                            </div>
                        </div>
                    </div>

                    <!-- Current Images Grid -->
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title mb-4">{{ trans('profile.your_images') }}</h3>

                            <!-- Empty State -->
                            <div v-if="images.length === 0" class="py-10 text-center">
                                <i class="ki-outline ki-picture fs-5x text-muted mb-3"></i>
                                <p class="fs-5 text-muted">{{ trans('profile.no_images_uploaded_yet') }}</p>
                                <p class="fs-7 text-gray-600">{{ trans('profile.upload_your_first_image_to_get_started') }}</p>
                            </div>

                            <!-- Images Grid -->
                            <div v-else class="row g-5">
                                <div v-for="image in images" :key="image.id" class="col-sm-6 col-md-4 col-lg-3 col-12">
                                    <div class="card position-relative h-100">
                                        <!-- add them in -->
                                        <!-- Main Badge -->
                                        <div v-if="image.is_main" class="badge badge-primary position-absolute">
                                            <i class="ki-outline ki-star fs-5"></i> {{ trans('profile.main') }}
                                        </div>

                                        <!-- Approval Badge -->
                                        <div
                                            :class="[
                                                'badge position-absolute',
                                                image.is_approved ? 'badge-success' : 'badge-warning',
                                            ]"
                                            v-if="!image.is_main"
                                        >
                                            {{ image.is_approved ? trans('profile.approved') : trans('profile.pending') }}
                                        </div>

                                        <!-- Image -->
                                        <img
                                            :src="image.thumbnail_url"
                                            :alt="`Profile image ${image.id}`"
                                            class="card-img-top"
                                            style="height: 200px; object-fit: cover"
                                            @error="(e) => console.error('Image load error:', image.thumbnail_url, e)"
                                        />

                                        <!-- Actions -->
                                        <div class="card-body d-flex flex-column gap-2">
                                            <Link
                                                preserve-state
                                                :href="route('profile.gallery.set-main', image.id)"
                                                v-if="!image.is_main"
                                                @click="setAsMainImage(image.id)"
                                                class="btn btn-sm btn-light-primary w-100"
                                                preserve-scroll
                                            >
                                                <i class="ki-outline ki-star fs-4"></i>
                                                {{ trans('profile.set_as_main') }}
                                        </Link>
                                            <button preserve-scroll @click="confirmDelete(image.id)" class="btn btn-sm btn-light-danger w-100">
                                                <i class="ki-outline ki-trash fs-4"></i>
                                                {{ trans('profile.delete') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('profile.confirm_delete') }}</h5>
                    <button type="button" class="btn-close" @click="showDeleteModal = false"></button>
                </div>
                <div class="modal-body">
                    <p>{{ trans('profile.are_you_sure_you_want_to_delete_this_image_this_action_cannot_be_undone') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" @click="showDeleteModal = false">{{ trans('profile.cancel') }}</button>
                    <button type="button" class="btn btn-danger" @click="deleteImage">{{ trans('profile.delete') }}</button>
                </div>
            </div>
        </div>
    </div>
</template>
