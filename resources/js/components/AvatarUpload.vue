<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref } from 'vue';

interface Props {
    modelValue: string | null;
    error?: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:modelValue': [value: string | File | null];
    'update:avatarType': [type: 'upload' | 'url'];
}>();

const avatarType = ref<'upload' | 'url'>('url');
const avatarUrl = ref(props.modelValue || '');
const selectedFile = ref<File | null>(null);
const previewUrl = ref<string | null>(props.modelValue || null);
const fileInputRef = ref<HTMLInputElement | null>(null);

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        if (!file.type.startsWith('image/')) {
            alert('Please select a valid image file');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            // 5MB limit
            alert('File size must be less than 5MB');
            return;
        }

        selectedFile.value = file;

        // Create preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            previewUrl.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);

        emit('update:modelValue', file);
        emit('update:avatarType', 'upload');
    }
};

const handleUrlChange = (value: string | number) => {
    const urlValue = String(value);
    avatarUrl.value = urlValue;
    previewUrl.value = urlValue;
    selectedFile.value = null;
    emit('update:modelValue', urlValue);
    emit('update:avatarType', 'url');
};

const triggerFileInput = () => {
    fileInputRef.value?.click();
};

const removeAvatar = () => {
    selectedFile.value = null;
    avatarUrl.value = '';
    previewUrl.value = null;
    emit('update:modelValue', null);
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

const setAvatarType = (type: 'upload' | 'url') => {
    avatarType.value = type;
    if (type === 'upload') {
        avatarUrl.value = '';
    } else {
        selectedFile.value = null;
        if (fileInputRef.value) {
            fileInputRef.value.value = '';
        }
    }
};
</script>

<template>
    <div class="space-y-4">
        <Label>Avatar</Label>

        <!-- Avatar Preview -->
        <div class="flex items-center gap-4">
            <div class="relative">
                <div
                    class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full bg-muted"
                >
                    <img
                        v-if="previewUrl"
                        :src="previewUrl"
                        alt="Avatar preview"
                        class="h-full w-full object-cover"
                    />
                    <svg
                        v-else
                        class="h-12 w-12 text-muted-foreground"
                        viewBox="0 0 24 24"
                    >
                        <path
                            fill="currentColor"
                            d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"
                        />
                    </svg>
                </div>
            </div>
        </div>

        <!-- File Input (Hidden) -->
        <input
            ref="fileInputRef"
            type="file"
            accept="image/*"
            class="hidden"
            @change="handleFileSelect"
        />

        <!-- Avatar Type Toggle -->
        <p class="text-xs text-muted-foreground">
            Upload an image or use an external URL
        </p>
        <div class="flex gap-2 border-b">
            <button
                type="button"
                :class="[
                    'border-b-2 px-4 py-2 text-sm font-medium transition-colors',
                    avatarType === 'upload'
                        ? 'border-primary text-foreground'
                        : 'border-transparent text-muted-foreground hover:text-foreground',
                ]"
                @click="setAvatarType('upload')"
            >
                Upload Image
            </button>
            <button
                type="button"
                :class="[
                    'border-b-2 px-4 py-2 text-sm font-medium transition-colors',
                    avatarType === 'url'
                        ? 'border-primary text-foreground'
                        : 'border-transparent text-muted-foreground hover:text-foreground',
                ]"
                @click="setAvatarType('url')"
            >
                External URL
            </button>
        </div>

        <!-- URL Input -->
        <div v-if="avatarType === 'url'" class="space-y-2">
            <Input
                :model-value="avatarUrl"
                @update:model-value="handleUrlChange"
                type="url"
                placeholder="https://www.gravatar.com/avatar/..."
                class="w-full"
            />
            <p class="text-xs text-muted-foreground">
                Use Gravatar, UI Avatars, or any other avatar service URL
            </p>
            <div class="space-y-1 text-xs text-muted-foreground">
                <p class="font-medium">Examples:</p>
                <ul class="ml-2 list-inside list-disc space-y-1">
                    <li>
                        Gravatar:
                        <code class="rounded bg-muted px-1 py-0.5 text-xs"
                            >https://www.gravatar.com/avatar/HASH</code
                        >
                    </li>
                    <li>
                        UI Avatars:
                        <code class="rounded bg-muted px-1 py-0.5 text-xs"
                            >https://ui-avatars.com/api/?name=John+Doe</code
                        >
                    </li>
                    <li>Any public image URL</li>
                </ul>
            </div>
        </div>

        <!-- Upload Info -->
        <div v-else class="text-xs text-muted-foreground">
            <div class="mb-4 flex flex-col gap-2">
                <div class="flex gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="triggerFileInput"
                    >
                        Upload Image
                    </Button>
                    <Button
                        v-if="previewUrl"
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="removeAvatar"
                    >
                        Remove
                    </Button>
                </div>
            </div>
            <p>Supported formats: JPG, PNG, GIF, WebP</p>
            <p>Maximum file size: 5MB</p>
        </div>

        <InputError v-if="error" :message="error" />
    </div>
</template>
