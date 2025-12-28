<script setup lang="ts">
let props = defineProps({
    label: {
        type: String,
        required: true,
    },
    modelValue: {
        type: String,
        required: true,
    },
    options: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['update:modelValue'])

const updateModelValue = (value: string | undefined) => {
    emit('update:modelValue', value)
}
</script>

<template>
    <div class="fv-row mb-5">
        <label class="required form-label">{{ label }}</label>
        <select class="form-select form-select-lg rounded-2" :value="modelValue"
            @input="updateModelValue($event?.target?.value)">
            <option value="">Select...</option>
            <option v-for="option in options" :key="option" :value="option">{{ option }}</option>
        </select>
    </div>
</template>

<style scoped></style>
