<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { Icon } from '@iconify/vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

// Use Inertia useForm for resending verification email
const form = useForm({});
const resendVerification = () => {
    form.post('/email/verification-notification', {
        preserveScroll: true,
    });
};
</script>

<template>
    <AuthLayout
        title="Verify email"
        description="Please verify your email address by clicking on the link we just emailed to you."
    >
        <Head title="Email verification" />

        <div
            v-if="status === 'verification-link-sent'"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            A new verification link has been sent to the email address you
            provided during registration.
        </div>

        <form
            @submit.prevent="resendVerification"
            class="space-y-6 text-center"
        >
            <Button :disabled="form.processing" variant="secondary">
                <Icon
                    v-if="form.processing"
                    icon="material-symbols-light:progress_activity"
                    class="h-4 w-4 animate-spin"
                />
                Resend verification email
            </Button>
        </form>

        <TextLink :href="logout()" as="button" class="mx-auto block text-sm">
            Log out
        </TextLink>
    </AuthLayout>
</template>
