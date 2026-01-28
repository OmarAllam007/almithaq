<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    title: string;
    price: string;
    duration: string;
    value: string;
    modelValue?: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const isSelected = computed(() => props.modelValue === props.value);

const select = () => {
    emit('update:modelValue', props.value);
};
</script>

<template>
    <!--begin::Tab link-->
    <label
        class="nav-link btn btn-outline btn-outline-dashed btn-color-dark d-flex flex-stack mb-6 p-6 text-start cursor-pointer"
        :class="{ 'btn-active btn-active-primary': isSelected }"
        @click="select"
    >
        <!--end::Description-->
        <div class="d-flex align-items-center me-2">
            <!--begin::Radio-->
            <div class="form-check form-check-custom form-check-solid form-check-success me-6 flex-shrink-0">
                <input
                    class="form-check-input"
                    type="radio"
                    name="plan"
                    :value="value"
                    :checked="isSelected"
                    @change="select"
                />
            </div>
            <!--end::Radio-->

            <!--begin::Info-->
            <div class="flex-grow-1">
                <div class="d-flex align-items-center fs-2 fw-bold flex-wrap">{{ duration }}</div>
            </div>
            <!--end::Info-->
        </div>
        <!--end::Description-->

        <!--begin::Price-->
        <div class="ms-5">
            <span class="mb-2">$</span>

            <span class="fs-3x fw-bold">{{ price }}</span>
        </div>
        <!--end::Price-->
    </label>
    <!--end::Tab link-->
</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
</style>

<style scoped></style>
