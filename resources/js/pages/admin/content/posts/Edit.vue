<template>
    <Head title="Edit Post" />
    <AppLayout>
        <div class="mx-auto space-y-6 p-4">
            <!-- Page header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-semibold text-neutral-900 dark:text-white"
                    >
                        Edit Post
                    </h1>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        Update your blog post or article
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

            <!-- Tabs -->
            <div class="border-b border-neutral-200 dark:border-neutral-700">
                <nav class="-mb-px flex space-x-8">
                    <button
                        type="button"
                        @click="activeTab = 'editor'"
                        :class="[
                            activeTab === 'editor'
                                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-300',
                            'border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap',
                        ]"
                    >
                        Editor
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'seo'"
                        :class="[
                            activeTab === 'seo'
                                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-300',
                            'border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap',
                        ]"
                    >
                        SEO
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'revisions'"
                        :class="[
                            activeTab === 'revisions'
                                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-300',
                            'border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap',
                        ]"
                    >
                        Revision History
                    </button>
                </nav>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Editor Tab -->
                <div v-show="activeTab === 'editor'">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <!-- Main Content Area -->
                        <div class="lg:col-span-2 space-y-6">
                        <!-- Title -->
                        <div
                            class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800"
                        >
                            <!-- General errors -->
                            <div
                                v-if="errors.general"
                                class="mb-4 rounded-md bg-red-50 p-4 dark:bg-red-900/20"
                            >
                                <p
                                    class="text-sm text-red-600 dark:text-red-400"
                                >
                                    {{ errors.general[0] }}
                                </p>
                            </div>

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
                        <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                            <label class="mb-4 block text-sm font-medium text-neutral-700 dark:text-neutral-300">
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
                        <!-- End Main Content Area -->

                        <!-- Sidebar -->
                        <div class="space-y-6">
                            <!-- Validation Panel -->
                            <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                                <h3 class="mb-4 text-lg font-medium text-neutral-900 dark:text-white">
                                    Validation
                                </h3>

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
                            </div>

                    <!-- Publish Settings -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                        <h3 class="mb-4 text-lg font-medium text-neutral-900 dark:text-white">
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
                                        >
                                            Publish Date
                                        </label>
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
                                        >
                                            Publish Time
                                        </label>
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
                                        >
                                            Timezone
                                        </label>
                                        <select
                                            id="timezone"
                                            v-model="selectedTimezone"
                                            class="mt-1 block w-full rounded-md border-neutral-300 p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
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
                                    <span
                                        v-if="loading"
                                        class="flex items-center"
                                    >
                                        <div
                                            class="mr-2 h-4 w-4 animate-spin rounded-full border-b-2 border-white"
                                        ></div>
                                        Updating...
                                    </span>
                                    <span v-else> Update Post </span>
                                </button>

                                <button
                                    v-if="form.status !== 'published'"
                                    @click="updateAndPublish"
                                    type="button"
                                    :disabled="loading"
                                    class="flex w-full justify-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 shadow-sm hover:bg-neutral-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-600"
                                >
                                    Update & Publish
                                </button>
                            </div>
                        </div>

                        <!-- Storage Info -->
                        <div
                            class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800"
                        >
                            <h3
                                class="mb-4 text-lg font-medium text-neutral-900 dark:text-white"
                            >
                                Storage
                            </h3>

                            <div class="space-y-4">
                                <!-- Storage Driver Display -->
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                        Storage Driver
                                    </label>
                                    <div class="mt-1 inline-flex items-center rounded-md bg-neutral-100 px-3 py-1.5 text-sm font-medium text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200">
                                        {{ props.post.storage_driver || 'database' }}
                                    </div>
                                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                        Current storage backend for this post
                                    </p>
                                </div>

                                <!-- Storage Path Display (if not database) -->
                                <div v-if="props.post.storage_driver && props.post.storage_driver !== 'database'">
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                        Storage Path
                                    </label>
                                    <div class="mt-1 rounded-md bg-neutral-50 px-3 py-2 font-mono text-xs text-neutral-600 dark:bg-neutral-700 dark:text-neutral-300">
                                        {{ props.post.storage_path || 'Not set' }}
                                    </div>
                                </div>

                                <!-- Commit Message (for Git-based storage) -->
                                <div v-if="requiresCommitMessage" class="space-y-2">
                                    <label for="commit_message" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                        Commit Message
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="commit_message"
                                        v-model="form.commit_message"
                                        type="text"
                                        required
                                        placeholder="Update: post changes"
                                        class="block w-full rounded-md border-neutral-300 p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                    />
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                        Required for {{ props.post.storage_driver }} storage
                                    </p>
                                    <div v-if="errors.commit_message" class="text-sm text-red-600 dark:text-red-400">
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
                                <!-- TODO: Load categories dynamically -->
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
                                    >
                                        Technology
                                    </label>
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
                                    >
                                        Programming
                                    </label>
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
                        <!-- End Sidebar -->
                    </div>
                    <!-- End Grid -->
                </div>
                <!-- End Editor Tab -->

                <!-- SEO Tab -->
                <div v-show="activeTab === 'seo'">
                    <SEOTab
                        :seo-analysis="seoAnalysis"
                        v-model:target-keyword="seoTargetKeyword"
                        @apply-slug="(value) => (form.slug = value)"
                    />
                </div>

                <!-- Revision History Tab -->
                <div v-show="activeTab === 'revisions'">
                    <RevisionHistoryTab
                        :content-id="post.id"
                        content-type="post"
                    />
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import MarkdownPageEditor from '@/components/PageBuilder/MarkdownPageEditor.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { marked } from 'marked';
import { computed, reactive, ref, toRef } from 'vue';
import { SEOTab } from '@/components/admin/seo';
import { useSEOAnalysis } from '@/composables/seo/useSEOAnalysis';
import { useShortcodeValidation } from '@/composables/useShortcodeValidation';
import { RevisionHistoryTab } from '@/components/admin/revisions';

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
        storage_driver?: string;
        storage_path?: string;
        created_at: string;
        updated_at: string;
    };
}

const props = defineProps<Props>();

const errors = ref<Record<string, string[]>>({});
const loading = ref(false);
const tagInput = ref('');
const activeTab = ref<'editor' | 'seo' | 'revisions'>('editor');

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
            hour12: false,
        });

        const parts = formatter.formatToParts(utcDate);
        const values: Record<string, string> = {};
        parts.forEach((part) => {
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

const form = reactive({
    title: props.post.title,
    slug: props.post.slug,
    content_markdown: props.post.content_markdown,
    excerpt: props.post.excerpt || '',
    status: props.post.status,
    categories: props.post.categories?.map((c) => c.id) || [],
    tags: props.post.tags?.map((t) => t.name) || [],
    custom_fields: {},
    commit_message: '',
});

// SEO Analysis
const seoTargetKeyword = ref('');
const seoMetaDescription = ref('');

// Initialize SEO analysis composable
const seoAnalysis = useSEOAnalysis(
    seoTargetKeyword,
    toRef(form, 'title'),
    toRef(form, 'content_markdown'),
    toRef(form, 'slug'),
    seoMetaDescription,
);

// Shortcode Validation
const { validating, validationErrors, isValid } = useShortcodeValidation(
    toRef(form, 'content_markdown'),
    800, // debounce 800ms
);

// Validation error helpers
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

// Computed property to check if commit message is required
const requiresCommitMessage = computed(() => {
    const driver = props.post.storage_driver || 'database';
    return ['github', 'gitlab', 'bitbucket'].includes(driver);
});

const theme = ref<'light' | 'dark'>(
    localStorage.getItem('appearance') === 'dark' ? 'dark' : 'light',
);

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
    // Prevent saving if there are validation errors
    if (!isValid.value) {
        alert('Please fix all validation errors before saving.');
        return;
    }

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

    router.put(
        `/admin/posts/${props.post.id}`,
        {
            ...form,
            content_html: htmlContent,
            table_of_contents: tableOfContents,
            published_at: publishedAtUTC,
            commit_message: form.commit_message || undefined,
        },
        {
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
            },
        },
    );
};

const updateAndPublish = async () => {
    form.status = 'published';
    await handleSubmit();
};
</script>
