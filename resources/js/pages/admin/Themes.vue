<template>
    <div class="p-6">
        <h1 class="mb-4 text-2xl font-bold">Themes</h1>
        <p class="mb-4 text-neutral-600">Manage installed themes.</p>

        <div v-if="loading" class="text-neutral-500">Loading themes...</div>
        <div v-else-if="error" class="text-red-600">{{ error }}</div>
        <ul v-else class="ml-6 list-disc">
            <li v-for="theme in themes" :key="theme.name">
                {{ theme.name }}
                <span class="text-neutral-500">v{{ theme.version }}</span>
            </li>
        </ul>
    </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const loading = ref(false);
const error = ref<string | null>(null);
const themes = ref<Array<{ name: string; version: string }>>([]);

const load = () => {
    loading.value = true;
    error.value = null;

    router.get(
        '/admin/themes',
        {},
        {
            preserveState: true,
            onSuccess: (page) => {
                const props: any = (page as any).props || {};
                themes.value = (props.data ?? props.themes ?? []) as Array<{
                    name: string;
                    version: string;
                }>;
            },
            onError: (e: any) => {
                error.value = e?.message || 'Failed to load themes';
            },
            onFinish: () => (loading.value = false),
        },
    );
};

onMounted(load);
</script>
