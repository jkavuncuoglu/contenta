<script setup lang="ts">
import { Icon } from '@iconify/vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import axios from 'axios';

import HeadingSmall from '@/components/HeadingSmall.vue';
import Input from '@/components/ui/input/Input.vue';
import { type BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Driver {
    value: string;
    label: string;
    description: string;
}

interface Settings {
    pages_storage_driver: string;
    posts_storage_driver: string;
    local_base_path: string;
    s3_region: string;
    s3_bucket: string;
    s3_prefix: string;
    s3_key: string;
    s3_secret: string;
    github_owner: string;
    github_repo: string;
    github_branch: string;
    github_base_path: string;
    github_token: string;
    azure_account_name: string;
    azure_container: string;
    azure_prefix: string;
    azure_account_key: string;
    gcs_project_id: string;
    gcs_bucket: string;
    gcs_prefix: string;
    gcs_key_file_path: string;
}

interface Props {
    settings: Settings;
    availableDrivers: Driver[];
}

const props = defineProps<Props>();

const page = usePage();
const flashMessage = computed(
    () => page.props.flash as { success?: string; error?: string } | null,
);

const form = useForm({
    pages_storage_driver: props.settings.pages_storage_driver || 'database',
    posts_storage_driver: props.settings.posts_storage_driver || 'database',
    local_base_path: props.settings.local_base_path || '',
    s3_region: props.settings.s3_region || 'us-east-1',
    s3_bucket: props.settings.s3_bucket || '',
    s3_prefix: props.settings.s3_prefix || '',
    s3_key: props.settings.s3_key || '',
    s3_secret: props.settings.s3_secret || '',
    github_owner: props.settings.github_owner || '',
    github_repo: props.settings.github_repo || '',
    github_branch: props.settings.github_branch || 'main',
    github_base_path: props.settings.github_base_path || '',
    github_token: props.settings.github_token || '',
    azure_account_name: props.settings.azure_account_name || '',
    azure_container: props.settings.azure_container || '',
    azure_prefix: props.settings.azure_prefix || '',
    azure_account_key: props.settings.azure_account_key || '',
    gcs_project_id: props.settings.gcs_project_id || '',
    gcs_bucket: props.settings.gcs_bucket || '',
    gcs_prefix: props.settings.gcs_prefix || '',
    gcs_key_file_path: props.settings.gcs_key_file_path || '',
});

const testingConnection = ref<{ [key: string]: boolean }>({});
const testResults = ref<{ [key: string]: { success: boolean; message: string } }>({});
const createPathLoading = ref<boolean>(false);
const createPathResult = ref<{ success: boolean; message: string } | null>(null);

const submit = () => {
    form.put('/admin/settings/content-storage', {
        preserveScroll: true,
    });
};

const testConnection = async (driver: string) => {
    testingConnection.value[driver] = true;
    testResults.value[driver] = { success: false, message: '' };

    try {
        const config: any = {};

        if (driver === 'local') {
            config.base_path = form.local_base_path;
        } else if (driver === 's3') {
            config.key = form.s3_key === '••••••••' ? '' : form.s3_key;
            config.secret = form.s3_secret === '••••••••' ? '' : form.s3_secret;
            config.region = form.s3_region;
            config.bucket = form.s3_bucket;
            config.prefix = form.s3_prefix;
        } else if (driver === 'github') {
            config.token = form.github_token === '••••••••' ? '' : form.github_token;
            config.owner = form.github_owner;
            config.repo = form.github_repo;
            config.branch = form.github_branch;
            config.base_path = form.github_base_path;
        } else if (driver === 'azure') {
            config.account_name = form.azure_account_name;
            config.account_key = form.azure_account_key === '••••••••' ? '' : form.azure_account_key;
            config.container = form.azure_container;
            config.prefix = form.azure_prefix;
        } else if (driver === 'gcs') {
            config.project_id = form.gcs_project_id;
            config.bucket = form.gcs_bucket;
            config.prefix = form.gcs_prefix;
            config.key_file_path = form.gcs_key_file_path;
        }

        const response = await axios.post('/admin/settings/content-storage/test-connection', {
            driver,
            config,
        });

        testResults.value[driver] = {
            success: response.data.success,
            message: response.data.message,
        };
    } catch (error: any) {
        testResults.value[driver] = {
            success: false,
            message: error.response?.data?.message || 'Connection test failed',
        };
    } finally {
        testingConnection.value[driver] = false;
    }
};

const createLocalPath = async () => {
    // Assumption: backend exposes POST /admin/settings/content-storage/create-path
    // with payload { base_path: string }
    if (!form.local_base_path || form.local_base_path.trim().length === 0) {
        createPathResult.value = { success: false, message: 'Please provide a base path' };
        return;
    }

    createPathLoading.value = true;
    createPathResult.value = null;

    try {
        const response = await axios.post('/admin/settings/content-storage/create-path', {
            base_path: form.local_base_path,
        });

        createPathResult.value = {
            success: response.data.success ?? true,
            message: response.data.message ?? 'Path created successfully',
        };
    } catch (err: any) {
        createPathResult.value = {
            success: false,
            message: err.response?.data?.message || 'Failed to create path',
        };
    } finally {
        createPathLoading.value = false;
    }
};

const breadcrumbItems: BreadcrumbItem[] = [
    {
        label: 'Settings',
        href: '/admin/settings',
    },
    {
        label: 'Content Storage',
        href: '/admin/settings/content-storage',
    },
];

const showSection = ref<string>('drivers');
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Content Storage Settings" />

        <div class="space-y-6">
            <HeadingSmall
                title="Content Storage"
                description="Configure where pages and posts are stored"
            />

            <!-- Flash Messages -->
            <div
                v-if="flashMessage?.success"
                class="rounded-md border border-green-200 bg-green-50 p-4"
            >
                <div class="flex">
                    <Icon
                        icon="material-symbols-light:check-circle"
                        class="h-5 w-5 text-green-400"
                    />
                    <div class="ml-3">
                        <p class="text-sm text-green-800">
                            {{ flashMessage.success }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                v-if="flashMessage?.error"
                class="rounded-md border border-red-200 bg-red-50 p-4"
            >
                <div class="flex">
                    <Icon
                        icon="material-symbols-light:error"
                        class="h-5 w-5 text-red-400"
                    />
                    <div class="ml-3">
                        <p class="text-sm text-red-800">
                            {{ flashMessage.error }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="border-b border-neutral-200 dark:border-neutral-700">
                <nav class="-mb-px flex space-x-8">
                    <button
                        @click="showSection = 'drivers'"
                        :class="[
                            showSection === 'drivers'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700',
                            'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
                        ]"
                    >
                        Storage Drivers
                    </button>
                    <button
                        @click="showSection = 'local'"
                        :class="[
                            showSection === 'local'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700',
                            'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
                        ]"
                    >
                        Local Filesystem
                    </button>
                    <button
                        @click="showSection = 's3'"
                        :class="[
                            showSection === 's3'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700',
                            'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
                        ]"
                    >
                        AWS S3
                    </button>
                    <button
                        @click="showSection = 'github'"
                        :class="[
                            showSection === 'github'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700',
                            'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
                        ]"
                    >
                        GitHub
                    </button>
                    <button
                        @click="showSection = 'azure'"
                        :class="[
                            showSection === 'azure'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700',
                            'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
                        ]"
                    >
                        Azure
                    </button>
                    <button
                        @click="showSection = 'gcs'"
                        :class="[
                            showSection === 'gcs'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700',
                            'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium',
                        ]"
                    >
                        Google Cloud
                    </button>
                </nav>
            </div>

            <div class="rounded-lg bg-white shadow dark:bg-neutral-800">
                <form @submit.prevent="submit">
                    <!-- Storage Drivers Selection -->
                    <div v-show="showSection === 'drivers'" class="space-y-6 px-6 py-6">
                        <div>
                            <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                Storage Driver Selection
                            </h3>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                Choose where to store your content
                            </p>
                        </div>

                        <!-- Pages Storage Driver -->
                        <div>
                            <label
                                for="pages_storage_driver"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Pages Storage Driver
                            </label>
                            <Select
                                v-model="form.pages_storage_driver"
                                class="mt-1"
                            >
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select driver" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="driver in availableDrivers"
                                        :key="driver.value"
                                        :value="driver.value"
                                    >
                                        <template #default>
                                            <div class="font-medium">{{ driver.label }}</div>
                                        </template>
                                        <template #description>
                                            {{ driver.description }}
                                        </template>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                                Select where your pages will be stored
                            </p>
                        </div>

                        <!-- Posts Storage Driver -->
                        <div>
                            <label
                                for="posts_storage_driver"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Posts Storage Driver
                            </label>
                            <Select
                                v-model="form.posts_storage_driver"
                                class="mt-1"
                            >
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select driver" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="driver in availableDrivers"
                                        :key="driver.value"
                                        :value="driver.value"
                                    >
                                        <template #default>
                                            <div class="font-medium">{{ driver.label }}</div>
                                        </template>
                                        <template #description>
                                            {{ driver.description }}
                                        </template>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                                Select where your posts will be stored
                            </p>
                        </div>
                    </div>

                    <!-- Local Filesystem Settings -->
                    <div v-show="showSection === 'local'" class="space-y-6 px-6 py-6">
                        <div>
                            <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                Local Filesystem Configuration
                            </h3>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                Configure local file storage settings
                            </p>
                        </div>

                        <div>
                            <label
                                for="local_base_path"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Base Path
                            </label>
                            <div class="mt-1 flex items-start gap-2">
                                <Input
                                    id="local_base_path"
                                    v-model="form.local_base_path"
                                    type="text"
                                    placeholder="e.g., content"
                                    class="py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                                <Button
                                    type="button"
                                    variant="default"
                                    :disabled="createPathLoading || form.processing"
                                    @click="createLocalPath"
                                >
                                    <Icon v-if="createPathLoading" icon="svg-spinners:ring-resize" class="mr-2 h-4 w-4" />
                                    {{ createPathLoading ? 'Creating...' : 'Create Path' }}
                                </Button>
                            </div>
                            <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">Optional base path within storage/content directory</p>

                            <div v-if="createPathResult" :class="['mt-2 rounded-md p-3', createPathResult.success ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800']">
                                {{ createPathResult.message }}
                            </div>
                        </div>

                        <div>
                            <Button
                                type="button"
                                @click="testConnection('local')"
                                :disabled="testingConnection.local"
                                variant="outline"
                            >
                                <Icon
                                    v-if="testingConnection.local"
                                    icon="svg-spinners:ring-resize"
                                    class="mr-2 h-4 w-4"
                                />
                                {{ testingConnection.local ? 'Testing...' : 'Test Connection' }}
                            </Button>
                            <div
                                v-if="testResults.local"
                                :class="[
                                    'mt-2 rounded-md p-3',
                                    testResults.local.success
                                        ? 'bg-green-50 text-green-800'
                                        : 'bg-red-50 text-red-800',
                                ]"
                            >
                                {{ testResults.local.message }}
                            </div>
                        </div>
                    </div>

                    <!-- AWS S3 Settings -->
                    <div v-show="showSection === 's3'" class="space-y-6 px-6 py-6">
                        <div>
                            <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                AWS S3 Configuration
                            </h3>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                Configure Amazon S3 cloud storage
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label
                                    for="s3_key"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Access Key ID
                                </label>
                                <Input
                                    id="s3_key"
                                    v-model="form.s3_key"
                                    type="text"
                                    class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                            </div>

                            <div>
                                <label
                                    for="s3_secret"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Secret Access Key
                                </label>
                                <Input
                                    id="s3_secret"
                                    v-model="form.s3_secret"
                                    type="password"
                                    class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label
                                    for="s3_region"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Region
                                </label>
                                <Input
                                    id="s3_region"
                                    v-model="form.s3_region"
                                    type="text"
                                    placeholder="us-east-1"
                                    class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                            </div>

                            <div>
                                <label
                                    for="s3_bucket"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Bucket Name
                                </label>
                                <Input
                                    id="s3_bucket"
                                    v-model="form.s3_bucket"
                                    type="text"
                                    class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                for="s3_prefix"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Prefix (Optional)
                            </label>
                            <Input
                                id="s3_prefix"
                                v-model="form.s3_prefix"
                                type="text"
                                placeholder="e.g., content"
                                class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                            />
                        </div>

                        <div>
                            <Button
                                type="button"
                                @click="testConnection('s3')"
                                :disabled="testingConnection.s3"
                                variant="outline"
                            >
                                <Icon
                                    v-if="testingConnection.s3"
                                    icon="svg-spinners:ring-resize"
                                    class="mr-2 h-4 w-4"
                                />
                                {{ testingConnection.s3 ? 'Testing...' : 'Test Connection' }}
                            </Button>
                            <div
                                v-if="testResults.s3"
                                :class="[
                                    'mt-2 rounded-md p-3',
                                    testResults.s3.success
                                        ? 'bg-green-50 text-green-800'
                                        : 'bg-red-50 text-red-800',
                                ]"
                            >
                                {{ testResults.s3.message }}
                            </div>
                        </div>
                    </div>

                    <!-- GitHub Settings -->
                    <div v-show="showSection === 'github'" class="space-y-6 px-6 py-6">
                        <div>
                            <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                GitHub Configuration
                            </h3>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                Configure Git-based version control storage
                            </p>
                        </div>

                        <div>
                            <label
                                for="github_token"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Personal Access Token
                            </label>
                            <Input
                                id="github_token"
                                v-model="form.github_token"
                                type="password"
                                class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                            />
                            <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                                Generate a token with repo permissions at github.com/settings/tokens
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label
                                    for="github_owner"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Repository Owner
                                </label>
                                <Input
                                    id="github_owner"
                                    v-model="form.github_owner"
                                    type="text"
                                    placeholder="username or org"
                                    class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                            </div>

                            <div>
                                <label
                                    for="github_repo"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Repository Name
                                </label>
                                <Input
                                    id="github_repo"
                                    v-model="form.github_repo"
                                    type="text"
                                    placeholder="my-content"
                                    class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label
                                    for="github_branch"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Branch
                                </label>
                                <Input
                                    id="github_branch"
                                    v-model="form.github_branch"
                                    type="text"
                                    placeholder="main"
                                    class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                            </div>

                            <div>
                                <label
                                    for="github_base_path"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Base Path (Optional)
                                </label>
                                <Input
                                    id="github_base_path"
                                    v-model="form.github_base_path"
                                    type="text"
                                    placeholder="e.g., content"
                                    class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                            </div>
                        </div>

                        <div>
                            <Button
                                type="button"
                                @click="testConnection('github')"
                                :disabled="testingConnection.github"
                                variant="outline"
                            >
                                <Icon
                                    v-if="testingConnection.github"
                                    icon="svg-spinners:ring-resize"
                                    class="mr-2 h-4 w-4"
                                />
                                {{ testingConnection.github ? 'Testing...' : 'Test Connection' }}
                            </Button>
                            <div
                                v-if="testResults.github"
                                :class="[
                                    'mt-2 rounded-md p-3',
                                    testResults.github.success
                                        ? 'bg-green-50 text-green-800'
                                        : 'bg-red-50 text-red-800',
                                ]"
                            >
                                {{ testResults.github.message }}
                            </div>
                        </div>
                    </div>

                    <!-- Azure Settings -->
                    <div v-show="showSection === 'azure'" class="space-y-6 px-6 py-6">
                        <div>
                            <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                Azure Blob Storage Configuration
                            </h3>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                Configure Microsoft Azure cloud storage
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label
                                    for="azure_account_name"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Account Name
                                </label>
                                <Input
                                    id="azure_account_name"
                                    v-model="form.azure_account_name"
                                    type="text"
                                    class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                            </div>

                            <div>
                                <label
                                    for="azure_account_key"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Account Key
                                </label>
                                <Input
                                    id="azure_account_key"
                                    v-model="form.azure_account_key"
                                    type="password"
                                    class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label
                                    for="azure_container"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Container Name
                                </label>
                                <Input
                                    id="azure_container"
                                    v-model="form.azure_container"
                                    type="text"
                                    class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                            </div>

                            <div>
                                <label
                                    for="azure_prefix"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Prefix (Optional)
                                </label>
                                <Input
                                    id="azure_prefix"
                                    v-model="form.azure_prefix"
                                    type="text"
                                    placeholder="e.g., content"
                                    class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                            </div>
                        </div>

                        <div>
                            <Button
                                type="button"
                                @click="testConnection('azure')"
                                :disabled="testingConnection.azure"
                                variant="outline"
                            >
                                <Icon
                                    v-if="testingConnection.azure"
                                    icon="svg-spinners:ring-resize"
                                    class="mr-2 h-4 w-4"
                                />
                                {{ testingConnection.azure ? 'Testing...' : 'Test Connection' }}
                            </Button>
                            <div
                                v-if="testResults.azure"
                                :class="[
                                    'mt-2 rounded-md p-3',
                                    testResults.azure.success
                                        ? 'bg-green-50 text-green-800'
                                        : 'bg-red-50 text-red-800',
                                ]"
                            >
                                {{ testResults.azure.message }}
                            </div>
                        </div>
                    </div>

                    <!-- GCS Settings -->
                    <div v-show="showSection === 'gcs'" class="space-y-6 px-6 py-6">
                        <div>
                            <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                Google Cloud Storage Configuration
                            </h3>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                Configure Google Cloud Platform storage
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label
                                    for="gcs_project_id"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Project ID
                                </label>
                                <Input
                                    id="gcs_project_id"
                                    v-model="form.gcs_project_id"
                                    type="text"
                                    class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                            </div>

                            <div>
                                <label
                                    for="gcs_bucket"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Bucket Name
                                </label>
                                <Input
                                    id="gcs_bucket"
                                    v-model="form.gcs_bucket"
                                    type="text"
                                    class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label
                                    for="gcs_key_file_path"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Service Account Key File Path
                                </label>
                                <Input
                                    id="gcs_key_file_path"
                                    v-model="form.gcs_key_file_path"
                                    type="text"
                                    placeholder="/path/to/service-account.json"
                                    class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                                <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                                    Absolute path to your GCS service account JSON key file
                                </p>
                            </div>

                            <div>
                                <label
                                    for="gcs_prefix"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Prefix (Optional)
                                </label>
                                <Input
                                    id="gcs_prefix"
                                    v-model="form.gcs_prefix"
                                    type="text"
                                    placeholder="e.g., content"
                                    class="mt-1 py-2 px-3 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                />
                            </div>
                        </div>

                        <div>
                            <Button
                                type="button"
                                @click="testConnection('gcs')"
                                :disabled="testingConnection.gcs"
                                variant="outline"
                            >
                                <Icon
                                    v-if="testingConnection.gcs"
                                    icon="svg-spinners:ring-resize"
                                    class="mr-2 h-4 w-4"
                                />
                                {{ testingConnection.gcs ? 'Testing...' : 'Test Connection' }}
                            </Button>
                            <div
                                v-if="testResults.gcs"
                                :class="[
                                    'mt-2 rounded-md p-3',
                                    testResults.gcs.success
                                        ? 'bg-green-50 text-green-800'
                                        : 'bg-red-50 text-red-800',
                                ]"
                            >
                                {{ testResults.gcs.message }}
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="border-t border-neutral-200 px-6 py-4 dark:border-neutral-700">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full sm:w-auto"
                        >
                            <Icon
                                v-if="form.processing"
                                icon="svg-spinners:ring-resize"
                                class="mr-2 h-4 w-4"
                            />
                            {{ form.processing ? 'Saving...' : 'Save Settings' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
