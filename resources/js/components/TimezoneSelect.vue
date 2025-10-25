<script setup lang="ts">
import { ref, computed } from 'vue';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';

interface Props {
    modelValue: string;
    error?: string;
    label?: string;
    required?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    label: 'Timezone',
    required: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

// Get all timezones from Intl API
const allTimezones =
    typeof Intl.supportedValuesOf === 'function'
        ? Intl.supportedValuesOf('timeZone')
        : [
            'UTC',
            'Europe/London',
            'Europe/Istanbul',
            'America/New_York',
            'Asia/Tokyo',
            'Asia/Shanghai',
            'Europe/Paris',
            'Europe/Berlin',
            'America/Los_Angeles',
            'Australia/Sydney',
        ];

const timezoneOptions = allTimezones;

const localValue = computed({
    get: () => props.modelValue,
    set: (value: string) => emit('update:modelValue', value),
});
</script>

<template>
    <div class="grid gap-2">
        <Label for="timezone">
            {{ label }}
            <span v-if="required" class="text-red-600">*</span>
        </Label>
        <Multiselect
            v-model="localValue"
            :options="timezoneOptions"
            placeholder="Select timezone..."
            :searchable="true"
            :clearable="false"
        />
        <InputError :message="error" />
    </div>
</template>

<style lang="postcss">
.multiselect {
    color: var(--foreground);
    background-color: transparent;
    border-radius: 0.375rem;
    border-width: 1px;
    border-color: var(--input);
    min-width: 0;
    width: 100%;
    font-size: 1rem;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.03);
    outline: none;
    transition:
        color 0.2s,
        box-shadow 0.2s;
    display: flex;
    min-height: 36px;
    max-height: 36px;
}

.multiselect__tags {
    background: color-mix(in oklab, var(--input) 30%, transparent);
    color: var(--primary-foreground);
    font-size: var(--text-xs);
    width: 100%;
    line-height: var(--tw-leading, var(--text-sm--line-height));
    border: 0 none;
    padding: 0;
}

.multiselect__single {
    background: transparent;
    color: var(--foreground);
    font-size: var(--text-sm);
    padding: 6px 0 0px 8px;
}

.multiselect__content-wrapper {
    max-height: 15rem;
    overflow-y: auto;
    border-radius: 0.375rem;
    box-shadow:
        0 10px 15px -3px rgb(0 0 0 / 0.1),
        0 4px 6px -2px rgb(0 0 0 / 0.05);
    background-color: var(--background);
    border: 1px solid var(--border);
    z-index: 100;
}

.multiselect__option--highlight {
    background-color: var(--primary);
    color: var(--primary-foreground);
}

.multiselect__option--selected {
    font-weight: 600;
}

.multiselect__option {
    padding: 0.5rem 1rem;
    cursor: pointer;
}
</style>
