<script setup lang="ts">
import MarkdownPageEditor from '@/components/PageBuilder/MarkdownPageEditor.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref, toRef } from 'vue';
import { useShortcodeValidation } from '@/composables/useShortcodeValidation';

interface Page {
    id: number;
    title: string;
    slug: string;
    markdown_content?: string;
    layout_template?: string;
    status: 'draft' | 'published' | 'archived';
    meta_title?: string;
    meta_description?: string;
    meta_keywords?: string;
    schema_data?: {
        type?: string;
        headline?: string;
        author?: string;
        publisher?: string;
        datePublished?: string;
        brand?: string;
        price?: string;
    };
    author?: {
        id: number;
        name: string;
    };
}

const props = defineProps<{
    page: Page;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: 'Pages', href: '/admin/pages' },
    { label: 'Edit Page', href: `/admin/pages/${props.page.id}/edit` },
]);

const activeTab = ref<'editor' | 'settings'>('editor');
const saving = ref(false);
const showValidationSidebar = ref(true);

const form = useForm({
    title: props.page.title || '',
    slug: props.page.slug || '',
    markdown_content: props.page.markdown_content || '',
    layout_template: props.page.layout_template || 'default',
    status: props.page.status || 'draft',
    meta_title: props.page.meta_title || '',
    meta_description: props.page.meta_description || '',
    meta_keywords: props.page.meta_keywords || '',
    schema_data: props.page.schema_data || {},
});

// Use shortcode validation
const { validating, validationErrors, isValid } = useShortcodeValidation(
    toRef(form, 'markdown_content'),
    800 // debounce 800ms
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
    form.put(`/admin/pages/${props.page.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            saving.value = false;
        },
        onError: () => {
            saving.value = false;
        },
    });
};

const publishPage = () => {
    // Prevent publishing if there are validation errors
    if (!isValid.value) {
        alert('Please fix all validation errors before publishing.');
        return;
    }

    if (confirm('Are you sure you want to publish this page?')) {
        router.post(`/admin/pages/${props.page.id}/publish`, {}, {
            preserveScroll: true,
        });
    }
};

const previewPage = async () => {
    try {
        const response = await fetch(`/admin/pages/${props.page.id}/preview`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                markdown_content: form.markdown_content,
                layout_template: form.layout_template,
            }),
        });

        if (response.ok) {
            const data = await response.json();
            const previewWindow = window.open('', '_blank');
            if (previewWindow) {
                previewWindow.document.write(data.html);
                previewWindow.document.close();
            }
        } else {
            alert('Failed to generate preview');
        }
    } catch (error) {
        console.error('Error generating preview:', error);
        alert('Failed to generate preview');
    }
};

const generateSlug = () => {
    form.slug = form.title
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
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
    <Head :title="`Edit ${page.title} - Pages`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex gap-6">
            <!-- Main Content Area -->
            <div class="flex-1 space-y-6">
                <!-- Page header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-neutral-900 dark:text-white">
                            Edit Page
                        </h1>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                            {{ page.title }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="showValidationSidebar = !showValidationSidebar"
                            class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
                            :class="{ 'ring-2 ring-red-500': validationErrors.length > 0 }"
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
                            <span v-if="validationErrors.length > 0" class="text-red-600 dark:text-red-400">
                                {{ validationErrors.length }} {{ validationErrors.length === 1 ? 'Error' : 'Errors' }}
                            </span>
                            <span v-else class="text-green-600 dark:text-green-400">Valid</span>
                        </button>
                        <button
                            @click="previewPage"
                            class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
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
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                ></path>
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                ></path>
                            </svg>
                            Preview
                        </button>
                        <button
                            v-if="page.status === 'draft'"
                            @click="publishPage"
                            :disabled="saving || !isValid"
                            class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
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
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
                                ></path>
                            </svg>
                            Publish
                        </button>
                        <button
                            @click="savePage"
                            :disabled="saving || !isValid"
                            class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
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

                <!-- Tabs -->
                <div class="border-b border-neutral-200 dark:border-neutral-700">
                    <nav class="-mb-px flex space-x-8">
                        <button
                            @click="activeTab = 'editor'"
                            :class="[
                                activeTab === 'editor'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-300',
                                'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
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
                                'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
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
                                    class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-600 inset-shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                                <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">
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
                                    class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 inset-shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                                <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.slug }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Markdown Editor -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                        <label class="mb-4 block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                            Content *
                        </label>
                        <MarkdownPageEditor v-model="form.markdown_content" />
                        <p v-if="form.errors.markdown_content" class="mt-1 text-sm text-red-600">
                            {{ form.errors.markdown_content }}
                        </p>
                    </div>
                </div>

                <!-- Settings Tab -->
                <div v-show="activeTab === 'settings'" class="space-y-6">
                    <!-- Layout Settings -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                        <h3 class="mb-4 text-lg font-medium text-neutral-900 dark:text-white">
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
                                    class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
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
                                    class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Settings -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                        <h3 class="mb-4 text-lg font-medium text-neutral-900 dark:text-white">
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
                                    class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
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
                                    class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
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
                                    class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Schema.org Structured Data -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                        <h3 class="mb-4 text-lg font-medium text-neutral-900 dark:text-white">
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
                                    class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
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

                            <div v-if="form.schema_data.type" class="grid grid-cols-1 gap-4 md:grid-cols-2">
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
                                        class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
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
                                        class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                    />
                                </div>
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
                <div class="sticky top-6 rounded-lg bg-white p-4 shadow dark:bg-neutral-800">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-white">
                            Validation
                        </h3>
                        <button
                            @click="showValidationSidebar = false"
                            class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Validating indicator -->
                    <div v-if="validating" class="flex items-center gap-2 text-sm text-neutral-500 dark:text-neutral-400 mb-4">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Validating...
                    </div>

                    <!-- No errors state -->
                    <div
                        v-else-if="validationErrors.length === 0"
                        class="flex items-center gap-2 rounded-lg bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-400"
                    >
                        <svg class="h-5 w-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span>No validation errors</span>
                    </div>

                    <!-- Error list -->
                    <div v-else class="space-y-3">
                        <div class="flex items-center gap-2 text-sm font-medium text-neutral-900 dark:text-white mb-2">
                            <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <span>{{ validationErrors.length }} {{ validationErrors.length === 1 ? 'Error' : 'Errors' }} Found</span>
                        </div>

                        <div
                            v-for="(error, index) in validationErrors"
                            :key="index"
                            class="rounded-lg border p-3"
                            :class="errorTypeColor(error.type)"
                        >
                            <div class="flex items-start gap-2">
                                <span class="text-lg flex-shrink-0">{{ errorTypeIcon(error.type) }}</span>
                                <div class="flex-1 min-w-0">
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
                                        <span v-if="error.line">Line {{ error.line }}</span>
                                        <span v-if="error.line && error.column">, </span>
                                        <span v-if="error.column">Column {{ error.column }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Help text -->
                    <div class="mt-4 rounded-lg bg-blue-50 p-3 text-xs text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                        <p class="font-medium mb-1">💡 Quick Tips:</p>
                        <ul class="list-disc list-inside space-y-1 ml-1">
                            <li>Errors disappear as you fix them</li>
                            <li>Save is disabled until all errors are resolved</li>
                            <li>Check the shortcode syntax documentation for help</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
