<script setup lang="ts">
import { Icon } from '@iconify/vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

import HeadingSmall from '@/components/HeadingSmall.vue';
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
}

interface Migration {
    id: number;
    content_type: string;
    from_driver: string;
    to_driver: string;
    status: string;
    progress: number;
    total_items: number;
    migrated_items: number;
    failed_items: number;
    started_at: string | null;
    completed_at: string | null;
    created_at: string;
}

interface Props {
    recentMigrations: Migration[];
    availableDrivers: Driver[];
}

const props = defineProps<Props>();

const page = usePage();
const flashMessage = computed(
    () => page.props.flash as { success?: string; error?: string } | null,
);

// Migration form
const contentType = ref<string>('pages');
const fromDriver = ref<string>('database');
const toDriver = ref<string>('local');
const deleteSource = ref<boolean>(false);
const isAsync = ref<boolean>(true);

// Migration state
const migrating = ref<boolean>(false);
const currentMigration = ref<Migration | null>(null);
const migrationError = ref<string>('');
const migrations = ref<Migration[]>(props.recentMigrations);

// Polling
let pollInterval: number | null = null;

const startMigration = async () => {
    if (fromDriver.value === toDriver.value) {
        migrationError.value = 'Source and destination drivers must be different';
        return;
    }

    migrating.value = true;
    migrationError.value = '';

    try {
        const response = await axios.post('/admin/settings/content-storage/migrations', {
            content_type: contentType.value,
            from_driver: fromDriver.value,
            to_driver: toDriver.value,
            delete_source: deleteSource.value,
            async: isAsync.value,
        });

        if (response.data.success) {
            currentMigration.value = response.data.migration;

            // Start polling for status if async
            if (isAsync.value) {
                startPolling(response.data.migration.id);
            } else {
                // Refresh migrations list
                await refreshMigrations();
                migrating.value = false;
            }
        }
    } catch (error: any) {
        migrationError.value = error.response?.data?.message || 'Migration failed';
        migrating.value = false;
    }
};

const startPolling = (migrationId: number) => {
    pollInterval = window.setInterval(async () => {
        try {
            const response = await axios.get(`/admin/settings/content-storage/migrations/${migrationId}`);
            currentMigration.value = response.data;

            // Stop polling if migration is complete or failed
            if (response.data.status === 'completed' || response.data.status === 'failed') {
                stopPolling();
                await refreshMigrations();
                migrating.value = false;
            }
        } catch (error) {
            console.error('Polling error:', error);
        }
    }, 2000); // Poll every 2 seconds
};

const stopPolling = () => {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
};

const refreshMigrations = async () => {
    try {
        const response = await axios.get('/admin/settings/content-storage/migrations');
        migrations.value = response.data.data || [];
    } catch (error) {
        console.error('Failed to refresh migrations:', error);
    }
};

const verifyMigration = async (migrationId: number) => {
    try {
        const response = await axios.post(
            `/admin/settings/content-storage/migrations/${migrationId}/verify`,
            { sample_size: 10 }
        );

        if (response.data.success) {
            alert(response.data.message);
        }
    } catch (error: any) {
        alert(error.response?.data?.message || 'Verification failed');
    }
};

const rollbackMigration = async (migrationId: number) => {
    if (!confirm('Are you sure you want to rollback this migration? This will create a reverse migration.')) {
        return;
    }

    try {
        const response = await axios.post(
            `/admin/settings/content-storage/migrations/${migrationId}/rollback`
        );

        if (response.data.success) {
            alert('Rollback started');
            await refreshMigrations();
        }
    } catch (error: any) {
        alert(error.response?.data?.message || 'Rollback failed');
    }
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'completed':
            return 'text-green-600 bg-green-50';
        case 'failed':
            return 'text-red-600 bg-red-50';
        case 'running':
            return 'text-blue-600 bg-blue-50';
        default:
            return 'text-neutral-600 bg-neutral-50';
    }
};

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleString();
};

onMounted(() => {
    // Check if there's an active migration and start polling
    const activeMigration = migrations.value.find(m => m.status === 'running' || m.status === 'pending');
    if (activeMigration) {
        currentMigration.value = activeMigration;
        migrating.value = true;
        startPolling(activeMigration.id);
    }
});

onUnmounted(() => {
    stopPolling();
});

const breadcrumbItems: BreadcrumbItem[] = [
    {
        label: 'Settings',
        href: '/admin/settings'
    },
    {
        label: 'Content Storage',
        href: '/admin/settings/content-storage'
    },
    {
        label: 'Migrate',
        href: '#'
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Content Migration" />

        <div class="space-y-6">
            <HeadingSmall
                title="Content Migration"
                description="Migrate content between storage backends"
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

            <!-- Migration Form -->
            <div class="rounded-lg bg-white shadow dark:bg-neutral-800">
                <div class="space-y-6 px-6 py-6">
                    <div>
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                            Start New Migration
                        </h3>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                            Migrate content from one storage backend to another
                        </p>
                    </div>

                    <!-- Error Message -->
                    <div
                        v-if="migrationError"
                        class="rounded-md border border-red-200 bg-red-50 p-4"
                    >
                        <div class="flex">
                            <Icon
                                icon="material-symbols-light:error"
                                class="h-5 w-5 text-red-400"
                            />
                            <div class="ml-3">
                                <p class="text-sm text-red-800">
                                    {{ migrationError }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Current Migration Progress -->
                    <div
                        v-if="currentMigration && migrating"
                        class="rounded-md border border-blue-200 bg-blue-50 p-6"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <h4 class="text-sm font-medium text-blue-900">
                                Migration in Progress
                            </h4>
                            <span
                                :class="['rounded-full px-3 py-1 text-xs font-medium', getStatusColor(currentMigration.status)]"
                            >
                                {{ currentMigration.status }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between text-sm text-blue-800">
                                <span>{{ currentMigration.content_type }} from {{ currentMigration.from_driver }} to {{ currentMigration.to_driver }}</span>
                                <span>{{ currentMigration.progress }}%</span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="h-2 w-full overflow-hidden rounded-full bg-blue-200">
                                <div
                                    class="h-full bg-blue-600 transition-all duration-300"
                                    :style="{ width: `${currentMigration.progress}%` }"
                                ></div>
                            </div>

                            <div class="flex justify-between text-xs text-blue-700">
                                <span>{{ currentMigration.migrated_items }} / {{ currentMigration.total_items }} items</span>
                                <span v-if="currentMigration.failed_items > 0" class="text-red-600">
                                    {{ currentMigration.failed_items }} failed
                                </span>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="startMigration" class="space-y-6">
                        <!-- Content Type -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                Content Type
                            </label>
                            <Select v-model="contentType" class="mt-1" :disabled="migrating">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select content type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="pages">Pages</SelectItem>
                                    <SelectItem value="posts">Posts</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <!-- From Driver -->
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                    From Driver
                                </label>
                                <Select v-model="fromDriver" class="mt-1" :disabled="migrating">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select source" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="driver in availableDrivers"
                                            :key="driver.value"
                                            :value="driver.value"
                                            class="dark:hover:bg-neutral-700 hover:bg-neutral-100"
                                            :class="{'bg-neutral-100': driver.value === toDriver}"
                                        >
                                            <template #default>
                                                {{ driver.label }}
                                            </template>
                                            <template #description>
                                                {{ driver.description }}
                                            </template>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- To Driver -->
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                    To Driver
                                </label>
                                <Select v-model="toDriver" class="mt-1" :disabled="migrating">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select destination" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="driver in availableDrivers"
                                            :key="driver.value"
                                            :value="driver.value"
                                        >
                                            <template #default>
                                                {{ driver.label }}
                                            </template>
                                            <template #description>
                                                {{ driver.description }}
                                            </template>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <!-- Options -->
                        <div class="space-y-3">
                            <label class="flex items-center space-x-2">
                                <input
                                    type="checkbox"
                                    v-model="deleteSource"
                                    :disabled="migrating"
                                    class="h-4 w-4 rounded border-neutral-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="text-sm text-neutral-700 dark:text-neutral-300">
                                    Delete source content after migration
                                </span>
                            </label>

                            <label class="flex items-center space-x-2">
                                <input
                                    type="checkbox"
                                    v-model="isAsync"
                                    :disabled="migrating"
                                    class="h-4 w-4 rounded border-neutral-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="text-sm text-neutral-700 dark:text-neutral-300">
                                    Run migration in background (recommended)
                                </span>
                            </label>
                        </div>

                        <Button
                            type="submit"
                            :disabled="migrating || fromDriver === toDriver"
                            class="w-full"
                        >
                            <Icon
                                v-if="migrating"
                                icon="svg-spinners:ring-resize"
                                class="mr-2 h-4 w-4"
                            />
                            {{ migrating ? 'Migrating...' : 'Start Migration' }}
                        </Button>
                    </form>
                </div>
            </div>

            <!-- Recent Migrations -->
            <div class="rounded-lg bg-white shadow dark:bg-neutral-800">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
                    <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                        Recent Migrations
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                        <thead class="bg-neutral-50 dark:bg-neutral-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">
                                    Content Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">
                                    From → To
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">
                                    Progress
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500">
                                    Started
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-neutral-500">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                            <tr v-for="migration in migrations" :key="migration.id">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                    {{ migration.content_type }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">
                                    {{ migration.from_driver }} → {{ migration.to_driver }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        :class="['inline-flex rounded-full px-2 py-1 text-xs font-semibold', getStatusColor(migration.status)]"
                                    >
                                        {{ migration.status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">
                                    <div class="flex items-center">
                                        <span class="mr-2">{{ migration.progress }}%</span>
                                        <div class="h-2 w-16 overflow-hidden rounded-full bg-neutral-200">
                                            <div
                                                class="h-full bg-blue-600"
                                                :style="{ width: `${migration.progress}%` }"
                                            ></div>
                                        </div>
                                    </div>
                                    <div class="text-xs text-neutral-500">
                                        {{ migration.migrated_items }} / {{ migration.total_items }}
                                        <span v-if="migration.failed_items > 0" class="text-red-600">
                                            ({{ migration.failed_items }} failed)
                                        </span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">
                                    {{ formatDate(migration.started_at) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <Button
                                        v-if="migration.status === 'completed'"
                                        @click="verifyMigration(migration.id)"
                                        variant="ghost"
                                        size="sm"
                                        class="mr-2"
                                    >
                                        Verify
                                    </Button>
                                    <Button
                                        v-if="migration.status === 'completed'"
                                        @click="rollbackMigration(migration.id)"
                                        variant="ghost"
                                        size="sm"
                                    >
                                        Rollback
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="migrations.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-neutral-500">
                                    No migrations yet
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
