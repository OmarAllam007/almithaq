<script setup lang="ts">
const props = defineProps({
    modelValue: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['update:modelValue']);

const updatePassword = (value: string) => {
    emit('update:modelValue', value);
};
</script>

<template>
    <div class="fv-row mb-5">
        <label class="required form-label">Password</label>
        <input
            type="password"
            class="form-control form-control-lg rounded-2"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
        />
    </div>
</template>

<style scoped></style>
