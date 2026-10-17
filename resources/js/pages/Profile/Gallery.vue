<script setup lang="ts">
import { destroy, setMain, store } from '@/actions/App/Http/Controllers/ProfileImageController';
import { useLang } from '@/composables/useLang';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, onUnmounted, ref } from 'vue';
const { trans } = useLang();
const page = usePage();
const isRtl = computed(() => page.props.locale === 'ar');

interface Image {
    id: number;
    thumbnail_url: string;
    original_url: string;
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
        router.delete(destroy(imageToDelete.value).url, {
            onSuccess: () => {
                showDeleteModal.value = false;
                imageToDelete.value = null;
            },
        });
    }
};

const setAsMainImage = (imageId: number) => {
    router.patch(setMain(imageId).url);
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
    <div id="kt_app_content" class="app-content flex-column-fluid member-page gallery-page">
        <div id="kt_app_content_container" class="app-container container-xxl member-container">
            <section class="member-hero member-reveal">
                <div class="member-hero-art"></div>
                <div class="member-hero-copy">
                    <div class="member-eyebrow">{{ isRtl ? 'صورتك، قصتك' : 'YOUR STORY IN PHOTOS' }}</div>
                    <h1>{{ trans('profile.my_gallery') }}<span>.</span></h1>
                    <p>
                        {{
                            isRtl
                                ? 'أضف صوراً تعكس شخصيتك واختر الصورة التي تمثلك.'
                                : 'Share photos that feel like you and choose the one that represents you best.'
                        }}
                    </p>
                </div>
                <div class="member-hero-stat">
                    <strong>{{ images.length }}<small>/ 5</small></strong
                    ><span>{{ trans('profile.your_images') }}</span>
                </div>
            </section>
            <div class="member-section-head member-reveal">
                <div>
                    <small>{{ isRtl ? 'مساحتك الخاصة' : 'YOUR PHOTO SPACE' }}</small>
                    <h2>{{ trans('profile.your_images') }}</h2>
                </div>
                <span class="member-count">{{ images.length }} / 5</span>
            </div>
            <div class="gallery-guidance member-reveal">
                <span class="guidance-icon"><i class="ki-outline ki-shield-tick"></i></span>
                <div>
                    <h3>{{ trans('profile.profile_image_verification') }}</h3>
                    <p>{{ trans('profile.upload_your_profile_images_for_verification_please_note') }}</p>
                    <div class="guidance-points">
                        <span>{{ trans('profile.you_can_upload_up_to_5_images') }}</span
                        ><span>{{ trans('profile.only_jpeg_png_and_jpg_formats_are_supported') }}</span
                        ><span>{{ trans('profile.maximum_file_size_is_5mb_per_image') }}</span
                        ><span>{{ trans('profile.all_images_require_admin_approval_before_being_visible_to_others') }}</span>
                    </div>
                </div>
            </div>
            <section v-if="canUploadMore && (totalImages < 5 || selectedFiles.length)" class="gallery-upload-panel member-reveal">
                <div class="gallery-panel-head">
                    <h3>{{ trans('profile.upload_images') }}</h3>
                    <span>{{ totalImages }}/5</span>
                </div>
                <div
                    v-if="totalImages < 5"
                    class="gallery-drop-zone"
                    :class="{ dragging: isDragging }"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop"
                >
                    <input
                        id="imageUpload"
                        class="visually-hidden"
                        type="file"
                        multiple
                        accept="image/jpeg,image/png,image/jpg"
                        @change="handleFileSelect"
                    /><label for="imageUpload"
                        ><span class="drop-icon"><i class="ki-outline ki-file-up"></i></span
                        ><strong>{{ trans('profile.drop_images_here_or_click_to_upload') }}</strong
                        ><small>{{ trans('profile.you_can_upload') }} {{ 5 - totalImages }} {{ trans('profile.more_image_s') }}</small></label
                    >
                </div>
                <div v-if="selectedFiles.length" class="gallery-previews">
                    <h4>{{ trans('profile.selected_images') }}</h4>
                    <div class="gallery-preview-grid">
                        <div v-for="(file, index) in selectedFiles" :key="`${file.name}-${index}`" class="gallery-preview">
                            <img :src="getPreviewUrl(file)" :alt="file.name" /><button
                                type="button"
                                :aria-label="trans('profile.delete')"
                                @click="removeSelectedFile(index)"
                            >
                                <i class="ki-outline ki-cross"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="gallery-primary" :disabled="form.processing" @click="uploadImages">
                        <i class="ki-outline ki-cloud-upload"></i>{{ form.processing ? trans('profile.uploading') : trans('profile.upload_images') }}
                    </button>
                </div>
                <div v-if="form.errors.images" class="gallery-error">{{ form.errors.images }}</div>
            </section>
            <div v-if="!images.length" class="member-empty gallery-empty">
                <div class="member-empty-icon"><i class="ki-outline ki-picture"></i></div>
                <h3>{{ trans('profile.no_images_uploaded_yet') }}</h3>
                <p>{{ trans('profile.upload_your_first_image_to_get_started') }}</p>
            </div>
            <div v-else class="gallery-grid">
                <article v-for="image in images" :key="image.id" class="gallery-item">
                    <div class="gallery-image">
                        <img :src="image.original_url" :alt="`${trans('profile.gallery')} ${image.id}`" /><span
                            v-if="image.is_main"
                            class="gallery-badge main"
                            ><i class="ki-outline ki-star"></i>{{ trans('profile.main') }}</span
                        ><span v-else class="gallery-badge" :class="image.is_approved ? 'approved' : 'pending'">{{
                            image.is_approved ? trans('profile.approved') : trans('profile.pending')
                        }}</span>
                    </div>
                    <div class="gallery-item-actions">
                        <button v-if="!image.is_main" type="button" class="gallery-set-main" @click="setAsMainImage(image.id)">
                            <i class="ki-outline ki-star"></i>{{ trans('profile.set_as_main') }}</button
                        ><button type="button" class="gallery-delete" :aria-label="trans('profile.delete')" @click="confirmDelete(image.id)">
                            <i class="ki-outline ki-trash"></i>
                        </button>
                    </div>
                </article>
            </div>
        </div>
        <div
            v-if="showDeleteModal"
            class="modal fade show d-block gallery-modal"
            tabindex="-1"
            role="dialog"
            aria-modal="true"
            :aria-label="trans('profile.confirm_delete')"
        >
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
                        <button type="button" class="gallery-secondary" @click="showDeleteModal = false">{{ trans('profile.cancel') }}</button
                        ><button type="button" class="gallery-danger" @click="deleteImage">{{ trans('profile.delete') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
