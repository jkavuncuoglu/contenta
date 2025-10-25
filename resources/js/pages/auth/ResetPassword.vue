<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

const props = defineProps<{ token: string; email: string }>();

const form = useForm({
  token: props.token,
  email: props.email,
  password: '' as string,
  password_confirmation: '' as string,
});

const submit = () => {
  form.post('/reset-password', {
    preserveScroll: true,
    onSuccess: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <AuthLayout
    title="Reset password"
    description="Please enter your new password below"
  >
    <Head title="Reset password" />

    <form @submit.prevent="submit" novalidate data-test="reset-password-form">
      <div class="grid gap-6">
        <div class="grid gap-2">
          <Label for="password">Password</Label>
          <Input
            id="password"
            type="password"
            name="password"
            v-model="form.password"
            autocomplete="new-password"
            class="mt-1 block w-full"
            autofocus
            placeholder="New password"
            required
            minlength="8"
            data-test="new-password-input"
          />
          <InputError :message="form.errors.password" />
        </div>

        <div class="grid gap-2">
          <Label for="password_confirmation">Confirm Password</Label>
            <Input
              id="password_confirmation"
              type="password"
              name="password_confirmation"
              v-model="form.password_confirmation"
              autocomplete="new-password"
              class="mt-1 block w-full"
              placeholder="Confirm password"
              required
              minlength="8"
              data-test="confirm-password-input"
            />
          <InputError :message="form.errors.password_confirmation" />
        </div>

        <Button
          type="submit"
          class="mt-4 w-full"
          :disabled="form.processing"
          data-test="reset-password-button"
        >
          <Icon
            v-if="form.processing"
            icon="material-symbols-light:progress_activity"
            class="h-4 w-4 animate-spin"
          />
          Reset password
        </Button>
      </div>
    </form>
  </AuthLayout>
</template>
