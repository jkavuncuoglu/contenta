<template>
    <div class="p-6">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-2xl font-bold">Backups</h1>
            <button
                :disabled="running"
                @click="runBackup"
                class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
            >
                {{ running ? 'Running...' : 'Run Backup' }}
            </button>
        </div>

        <div v-if="loading" class="text-gray-500">Loading backups...</div>
        <div v-else-if="error" class="text-red-600">{{ error }}</div>

        <div v-else>
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
            >
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                        >
                            Name
                        </th>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                        >
                            Created
                        </th>
                    </tr>
                </thead>
                <tbody
                    class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900"
                >
                    <tr v-for="b in backups" :key="b.name">
                        <td class="px-4 py-2">{{ b.name }}</td>
                        <td class="px-4 py-2 text-gray-500">
                            {{ b.created_at }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="!backups.length" class="mt-3 text-gray-500">
                No backups found.
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

interface BackupItem {
    name: string;
    created_at: string;
}

const loading = ref(false);
const running = ref(false);
const error = ref<string | null>(null);
const backups = ref<BackupItem[]>([]);

const load = () => {
    loading.value = true;
    error.value = null;

    router.get(
        '/admin/backups',
        {},
        {
            preserveState: true,
            onSuccess: (page) => {
                // support either `data` or `backups` in props
                const props: any = (page as any).props || {};
                backups.value = (props.data ??
                    props.backups ??
                    []) as BackupItem[];
            },
            onError: (e: any) => {
                error.value = e?.message || 'Failed to load backups';
            },
            onFinish: () => {
                loading.value = false;
            },
        },
    );
};

const runBackup = () => {
    running.value = true;
    error.value = null;

    router.post(
        '/admin/backups',
        {},
        {
            preserveState: true,
            onSuccess: () => {
                // reload list after successful backup
                router.get(
                    '/admin/backups',
                    {},
                    {
                        preserveState: true,
                        onSuccess: (page) => {
                            const props: any = (page as any).props || {};
                            backups.value = (props.data ??
                                props.backups ??
                                []) as BackupItem[];
                        },
                    },
                );
            },
            onError: (e: any) => {
                error.value = e?.message || 'Failed to start backup';
            },
            onFinish: () => {
                running.value = false;
            },
        },
    );
};

onMounted(load);
</script>

<style scoped></style>
