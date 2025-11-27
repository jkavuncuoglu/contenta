<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import admin from '@/routes/admin';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Theme {
    id: number;
    name: string;
    display_name: string;
    description: string | null;
    version: string;
    author: string | null;
    screenshot: string | null;
    is_active: boolean;
    metadata: any;
}

interface Props {
    themes: Theme[];
}

const { themes } = defineProps<Props>();

const uploadDialogOpen = ref(false);
const uploadForm = useForm({
    theme: null as File | null,
});

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        uploadForm.theme = target.files[0];
    }
};

const handleUpload = () => {
    uploadForm.post(admin.themes.install.url(), {
        onSuccess: () => {
            uploadDialogOpen.value = false;
            uploadForm.reset();
        },
    });
};

const activateTheme = (themeId: number) => {
    router.post(admin.themes.activate.url(themeId));
};

const uninstallTheme = (themeId: number, themeName: string) => {
    if (
        confirm(
            `Are you sure you want to uninstall "${themeName}"? This action cannot be undone.`,
        )
    ) {
        router.delete(admin.themes.uninstall.url(themeId));
    }
};

const scanThemes = () => {
    router.post(admin.themes.scan.url());
};
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/admin/dashboard' },
            { title: 'Appearance' },
            { title: 'Themes' },
        ]"
    >
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Themes</h1>
                    <p class="mt-2 text-muted-foreground">
                        Manage your site's appearance with themes
                    </p>
                </div>
                <div class="flex gap-3">
                    <Button variant="outline" @click="scanThemes">
                        Scan for Themes
                    </Button>
                    <Button @click="uploadDialogOpen = true">
                        Upload Theme
                    </Button>
                </div>
            </div>

            <!-- Active Theme -->
            <div v-if="themes.find((t) => t.is_active)">
                <h2 class="mb-4 text-xl font-semibold">Active Theme</h2>
                <Card
                    v-for="theme in themes.filter((t) => t.is_active)"
                    :key="theme.id"
                    class="border-2 border-primary"
                >
                    <div class="grid gap-6 md:grid-cols-[300px_1fr]">
                        <!-- Screenshot -->
                        <div class="relative">
                            <img
                                v-if="theme.screenshot"
                                :src="theme.screenshot"
                                :alt="theme.display_name"
                                class="h-[225px] w-full rounded-l-lg object-cover"
                            />
                            <div
                                v-else
                                class="flex h-[225px] w-full items-center justify-center rounded-l-lg bg-muted"
                            >
                                <span class="text-muted-foreground"
                                >No preview available</span
                                >
                            </div>
                            <Badge class="absolute top-3 right-3 bg-primary">
                                Active
                            </Badge>
                        </div>

                        <!-- Details -->
                        <div class="flex flex-col justify-between p-6">
                            <div>
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="text-2xl font-bold">
                                            {{ theme.display_name }}
                                        </h3>
                                        <p
                                            class="mt-1 text-sm text-muted-foreground"
                                        >
                                            Version {{ theme.version }}
                                            <span v-if="theme.author"
                                            >by {{ theme.author }}</span
                                            >
                                        </p>
                                    </div>
                                </div>
                                <p
                                    v-if="theme.description"
                                    class="mt-4 text-muted-foreground"
                                >
                                    {{ theme.description }}
                                </p>
                                <div
                                    v-if="theme.metadata?.tags"
                                    class="mt-4 flex flex-wrap gap-2"
                                >
                                    <Badge
                                        v-for="tag in theme.metadata.tags"
                                        :key="tag"
                                        variant="secondary"
                                    >
                                        {{ tag }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Available Themes -->
            <div v-if="themes.filter((t) => !t.is_active).length">
                <h2 class="mb-4 text-xl font-semibold">Available Themes</h2>
                <div
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <Card
                        v-for="theme in themes.filter((t) => !t.is_active)"
                        :key="theme.id"
                        class="overflow-hidden"
                    >
                        <!-- Screenshot -->
                        <div class="relative">
                            <img
                                v-if="theme.screenshot"
                                :src="theme.screenshot"
                                :alt="theme.display_name"
                                class="h-48 w-full object-cover"
                            />
                            <div
                                v-else
                                class="flex h-48 w-full items-center justify-center bg-muted"
                            >
                                <span class="text-muted-foreground"
                                >No preview</span
                                >
                            </div>
                        </div>

                        <CardHeader>
                            <CardTitle>{{ theme.display_name }}</CardTitle>
                            <CardDescription>
                                Version {{ theme.version }}
                                <span v-if="theme.author"
                                >by {{ theme.author }}</span
                                >
                            </CardDescription>
                        </CardHeader>

                        <CardContent>
                            <p
                                v-if="theme.description"
                                class="line-clamp-2 text-sm text-muted-foreground"
                            >
                                {{ theme.description }}
                            </p>
                            <div
                                v-if="theme.metadata?.tags"
                                class="mt-3 flex flex-wrap gap-2"
                            >
                                <Badge
                                    v-for="tag in theme.metadata.tags.slice(
                                        0,
                                        3,
                                    )"
                                    :key="tag"
                                    variant="secondary"
                                    class="text-xs"
                                >
                                    {{ tag }}
                                </Badge>
                            </div>
                        </CardContent>

                        <CardFooter class="flex gap-2">
                            <Button
                                size="sm"
                                class="flex-1"
                                @click="activateTheme(theme.id)"
                            >
                                Activate
                            </Button>
                            <Button
                                size="sm"
                                variant="outline"
                                @click="
                                    uninstallTheme(theme.id, theme.display_name)
                                "
                            >
                                Delete
                            </Button>
                        </CardFooter>
                    </Card>
                </div>
            </div>

            <!-- Empty State -->
            <Card v-if="themes.length === 0" class="p-12">
                <div class="text-center">
                    <h3 class="text-lg font-semibold">No themes found</h3>
                    <p class="mt-2 text-muted-foreground">
                        Upload a theme or scan the themes directory to get
                        started.
                    </p>
                    <div class="mt-6 flex justify-center gap-3">
                        <Button @click="scanThemes">Scan for Themes</Button>
                        <Button
                            variant="outline"
                            @click="uploadDialogOpen = true"
                        >
                            Upload Theme
                        </Button>
                    </div>
                </div>
            </Card>
        </div>

        <!-- Upload Dialog -->
        <Dialog v-model:open="uploadDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Upload Theme</DialogTitle>
                    <DialogDescription>
                        Upload a theme ZIP file to install it on your site.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="theme-file">Theme File (ZIP)</Label>
                        <Input
                            id="theme-file"
                            type="file"
                            accept=".zip"
                            @change="handleFileSelect"
                        />
                        <p class="text-xs text-muted-foreground">
                            Maximum file size: 10MB
                        </p>
                    </div>

                    <div
                        v-if="uploadForm.errors.theme"
                        class="text-sm text-destructive"
                    >
                        {{ uploadForm.errors.theme }}
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="uploadDialogOpen = false">
                        Cancel
                    </Button>
                    <Button
                        :disabled="!uploadForm.theme || uploadForm.processing"
                        @click="handleUpload"
                    >
                        {{ uploadForm.processing ? 'Uploading...' : 'Upload' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
