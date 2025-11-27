<template>
    <div class="page-builder-canvas">
        <!-- Canvas Header -->
        <div
            class="mb-4 flex items-center justify-between rounded-lg bg-gray-50 p-4 dark:bg-gray-700"
        >
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Page Canvas
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Click "Add Block" to build your page
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <button
                    @click="showBlockLibrary = true"
                    class="flex items-center rounded-md border border-blue-600 bg-blue-600 px-3 py-2 text-sm text-white hover:bg-blue-700"
                >
                    <svg
                        class="mr-1 h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        ></path>
                    </svg>
                    Add Block
                </button>
                <button
                    @click="togglePreviewMode"
                    class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-500 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500"
                >
                    {{ previewMode ? 'Edit Mode' : 'Preview Mode' }}
                </button>
                <button
                    @click="clearAll"
                    class="rounded-md border border-red-300 bg-red-100 px-3 py-2 text-sm text-red-700 hover:bg-red-200"
                >
                    Clear All
                </button>
            </div>
        </div>

        <!-- Canvas Content -->
        <div
            class="relative min-h-96 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600"
            :class="{ 'border-blue-400 dark:border-blue-500': isDragOver }"
            @dragover.prevent="handleDragOver"
            @dragleave.prevent="handleDragLeave"
            @drop.prevent="handleDrop"
        >
            <!-- Empty State -->
            <div
                v-if="sections.length === 0"
                class="absolute inset-0 flex items-center justify-center"
            >
                <div class="text-center">
                    <svg
                        class="mx-auto h-12 w-12 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                        ></path>
                    </svg>
                    <h3
                        class="mt-2 text-sm font-medium text-gray-900 dark:text-white"
                    >
                        No blocks yet
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Drag blocks from the library to start building your page
                    </p>
                </div>
            </div>

            <!-- Blocks/Sections -->
            <div v-else class="space-y-4 p-4">
                <draggable
                    v-model="sections"
                    group="blocks"
                    item-key="id"
                    @start="isDragging = true"
                    @end="isDragging = false"
                    @change="handleSectionChange"
                >
                    <template #item="{ element: section, index }">
                        <div class="block-wrapper group relative">
                            <!-- Block Controls (visible on hover) -->
                            <div
                                v-if="!previewMode"
                                class="absolute -top-2 -right-2 z-20 opacity-0 transition-opacity group-hover:opacity-100"
                            >
                                <div class="flex space-x-1">
                                    <button
                                        @click.stop="editBlock(section, index)"
                                        class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-white hover:bg-blue-700"
                                        title="Edit block"
                                    >
                                        <svg
                                            class="h-3 w-3"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                            ></path>
                                        </svg>
                                    </button>
                                    <button
                                        @click.stop="
                                            duplicateBlock(section, index)
                                        "
                                        class="flex h-6 w-6 items-center justify-center rounded-full bg-green-600 text-white hover:bg-green-700"
                                        title="Duplicate block"
                                    >
                                        <svg
                                            class="h-3 w-3"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                            ></path>
                                        </svg>
                                    </button>
                                    <button
                                        @click.stop="removeBlock(index)"
                                        class="flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-white hover:bg-red-700"
                                        title="Remove block"
                                    >
                                        <svg
                                            class="h-3 w-3"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            ></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Block Content -->
                            <div
                                class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-600"
                                :class="{
                                    'ring-2 ring-blue-500':
                                        selectedBlockId === section.id,
                                    'hover:border-blue-400 dark:hover:border-blue-500':
                                        !previewMode,
                                }"
                                @click="selectBlock(section, index)"
                            >
                                <BlockRenderer
                                    :section="section"
                                    :available-blocks="availableBlocks"
                                    :preview-mode="previewMode"
                                    :edit-mode="!previewMode"
                                    @update:config="updateBlockConfig(index, $event)"
                                />
                            </div>

                            <!-- Drag Handle -->
                            <div
                                v-if="!previewMode"
                                class="drag-handle absolute top-2 left-2 cursor-move opacity-0 transition-opacity group-hover:opacity-100"
                            >
                                <svg
                                    class="h-5 w-5 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"
                                    ></path>
                                </svg>
                            </div>
                        </div>
                    </template>
                </draggable>
            </div>

            <!-- Drop Zone Indicator -->
            <div
                v-if="isDragOver && !isDragging"
                class="pointer-events-none absolute inset-0 flex items-center justify-center bg-blue-50 dark:bg-blue-900/20"
            >
                <div
                    class="text-lg font-medium text-blue-600 dark:text-blue-400"
                >
                    Drop block here
                </div>
            </div>
        </div>

        <!-- Block Library Modal -->
        <div
            v-if="showBlockLibrary"
            class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black"
            @click.self="showBlockLibrary = false"
        >
            <div
                class="mx-4 flex max-h-[90vh] w-full max-w-4xl flex-col rounded-lg bg-white shadow-xl dark:bg-gray-800"
            >
                <!-- Modal Header -->
                <div
                    class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-700"
                >
                    <h3
                        class="text-lg font-medium text-gray-900 dark:text-white"
                    >
                        Block Library
                    </h3>
                    <button
                        @click="showBlockLibrary = false"
                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            ></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 overflow-y-auto p-6">
                    <BlockLibrary
                        :blocks="availableBlocks"
                        :categories="categories"
                        @add-block="handleAddBlockFromLibrary"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import draggable from 'vuedraggable';
import BlockLibrary from './BlockLibrary.vue';
import BlockRenderer from './BlockRenderer.vue';

interface Block {
    id: number;
    name: string;
    type: string;
    category: string;
    config_schema: any;
    preview_image?: string;
    description?: string;
    is_active: boolean;
}

interface Section {
    id: string;
    type: string;
    config: Record<string, any>;
}

interface Props {
    modelValue: Section[];
    availableBlocks: Block[];
    categories: Record<string, string>;
}

interface Emits {
    (e: 'update:modelValue', value: Section[]): void;
    (
        e: 'block-selected',
        block: { block: Block; section: Section; index: number } | null,
    ): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const showBlockLibrary = ref(false);

const sections = computed({
    get: () => props.modelValue || [],
    set: (value: Section[]) => emit('update:modelValue', value),
});

const isDragOver = ref(false);
const isDragging = ref(false);
const previewMode = ref(false);
const selectedBlockId = ref<string | null>(null);

const handleDragOver = (event: DragEvent) => {
    if (event.dataTransfer?.types.includes('text/block-type')) {
        isDragOver.value = true;
    }
};

const handleDragLeave = () => {
    isDragOver.value = false;
};

const handleDrop = (event: DragEvent) => {
    isDragOver.value = false;

    const blockType = event.dataTransfer?.getData('text/block-type');
    if (blockType) {
        addBlock(blockType);
    }
};

const addBlock = (blockType: string) => {
    const block = props.availableBlocks.find((b) => b.type === blockType);
    if (!block) return;

    const newSection: Section = {
        id: Date.now().toString() + Math.random().toString(36).substr(2, 9),
        type: blockType,
        config: getDefaultConfig(block),
    };

    sections.value = [...sections.value, newSection];
};

const getDefaultConfig = (block: Block) => {
    const defaults: Record<string, any> = {};
    const schema = block.config_schema || {};

    Object.entries(schema).forEach(([key, config]: [string, any]) => {
        defaults[key] = config.default || '';
    });

    return defaults;
};

const selectBlock = (section: Section, index: number) => {
    if (previewMode.value) return;

    selectedBlockId.value = section.id;

    // For features block, open the section settings modal
    // Column clicks have @click.stop, so they won't trigger this
    if (section.type === 'features') {
        const block = props.availableBlocks.find((b) => b.type === section.type);
        if (block) {
            emit('block-selected', { block, section, index });
        }
    }
    // For other blocks, just selecting doesn't open modal
};

const editBlock = (section: Section, index: number) => {
    if (previewMode.value) return;

    selectedBlockId.value = section.id;
    const block = props.availableBlocks.find((b) => b.type === section.type);

    if (block) {
        // Open section settings modal for all blocks including features
        emit('block-selected', { block, section, index });
    } else {
        console.error(
            'Block not found for type:',
            section.type,
            'Available types:',
            props.availableBlocks.map((b) => b.type),
        );
    }
};

const duplicateBlock = (section: Section, index: number) => {
    const duplicatedSection: Section = {
        id: Date.now().toString() + Math.random().toString(36).substr(2, 9),
        type: section.type,
        config: { ...section.config },
    };

    const newSections = [...sections.value];
    newSections.splice(index + 1, 0, duplicatedSection);
    sections.value = newSections;
};

const removeBlock = (index: number) => {
    if (confirm('Are you sure you want to remove this block?')) {
        const newSections = [...sections.value];
        newSections.splice(index, 1);
        sections.value = newSections;

        // Clear selection if removed block was selected
        const removedSection = sections.value[index];
        if (removedSection && selectedBlockId.value === removedSection.id) {
            selectedBlockId.value = null;
            emit('block-selected', null);
        }
    }
};

const clearAll = () => {
    if (confirm('Are you sure you want to clear all blocks?')) {
        sections.value = [];
        selectedBlockId.value = null;
        emit('block-selected', null);
    }
};

const togglePreviewMode = () => {
    previewMode.value = !previewMode.value;
    if (previewMode.value) {
        selectedBlockId.value = null;
        emit('block-selected', null);
    }
};

const handleSectionChange = () => {
    // This is called when drag and drop reorders sections
    // The sections array is automatically updated by vue-draggable
};

const handleAddBlockFromLibrary = (block: Block) => {
    // Add the block to the canvas
    addBlock(block.type);
    // Close the modal
    showBlockLibrary.value = false;
};

const updateBlockConfig = (index: number, config: Record<string, any>) => {
    const newSections = [...sections.value];
    if (newSections[index]) {
        newSections[index].config = { ...config };
        sections.value = newSections;
    }
};

// Watch for external selection changes
watch(
    () => selectedBlockId.value,
    (newId) => {
        if (!newId) {
            emit('block-selected', null);
        }
    },
);
</script>

<style scoped>
.drag-handle {
    cursor: grab;
}

.drag-handle:active {
    cursor: grabbing;
}

.sortable-ghost {
    opacity: 0.5;
}

.sortable-chosen {
    transform: scale(1.02);
}
</style>
