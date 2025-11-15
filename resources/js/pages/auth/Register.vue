<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import LanguageSelect from '@/components/LanguageSelect.vue';
import TextLink from '@/components/TextLink.vue';
import TimezoneSelect from '@/components/TimezoneSelect.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { faker } from '@faker-js/faker';
import { Icon } from '@iconify/vue';
import { Form, Head, router, useForm } from '@inertiajs/vue3';
import { useClipboard } from '@vueuse/core';
import { ref, watch } from 'vue';

const form = useForm({
    first_name: '',
    last_name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    language: 'en-US',
    timezone: 'UTC',
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

const passwordInputType = ref<'password' | 'text'>('password');
const passwordConfirmType = ref<'password' | 'text'>('password');
const generatedPassphrase = ref('');
const { copy, copied } = useClipboard();

function generatePassphrase() {
    let passphrase = '';
    while (true) {
        const words = [
            faker.word.sample(),
            faker.word.sample(),
            faker.word.sample(),
        ];
        passphrase = words.join('-');
        if (passphrase.length >= 12) break;
    }
    form.password = passphrase;
    form.password_confirmation = passphrase;
    passwordInputType.value = 'text';
    passwordConfirmType.value = 'text';
    generatedPassphrase.value = passphrase;
}

function copyPassphrase() {
    copy(form.password);
}

function suggestUsername() {
    if (form.username) return; // Don't overwrite if already set
    if (!form.first_name && !form.last_name) return; // Need at least one name part
    const baseUsername = (
        form.first_name.charAt(0) + form.last_name
    ).toLowerCase();
    const randomNum = Math.floor(100 + Math.random() * 900);
    form.username = baseUsername + randomNum;
}

const usernameAvailable = ref(false);
const checkingUsername = ref(false);
const usernameCheckTimeout = ref<number | null>(null);

async function checkUsernameAvailability(username: string) {
    if (!username) {
        usernameAvailable.value = false;
        return;
    }
    checkingUsername.value = true;
    try {
        router.get(
            '/check-username',
            { username },
            {
                preserveState: true,
                only: [],
                onSuccess: (page) => {
                    usernameAvailable.value = !!page.props?.available;
                },
                onError: () => {
                    usernameAvailable.value = false;
                },
                onFinish: () => {
                    checkingUsername.value = false;
                },
            },
        );
    } catch {
        usernameAvailable.value = false;
    } finally {
        checkingUsername.value = false;
    }
}

watch(
    () => form.username,
    (val) => {
        usernameAvailable.value = false;
        if (usernameCheckTimeout.value)
            clearTimeout(usernameCheckTimeout.value);
        usernameCheckTimeout.value = setTimeout(async () => {
            await checkUsernameAvailability(val);
            if (!usernameAvailable.value && val) {
                // Only append random number if not already present
                if (!/\d{3,}$/.test(val)) {
                    const randomNum = Math.floor(100 + Math.random() * 900);
                    form.username = `${val}${randomNum}`;
                    await checkUsernameAvailability(form.username);
                }
            }
        }, 400);
    },
);

const emailAvailable = ref(false);
const checkingEmail = ref(false);
const emailCheckTimeout = ref<number | null>(null);

async function checkEmailAvailability(email: string) {
    if (!email) {
        emailAvailable.value = false;
        return;
    }
    checkingEmail.value = true;
    try {
        router.get(
            '/check-email',
            { email },
            {
                preserveState: true,
                only: [],
                onSuccess: (page) => {
                    emailAvailable.value = !!page.props?.emailAvailable;
                },
                onError: () => {
                    emailAvailable.value = false;
                },
                onFinish: () => {
                    checkingEmail.value = false;
                },
            },
        );
    } catch {
        emailAvailable.value = false;
    } finally {
        checkingEmail.value = false;
    }
}

watch(
    () => form.email,
    (val) => {
        emailAvailable.value = false;
        if (emailCheckTimeout.value) clearTimeout(emailCheckTimeout.value);
        emailCheckTimeout.value = setTimeout(async () => {
            await checkEmailAvailability(val);
        }, 400);
    },
);
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
                    <Label for="first_name"
                        >First Name <span class="text-red-600">*</span>
                    </Label>
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
                    <Label for="last_name"
                        >Last Name<span class="text-red-600">*</span></Label
                    >
                    <Input
                        id="last_name"
                        type="text"
                        required
                        :tabindex="2"
                        autocomplete="family-name"
                        name="last_name"
                        placeholder="Last name"
                        v-model="form.last_name"
                        @change="suggestUsername"
                    />
                    <InputError :message="errors.last_name" />
                </div>
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <div class="relative flex items-center">
                        <Input
                            id="email"
                            type="email"
                            required
                            :tabindex="4"
                            autocomplete="email"
                            name="email"
                            placeholder="email@example.com"
                            v-model="form.email"
                            class="pr-10"
                        />
                        <Icon
                            v-if="checkingEmail"
                            icon="material-symbols-light:hourglass-top"
                            class="absolute top-1/2 right-2 h-4 w-4 -translate-y-1/2 animate-spin text-white"
                        />
                        <Icon
                            v-else-if="emailAvailable"
                            icon="material-symbols-light:check"
                            class="absolute top-1/2 right-2 h-4 w-4 -translate-y-1/2 text-green-600 text-white"
                        />
                        <Icon
                            v-else-if="
                                form.email && !emailAvailable && !checkingEmail
                            "
                            icon="material-symbols-light:close"
                            class="absolute top-1/2 right-2 h-4 w-4 -translate-y-1/2 text-red-600 text-white"
                        />
                    </div>
                    <InputError :message="errors.email" />
                </div>
                <div class="grid gap-2">
                    <Label for="username">Username</Label>
                    <div class="relative flex items-center">
                        <Input
                            id="username"
                            type="text"
                            required
                            :tabindex="3"
                            autocomplete="username"
                            name="username"
                            placeholder="Username"
                            v-model="form.username"
                            class="pr-10"
                        />
                        <Icon
                            v-if="checkingUsername"
                            icon="material-symbols-light:hourglass-top"
                            class="absolute top-1/2 right-2 h-4 w-4 -translate-y-1/2 animate-spin text-white"
                        />
                        <Icon
                            v-else-if="usernameAvailable"
                            icon="material-symbols-light:check"
                            class="absolute top-1/2 right-2 h-4 w-4 -translate-y-1/2 text-green-600 text-white"
                        />
                    </div>
                    <InputError :message="errors.username" />
                </div>

                <TimezoneSelect
                    v-model="form.timezone"
                    :error="errors.timezone"
                />

                <LanguageSelect
                    v-model="form.language"
                    :timezone="form.timezone"
                    :error="errors.language"
                />

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <div class="relative flex items-center">
                        <Input
                            id="password"
                            :type="passwordInputType"
                            required
                            :tabindex="7"
                            autocomplete="new-password"
                            name="password"
                            placeholder="Password"
                            v-model="form.password"
                            class="pr-32"
                        />
                        <div class="absolute right-2 ml-auto flex space-x-1">
                            <button
                                type="button"
                                class="border-color-muted-foreground border-l border-dotted p-2 text-xs"
                                @click="generatePassphrase"
                                title="Generate secure passphrase"
                            >
                                <Icon
                                    icon="material-symbols-light:rotate-left-rounded"
                                    class="h-4 w-4 text-blue-500"
                                />
                            </button>
                            <button
                                v-if="passwordInputType === 'text'"
                                type="button"
                                title="Copy to clipboard"
                                class="border-color-muted-foreground border-l border-dotted p-2 text-xs text-blue-500"
                                @click="copyPassphrase"
                            >
                                <Icon
                                    v-if="!copied"
                                    icon="material-symbols-light:content-copy-outline"
                                    class="mr-1 h-4 w-4"
                                />
                                <Icon
                                    v-else
                                    icon="material-symbols-light:library-add-check-rounded"
                                    class="mr-1 h-4 w-4 text-green-600"
                                />
                            </button>
                        </div>
                    </div>
                    <InputError :message="errors.password" />
                </div>
                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        :type="passwordConfirmType"
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
                    <Icon
                        icon="material-symbols-light:progress_activity"
                        v-if="processing"
                        class="h-4 w-4 animate-spin"
                    />
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
                </TextLink>
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
    transition:
        color 0.2s,
        box-shadow 0.2s;
    display: flex;
    min-height: 36px;
    max-height: 36px;
}

.multiselect__tags {
    background: color-mix(in oklab, var(--input) 30%, transparent);
    color: var(--primary-foreground);
    font-size: var(--text-xs);
    width: 100%;
    line-height: var(--tw-leading, var(--text-sm--line-height));
    border: 0 none;
    padding: 0;
}

.multiselect__single {
    background: transparent;
    color: var(--foreground);
    font-size: var(--text-sm);
    padding: 6px 0 0px 8px;
}

.multiselect__content-wrapper {
    max-height: 15rem;
    overflow-y: auto;
    border-radius: 0.375rem;
    box-shadow:
        0 10px 15px -3px rgb(0 0 0 / 0.1),
        0 4px 6px -2px rgb(0 0 0 / 0.05);
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
