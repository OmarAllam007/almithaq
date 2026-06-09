<script setup lang="ts">
import { ref } from 'vue';

defineProps({
    modelValue: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        default: 'Password',
    },
});

defineEmits(['update:modelValue']);

const showPassword = ref(false);
</script>

<template>
    <div class="fv-row mb-5">
        <label class="required form-label">{{ label }}</label>
        <div class="position-relative">
            <input
                :type="showPassword ? 'text' : 'password'"
                class="form-control form-control-lg rounded-2 pe-12"
                :value="modelValue"
                @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
            />
            <button
                type="button"
                class="toggle-btn position-absolute top-50 translate-middle-y text-muted"
                @click="showPassword = !showPassword"
                tabindex="-1"
            >
                <i :class="showPassword ? 'ki-outline ki-eye-slash fs-3' : 'ki-outline ki-eye fs-3'"></i>
            </button>
        </div>
    </div>
</template>

<style scoped>
.toggle-btn {
    right: 0.75rem;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    line-height: 1;
}

/* Keep button on the right side even in RTL */
[dir='rtl'] .toggle-btn {
    right: auto;
    left: 0.75rem;
}
</style>
