<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="w-1/2 max-h-[85vh] flex flex-col">
            <DialogHeader class="flex-shrink-0">
                <DialogTitle>{{ block?.name }}</DialogTitle>
                <DialogDescription v-if="block?.description">
                    {{ block.description }}
                </DialogDescription>
            </DialogHeader>

            <div class="flex-1 overflow-y-auto py-4">
                <BlockConfigForm
                    v-if="block && section"
                    :block="block"
                    :config="section.config"
                    @update:config="handleConfigUpdate"
                />
            </div>

            <DialogFooter class="flex-shrink-0">
                <button
                    @click="closeModal"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    Done
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
import BlockConfigForm from './BlockConfigForm.vue';

interface Block {
    id: number;
    name: string;
    type: string;
    config_schema: any;
    description?: string;
}

interface Section {
    id: string;
    type: string;
    config: Record<string, any>;
}

interface Props {
    block: Block | null;
    section: Section | null;
    modelValue: boolean;
}

interface Emits {
    (e: 'update:modelValue', value: boolean): void;
    (e: 'update:config', config: Record<string, any>): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const isOpen = ref(props.modelValue);

watch(
    () => props.modelValue,
    (value) => {
        isOpen.value = value;
    },
);

watch(isOpen, (value) => {
    emit('update:modelValue', value);
});

const closeModal = () => {
    isOpen.value = false;
};

const handleConfigUpdate = (config: Record<string, any>) => {
    emit('update:config', config);
};
</script>
