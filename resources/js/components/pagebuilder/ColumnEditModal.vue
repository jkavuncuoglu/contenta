<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="w-1/2 max-h-[85vh] flex flex-col">
            <DialogHeader class="flex-shrink-0">
                <DialogTitle>Edit Column</DialogTitle>
                <DialogDescription>
                    Customize the title and content for this column
                </DialogDescription>
            </DialogHeader>

            <div class="flex-1 overflow-y-auto py-4 space-y-4">
                <!-- Column Title -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Column Title
                    </label>
                    <input
                        v-model="localColumn.title"
                        type="text"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Enter column title"
                    />
                </div>

                <!-- Column Content -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Column Content
                    </label>
                    <MdEditor
                        v-model="localColumn.content"
                        class="min-h-[200px]"
                    />
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Supports Markdown formatting
                    </p>
                </div>
            </div>

            <DialogFooter class="flex-shrink-0">
                <button
                    @click="closeModal"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    Cancel
                </button>
                <button
                    @click="saveColumn"
                    class="ml-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >
                    Save Changes
                </button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { MdEditor } from 'md-editor-v3';
import 'md-editor-v3/lib/style.css';

interface Column {
    title: string;
    content: string;
}

interface Props {
    column: Column | null;
    modelValue: boolean;
}

interface Emits {
    (e: 'update:modelValue', value: boolean): void;
    (e: 'save', column: Column): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const isOpen = ref(props.modelValue);
const localColumn = ref<Column>({
    title: props.column?.title || '',
    content: props.column?.content || '',
});

watch(
    () => props.modelValue,
    (value) => {
        isOpen.value = value;
        if (value && props.column) {
            localColumn.value = {
                title: props.column.title,
                content: props.column.content,
            };
        }
    },
);

watch(isOpen, (value) => {
    emit('update:modelValue', value);
});

const closeModal = () => {
    isOpen.value = false;
};

const saveColumn = () => {
    emit('save', { ...localColumn.value });
    closeModal();
};
</script>
