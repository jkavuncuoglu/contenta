<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useWebAuthn } from '@/composables/useWebAuthn';
import { Icon } from '@iconify/vue';
import { onMounted, ref } from 'vue';

const {
    credentials,
    isLoading,
    errors,
    fetchCredentials,
    registerCredential,
    updateCredential,
    deleteCredential,
    clearErrors,
} = useWebAuthn();

const showRegisterDialog = ref(false);
const newCredentialName = ref('');
const editingCredentialId = ref<string | null>(null);
const editingCredentialName = ref('');

const handleRegister = async () => {
    const result = await registerCredential(
        newCredentialName.value || 'Security Key',
    );
    if (result.success) {
        showRegisterDialog.value = false;
        newCredentialName.value = '';
    }
};

const startEditing = (credential: any) => {
    editingCredentialId.value = credential.id;
    editingCredentialName.value = credential.name;
};

const handleUpdate = async (id: string) => {
    const result = await updateCredential(id, editingCredentialName.value);
    if (result.success) {
        editingCredentialId.value = null;
        editingCredentialName.value = '';
    }
};

const handleDelete = async (id: string) => {
    if (
        confirm(
            'Are you sure you want to remove this security key? This action cannot be undone.',
        )
    ) {
        await deleteCredential(id);
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

onMounted(() => {
    fetchCredentials();
});
</script>

<template>
    <div class="space-y-4">
        <div
            v-if="errors.length"
            class="rounded border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive"
        >
            <ul class="list-disc space-y-1 pl-4">
                <li v-for="err in errors" :key="err">{{ err }}</li>
            </ul>
            <Button size="sm" variant="ghost" class="mt-2" @click="clearErrors">
                Dismiss
            </Button>
        </div>

        <p class="text-sm text-muted-foreground">
            Security keys provide the strongest form of two-factor
            authentication. Use a physical device like a YubiKey or your
            device's built-in authenticator (Face ID, Touch ID, Windows Hello).
        </p>

        <div
            v-if="credentials.length === 0 && !isLoading"
            class="rounded border border-dashed p-6 text-center"
        >
            <Icon
                icon="material-symbols-light:key-outline"
                class="mx-auto mb-2 h-12 w-12 text-muted-foreground"
            />
            <p class="mb-4 text-sm text-muted-foreground">
                No security keys registered yet
            </p>
            <Button @click="showRegisterDialog = true">
                <Icon icon="material-symbols-light:add" class="mr-1" />
                Register Security Key
            </Button>
        </div>

        <div v-else class="space-y-4">
            <div class="flex items-center justify-between">
                <Badge variant="default">
                    {{ credentials.length }} key{{
                        credentials.length !== 1 ? 's' : ''
                    }}
                    registered
                </Badge>
                <Button
                    size="sm"
                    @click="showRegisterDialog = true"
                    :disabled="isLoading"
                >
                    <Icon icon="material-symbols-light:add" class="mr-1" />
                    Add Key
                </Button>
            </div>

            <div class="space-y-2">
                <div
                    v-for="credential in credentials"
                    :key="credential.id"
                    class="flex items-center justify-between rounded border p-4"
                >
                    <div class="flex-1 space-y-1">
                        <div
                            v-if="editingCredentialId === credential.id"
                            class="flex items-center gap-2"
                        >
                            <Input
                                v-model="editingCredentialName"
                                class="max-w-xs"
                                @keydown.enter="handleUpdate(credential.id)"
                                @keydown.esc="editingCredentialId = null"
                            />
                            <Button
                                size="sm"
                                @click="handleUpdate(credential.id)"
                                :disabled="isLoading"
                            >
                                Save
                            </Button>
                            <Button
                                size="sm"
                                variant="ghost"
                                @click="editingCredentialId = null"
                            >
                                Cancel
                            </Button>
                        </div>
                        <div v-else class="flex items-center gap-2">
                            <Icon
                                icon="material-symbols-light:key"
                                class="h-5 w-5 text-muted-foreground"
                            />
                            <span class="font-medium">{{
                                credential.name
                            }}</span>
                        </div>
                        <div class="text-xs text-muted-foreground">
                            <span
                                >Added
                                {{ formatDate(credential.created_at) }}</span
                            >
                            <span class="mx-2">•</span>
                            <span
                                >Last used
                                {{ formatDate(credential.last_used_at) }}</span
                            >
                        </div>
                    </div>

                    <div
                        v-if="editingCredentialId !== credential.id"
                        class="flex gap-2"
                    >
                        <Button
                            size="sm"
                            variant="ghost"
                            @click="startEditing(credential)"
                            :disabled="isLoading"
                        >
                            <Icon
                                icon="material-symbols-light:edit"
                                class="h-4 w-4"
                            />
                        </Button>
                        <Button
                            size="sm"
                            variant="ghost"
                            @click="handleDelete(credential.id)"
                            :disabled="isLoading"
                        >
                            <Icon
                                icon="material-symbols-light:delete"
                                class="h-4 w-4 text-destructive"
                            />
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Register Dialog -->
        <div
            v-if="showRegisterDialog"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="showRegisterDialog = false"
        >
            <div
                class="w-full max-w-md space-y-4 rounded-lg border bg-background p-6"
            >
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold">Register Security Key</h3>
                    <p class="text-sm text-muted-foreground">
                        Give your security key a memorable name and follow your
                        browser's prompts to register it.
                    </p>
                </div>

                <div class="space-y-2">
                    <Input
                        v-model="newCredentialName"
                        placeholder="e.g., YubiKey 5C, Touch ID"
                        @keydown.enter="handleRegister"
                    />
                </div>

                <div class="flex justify-end gap-2">
                    <Button
                        variant="ghost"
                        @click="showRegisterDialog = false"
                        :disabled="isLoading"
                    >
                        Cancel
                    </Button>
                    <Button @click="handleRegister" :disabled="isLoading">
                        <Icon
                            v-if="isLoading"
                            icon="material-symbols-light:progress-activity"
                            class="mr-1 animate-spin"
                        />
                        Register Key
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
