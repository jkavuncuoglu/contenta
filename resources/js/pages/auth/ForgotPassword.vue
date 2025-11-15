<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { Icon } from '@iconify/vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{ status?: string }>();

// Inertia form state
const form = useForm({
    email: '' as string,
});

const submit = () => {
    form.post('/forgot-password', {
        preserveScroll: true,
        onSuccess: () => form.reset('email'),
    });
};
</script>

<template>
    <AuthLayout
        title="Forgot password"
        description="Enter your email to receive a password reset link"
    >
        <Head title="Forgot password" />

        <div
            v-if="props.status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ props.status }}
        </div>

        <div class="space-y-6">
            <form
                @submit.prevent="submit"
                novalidate
                data-test="forgot-password-form"
            >
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        v-model="form.email"
                        autocomplete="off"
                        autofocus
                        placeholder="email@example.com"
                        required
                        data-test="email-input"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="my-6 flex items-center justify-start">
                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="form.processing"
                        data-test="email-password-reset-link-button"
                    >
                        <Icon
                            v-if="form.processing"
                            icon="material-symbols-light:progress_activity"
                            class="h-4 w-4 animate-spin"
                        />
                        Email password reset link
                    </Button>
                </div>
            </form>

            <div class="space-x-1 text-center text-sm text-muted-foreground">
                <span>Or, return to</span>
                <TextLink :href="login()">log in</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
