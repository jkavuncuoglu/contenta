<script setup lang="ts">
import MarkdownPageEditor from '@/components/PageBuilder/MarkdownPageEditor.vue';
import { useShortcodeValidation } from '@/composables/useShortcodeValidation';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, toRef } from 'vue';

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: 'Pages', href: '/admin/pages' },
    { label: 'Create Page', href: '/admin/pages/create' },
]);

const activeTab = ref<'editor' | 'settings'>('editor');
const saving = ref(false);
const showValidationSidebar = ref(true);

const form = useForm({
    title: '',
    slug: '',
    markdown_content: '',
    layout_template: 'default',
    status: 'draft',
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    schema_data: {},
    storage_driver: 'database',
    commit_message: '',
});

// Computed property to check if commit message is required
const requiresCommitMessage = computed(() => {
    return ['github', 'gitlab', 'bitbucket'].includes(form.storage_driver || '');
});

// Use shortcode validation
const { validating, validationErrors, isValid } = useShortcodeValidation(
    toRef(form, 'markdown_content'),
    800, // debounce 800ms
);

const layoutTemplates = [
    { value: 'default', label: 'Default Layout' },
    { value: 'full-width', label: 'Full Width' },
    { value: 'sidebar-left', label: 'Sidebar Left' },
    { value: 'sidebar-right', label: 'Sidebar Right' },
];

const schemaTypes = [
    { value: '', label: 'None' },
    { value: 'Article', label: 'Article' },
    { value: 'WebPage', label: 'Web Page' },
    { value: 'Product', label: 'Product' },
    { value: 'Event', label: 'Event' },
    { value: 'Organization', label: 'Organization' },
    { value: 'Person', label: 'Person' },
];

const savePage = () => {
    // Prevent saving if there are validation errors
    if (!isValid.value) {
        alert('Please fix all validation errors before saving.');
        return;
    }

    saving.value = true;
    form.post('/admin/pages', {
        preserveScroll: true,
        onSuccess: () => {
            saving.value = false;
        },
        onError: () => {
            saving.value = false;
        },
    });
};

const generateSlug = () => {
    if (!form.slug) {
        form.slug = form.title
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
};

const errorTypeIcon = (type: string) => {
    switch (type) {
        case 'render':
            return '⚠️';
        case 'parse':
            return '❌';
        case 'fatal':
            return '🔴';
        default:
            return 'ℹ️';
    }
};

const errorTypeColor = (type: string) => {
    switch (type) {
        case 'render':
            return 'text-yellow-700 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20';
        case 'parse':
            return 'text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20';
        case 'fatal':
            return 'text-red-800 dark:text-red-300 bg-red-100 dark:bg-red-900/30';
        default:
            return 'text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20';
    }
};
</script>

<template>
    <Head title="Create Page - Pages" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Page header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-semibold text-neutral-900 dark:text-white"
                    >
                        Create New Page
                    </h1>
                    <p
                        class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"
                    >
                        Create a new page using markdown and shortcodes
                    </p>
                </div>
                <div class="flex gap-2">
                        <button
                            @click="
                                showValidationSidebar = !showValidationSidebar
                            "
                            class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
                            :class="{
                                'ring-2 ring-red-500':
                                    validationErrors.length > 0,
                            }"
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
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
                                ></path>
                            </svg>
                            <span
                                v-if="validationErrors.length > 0"
                                class="text-red-600 dark:text-red-400"
                            >
                                {{ validationErrors.length }}
                                {{
                                    validationErrors.length === 1
                                        ? 'Error'
                                        : 'Errors'
                                }}
                            </span>
                            <span
                                v-else
                                class="text-green-600 dark:text-green-400"
                                >Valid</span
                            >
                        </button>
                        <button
                            @click="savePage"
                            :disabled="saving || !isValid"
                            class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
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
                                    d="M5 13l4 4L19 7"
                                ></path>
                            </svg>
                            {{ saving ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-neutral-200 dark:border-neutral-700">
                <nav class="-mb-px flex space-x-8">
                    <button
                        @click="activeTab = 'editor'"
                        :class="[
                            activeTab === 'editor'
                                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-300',
                            'border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap',
                        ]"
                    >
                        Markdown Editor
                    </button>
                    <button
                        @click="activeTab = 'settings'"
                        :class="[
                            activeTab === 'settings'
                                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-300',
                            'border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap',
                        ]"
                    >
                        Settings
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div v-show="activeTab === 'editor'" class="space-y-6">
                <!-- Basic Info -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label
                                for="title"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Title *
                            </label>
                            <input
                                id="title"
                                v-model="form.title"
                                type="text"
                                required
                                @blur="generateSlug"
                                class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                            />
                            <p
                                v-if="form.errors.title"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.title }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="slug"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Slug *
                            </label>
                            <input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                required
                                class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                            />
                            <p
                                v-if="form.errors.slug"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.slug }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Markdown Editor -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                    <label
                        class="mb-4 block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                    >
                        Content *
                    </label>
                    <MarkdownPageEditor v-model="form.markdown_content" />
                    <p
                        v-if="form.errors.markdown_content"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ form.errors.markdown_content }}
                    </p>
                </div>
            </div>

            <!-- Settings Tab -->
            <div v-show="activeTab === 'settings'" class="space-y-6">
                <!-- Layout Settings -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                    <h3
                        class="mb-4 text-lg font-medium text-neutral-900 dark:text-white"
                    >
                        Layout Settings
                    </h3>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label
                                for="layout_template"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Layout Template
                            </label>
                            <select
                                id="layout_template"
                                v-model="form.layout_template"
                                class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                            >
                                <option
                                    v-for="template in layoutTemplates"
                                    :key="template.value"
                                    :value="template.value"
                                >
                                    {{ template.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                for="status"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Status
                            </label>
                            <select
                                id="status"
                                v-model="form.status"
                                class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                            >
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Storage Settings -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                    <h3
                        class="mb-4 text-lg font-medium text-neutral-900 dark:text-white"
                    >
                        Storage Settings
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label
                                for="storage_driver"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Storage Driver
                            </label>
                            <select
                                id="storage_driver"
                                v-model="form.storage_driver"
                                class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                            >
                                <option value="database">Database (Default)</option>
                                <option value="local">Local Filesystem</option>
                                <option value="s3">Amazon S3</option>
                                <option value="azure">Azure Blob Storage</option>
                                <option value="gcs">Google Cloud Storage</option>
                                <option value="github">GitHub</option>
                            </select>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Choose where to store page content
                            </p>
                        </div>

                        <!-- Commit Message (for Git-based storage) -->
                        <div v-if="requiresCommitMessage" class="space-y-2">
                            <label
                                for="commit_message"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Commit Message
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="commit_message"
                                v-model="form.commit_message"
                                type="text"
                                required
                                placeholder="Add new page: [title]"
                                class="block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                            />
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                Required for {{ form.storage_driver }} storage
                            </p>
                            <div
                                v-if="form.errors.commit_message"
                                class="text-sm text-red-600 dark:text-red-400"
                            >
                                {{ form.errors.commit_message }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEO Settings -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                    <h3
                        class="mb-4 text-lg font-medium text-neutral-900 dark:text-white"
                    >
                        SEO Settings
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label
                                for="meta_title"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Meta Title
                            </label>
                            <input
                                id="meta_title"
                                v-model="form.meta_title"
                                type="text"
                                class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                            />
                        </div>

                        <div>
                            <label
                                for="meta_description"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Meta Description
                            </label>
                            <textarea
                                id="meta_description"
                                v-model="form.meta_description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                            ></textarea>
                        </div>

                        <div>
                            <label
                                for="meta_keywords"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Meta Keywords
                            </label>
                            <input
                                id="meta_keywords"
                                v-model="form.meta_keywords"
                                type="text"
                                placeholder="keyword1, keyword2, keyword3"
                                class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                            />
                        </div>
                    </div>
                </div>

                <!-- Schema.org Structured Data -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                    <h3
                        class="mb-4 text-lg font-medium text-neutral-900 dark:text-white"
                    >
                        Structured Data (Schema.org)
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label
                                for="schema_type"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Schema Type
                            </label>
                            <select
                                id="schema_type"
                                v-model="form.schema_data.type"
                                class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                            >
                                <option
                                    v-for="type in schemaTypes"
                                    :key="type.value"
                                    :value="type.value"
                                >
                                    {{ type.label }}
                                </option>
                            </select>
                        </div>

                        <div
                            v-if="form.schema_data.type"
                            class="grid grid-cols-1 gap-4 md:grid-cols-2"
                        >
                            <div>
                                <label
                                    for="schema_headline"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Headline
                                </label>
                                <input
                                    id="schema_headline"
                                    v-model="form.schema_data.headline"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                />
                            </div>

                            <div>
                                <label
                                    for="schema_author"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Author
                                </label>
                                <input
                                    id="schema_author"
                                    v-model="form.schema_data.author"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Validation Sidebar -->
            <div
                v-if="showValidationSidebar"
                class="w-80 flex-shrink-0 space-y-4"
            >
                <div
                    class="sticky top-6 rounded-lg bg-white p-4 shadow dark:bg-neutral-800"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h3
                            class="text-lg font-medium text-neutral-900 dark:text-white"
                        >
                            Validation
                        </h3>
                        <button
                            @click="showValidationSidebar = false"
                            class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300"
                        >
                            <svg
                                class="h-5 w-5"
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

                    <!-- Validating indicator -->
                    <div
                        v-if="validating"
                        class="mb-4 flex items-center gap-2 text-sm text-neutral-500 dark:text-neutral-400"
                    >
                        <svg
                            class="h-4 w-4 animate-spin"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        Validating...
                    </div>

                    <!-- No errors state -->
                    <div
                        v-else-if="validationErrors.length === 0"
                        class="flex items-center gap-2 rounded-lg bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-400"
                    >
                        <svg
                            class="h-5 w-5 flex-shrink-0"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            ></path>
                        </svg>
                        <span>No validation errors</span>
                    </div>

                    <!-- Error list -->
                    <div v-else class="space-y-3">
                        <div
                            class="mb-2 flex items-center gap-2 text-sm font-medium text-neutral-900 dark:text-white"
                        >
                            <svg
                                class="h-5 w-5 text-red-500"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd"
                                ></path>
                            </svg>
                            <span
                                >{{ validationErrors.length }}
                                {{
                                    validationErrors.length === 1
                                        ? 'Error'
                                        : 'Errors'
                                }}
                                Found</span
                            >
                        </div>

                        <div
                            v-for="(error, index) in validationErrors"
                            :key="index"
                            class="rounded-lg border p-3"
                            :class="errorTypeColor(error.type)"
                        >
                            <div class="flex items-start gap-2">
                                <span class="flex-shrink-0 text-lg">{{
                                    errorTypeIcon(error.type)
                                }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium capitalize">
                                        {{ error.type }} Error
                                    </p>
                                    <p class="mt-1 text-sm break-words">
                                        {{ error.message }}
                                    </p>
                                    <p
                                        v-if="error.line || error.column"
                                        class="mt-1 text-xs opacity-75"
                                    >
                                        <span v-if="error.line"
                                            >Line {{ error.line }}</span
                                        >
                                        <span v-if="error.line && error.column"
                                            >,
                                        </span>
                                        <span v-if="error.column"
                                            >Column {{ error.column }}</span
                                        >
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Help text -->
                    <div
                        class="mt-4 rounded-lg bg-blue-50 p-3 text-xs text-blue-800 dark:bg-blue-900/20 dark:text-blue-400"
                    >
                        <p class="mb-1 font-medium">💡 Quick Tips:</p>
                        <ul class="ml-1 list-inside list-disc space-y-1">
                            <li>Errors disappear as you fix them</li>
                            <li>
                                Save is disabled until all errors are resolved
                            </li>
                            <li>
                                Check the shortcode syntax documentation for
                                help
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
    </AppLayout>
</template>
