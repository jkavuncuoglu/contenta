<script setup lang="ts">
import { computed } from 'vue';
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
    label: 'Theme Mode',
    required: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

interface ThemeModeOption {
    label: string;
    value: string;
    description: string;
}

const themeModeOptions: ThemeModeOption[] = [
    {
        label: 'Light',
        value: 'light',
        description: 'Always use light mode'
    },
    {
        label: 'Dark',
        value: 'dark',
        description: 'Always use dark mode'
    },
    {
        label: 'System',
        value: 'system',
        description: 'Use system preference'
    },
];

const localValue = computed({
    get: () => {
        const option = themeModeOptions.find(opt => opt.value === props.modelValue);
        return option || themeModeOptions[2]; // Default to system
    },
    set: (option: ThemeModeOption) => {
        emit('update:modelValue', option.value);
    },
});
</script>

<template>
    <div class="grid gap-2">
        <Label for="theme_mode">
            {{ label }}
            <span v-if="required" class="text-red-600">*</span>
        </Label>
        <Multiselect
            v-model="localValue"
            :options="themeModeOptions"
            label="label"
            track-by="value"
            placeholder="Select theme mode..."
            :searchable="false"
            :clearable="false"
            :show-labels="false"
        >
            <template #option="{ option }">
                <div class="flex flex-col">
                    <span class="font-medium">{{ option.label }}</span>
                    <span class="text-xs text-muted-foreground">{{ option.description }}</span>
                </div>
            </template>
            <template #singleLabel="{ option }">
                <span>{{ option.label }}</span>
            </template>
        </Multiselect>
        <InputError :message="error" />
    </div>
</template>

<style lang="postcss">
/* Styles are inherited from TimezoneSelect.vue component */
</style>

