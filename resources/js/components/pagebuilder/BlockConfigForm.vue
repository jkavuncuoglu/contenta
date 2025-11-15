<template>
    <div class="block-config-form">
        <div v-if="!block" class="py-8 text-center">
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
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                ></path>
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                ></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                Select a block
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Click on a block in the canvas to configure its settings
            </p>
        </div>

        <div v-else>
            <!-- Block Header -->
            <div
                class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-600"
            >
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ block.name }}
                </h3>
                <p
                    v-if="block.description"
                    class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                >
                    {{ block.description }}
                </p>
            </div>

            <!-- Configuration Fields -->
            <div class="space-y-6">
                <div
                    v-for="(fieldConfig, fieldName) in block.config_schema"
                    :key="fieldName"
                    class="field-group"
                >
                    <!-- String Fields -->
                    <div v-if="fieldConfig.type === 'string'">
                        <label
                            :for="fieldName"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            {{ fieldConfig.label || fieldName }}
                            <span
                                v-if="fieldConfig.required"
                                class="text-red-500"
                                >*</span
                            >
                        </label>

                        <!-- Select field with options -->
                        <select
                            v-if="
                                fieldConfig.options &&
                                fieldConfig.options.length > 0
                            "
                            :id="fieldName"
                            v-model="localConfig[fieldName]"
                            :required="fieldConfig.required"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            @change="updateConfig"
                        >
                            <option value="">
                                {{
                                    fieldConfig.required ? 'Select...' : 'None'
                                }}
                            </option>
                            <option
                                v-for="option in fieldConfig.options"
                                :key="option"
                                :value="option"
                            >
                                {{ option }}
                            </option>
                        </select>

                        <!-- Textarea for longer text -->
                        <textarea
                            v-else-if="isLongText(fieldName)"
                            :id="fieldName"
                            v-model="localConfig[fieldName]"
                            :required="fieldConfig.required"
                            :placeholder="
                                fieldConfig.default ||
                                `Enter ${fieldConfig.label || fieldName}`
                            "
                            rows="4"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            @input="updateConfig"
                        ></textarea>

                        <!-- Regular input -->
                        <input
                            v-else
                            :id="fieldName"
                            v-model="localConfig[fieldName]"
                            :type="getInputType(fieldName)"
                            :required="fieldConfig.required"
                            :placeholder="
                                fieldConfig.default ||
                                `Enter ${fieldConfig.label || fieldName}`
                            "
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            @input="updateConfig"
                        />

                        <p
                            v-if="fieldConfig.description"
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            {{ fieldConfig.description }}
                        </p>
                    </div>

                    <!-- Number Fields -->
                    <div v-else-if="fieldConfig.type === 'number'">
                        <label
                            :for="fieldName"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            {{ fieldConfig.label || fieldName }}
                            <span
                                v-if="fieldConfig.required"
                                class="text-red-500"
                                >*</span
                            >
                        </label>
                        <input
                            :id="fieldName"
                            v-model.number="localConfig[fieldName]"
                            type="number"
                            :required="fieldConfig.required"
                            :placeholder="
                                fieldConfig.default ||
                                `Enter ${fieldConfig.label || fieldName}`
                            "
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            @input="updateConfig"
                        />
                        <p
                            v-if="fieldConfig.description"
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            {{ fieldConfig.description }}
                        </p>
                    </div>

                    <!-- Boolean Fields -->
                    <div
                        v-else-if="fieldConfig.type === 'boolean'"
                        class="flex items-center"
                    >
                        <input
                            :id="fieldName"
                            v-model="localConfig[fieldName]"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            @change="updateConfig"
                        />
                        <label
                            :for="fieldName"
                            class="ml-2 block text-sm text-gray-700 dark:text-gray-300"
                        >
                            {{ fieldConfig.label || fieldName }}
                            <span
                                v-if="fieldConfig.required"
                                class="text-red-500"
                                >*</span
                            >
                        </label>
                        <p
                            v-if="fieldConfig.description"
                            class="ml-6 text-sm text-gray-500 dark:text-gray-400"
                        >
                            {{ fieldConfig.description }}
                        </p>
                    </div>

                    <!-- Array Fields (simplified as comma-separated string) -->
                    <div v-else-if="fieldConfig.type === 'array'">
                        <label
                            :for="fieldName"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            {{ fieldConfig.label || fieldName }}
                            <span
                                v-if="fieldConfig.required"
                                class="text-red-500"
                                >*</span
                            >
                        </label>
                        <input
                            :id="fieldName"
                            v-model="localConfig[fieldName]"
                            type="text"
                            :required="fieldConfig.required"
                            :placeholder="
                                fieldConfig.default ||
                                'Enter comma-separated values'
                            "
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            @input="updateConfig"
                        />
                        <p
                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                        >
                            {{
                                fieldConfig.description ||
                                'Separate multiple values with commas'
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Preview Section -->
            <div
                class="mt-8 border-t border-gray-200 pt-6 dark:border-gray-600"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h4
                        class="text-md font-medium text-gray-900 dark:text-white"
                    >
                        Live Preview
                    </h4>
                    <button
                        @click="resetToDefaults"
                        class="text-sm text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                        Reset to Defaults
                    </button>
                </div>

                <div
                    class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700"
                >
                    <BlockRenderer
                        :section="{
                            id: 'preview',
                            type: block.type,
                            config: localConfig,
                        }"
                        :available-blocks="[block]"
                        :preview-mode="true"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, watch } from 'vue';
import BlockRenderer from './BlockRenderer.vue';

interface Block {
    id: number;
    name: string;
    type: string;
    config_schema: any;
    description?: string;
}

interface Props {
    block: Block | null;
    config: Record<string, any>;
}

interface Emits {
    (e: 'update:config', config: Record<string, any>): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const localConfig = reactive<Record<string, any>>({});

// Initialize local config when block or config changes
const initializeConfig = () => {
    if (props.block && props.config) {
        // Clear existing config
        Object.keys(localConfig).forEach((key) => {
            delete localConfig[key];
        });

        // Set defaults from schema
        const schema = props.block.config_schema || {};
        Object.entries(schema).forEach(([key, fieldConfig]: [string, any]) => {
            localConfig[key] = props.config[key] ?? fieldConfig.default ?? '';
        });
    }
};

const updateConfig = () => {
    emit('update:config', { ...localConfig });
};

const resetToDefaults = () => {
    if (props.block) {
        const schema = props.block.config_schema || {};
        Object.entries(schema).forEach(([key, fieldConfig]: [string, any]) => {
            localConfig[key] = fieldConfig.default ?? '';
        });
        updateConfig();
    }
};

const isLongText = (fieldName: string) => {
    return (
        fieldName.includes('content') ||
        fieldName.includes('description') ||
        fieldName.includes('text') ||
        fieldName.includes('bio')
    );
};

const getInputType = (fieldName: string) => {
    if (fieldName.includes('email')) return 'email';
    if (
        fieldName.includes('url') ||
        fieldName.includes('link') ||
        fieldName.includes('image')
    )
        return 'url';
    if (fieldName.includes('phone')) return 'tel';
    if (fieldName.includes('password')) return 'password';
    if (fieldName.includes('color')) return 'color';
    if (fieldName.includes('date')) return 'date';
    return 'text';
};

// Watch for prop changes
watch(() => [props.block, props.config], initializeConfig, { immediate: true });

onMounted(() => {
    initializeConfig();
});
</script>

<style scoped>
.field-group + .field-group {
    border-top: 1px solid #f3f4f6;
    padding-top: 1.5rem;
}

.dark .field-group + .field-group {
    border-top-color: #4b5563;
}
</style>
