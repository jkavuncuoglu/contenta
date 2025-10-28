<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
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

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

import SettingsLayout from '@/layouts/settings/Layout.vue';
import settings from '@/routes/user/settings';
import { Icon } from '@iconify/vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface Token {
    id: string;
    name: string;
    abilities: string[];
    last_used_at: string | null;
    created_at: string;
    created_at_human: string;
}

interface Props {
    tokens: Token[];
    maxTokens: number;
    plainTextToken?: string;
    tokenName?: string;
}

const props = defineProps<Props>();

const showCreateDialog = ref(false);
const showTokenDialog = ref(false);
const showDeleteDialog = ref(false);
const showDeleteAllDialog = ref(false);
const tokenToDelete = ref<Token | null>(null);
const displayedToken = ref('');
const displayedTokenName = ref('');
const tokenCopied = ref(false);

const createForm = useForm({
    name: '',
});

const canCreateToken = computed(() => props.tokens.length < props.maxTokens);

// Watch for plainTextToken from server (after creation)
watch(
    () => props.plainTextToken,
    (token) => {
        if (token) {
            displayedToken.value = token;
            displayedTokenName.value = props.tokenName || '';
            showTokenDialog.value = true;
            showCreateDialog.value = false;
        }
    },
    { immediate: true },
);

const openCreateDialog = () => {
    createForm.reset();
    createForm.clearErrors();
    showCreateDialog.value = true;
};

const closeCreateDialog = () => {
    showCreateDialog.value = false;
    createForm.reset();
};

const submitCreate = () => {
    createForm.post(settings.apiTokens.store().url, {
        preserveScroll: true,
        onSuccess: () => {
            // Token dialog will be shown by watcher
        },
        onError: () => {
            // Form errors will be displayed
        },
    });
};

const copyToken = async () => {
    try {
        await navigator.clipboard.writeText(displayedToken.value);
        tokenCopied.value = true;
        setTimeout(() => {
            tokenCopied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy token:', err);
    }
};

const closeTokenDialog = () => {
    showTokenDialog.value = false;
    displayedToken.value = '';
    displayedTokenName.value = '';
    tokenCopied.value = false;
};

const confirmDelete = (token: Token) => {
    tokenToDelete.value = token;
    showDeleteDialog.value = true;
    // Removed immediate deletion; user will confirm in the dialog
};

const deleteToken = () => {
    if (!tokenToDelete.value) return;

    router.delete(settings.apiTokens.destroy(tokenToDelete.value.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            tokenToDelete.value = null;
        },
    });
};

const confirmDeleteAll = () => {
    showDeleteAllDialog.value = true;
};

const deleteAllTokens = () => {
    router.delete(settings.apiTokens.destroyAll().url, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteAllDialog.value = false;
        },
    });
};

const downloadToken = () => {
    const element = document.createElement('a');
    const file = new Blob(
        [
            `Token Name: ${displayedTokenName.value}\n\nToken: ${displayedToken.value}\n\nKeep this token secure. It will not be shown again.`,
        ],
        { type: 'text/plain' },
    );
    element.href = URL.createObjectURL(file);
    element.download = `${displayedTokenName.value
        .replace(/\s+/g, '_')
        .toLowerCase()}_api_token.txt`;
    document.body.appendChild(element); // Required for this to work in FireFox
    element.click();
    document.body.removeChild(element);
};
</script>

<template>
    <Head title="API Tokens" />
    <SettingsLayout>
        <div class="space-y-6">
            <Heading
                title="API Tokens"
                description="Manage API tokens for accessing your account programmatically"
            />

            <div class="mb-4 space-y-6 rounded bg-neutral-800/30 p-4">
                <div class="flex items-center justify-between">
                    <HeadingSmall
                        title="Personal Access Tokens"
                        :description="`${tokens.length} of ${maxTokens} tokens created`"
                    />
                    <Button
                        @click="openCreateDialog"
                        :disabled="!canCreateToken"
                        size="sm"
                    >
                        <Icon icon="material-symbols:add" class="mr-1" />
                        Create Token
                    </Button>
                </div>

                <div
                    v-if="!canCreateToken"
                    class="rounded border border-amber-500/50 bg-amber-500/10 p-3 text-sm text-amber-600"
                >
                    You have reached the maximum number of API tokens ({{
                        maxTokens
                    }}). Delete some tokens to create new ones.
                </div>

                <div class="space-y-3">
                    <div
                        v-if="tokens.length === 0"
                        class="rounded border border-dashed p-8 text-center text-muted-foreground"
                    >
                        <Icon
                            icon="material-symbols:key-outline"
                            class="mx-auto mb-2 h-12 w-12 opacity-50"
                        />
                        <p class="text-sm">No API tokens created yet</p>
                        <p class="text-xs">Create a token to access the API</p>
                    </div>

                    <div
                        v-for="token in tokens"
                        :key="token.id"
                        class="rounded border"
                    >
                        <div
                            class="flex items-center justify-between p-4 hover:bg-muted/50"
                        >
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-medium">
                                        {{ token.name }}
                                    </h4>
                                </div>
                                <div
                                    class="mt-1 flex gap-4 text-xs text-muted-foreground"
                                >
                                    <span
                                        >Created
                                        {{ token.created_at_human }}</span
                                    >
                                    <span v-if="token.last_used_at"
                                        >Last used
                                        {{ token.last_used_at }}</span
                                    >
                                    <span v-else>Never used</span>
                                </div>
                            </div>
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="confirmDelete(token)"
                            >
                                <Icon icon="material-symbols:delete-outline" />
                            </Button>
                        </div>
                    </div>
                </div>

                <div v-if="tokens.length > 0" class="border-t pt-4">
                    <Button
                        variant="destructive"
                        size="sm"
                        @click="confirmDeleteAll"
                    >
                        <Icon
                            icon="material-symbols:delete-sweep"
                            class="mr-1"
                        />
                        Delete All Tokens
                    </Button>
                </div>
            </div>

            <!-- Information Section -->
            <div class="rounded border border-blue-500/50 bg-blue-500/10 p-4">
                <div class="flex gap-3">
                    <Icon
                        icon="material-symbols:info-outline"
                        class="mt-0.5 h-5 w-5 text-blue-500"
                    />
                    <div class="space-y-2 text-sm">
                        <p class="font-medium text-blue-500">API Token Usage</p>
                        <p class="text-muted-foreground">
                            Include your token in API requests using the
                            Authorization header:
                        </p>
                        <code
                            class="block rounded bg-black/50 p-2 font-mono text-xs"
                        >
                            Authorization: Bearer YOUR_TOKEN_HERE
                        </code>
                        <p class="text-xs text-muted-foreground">
                            Tokens are only shown once upon creation. Store them
                            securely.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Token Dialog -->
        <Dialog v-model:open="showCreateDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Create API Token</DialogTitle>
                    <DialogDescription>
                        Create a new personal access token for API access. The
                        token will inherit your account permissions.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="token-name">Token Name</Label>
                        <Input
                            id="token-name"
                            v-model="createForm.name"
                            placeholder="My Application Token"
                            @keyup.enter="submitCreate"
                        />
                        <InputError :message="createForm.errors.name" />
                    </div>
                </div>

                <DialogFooter>
                    <Button
                        variant="outline"
                        @click="closeCreateDialog"
                        type="button"
                    >
                        Cancel
                    </Button>
                    <Button
                        @click="submitCreate"
                        :disabled="!createForm.name || createForm.processing"
                    >
                        Create Token
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Token Display Dialog -->
        <Dialog v-model:open="showTokenDialog">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>API Token Created</DialogTitle>
                    <DialogDescription>
                        Copy your new API token. For security, it won't be shown
                        again.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label>Token Name</Label>
                        <div
                            class="rounded bg-muted px-3 py-2 text-sm font-medium"
                        >
                            {{ displayedTokenName }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label>Token</Label>
                        <div class="flex gap-2 p-2 rounded border hover:cursor-text">
                            <span class="text-xs font-mono"> {{displayedToken}}</span>
                        </div>
                        <p class="text-xs text-amber-600">
                            <Icon
                                icon="material-symbols:warning"
                                class="inline h-4 w-4"
                            />
                            Make sure to copy your token now. You won't be able
                            to see it again!
                        </p>
                        <Button @click="copyToken" size="sm" class="mr-2">
                            <Icon
                                :icon="
                                        tokenCopied
                                            ? 'material-symbols:check'
                                            : 'material-symbols:content-copy'
                                    "
                            />
                            Copy
                        </Button>
                        <Button @click="downloadToken" size="sm">
                            <Icon
                                :icon="
                                        tokenCopied
                                            ? 'material-symbols:check'
                                            : 'material-symbols:download-2'
                                    "
                                class=""
                            />
                            Download
                        </Button>
                    </div>
                </div>

                <DialogFooter>
                    <Button @click="closeTokenDialog">Done</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Delete Token Confirmation -->
        <AlertDialog v-model:open="showDeleteDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete API Token?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to delete the token "{{
                            tokenToDelete?.name
                        }}"? This action cannot be undone and any applications
                        using this token will lose access.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        @click="deleteToken"
                        class="bg-destructive hover:bg-destructive/90"
                    >
                        Delete Token
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <!-- Delete All Tokens Confirmation -->
        <AlertDialog v-model:open="showDeleteAllDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete All API Tokens?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to delete all {{ tokens.length }}
                        API tokens? This action cannot be undone and all
                        applications using these tokens will lose access
                        immediately.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        @click="deleteAllTokens"
                        class="bg-destructive hover:bg-destructive/90"
                    >
                        Delete All Tokens
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </SettingsLayout>
</template>
