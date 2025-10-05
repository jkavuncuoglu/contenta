<script setup lang="ts">
import { reactive, ref, computed, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { Form, Head, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';

const form = useForm({
    first_name: '',
    last_name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    language: 'en-US',
    timezone: 'UTC'
});

// Get all timezones from Intl API
const allTimezones = typeof Intl.supportedValuesOf === 'function'
    ? Intl.supportedValuesOf('timeZone')
    : [
        'UTC', 'Europe/London', 'Europe/Istanbul', 'America/New_York', 'Asia/Tokyo', 'Asia/Shanghai', 'Europe/Paris', 'Europe/Berlin', 'America/Los_Angeles', 'Australia/Sydney'
    ];
const timezoneOptions = allTimezones.map(tz => ({ label: tz, value: tz }));

// Simple mapping from timezone to likely languages
const timezoneLanguages: Record<string, string[]> = {
    'Europe/Istanbul': ['tr-TR', 'en-US'],
    'Europe/London': ['en-GB', 'en-US'],
    'America/New_York': ['en-US', 'es-US'],
    'Asia/Tokyo': ['ja-JP', 'en-US'],
    'Asia/Shanghai': ['zh-CN', 'en-US'],
    'Europe/Paris': ['fr-FR', 'en-US'],
    'Europe/Berlin': ['de-DE', 'en-US'],
    'America/Los_Angeles': ['en-US', 'es-US'],
    'Australia/Sydney': ['en-AU', 'en-US'],
    'UTC': ['en-US']
};

const languageOptions = ref([{ label: 'English (US)', value: 'en-US' }]);

watch(() => form.timezone, (tz) => {
    const langs = timezoneLanguages[tz] || ['en-US'];
    languageOptions.value = langs.map(l => ({ label: l, value: l }));
    if (!langs.includes(form.language)) {
        form.language = langs[0];
    }
});

const searchTimezone = ref('');
const filteredTimezoneOptions = computed(() => {
    if (!searchTimezone.value) return timezoneOptions;
    return timezoneOptions.filter(opt => opt.label.toLowerCase().includes(searchTimezone.value.toLowerCase()));
});

const searchLanguage = ref('');
const filteredLanguageOptions = computed(() => {
    if (!searchLanguage.value) return languageOptions.value;
    return languageOptions.value.filter(opt => opt.label.toLowerCase().includes(searchLanguage.value.toLowerCase()));
});

const handleRegister = () => {
    form.post('/register', {
        data: {
            name: `${form.first_name} ${form.last_name}`.trim(),
            email: form.email,
            password: form.password,
            password_confirmation: form.password_confirmation,
            username: form.username,
            language: form.language,
            timezone: form.timezone,
        },
        onSuccess: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthBase
        title="Create an account"
        description="Enter your details below to create your account"
    >
        <Head title="Register" />

        <Form
            :model-value="form"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
            @submit.prevent="handleRegister"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="first_name">First Name</Label>
                    <Input
                        id="first_name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="given-name"
                        name="first_name"
                        placeholder="First name"
                        v-model="form.first_name"
                    />
                    <InputError :message="errors.first_name" />
                </div>
                <div class="grid gap-2">
                    <Label for="last_name">Last Name</Label>
                    <Input
                        id="last_name"
                        type="text"
                        required
                        :tabindex="2"
                        autocomplete="family-name"
                        name="last_name"
                        placeholder="Last name"
                        v-model="form.last_name"
                    />
                    <InputError :message="errors.last_name" />
                </div>
                <div class="grid gap-2">
                    <Label for="username">Username</Label>
                    <Input
                        id="username"
                        type="text"
                        required
                        :tabindex="3"
                        autocomplete="username"
                        name="username"
                        placeholder="Username"
                        v-model="form.username"
                    />
                    <InputError :message="errors.username" />
                </div>
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="4"
                        autocomplete="email"
                        name="email"
                        placeholder="email@example.com"
                        v-model="form.email"
                    />
                    <InputError :message="errors.email" />
                </div>
                <!-- Timezone Select (moved before Language) -->
                <div class="grid gap-2">
                    <Label for="timezone">Timezone</Label>
                    <Multiselect
                        v-model="form.timezone"
                        :options="filteredTimezoneOptions"
                        placeholder="Select timezone..."
                        :searchable="true"
                        :clearable="false"
                        track-by="value"
                        label="label"
                    />
                    <InputError :message="errors.timezone" />
                </div>
                <div class="grid gap-2">
                    <Label for="language">Language</Label>
                    <Multiselect
                        v-model="form.language"
                        :options="filteredLanguageOptions"
                        placeholder="Select language..."
                        :searchable="true"
                        :clearable="false"
                        track-by="value"
                        label="label"
                    />
                    <InputError :message="errors.language" />
                </div>
                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="7"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Password"
                        v-model="form.password"
                    />
                    <InputError :message="errors.password" />
                </div>
                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="8"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Confirm password"
                        v-model="form.password_confirmation"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>
                <Button
                    type="submit"
                    class="mt-2 w-full"
                    tabindex="9"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <Icon icon="material-symbols-light:progress_activity" v-if="processing" class="h-4 w-4 animate-spin" />
                    Create account
                </Button>
            </div>
            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="10"
                >Log in
                </TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>

<style lang="postcss">
.multiselect {
    color: var(--foreground);
    background-color: transparent;
    border-radius: 0.375rem;
    border-width: 1px;
    border-color: var(--input);
    min-width: 0;
    width: 100%;
    font-size: 1rem;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.03);
    outline: none;
    transition: color 0.2s, box-shadow 0.2s;
    display: flex;
}

.multiselect__tags {
    background: color-mix(in oklab, var(--input) 30%, transparent);
    color: var(--primary-foreground);
    font-size: var(--text-xs);
    width: 100%;
    line-height: var(--tw-leading, var(--text-sm--line-height));
    border: 0 none;
}

.multiselect__single {
    background: transparent;
    color: var(--foreground);
    font-size: var(--text-sm);
}

.multiselect__content-wrapper {
    max-height: 15rem;
    overflow-y: auto;
    border-radius: 0.375rem;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -2px rgb(0 0 0 / 0.05);
    background-color: var(--background);
    border: 1px solid var(--border);
    z-index: 100;
}

.multiselect__option--highlight {
    background-color: var(--primary);
    color: var(--primary-foreground);
}

.multiselect__option--selected {
    font-weight: 600;
}

.multiselect__option {
    padding: 0.5rem 1rem;
    cursor: pointer;
}

.multiselect__option:hover {
    background-color: var(--primary);
    color: var(--primary-foreground);
}

.multiselect__input {
    color: var(--foreground);
}

.multiselect__option--disabled {
    color: var(--muted-foreground);
    cursor: not-allowed;
}

.multiselect__option--disabled:hover {
    background-color: transparent;
    color: var(--muted-foreground);
}

.multiselect__caret {
    border-top-color: var(--muted-foreground);
}

.multiselect--open .multiselect__caret {
    border-bottom-color: var(--muted-foreground);
    border-top-color: transparent;
}


</style>
