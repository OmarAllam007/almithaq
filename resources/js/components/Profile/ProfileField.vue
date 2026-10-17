<script setup lang="ts">
import Select2Input from '@/components/Inputs/Select2Input.vue';
import { useLang } from '@/composables/useLang';
import { computed } from 'vue';
const { trans } = useLang();
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
const props = withDefaults(defineProps<Props>(), { type: 'text', colSpan: 'full', isEditing: false });
const emit = defineEmits<{ 'update:value': [value: string | number] }>();
const computedDisplayValue = computed(() => {
    const value = props.displayValue ?? props.value;
    return value === null || value === undefined || value === '' ? trans('profile.not_specified') : value;
});
const selectValue = computed(() => (props.value === null || props.value === undefined ? '' : String(props.value)));
const handleInput = (event: Event) => emit('update:value', (event.target as HTMLInputElement | HTMLTextAreaElement).value);
</script>

<template>
    <div class="profile-field" :class="{ 'col-lg-6': colSpan === 'half' }">
        <label class="profile-field-label">{{ label }}</label>
        <div class="profile-field-value">
            <template v-if="!isEditing"
                ><span>{{ computedDisplayValue }}</span></template
            ><template v-else>
                <textarea v-if="type === 'textarea'" :value="value ?? ''" @input="handleInput" class="form-control" rows="4" /><Select2Input
                    v-else-if="type === 'select'"
                    :model-value="selectValue"
                    :options="options ?? []"
                    @update:model-value="(value) => emit('update:value', value)"
                /><input v-else :type="type" :value="value ?? ''" @input="handleInput" class="form-control" />
                <div v-if="error" class="field-error">{{ error }}</div></template
            >
        </div>
    </div>
</template>

<style scoped>
.profile-field {
    display: flex;
    flex-direction: column;
    gap: 7px;
    min-width: 0;
    margin-bottom: 18px;
}
.profile-field-label {
    color: #9a818e;
    font-size: 11px;
    font-weight: 800;
}
.profile-field-value {
    min-height: 25px;
    min-width: 0;
    color: #46313f;
    font-size: 13px;
    font-weight: 700;
    overflow-wrap: anywhere;
}
.profile-field-value :deep(.form-control),
.profile-field-value :deep(.form-select) {
    min-height: 40px;
    border: 1px solid #e9dde1;
    border-radius: 9px;
    background: #fffcfb;
    color: #493240;
    font-size: 12px;
}
.profile-field-value :deep(.form-control:focus),
.profile-field-value :deep(.form-select:focus) {
    border-color: #af6280;
    box-shadow: 0 0 0 3px #a342681c;
}
.field-error {
    margin-top: 5px;
    color: #c03d59;
    font-size: 11px;
}
</style>
