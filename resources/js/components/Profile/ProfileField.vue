<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    label: string;
    value?: string | number | null;
    displayValue?: string | number | null;
    isEditing?: boolean;
    error?: string;
    type?: 'text' | 'email' | 'number' | 'textarea' | 'select';
    options?: { value: string; label: string }[];
    colSpan?: 'full' | 'half';
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    colSpan: 'full',
    isEditing: false,
});

const emit = defineEmits<{
    'update:value': [value: string | number];
}>();

const computedDisplayValue = computed(() => {
    const val = props.displayValue ?? props.value;
    if (val === null || val === undefined || val === '') {
        return 'Not specified';
    }
    return val;
});

const selectValue = computed(() => {
    if (props.value === null || props.value === undefined) {
        return '';
    }
    // Handle object values (extract id or name)
    if (typeof props.value === 'object') {
        return (props.value as { id?: number; name?: string }).id?.toString()
            ?? (props.value as { id?: number; name?: string }).name?.toString()
            ?? '';
    }
    return props.value.toString();
});

const handleInput = (event: Event) => {
    const target = event.target as HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement;
    emit('update:value', target.value);
};
</script>

<template>
    <div :class="['row mb-7', colSpan === 'half' ? 'col-lg-6' : '']">
        <label :class="['fw-semibold text-muted', colSpan === 'full' ? 'col-lg-4' : 'col-lg-8']">
            {{ label }}
        </label>
        <div :class="[colSpan === 'full' ? 'col-lg-8' : 'col-lg-4']">
            <template v-if="!isEditing">
                <span class="fw-bold fs-6 text-gray-800">{{ computedDisplayValue }}</span>
            </template>
            <template v-else>
                <textarea
                    v-if="type === 'textarea'"
                    :value="value"
                    @input="handleInput"
                    class="form-control form-control-solid"
                    rows="4"
                />
                <select
                    v-else-if="type === 'select'"
                    :value="selectValue"
                    @change="handleInput"
                    class="form-select form-select-solid"
                >
                    <option value="">Select {{ label }}</option>
                    <option
                        v-for="option in options"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
                <input
                    v-else
                    :type="type"
                    :value="value"
                    @input="handleInput"
                    class="form-control form-control-solid"
                />
                <div v-if="error" class="fv-plugins-message-container">
                    <div class="fv-help-block">{{ error }}</div>
                </div>
            </template>
        </div>
    </div>
</template>
