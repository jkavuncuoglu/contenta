<template>
    <Head title="Edit Post" />
    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-6 p-4">
            <!-- Page header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Post</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Update your blog post or article
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <Link
                        href="/admin/posts"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Cancel
                    </Link>
                </div>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Title -->
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                            <!-- General errors -->
                            <div v-if="errors.general" class="mb-4 rounded-md bg-red-50 p-4 dark:bg-red-900/20">
                                <p class="text-sm text-red-600 dark:text-red-400">
                                    {{ errors.general[0] }}
                                </p>
                            </div>

                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Title *
                                </label>
                                <input
                                    id="title"
                                    v-model="form.title"
                                    type="text"
                                    required
                                    class="p-2 mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                    placeholder="Enter your post title..."
                                    @input="generateSlug"
                                />
                                <div v-if="errors.title" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ errors.title[0] }}
                                </div>
                            </div>

                            <!-- Slug -->
                            <div class="mt-4">
                                <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Slug
                                </label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-400 text-sm">
                                        posts/
                                    </span>
                                    <input
                                        id="slug"
                                        v-model="form.slug"
                                        type="text"
                                        class="p-2 border flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                        placeholder="post-slug"
                                    />
                                </div>
                                <div v-if="errors.slug" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ errors.slug[0] }}
                                </div>
                            </div>
                        </div>

                        <MdEditor v-model="form.content_markdown" language="en-US" :theme="theme" />

                        <div v-if="errors.content_markdown" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ errors.content_markdown[0] }}
                        </div>

                        <!-- Excerpt -->
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                            <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Excerpt
                            </label>
                            <textarea
                                id="excerpt"
                                v-model="form.excerpt"
                                rows="3"
                                class="p-2 mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                placeholder="Optional excerpt for post previews..."
                            ></textarea>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Brief description of your post. If left empty, it will be generated automatically.
                            </p>
                            <div v-if="errors.excerpt" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ errors.excerpt[0] }}
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Publish -->
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Publish</h3>

                            <div class="space-y-4">
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Status
                                    </label>
                                    <select
                                        id="status"
                                        v-model="form.status"
                                        class="p-2 mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="draft">Draft</option>
                                        <option value="published">Published</option>
                                        <option value="scheduled">Scheduled</option>
                                        <option value="private">Private</option>
                                    </select>
                                </div>

                                <!-- Scheduled publish date -->
                                <div v-if="form.status === 'scheduled'" class="space-y-3">
                                    <div>
                                        <label for="published_date"
                                               class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Publish Date
                                        </label>
                                        <input
                                            id="published_date"
                                            v-model="publishDate"
                                            type="date"
                                            class="p-2 mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label for="published_time"
                                               class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Publish Time
                                        </label>
                                        <input
                                            id="published_time"
                                            v-model="publishTime"
                                            type="time"
                                            step="60"
                                            class="p-2 mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label for="timezone"
                                               class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Timezone
                                        </label>
                                        <select
                                            id="timezone"
                                            v-model="selectedTimezone"
                                            class="p-2 mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                        >
                                            <option value="UTC">UTC</option>
                                            <option value="America/New_York">Eastern Time (ET)</option>
                                            <option value="America/Chicago">Central Time (CT)</option>
                                            <option value="America/Denver">Mountain Time (MT)</option>
                                            <option value="America/Los_Angeles">Pacific Time (PT)</option>
                                            <option value="Europe/London">London (GMT)</option>
                                            <option value="Europe/Paris">Paris (CET)</option>
                                            <option value="Europe/Istanbul">Istanbul (TRT)</option>
                                            <option value="Asia/Dubai">Dubai (GST)</option>
                                            <option value="Asia/Tokyo">Tokyo (JST)</option>
                                            <option value="Asia/Shanghai">Shanghai (CST)</option>
                                            <option value="Australia/Sydney">Sydney (AEDT)</option>
                                        </select>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Time will be converted to UTC for storage
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit buttons -->
                            <div class="mt-6 flex flex-col space-y-2">
                                <button
                                    type="submit"
                                    :disabled="loading"
                                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="loading" class="flex items-center">
                                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                        Updating...
                                    </span>
                                    <span v-else>
                                        Update Post
                                    </span>
                                </button>

                                <button
                                    v-if="form.status !== 'published'"
                                    @click="updateAndPublish"
                                    type="button"
                                    :disabled="loading"
                                    class="w-full flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Update & Publish
                                </button>
                            </div>
                        </div>

                        <!-- Categories -->
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Categories</h3>
                            <div class="space-y-2">
                                <!-- TODO: Load categories dynamically -->
                                <div class="flex items-center">
                                    <input
                                        id="category-1"
                                        type="checkbox"
                                        value="1"
                                        v-model="form.categories"
                                        class="p-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700"
                                    />
                                    <label for="category-1" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                        Technology
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input
                                        id="category-2"
                                        type="checkbox"
                                        value="2"
                                        v-model="form.categories"
                                        class="p-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700"
                                    />
                                    <label for="category-2" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                        Programming
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Tags</h3>
                            <div>
                                <input
                                    type="text"
                                    v-model="tagInput"
                                    @keydown.enter.prevent="addTag"
                                    @keydown="handleTagKeydown"
                                    placeholder="Add tags separated by commas or press Enter"
                                    class="p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Press Enter or comma to add tags
                                </p>

                                <!-- Tag list -->
                                <div v-if="form.tags.length > 0" class="mt-3 flex flex-wrap gap-2">
                                    <span
                                        v-for="(tag, index) in form.tags"
                                        :key="index"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200"
                                    >
                                        {{ tag }}
                                        <button
                                            @click="removeTag(index)"
                                            type="button"
                                            class="ml-1 inline-flex items-center p-0.5 rounded-full text-blue-400 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-800 hover:text-blue-500 dark:hover:text-blue-200"
                                        >
                                            <Icon name="x"/>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { MdEditor, config } from 'md-editor-v3';
import 'md-editor-v3/lib/style.css';
import Icon from '@/components/Icon.vue';

// Import required libraries for MdEditor features
import screenfull from 'screenfull';
import katex from 'katex';
import 'katex/dist/katex.min.css';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import mermaid from 'mermaid';
import hljs from 'highlight.js';
import 'highlight.js/styles/github.css';
import prettier from 'prettier';
import parserMarkdown from 'prettier/plugins/markdown';
import { marked } from 'marked';

// Configure MdEditor globally with extensions
config({
    editorExtensions: {
        screenfull: {
            instance: screenfull
        },
        katex: {
            instance: katex
        },
        cropper: {
            instance: Cropper
        },
        highlight: {
            instance: hljs
        },
        mermaid: {
            instance: mermaid
        },
        prettier: {
            prettierInstance: prettier,
            parserMarkdownInstance: parserMarkdown
        }
    },
    editorConfig: {
        languageUserDefined: {
            'en-US': {
                // English language configuration
            }
        }
    }
});

interface Props {
    post: {
        id: number;
        title: string;
        slug: string;
        content_markdown: string;
        content_html?: string;
        excerpt?: string;
        status: string;
        published_at?: string;
        author_id: number;
        author?: { id: number; name: string };
        categories?: Array<{ id: number; name: string }>;
        tags?: Array<{ id: number; name: string }>;
        created_at: string;
        updated_at: string;
    };
}

const props = defineProps<Props>();

const errors = ref<Record<string, string[]>>({});
const loading = ref(false);
const tagInput = ref('');

// Timezone handling for scheduled posts
const publishDate = ref('');
const publishTime = ref('');
const selectedTimezone = ref(Intl.DateTimeFormat().resolvedOptions().timeZone);

// Convert UTC published_at to user's local timezone for display
const initializePublishDateTime = () => {
    if (props.post.published_at && props.post.status === 'scheduled') {
        const utcDate = new Date(props.post.published_at);

        // Convert to user's timezone
        const formatter = new Intl.DateTimeFormat('en-CA', {
            timeZone: selectedTimezone.value,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });

        const parts = formatter.formatToParts(utcDate);
        const values: Record<string, string> = {};
        parts.forEach(part => {
            if (part.type !== 'literal') {
                values[part.type] = part.value;
            }
        });

        publishDate.value = `${values.year}-${values.month}-${values.day}`;
        publishTime.value = `${values.hour}:${values.minute}`;
    }
};

// Initialize on mount
initializePublishDateTime();

// Helper function to convert local datetime to UTC
const convertToUTC = (date: string, time: string, timezone: string): string => {
    if (!date || !time) return '';

    // Create date string in the selected timezone
    const dateTimeStr = `${date}T${time}:00`;

    // Parse the date in the selected timezone
    const localDate = new Date(dateTimeStr);

    // Get timezone offset in minutes
    const formatter = new Intl.DateTimeFormat('en-US', {
        timeZone: timezone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });

    const parts = formatter.formatToParts(localDate);
    const values: Record<string, string> = {};
    parts.forEach(part => {
        if (part.type !== 'literal') {
            values[part.type] = part.value;
        }
    });

    // Create date in selected timezone
    const tzDate = new Date(`${values.year}-${values.month}-${values.day}T${values.hour}:${values.minute}:${values.second}`);

    // Get the time difference
    const utcDate = new Date(date + 'T' + time);
    const tzOffset = (tzDate.getTime() - utcDate.getTime());

    // Adjust for timezone
    const finalDate = new Date(utcDate.getTime() - tzOffset);

    return finalDate.toISOString();
};

const form = reactive({
    title: props.post.title,
    slug: props.post.slug,
    content_markdown: props.post.content_markdown,
    excerpt: props.post.excerpt || '',
    status: props.post.status,
    categories: props.post.categories?.map(c => c.id) || [],
    tags: props.post.tags?.map(t => t.name) || [],
    custom_fields: {}
});

const theme = ref<'light' | 'dark'>(localStorage.getItem('appearance') === 'dark' ? 'dark' : 'light');

const generateSlug = () => {
    if (form.title) {
        form.slug = form.title
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
};

const addTag = () => {
    const tag = tagInput.value.trim().replace(',', '');
    if (tag && !form.tags.includes(tag)) {
        form.tags.push(tag);
    }
    tagInput.value = '';
};

const handleTagKeydown = (e: KeyboardEvent) => {
    if (e.key === ',') {
        e.preventDefault();
        addTag();
    }
};

const removeTag = (index: number) => {
    form.tags.splice(index, 1);
};

// Function to generate table of contents from markdown
const generateTOC = (markdown: string) => {
    const headingRegex = /^(#{1,6})\s+(.+)$/gm;
    const toc: Array<{ level: number; text: string; id: string }> = [];
    let match;

    while ((match = headingRegex.exec(markdown)) !== null) {
        const level = match[1].length;
        const text = match[2].trim();
        const id = text
            .toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-');

        toc.push({ level, text, id });
    }

    return toc;
};

const handleSubmit = async () => {
    loading.value = true;
    errors.value = {};

    // Convert markdown to HTML
    const htmlContent = await marked.parse(form.content_markdown || '');

    // Generate table of contents
    const tableOfContents = generateTOC(form.content_markdown || '');

    // Convert scheduled datetime to UTC if status is scheduled
    let publishedAtUTC = null;
    if (form.status === 'scheduled' && publishDate.value && publishTime.value) {
        publishedAtUTC = convertToUTC(publishDate.value, publishTime.value, selectedTimezone.value);
    }

    router.put(`/admin/posts/${props.post.id}`, {
        ...form,
        content_html: htmlContent,
        table_of_contents: tableOfContents,
        published_at: publishedAtUTC
    }, {
        onSuccess: () => {
            router.visit('/admin/posts');
        },
        onError: (err) => {
            if (err && typeof err === 'object') {
                errors.value = err as Record<string, string[]>;
            }
            loading.value = false;
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

const updateAndPublish = async () => {
    form.status = 'published';
    await handleSubmit();
};
</script>
