<script setup lang="ts">
import { Icon } from '@iconify/vue';
import { ref, computed } from 'vue';
import { MdEditor } from 'md-editor-v3';
import 'md-editor-v3/lib/style.css';
import ColumnEditModal from '../ColumnEditModal.vue';

interface Column {
    title: string;
    content: string;
}

interface Props {
    config: {
        title?: string;
        subtitle?: string;
        columns?: Column[];
        numColumns?: 2 | 3 | 4;
        backgroundColor?: string;
    };
    editMode?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    config: () => ({
        title: 'Our Features',
        subtitle: 'Everything you need to succeed',
        columns: [
            {
                title: 'Feature 1',
                content: 'Description of feature 1',
            },
            {
                title: 'Feature 2',
                content: 'Description of feature 2',
            },
            {
                title: 'Feature 3',
                content: 'Description of feature 3',
            },
        ],
        numColumns: 3,
        backgroundColor: 'bg-white dark:bg-gray-900',
    }),
    editMode: false,
});

const emit = defineEmits<{
    (e: 'update:config', config: any): void;
}>();

const localConfig = ref({
    ...props.config,
    // Ensure columns is always an array
    columns: Array.isArray(props.config.columns) ? props.config.columns : []
});

const showColumnModal = ref(false);
const editingColumnIndex = ref<number | null>(null);
const editingColumn = ref<Column | null>(null);

const gridClass = computed(() => {
    const cols = localConfig.value.numColumns || 3;
    return {
        2: 'md:grid-cols-2',
        3: 'md:grid-cols-3',
        4: 'md:grid-cols-2 lg:grid-cols-4',
    }[cols];
});

const updateConfig = () => {
    emit('update:config', { ...localConfig.value });
};

const openColumnModal = (index: number) => {
    if (!props.editMode) return;

    editingColumnIndex.value = index;
    editingColumn.value = { ...localConfig.value.columns[index] };
    showColumnModal.value = true;
};

const saveColumn = (column: Column) => {
    if (editingColumnIndex.value !== null) {
        localConfig.value.columns[editingColumnIndex.value] = column;
        updateConfig();
    }
    editingColumnIndex.value = null;
    editingColumn.value = null;
};

const addColumn = () => {
    // Ensure columns is an array before pushing
    if (!Array.isArray(localConfig.value.columns)) {
        localConfig.value.columns = [];
    }
    localConfig.value.columns.push({
        title: 'New Feature',
        content: 'Feature description',
    });
    updateConfig();
};

const removeColumn = (index: number) => {
    if (localConfig.value.columns) {
        localConfig.value.columns.splice(index, 1);
        updateConfig();
    }
};
</script>

<template>
    <section class="py-16" :class="config.backgroundColor">
        <div class="container mx-auto px-4">
            <!-- Header -->
            <div v-if="config.title || config.subtitle || editMode" class="mx-auto mb-12 max-w-3xl text-center">
                <h2
                    v-if="!editMode && config.title"
                    class="mb-4 text-3xl font-bold text-gray-900 md:text-4xl dark:text-white"
                >
                    {{ config.title }}
                </h2>
                <input
                    v-if="editMode"
                    v-model="localConfig.title"
                    @input="updateConfig"
                    type="text"
                    class="mb-4 w-full text-center text-3xl font-bold text-gray-900 md:text-4xl dark:text-white bg-transparent border border-dashed border-gray-300 dark:border-gray-600 rounded px-4 py-2 focus:outline-none focus:border-blue-500"
                    placeholder="Section Title (optional)"
                />

                <p
                    v-if="!editMode && config.subtitle"
                    class="text-lg text-gray-600 dark:text-gray-300"
                >
                    {{ config.subtitle }}
                </p>
                <input
                    v-if="editMode"
                    v-model="localConfig.subtitle"
                    @input="updateConfig"
                    type="text"
                    class="w-full text-center text-lg text-gray-600 dark:text-gray-300 bg-transparent border border-dashed border-gray-300 dark:border-gray-600 rounded px-4 py-2 focus:outline-none focus:border-blue-500"
                    placeholder="Section Subtitle (optional)"
                />
            </div>

            <!-- Columns Grid -->
            <div
                class="mx-auto grid max-w-6xl grid-cols-1 gap-8"
                :class="gridClass"
            >
                <div
                    v-for="(column, index) in config.columns"
                    :key="index"
                    class="group relative rounded-xl bg-white p-8 shadow-sm transition-all hover:shadow-md dark:bg-gray-800"
                    :class="{
                        'border-2 border-dashed border-blue-300 dark:border-blue-600': editMode,
                        'cursor-pointer hover:border-blue-500': editMode
                    }"
                    @click.stop="editMode && openColumnModal(index)"
                >
                    <!-- Edit/Delete Buttons (Edit Mode Only) -->
                    <div v-if="editMode" class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button
                            @click.stop="openColumnModal(index)"
                            class="p-1 rounded hover:bg-blue-100 dark:hover:bg-blue-900/30"
                            title="Edit column"
                        >
                            <svg
                                class="h-5 w-5 text-blue-600 dark:text-blue-400"
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
                            @click.stop="removeColumn(index)"
                            class="p-1 rounded hover:bg-red-100 dark:hover:bg-red-900/30"
                            title="Remove column"
                        >
                        <svg
                            class="h-5 w-5 text-red-600 dark:text-red-400"
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

                    <!-- Column Title -->
                    <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                        {{ column.title }}
                    </h3>

                    <!-- Column Content -->
                    <div class="text-gray-600 dark:text-gray-400 prose dark:prose-invert max-w-none">
                        <div v-html="column.content"></div>
                    </div>
                </div>
            </div>

            <!-- Column Edit Modal -->
            <ColumnEditModal
                v-model="showColumnModal"
                :column="editingColumn"
                @save="saveColumn"
            />

            <!-- Add Column Button (Edit Mode Only) -->
            <div v-if="editMode" class="mt-8 text-center">
                <button
                    @click="addColumn"
                    class="inline-flex items-center rounded-md border border-dashed border-gray-400 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    <svg
                        class="mr-2 h-4 w-4"
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
                    Add Column
                </button>
            </div>
        </div>
    </section>
</template>
