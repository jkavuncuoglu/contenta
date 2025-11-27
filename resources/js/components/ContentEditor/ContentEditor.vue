<template>
    <div>
        <MdEditor
            v-model="content"
            @change="emit('update:modelValue', content)"
            class="min-h-[200px] w-full rounded border p-2 dark:bg-gray-800 dark:text-white"
        />
    </div>
</template>

<script setup lang="ts">
import { MdEditor } from 'md-editor-v3';
import 'md-editor-v3/lib/style.css';
import { defineProps, defineEmits, ref, watch } from 'vue';

const props = defineProps<{ modelValue?: string }>();
const emit = defineEmits<{ (e: 'update:modelValue', value: string): void }>();
const content = ref(props.modelValue ?? '');
watch(
    () => props.modelValue,
    (val) => {
        if (val !== content.value) content.value = val ?? '';
    },
);
</script>
