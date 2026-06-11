<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

interface Option {
    value: number | string;
    label: string;
}

const props = defineProps<{
    modelValue: number | string;
    options: Option[];
    placeholder?: string;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: number | string];
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
    });

    // @ts-ignore
    $(selectEl.value).on('change.select2', function (this: HTMLSelectElement) {
        emit('update:modelValue', this.value);
    });

    if (props.modelValue !== '' && props.modelValue !== null && props.modelValue !== undefined) {
        // @ts-ignore
        $(selectEl.value).val(props.modelValue).trigger('change.select2');
    }

    // @ts-ignore
    $(selectEl.value).prop('disabled', !!props.disabled);
});

watch(
    () => props.modelValue,
    (newVal) => {
        if (!selectEl.value) return;
        // @ts-ignore
        const current = $(selectEl.value).val();
        if (String(current) !== String(newVal)) {
            // @ts-ignore
            $(selectEl.value).val(newVal).trigger('change.select2');
        }
    },
);

// When the option list changes (e.g. cascading selects), let select2 re-sync
// its rendered selection against the freshly rendered <option> elements.
watch(
    () => props.options,
    async () => {
        await nextTick();
        if (!selectEl.value) return;
        // @ts-ignore
        $(selectEl.value).val(props.modelValue ?? '').trigger('change.select2');
    },
    { deep: true },
);

watch(
    () => props.disabled,
    (val) => {
        if (!selectEl.value) return;
        // @ts-ignore
        $(selectEl.value).prop('disabled', !!val);
    },
);

onBeforeUnmount(() => {
    if (!selectEl.value) return;
    // @ts-ignore
    $(selectEl.value).select2('destroy');
});
</script>

<template>
    <select ref="selectEl" class="form-select form-select-lg">
        <option v-if="placeholder" value=""></option>
        <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
    </select>
</template>

<style>
[dir='rtl'] .select2-container--bootstrap5 .select2-selection__clear {
    right: auto;
    left: 3rem;
}
</style>
