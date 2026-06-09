<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

interface Option {
    value: number | string;
    label: string;
}

const props = defineProps<{
    modelValue: (number | string)[];
    options: Option[];
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: (number | string)[]];
}>();

const selectEl = ref<HTMLSelectElement | null>(null);

onMounted(() => {
    if (!selectEl.value) return;

    // @ts-ignore
    $(selectEl.value).select2({
        theme: 'bootstrap5',
        placeholder: props.placeholder ?? '',
        allowClear: !!props.placeholder,
        width: '100%',
        multiple: true,
    });

    // @ts-ignore
    $(selectEl.value).on('change.select2', function (this: HTMLSelectElement) {
        // @ts-ignore
        const selected = $(this).val() as string[];
        emit('update:modelValue', selected ? selected.map(Number) : []);
    });

    if (props.modelValue?.length) {
        // @ts-ignore
        $(selectEl.value).val(props.modelValue).trigger('change.select2');
    }
});

watch(
    () => props.modelValue,
    (newVal) => {
        if (!selectEl.value) return;
        // @ts-ignore
        const current = $(selectEl.value).val() as string[];
        const currentNums = current ? current.map(Number) : [];
        const newNums = newVal ?? [];
        const same =
            currentNums.length === newNums.length &&
            currentNums.every((v, i) => v === newNums[i]);
        if (!same) {
            // @ts-ignore
            $(selectEl.value).val(newVal).trigger('change.select2');
        }
    },
);

onBeforeUnmount(() => {
    if (!selectEl.value) return;
    // @ts-ignore
    $(selectEl.value).select2('destroy');
});
</script>

<template>
    <select ref="selectEl" class="form-select form-select-lg" multiple>
        <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
    </select>
</template>
