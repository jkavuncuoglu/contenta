<template>
    <div>
        <!-- Simple textarea-based editor as a stub for the real component -->
        <MdEditor
            :model-value="modelValue"
            @update:model-value="$emit('update:modelValue', $event)"
            class="min-h-[200px] w-full rounded border p-2 dark:bg-gray-800 dark:text-white"
        />
    </div>
</template>

<script setup lang="ts">
import { MdEditor } from 'md-editor-v3';
import 'md-editor-v3/lib/style.css';

defineProps<{ modelValue?: string }>();
defineEmits<{ 'update:modelValue': [value: string] }>();
</script>
