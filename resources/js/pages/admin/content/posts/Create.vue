<template>
    <Head title="Create Post" />
    <AppLayout>
        <div class="mx-auto space-y-6 p-4">
            <!-- Page header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-semibold text-neutral-900 dark:text-white"
                    >
                        Create New Post
                    </h1>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        Write and publish your new blog post or article
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <Link
                        href="/admin/posts"
                        class="rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
                    >
                        Cancel
                    </Link>
                </div>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Main content -->
                    <div class="space-y-6 lg:col-span-2">
                        <!-- Title -->
                        <div
                            class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800"
                        >
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
                                    class="mt-1 block w-full rounded-md border-neutral-300 p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                    placeholder="Enter your post title..."
                                    @input="generateSlug"
                                />
                                <div
                                    v-if="errors.title"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"
                                >
                                    {{ errors.title[0] }}
                                </div>
                            </div>

                            <!-- Slug -->
                            <div class="mt-4">
                                <label
                                    for="slug"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Slug
                                </label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <span
                                        class="inline-flex items-center rounded-l-md border border-r-0 border-neutral-300 bg-neutral-50 px-3 text-sm text-neutral-500 dark:border-neutral-600 dark:bg-neutral-600 dark:text-neutral-400"
                                    >
                                        posts/
                                    </span>
                                    <input
                                        id="slug"
                                        v-model="form.slug"
                                        type="text"
                                        class="block w-full min-w-0 flex-1 rounded-none rounded-r-md border border-neutral-300 p-2 px-3 py-2 focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                        placeholder="post-slug"
                                    />
                                </div>
                                <div
                                    v-if="errors.slug"
                                    class="mt-1 text-sm text-red-600 dark:text-red-400"
                                >
                                    {{ errors.slug[0] }}
                                </div>
                            </div>
                        </div>

                        <!-- Markdown Editor with Shortcode Support -->
                        <div
                            class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800"
                        >
                            <label
                                class="mb-4 block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Content *
                            </label>
                            <MarkdownPageEditor
                                v-model="form.content_markdown"
                            />
                            <div
                                v-if="errors.content_markdown"
                                class="mt-2 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ errors.content_markdown[0] }}
                            </div>
                        </div>

                        <!-- Excerpt -->
                        <div
                            class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800"
                        >
                            <label
                                for="excerpt"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Excerpt
                            </label>
                            <textarea
                                id="excerpt"
                                v-model="form.excerpt"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-neutral-300 p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                placeholder="Optional excerpt for post previews..."
                            ></textarea>
                            <p
                                class="mt-2 text-sm text-neutral-500 dark:text-neutral-400"
                            >
                                Brief description of your post. If left empty,
                                it will be generated automatically.
                            </p>
                            <div
                                v-if="errors.excerpt"
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                {{ errors.excerpt[0] }}
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Publish -->
                        <div
                            class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800"
                        >
                            <h3
                                class="mb-4 text-lg font-medium text-neutral-900 dark:text-white"
                            >
                                Publish
                            </h3>

                            <div class="space-y-4">
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
                                        class="mt-1 block w-full rounded-md border-neutral-300 p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                    >
                                        <option value="draft">Draft</option>
                                        <option value="published">
                                            Published
                                        </option>
                                        <option value="scheduled">
                                            Scheduled
                                        </option>
                                        <option value="private">Private</option>
                                    </select>
                                </div>

                                <!-- Scheduled publish date -->
                                <div
                                    v-if="form.status === 'scheduled'"
                                    class="space-y-3"
                                >
                                    <div>
                                        <label
                                            for="published_date"
                                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                            >Publish Date</label
                                        >
                                        <input
                                            id="published_date"
                                            v-model="publishDate"
                                            type="date"
                                            class="mt-1 block w-full rounded-md border-neutral-300 p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            for="published_time"
                                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                            >Publish Time</label
                                        >
                                        <input
                                            id="published_time"
                                            v-model="publishTime"
                                            type="time"
                                            step="60"
                                            class="mt-1 block w-full rounded-md border-neutral-300 p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            for="timezone"
                                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                            >Timezone</label
                                        >
                                        <select
                                            id="timezone"
                                            v-model="selectedTimezone"
                                            class="mt-1 block w-full rounded-md border-neutral-300 p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-300"
                                        >
                                            <option value="UTC">UTC</option>
                                            <option value="America/New_York">
                                                Eastern Time (ET)
                                            </option>
                                            <option value="America/Chicago">
                                                Central Time (CT)
                                            </option>
                                            <option value="America/Denver">
                                                Mountain Time (MT)
                                            </option>
                                            <option value="America/Los_Angeles">
                                                Pacific Time (PT)
                                            </option>
                                            <option value="Europe/London">
                                                London (GMT)
                                            </option>
                                            <option value="Europe/Paris">
                                                Paris (CET)
                                            </option>
                                            <option value="Europe/Istanbul">
                                                Istanbul (TRT)
                                            </option>
                                            <option value="Asia/Dubai">
                                                Dubai (GST)
                                            </option>
                                            <option value="Asia/Tokyo">
                                                Tokyo (JST)
                                            </option>
                                            <option value="Asia/Shanghai">
                                                Shanghai (CST)
                                            </option>
                                            <option value="Australia/Sydney">
                                                Sydney (AEDT)
                                            </option>
                                        </select>
                                        <p
                                            class="mt-1 text-xs text-neutral-500 dark:text-neutral-400"
                                        >
                                            Time will be converted to UTC for
                                            storage
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit buttons -->
                            <div class="mt-6 flex flex-col space-y-2">
                                <button
                                    type="submit"
                                    :disabled="loading"
                                    class="flex w-full justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <span v-if="loading">
                                        <span
                                            class="mr-2 h-4 w-4 animate-spin rounded-full border-b-2 border-white"
                                        ></span>
                                        {{
                                            form.status === 'published'
                                                ? 'Publishing...'
                                                : 'Saving...'
                                        }}
                                    </span>
                                    <span v-else>{{
                                        form.status === 'published'
                                            ? 'Publish'
                                            : 'Save Draft'
                                    }}</span>
                                </button>

                                <button
                                    v-if="form.status === 'draft'"
                                    @click="publishNow"
                                    type="button"
                                    :disabled="loading"
                                    class="flex w-full justify-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 shadow-sm hover:bg-neutral-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-600"
                                >
                                    Save & Publish
                                </button>
                            </div>
                        </div>

                        <!-- Storage Settings -->
                        <div
                            class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800"
                        >
                            <h3
                                class="mb-4 text-lg font-medium text-neutral-900 dark:text-white"
                            >
                                Storage
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
                                        class="mt-1 block w-full rounded-md border-neutral-300 p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                    >
                                        <option value="database">Database (Default)</option>
                                        <option value="local">Local Filesystem</option>
                                        <option value="s3">Amazon S3</option>
                                        <option value="azure">Azure Blob Storage</option>
                                        <option value="gcs">Google Cloud Storage</option>
                                        <option value="github">GitHub</option>
                                    </select>
                                    <p
                                        class="mt-1 text-xs text-neutral-500 dark:text-neutral-400"
                                    >
                                        Choose where to store post content
                                    </p>
                                </div>

                                <!-- Commit Message (for Git-based storage) -->
                                <div
                                    v-if="requiresCommitMessage"
                                    class="space-y-2"
                                >
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
                                        placeholder="Add new post: [title]"
                                        class="block w-full rounded-md border-neutral-300 p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                    />
                                    <p
                                        class="text-xs text-neutral-500 dark:text-neutral-400"
                                    >
                                        Required for {{ form.storage_driver }} storage
                                    </p>
                                    <div
                                        v-if="errors.commit_message"
                                        class="text-sm text-red-600 dark:text-red-400"
                                    >
                                        {{ errors.commit_message[0] }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Categories -->
                        <div
                            class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800"
                        >
                            <h3
                                class="mb-4 text-lg font-medium text-neutral-900 dark:text-white"
                            >
                                Categories
                            </h3>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input
                                        id="category-1"
                                        type="checkbox"
                                        value="1"
                                        v-model="form.categories"
                                        class="h-4 w-4 rounded border-neutral-300 p-2 text-blue-600 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700"
                                    />
                                    <label
                                        for="category-1"
                                        class="ml-2 block text-sm text-neutral-900 dark:text-white"
                                        >Technology</label
                                    >
                                </div>
                                <div class="flex items-center">
                                    <input
                                        id="category-2"
                                        type="checkbox"
                                        value="2"
                                        v-model="form.categories"
                                        class="h-4 w-4 rounded border-neutral-300 p-2 text-blue-600 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700"
                                    />
                                    <label
                                        for="category-2"
                                        class="ml-2 block text-sm text-neutral-900 dark:text-white"
                                        >Programming</label
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div
                            class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800"
                        >
                            <h3
                                class="mb-4 text-lg font-medium text-neutral-900 dark:text-white"
                            >
                                Tags
                            </h3>
                            <div>
                                <input
                                    type="text"
                                    v-model="tagInput"
                                    @keydown.enter.prevent="addTag"
                                    @keydown="handleTagKeydown"
                                    placeholder="Add tags separated by commas or press Enter"
                                    class="block w-full rounded-md border-neutral-300 p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                />
                                <p
                                    class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"
                                >
                                    Press Enter or comma to add tags
                                </p>

                                <!-- Tag list -->
                                <div
                                    v-if="form.tags.length > 0"
                                    class="mt-3 flex flex-wrap gap-2"
                                >
                                    <span
                                        v-for="(tag, index) in form.tags"
                                        :key="index"
                                        class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                    >
                                        {{ tag }}
                                        <button
                                            @click="removeTag(index)"
                                            type="button"
                                            class="ml-1 inline-flex items-center rounded-full p-0.5 text-blue-400 hover:bg-blue-200 hover:text-blue-500 dark:text-blue-300 dark:hover:bg-blue-800 dark:hover:text-blue-200"
                                        >
                                            <Icon name="x" />
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
import Icon from '@/components/Icon.vue';
import MarkdownPageEditor from '@/components/PageBuilder/MarkdownPageEditor.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { PostForm } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { marked } from 'marked';
import { computed, reactive, ref } from 'vue';

const errors = ref<Record<string, string[]>>({});
const loading = ref(false);
const tagInput = ref('');

// Timezone handling for scheduled posts
const publishDate = ref('');
const publishTime = ref('');
const selectedTimezone = ref(Intl.DateTimeFormat().resolvedOptions().timeZone);

// Helper function to convert local datetime to UTC
const convertToUTC = (date: string, time: string, timezone: string): string => {
    if (!date || !time) return '';

    // Create date string in the selected timezone
    const dateTimeStr = `${date}T${time}:00`;

    // Parse the date in the selected timezone
    const localDate = new Date(dateTimeStr);

    // Use Intl formatter to get parts in the chosen timezone
    const formatter = new Intl.DateTimeFormat('en-US', {
        timeZone: timezone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    });

    const parts = formatter.formatToParts(localDate);
    const values: Record<string, string> = {};
    parts.forEach((part) => {
        if (part.type !== 'literal') {
            values[part.type] = part.value;
        }
    });

    // Create date in selected timezone
    const tzDate = new Date(
        `${values.year}-${values.month}-${values.day}T${values.hour}:${values.minute}:${values.second}`,
    );

    // Get the time difference
    const utcDate = new Date(date + 'T' + time);
    const tzOffset = tzDate.getTime() - utcDate.getTime();

    // Adjust for timezone
    const finalDate = new Date(utcDate.getTime() - tzOffset);

    return finalDate.toISOString();
};

const form = reactive<PostForm>({
    title: '',
    slug: '',
    content_markdown: '',
    excerpt: '',
    status: 'draft',
    categories: [],
    tags: [],
    custom_fields: {},
    storage_driver: 'database',
    commit_message: '',
});

// Computed property to check if commit message is required
const requiresCommitMessage = computed(() => {
    return ['github', 'gitlab', 'bitbucket'].includes(form.storage_driver || '');
});

const generateSlug = () => {
    if (!form.slug && form.title) {
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
        const level = match[1].length; // Count number of #
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
        publishedAtUTC = convertToUTC(
            publishDate.value,
            publishTime.value,
            selectedTimezone.value,
        );
    }

    router.post(
        '/admin/posts',
        {
            ...form,
            content_html: htmlContent,
            table_of_contents: tableOfContents,
            published_at: publishedAtUTC,
            storage_driver: form.storage_driver,
            commit_message: form.commit_message || undefined,
        },
        {
            onSuccess: () => {
                router.visit('/admin/posts');
            },
            onError: (err) => {
                if (err && typeof err === 'object') {
                    errors.value = err as unknown as Record<string, string[]>;
                }
                loading.value = false;
            },
            onFinish: () => {
                loading.value = false;
            },
        },
    );
};

const publishNow = async () => {
    form.status = 'published';
    await handleSubmit();
};
</script>

<style scoped>
.prose {
    max-width: none;
}

.prose code {
    background-color: rgb(243 244 246);
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
}

.dark .prose code {
    background-color: rgb(55 65 81);
}
</style>
