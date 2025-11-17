<template>
    <div>
        <!-- Simple textarea-based editor as a stub for the real component -->
        <MdEditor
            v-model="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class="min-h-[200px] w-full rounded border p-2 dark:bg-gray-800 dark:text-white"
        />
    </div>
</template>

<script setup lang="ts">
import { MdEditor } from 'md-editor-v3';
import 'md-editor-v3/lib/style.css';
import { defineProps } from 'vue';

defineProps<{ modelValue?: string }>();
</script>
