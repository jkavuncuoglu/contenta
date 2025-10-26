<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import settings from '@/routes/user/settings';
import { send } from '@/routes/verification';
import { Form, Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { watch } from 'vue';

import AvatarUpload from '@/components/AvatarUpload.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import LanguageSelect from '@/components/LanguageSelect.vue';
import SocialLinksInput from '@/components/SocialLinksInput.vue';
import ThemeModeSelect from '@/components/ThemeModeSelect.vue';
import TimezoneSelect from '@/components/TimezoneSelect.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string | null;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: settings.profile.edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;

const { appearance, updateAppearance } = useAppearance();

const form = useForm({
    first_name: user.first_name,
    last_name: user.last_name,
    email: user.email,
    username: user.username,
    bio: user.bio,
    avatar: user.avatar,
    timezone: user.timezone || 'UTC',
    language: user.language || 'en',
    theme_mode: appearance.value || 'system',
    preferences: (user as any).preferences || {},
    social_links: (user as any).social_links || {},
});

// Watch for theme_mode changes and update appearance immediately
watch(
    () => form.theme_mode,
    (newValue) => {
        updateAppearance(newValue as 'light' | 'dark' | 'system');
    },
);
</script>

<template>
    <Head title="Profile settings" />

    <SettingsLayout>
        <div class="flex flex-col space-y-6">
            <HeadingSmall
                title="Profile information"
                description="Update your name, email address and other profile details."
            />

            <Form
                :form="form"
                class="space-y-6"
                v-slot="{ errors, processing, recentlySuccessful }"
            >
                <div class="mb-8 grid gap-2">
                    <AvatarUpload
                        id="avatar"
                        class="mt-1 block w-full"
                        name="avatar"
                        v-model="form.avatar"
                        required
                    />
                    <InputError class="mt-2" :message="errors.avatar" />
                </div>

                <div class="grid gap-2">
                    <Label for="firstName">Name</Label>
                    <Input
                        id="firstName"
                        class="mt-1 block w-full"
                        name="first_name"
                        v-model="form.first_name"
                        required
                        autocomplete="first_name"
                        placeholder="First name"
                    />
                    <InputError class="mt-2" :message="errors.first_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="last_name">Name</Label>
                    <Input
                        id="last_name"
                        class="mt-1 block w-full"
                        name="last_name"
                        v-model="form.last_name"
                        required
                        autocomplete="last_name"
                        placeholder="Full name"
                    />
                    <InputError class="mt-2" :message="errors.last_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        name="email"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        placeholder="Email address"
                    />
                    <InputError class="mt-2" :message="errors.email" />
                </div>

                <div v-if="mustVerifyEmail && !user.email_verified_at">
                    <p class="-mt-4 text-sm text-muted-foreground">
                        Your email address is unverified.
                        <Link
                            :href="send()"
                            as="button"
                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        >
                            Click here to resend the verification email.
                        </Link>
                    </p>

                    <div
                        v-if="status === 'verification-link-sent'"
                        class="mt-2 text-sm font-medium text-green-600"
                    >
                        A new verification link has been sent to your email
                        address.
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="username">Username</Label>
                    <Input
                        id="username"
                        type="text"
                        class="mt-1 block w-full"
                        name="username"
                        v-model="form.username"
                        required
                        autocomplete="username"
                        placeholder="Username"
                    />
                    <InputError class="mt-2" :message="errors.username" />
                </div>

                <HeadingSmall
                    title="Biography"
                    description="A short description about yourself."
                />

                <div class="grid gap-2">
                    <Label for="bio">Bio</Label>
                    <textarea
                        id="bio"
                        rows="7"
                        class="mt-1 block w-full min-w-0 rounded-md border border-input bg-transparent p-3 text-base shadow-xs transition-[color,box-shadow] outline-none selection:bg-primary selection:text-primary-foreground placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive aria-invalid:ring-destructive/20 md:text-sm dark:bg-input/30 dark:aria-invalid:ring-destructive/40"
                        name="bio"
                        v-model="form.bio"
                        required
                        autocomplete="bio"
                        placeholder="Short Bio"
                    />
                    <InputError class="mt-2" :message="errors.bio" />
                </div>

                <SocialLinksInput
                    v-model="form.social_links"
                    :error="errors.social_links"
                />

                <HeadingSmall
                    title="Timezone & Language"
                    description="Set your preferred timezone and language."
                />

                <TimezoneSelect
                    v-model="form.timezone"
                    :error="errors.timezone"
                />

                <LanguageSelect
                    v-model="form.language"
                    :timezone="form.timezone"
                    :error="errors.language"
                />

                <HeadingSmall
                    title="Preferences"
                    description="Set your appearance preferences."
                />

                <ThemeModeSelect
                    v-model="form.theme_mode"
                    :error="errors.theme_mode"
                />

                <div class="flex items-center gap-4">
                    <Button
                        :disabled="processing"
                        data-test="update-profile-button"
                        >Save</Button
                    >

                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <p
                            v-show="recentlySuccessful"
                            class="text-sm text-neutral-600"
                        >
                            Saved.
                        </p>
                    </Transition>
                </div>
            </Form>
        </div>
    </SettingsLayout>
</template>
