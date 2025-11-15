<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Plus, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Props {
    modelValue: Record<string, string>;
    error?: string;
    label?: string;
}

const props = withDefaults(defineProps<Props>(), {
    label: 'Social Media Links',
});

const emit = defineEmits<{
    'update:modelValue': [value: Record<string, string>];
}>();

interface SocialLink {
    id: string;
    platform: string;
    url: string;
}

// Convert object to array for easier manipulation
const socialLinks = ref<SocialLink[]>([]);

// Initialize from modelValue
const initializeLinks = () => {
    const links: SocialLink[] = [];
    if (props.modelValue && typeof props.modelValue === 'object') {
        Object.entries(props.modelValue).forEach(([platform, url]) => {
            if (url) {
                links.push({
                    id: Math.random().toString(36).substr(2, 9),
                    platform,
                    url,
                });
            }
        });
    }
    // Add empty row if none exist
    if (links.length === 0) {
        links.push({
            id: Math.random().toString(36).substr(2, 9),
            platform: '',
            url: '',
        });
    }
    socialLinks.value = links;
};

// Initialize on mount
initializeLinks();

// Watch for external changes to modelValue
watch(
    () => props.modelValue,
    (newValue) => {
        // Only reinitialize if the structure has changed significantly
        const currentPlatforms = socialLinks.value
            .map((l) => l.platform)
            .sort()
            .join(',');
        const newPlatforms = Object.keys(newValue || {})
            .sort()
            .join(',');
        if (currentPlatforms !== newPlatforms) {
            initializeLinks();
        }
    },
    { deep: true },
);

// Add a new empty social link row
const addLink = () => {
    socialLinks.value.push({
        id: Math.random().toString(36).substr(2, 9),
        platform: '',
        url: '',
    });
};

// Remove a social link
const removeLink = (id: string) => {
    socialLinks.value = socialLinks.value.filter((link) => link.id !== id);
    // Ensure at least one empty row exists
    if (socialLinks.value.length === 0) {
        addLink();
    }
    emitUpdate();
};

// Emit updates to parent
const emitUpdate = () => {
    const result: Record<string, string> = {};
    socialLinks.value.forEach((link) => {
        if (link.platform.trim() && link.url.trim()) {
            result[link.platform.trim()] = link.url.trim();
        }
    });
    emit('update:modelValue', result);
};

// Handle input changes
const updateLink = () => {
    emitUpdate();
};

// Common social media platforms for suggestions
const commonPlatforms = [
    'Twitter/X',
    'LinkedIn',
    'Facebook',
    'Instagram',
    'TikTok',
    'YouTube',
    'GitHub',
    'Medium',
    'Behance',
    'Dribbble',
    'Pinterest',
    'Reddit',
    'Discord',
    'Twitch',
    'Mastodon',
];
</script>

<template>
    <div class="grid gap-4">
        <div class="flex items-center justify-between">
            <Label>
                {{ label }}
            </Label>
            <Button
                type="button"
                variant="outline"
                size="sm"
                @click="addLink"
                class="h-8"
            >
                <Plus class="mr-1 h-4 w-4" />
                Add Link
            </Button>
        </div>

        <div class="space-y-3">
            <div
                v-for="link in socialLinks"
                :key="link.id"
                class="flex items-start gap-2"
            >
                <div class="grid flex-1 grid-cols-1 gap-2 md:grid-cols-2">
                    <div class="relative">
                        <Input
                            v-model="link.platform"
                            @input="updateLink"
                            type="text"
                            placeholder="Platform (e.g., Twitter/X, LinkedIn)"
                            list="social-platforms"
                            class="w-full"
                        />
                    </div>
                    <div>
                        <Input
                            v-model="link.url"
                            @input="updateLink"
                            type="url"
                            placeholder="https://..."
                            class="w-full"
                        />
                    </div>
                </div>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    @click="removeLink(link.id)"
                    class="h-10 w-10 shrink-0"
                    :disabled="
                        socialLinks.length === 1 && !link.platform && !link.url
                    "
                >
                    <X class="h-4 w-4" />
                </Button>
            </div>
        </div>

        <!-- Datalist for platform suggestions -->
        <datalist id="social-platforms">
            <option
                v-for="platform in commonPlatforms"
                :key="platform"
                :value="platform"
            />
        </datalist>

        <p class="text-xs text-muted-foreground">
            Add links to your social media profiles. The platform name can be
            anything (Twitter/X, LinkedIn, GitHub, etc.).
        </p>

        <InputError :message="error" />
    </div>
</template>

<style scoped>
/* Add any custom styles if needed */
</style>
