<template>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1
                    class="text-2xl font-semibold text-neutral-900 dark:text-white"
                >
                    Security Settings
                </h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Configure security features including CAPTCHA, honeypot, and
                    access controls.
                </p>
            </div>
            <button
                @click="saveSettings"
                :disabled="loading"
                class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
            >
                {{ loading ? 'Saving...' : 'Save Security Settings' }}
            </button>
        </div>

        <!-- Security Tabs -->
        <div class="border-b border-neutral-200 dark:border-neutral-700">
            <nav class="-mb-px flex space-x-8">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    :class="[
                        activeTab === tab.id
                            ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                            : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-300',
                        'border-b-2 px-1 py-2 text-sm font-medium whitespace-nowrap',
                    ]"
                >
                    {{ tab.name }}
                </button>
            </nav>
        </div>

        <!-- CAPTCHA Settings -->
        <div
            v-show="activeTab === 'captcha'"
            class="grid grid-cols-1 gap-6 lg:grid-cols-2"
        >
            <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                <h3
                    class="mb-6 text-lg font-medium text-neutral-900 dark:text-white"
                >
                    CAPTCHA Configuration
                </h3>
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <label
                                class="text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Enable CAPTCHA
                            </label>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                Protect forms from automated spam
                            </p>
                        </div>
                        <button
                            @click="
                                settings.captcha_enabled =
                                    !settings.captcha_enabled
                            "
                            :class="[
                                settings.captcha_enabled
                                    ? 'bg-blue-600'
                                    : 'bg-neutral-200 dark:bg-neutral-700',
                                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none',
                            ]"
                        >
                            <span
                                :class="[
                                    settings.captcha_enabled
                                        ? 'translate-x-5'
                                        : 'translate-x-0',
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                ]"
                            />
                        </button>
                    </div>

                    <div v-if="settings.captcha_enabled">
                        <label
                            for="captcha-provider"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                        >
                            CAPTCHA Provider
                        </label>
                        <select
                            id="captcha-provider"
                            v-model="settings.captcha_provider"
                            class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                        >
                            <option value="recaptcha">Google reCAPTCHA</option>
                            <option value="hcaptcha">hCaptcha</option>
                            <option value="turnstile">
                                Cloudflare Turnstile
                            </option>
                        </select>
                    </div>

                    <div
                        v-if="
                            settings.captcha_enabled &&
                            settings.captcha_provider === 'recaptcha'
                        "
                    >
                        <div class="space-y-4">
                            <div>
                                <label
                                    for="recaptcha-site-key"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    reCAPTCHA Site Key
                                </label>
                                <input
                                    id="recaptcha-site-key"
                                    v-model="settings.recaptcha_site_key"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-neutral-300 p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                    placeholder="6Lc..."
                                />
                            </div>
                            <div>
                                <label
                                    for="recaptcha-secret-key"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    reCAPTCHA Secret Key
                                </label>
                                <input
                                    id="recaptcha-secret-key"
                                    v-model="settings.recaptcha_secret_key"
                                    type="password"
                                    class="mt-1 block w-full rounded-md border-neutral-300 p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                    placeholder="6Lc..."
                                />
                            </div>
                            <div>
                                <label
                                    for="recaptcha-version"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    reCAPTCHA Version
                                </label>
                                <select
                                    id="recaptcha-version"
                                    v-model="settings.recaptcha_version"
                                    class="mt-1 block w-full rounded-md border-neutral-300 p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                >
                                    <option value="v2">
                                        v2 (I'm not a robot)
                                    </option>
                                    <option value="v2_invisible">
                                        v2 Invisible
                                    </option>
                                    <option value="v3">v3 (Score-based)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="
                            settings.captcha_enabled &&
                            settings.captcha_provider === 'hcaptcha'
                        "
                    >
                        <div class="space-y-4">
                            <div>
                                <label
                                    for="hcaptcha-site-key"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    hCaptcha Site Key
                                </label>
                                <input
                                    id="hcaptcha-site-key"
                                    v-model="settings.hcaptcha_site_key"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-neutral-300 p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                    placeholder="10000000-ffff-ffff-ffff-000000000001"
                                />
                            </div>
                            <div>
                                <label
                                    for="hcaptcha-secret-key"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    hCaptcha Secret Key
                                </label>
                                <input
                                    id="p-2 hcaptcha-secret-key"
                                    v-model="settings.hcaptcha_secret_key"
                                    type="password"
                                    class="dark:bg_neutral-700 mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:text-white"
                                    placeholder="0x0000000000000000000000000000000000000000"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="
                            settings.captcha_enabled &&
                            settings.captcha_provider === 'turnstile'
                        "
                    >
                        <div class="space-y-4">
                            <div>
                                <label
                                    for="turnstile-site-key"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Turnstile Site Key
                                </label>
                                <input
                                    id="turnstile-site-key"
                                    v-model="settings.turnstile_site_key"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-neutral-300 p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                    placeholder="0x4AAAAAAA..."
                                />
                            </div>
                            <div>
                                <label
                                    for="turnstile-secret-key"
                                    class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Turnstile Secret Key
                                </label>
                                <input
                                    id="turnstile-secret-key"
                                    v-model="settings.turnstile_secret_key"
                                    type="password"
                                    class="mt-1 block w-full rounded-md border-neutral-300 p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                    placeholder="0x4AAAAAAA..."
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                <h3
                    class="mb-6 text-lg font-medium text-neutral-900 dark:text-white"
                >
                    CAPTCHA Application
                </h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <label
                                class="text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Login Form
                            </label>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                Require CAPTCHA on login attempts
                            </p>
                        </div>
                        <button
                            @click="
                                settings.captcha_login = !settings.captcha_login
                            "
                            :class="[
                                settings.captcha_login
                                    ? 'bg-blue-600'
                                    : 'bg-neutral-200 dark:bg-neutral-700',
                                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none',
                            ]"
                        >
                            <span
                                :class="[
                                    settings.captcha_login
                                        ? 'translate-x-5'
                                        : 'translate-x-0',
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                ]"
                            />
                        </button>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label
                                class="text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Registration Form
                            </label>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                Require CAPTCHA on registration
                            </p>
                        </div>
                        <button
                            @click="
                                settings.captcha_register =
                                    !settings.captcha_register
                            "
                            :class="[
                                settings.captcha_register
                                    ? 'bg-blue-600'
                                    : 'bg-neutral-200 dark:bg-neutral-700',
                                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none',
                            ]"
                        >
                            <span
                                :class="[
                                    settings.captcha_register
                                        ? 'translate-x-5'
                                        : 'translate-x-0',
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                ]"
                            />
                        </button>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label
                                class="text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Contact Forms
                            </label>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                Require CAPTCHA on contact forms
                            </p>
                        </div>
                        <button
                            @click="
                                settings.captcha_contact =
                                    !settings.captcha_contact
                            "
                            :class="[
                                settings.captcha_contact
                                    ? 'bg-blue-600'
                                    : 'bg-neutral-200 dark:bg-neutral-700',
                                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none',
                            ]"
                        >
                            <span
                                :class="[
                                    settings.captcha_contact
                                        ? 'translate-x-5'
                                        : 'translate-x-0',
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                ]"
                            />
                        </button>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label
                                class="text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Comments
                            </label>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                Require CAPTCHA for comments
                            </p>
                        </div>
                        <button
                            @click="
                                settings.captcha_comments =
                                    !settings.captcha_comments
                            "
                            :class="[
                                settings.captcha_comments
                                    ? 'bg-blue-600'
                                    : 'bg-neutral-200 dark:bg-neutral-700',
                                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none',
                            ]"
                        >
                            <span
                                :class="[
                                    settings.captcha_comments
                                        ? 'translate-x-5'
                                        : 'translate-x-0',
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                ]"
                            />
                        </button>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                <h3
                    class="mb-6 text-lg font-medium text-neutral-900 dark:text-white"
                >
                    Honeypot Protection
                </h3>
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <label
                                class="text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Enable Honeypot
                            </label>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                Invisible spam protection using hidden fields
                            </p>
                        </div>
                        <button
                            @click="
                                settings.honeypot_enabled =
                                    !settings.honeypot_enabled
                            "
                            :class="[
                                settings.honeypot_enabled
                                    ? 'bg-blue-600'
                                    : 'bg-neutral-200 dark:bg-neutral-700',
                                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none',
                            ]"
                        >
                            <span
                                :class="[
                                    settings.honeypot_enabled
                                        ? 'translate-x-5'
                                        : 'translate-x-0',
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                ]"
                            />
                        </button>
                    </div>

                    <div v-if="settings.honeypot_enabled">
                        <label
                            for="honeypot-field-name"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                        >
                            Honeypot Field Name
                        </label>
                        <input
                            id="honeypot-field-name"
                            v-model="settings.honeypot_field_name"
                            type="text"
                            class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                            placeholder="my_name"
                        />
                        <p
                            class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"
                        >
                            Name of the hidden honeypot field. Change this
                            regularly.
                        </p>
                    </div>

                    <div v-if="settings.honeypot_enabled">
                        <label
                            for="honeypot-time-limit"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                        >
                            Time Limit (seconds)
                        </label>
                        <input
                            id="honeypot-time-limit"
                            v-model.number="settings.honeypot_time_limit"
                            type="number"
                            min="0"
                            max="3600"
                            class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                        />
                        <p
                            class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"
                        >
                            Minimum time before form submission is allowed
                            (prevents bot submissions)
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Access Control -->
        <div v-show="activeTab === 'access'" class="grid grid-cols-1 gap-6">
            <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                <h3
                    class="mb-6 text-lg font-medium text-neutral-900 dark:text-white"
                >
                    Access Control & Rate Limiting
                </h3>
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="space-y-4">
                        <div>
                            <label
                                for="max-login-attempts"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Max Login Attempts
                            </label>
                            <input
                                id="max-login-attempts"
                                v-model.number="settings.max_login_attempts"
                                type="number"
                                min="1"
                                max="20"
                                class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                            />
                            <p
                                class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"
                            >
                                Number of failed login attempts before lockout
                            </p>
                        </div>

                        <div>
                            <label
                                for="lockout-duration"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Lockout Duration (minutes)
                            </label>
                            <input
                                id="lockout-duration"
                                v-model.number="settings.lockout_duration"
                                type="number"
                                min="1"
                                max="1440"
                                class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                            />
                            <p
                                class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"
                            >
                                How long to lock out users after max attempts
                            </p>
                        </div>

                        <div>
                            <label
                                for="password-min-length"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Minimum Password Length
                            </label>
                            <input
                                id="password-min-length"
                                v-model.number="settings.password_min_length"
                                type="number"
                                min="6"
                                max="128"
                                class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                            />
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <label
                                    class="text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Require Strong Passwords
                                </label>
                                <p
                                    class="text-sm text-neutral-500 dark:text-neutral-400"
                                >
                                    Enforce uppercase, lowercase, numbers,
                                    symbols
                                </p>
                            </div>
                            <button
                                @click="
                                    settings.require_strong_passwords =
                                        !settings.require_strong_passwords
                                "
                                :class="[
                                    settings.require_strong_passwords
                                        ? 'bg-blue-600'
                                        : 'bg-neutral-200 dark:bg-neutral-700',
                                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none',
                                ]"
                            >
                                <span
                                    :class="[
                                        settings.require_strong_passwords
                                            ? 'translate-x-5'
                                            : 'translate-x-0',
                                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                    ]"
                                />
                            </button>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <label
                                    class="text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Force Password Reset
                                </label>
                                <p
                                    class="text-sm text-neutral-500 dark:text-neutral-400"
                                >
                                    Force users to change weak passwords on next
                                    login
                                </p>
                            </div>
                            <button
                                @click="
                                    settings.force_password_reset =
                                        !settings.force_password_reset
                                "
                                :class="[
                                    settings.force_password_reset
                                        ? 'bg-blue-600'
                                        : 'bg-neutral-200 dark:bg-neutral-700',
                                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none',
                                ]"
                            >
                                <span
                                    :class="[
                                        settings.force_password_reset
                                            ? 'translate-x-5'
                                            : 'translate-x-0',
                                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                    ]"
                                />
                            </button>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <label
                                    class="text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >
                                    Session Timeout
                                </label>
                                <p
                                    class="text-sm text-neutral-500 dark:text-neutral-400"
                                >
                                    Auto-logout inactive users
                                </p>
                            </div>
                            <button
                                @click="
                                    settings.session_timeout_enabled =
                                        !settings.session_timeout_enabled
                                "
                                :class="[
                                    settings.session_timeout_enabled
                                        ? 'bg-blue-600'
                                        : 'bg-neutral-200 dark:bg-neutral-700',
                                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none',
                                ]"
                            >
                                <span
                                    :class="[
                                        settings.session_timeout_enabled
                                            ? 'translate-x-5'
                                            : 'translate-x-0',
                                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                    ]"
                                />
                            </button>
                        </div>

                        <div v-if="settings.session_timeout_enabled">
                            <label
                                for="session-timeout"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                            >
                                Session Timeout (minutes)
                            </label>
                            <input
                                id="session-timeout"
                                v-model.number="settings.session_timeout"
                                type="number"
                                min="5"
                                max="480"
                                class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Login Attempt Tracking -->
            <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                <h3
                    class="mb-6 text-lg font-medium text-neutral-900 dark:text-white"
                >
                    Login Attempt Tracking
                </h3>
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div>
                        <label
                            for="max-attempts-before-block"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                        >
                            Max Attempts Before Block
                        </label>
                        <input
                            id="max-attempts-before-block"
                            v-model.number="settings.max_attempts_before_block"
                            type="number"
                            min="1"
                            max="10"
                            class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                        />
                        <p
                            class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"
                        >
                            Number of failed attempts before blocking (default:
                            3)
                        </p>
                    </div>

                    <div>
                        <label
                            for="cross-username-attack-threshold"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                        >
                            Cross-Username Attack Threshold
                        </label>
                        <input
                            id="cross-username-attack-threshold"
                            v-model.number="
                                settings.cross_username_attack_threshold
                            "
                            type="number"
                            min="3"
                            max="20"
                            class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                        />
                        <p
                            class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"
                        >
                            Failed attempts across different usernames before IP
                            block (default: 5)
                        </p>
                    </div>

                    <div>
                        <label
                            for="rate-limit-window-minutes"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                        >
                            Rate Limit Window (minutes)
                        </label>
                        <input
                            id="rate-limit-window-minutes"
                            v-model.number="settings.rate_limit_window_minutes"
                            type="number"
                            min="5"
                            max="120"
                            class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                        />
                        <p
                            class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"
                        >
                            Time window for rate limiting (default: 15 minutes)
                        </p>
                    </div>

                    <div>
                        <label
                            for="rate-limit-max-attempts"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                        >
                            Rate Limit Max Attempts
                        </label>
                        <input
                            id="rate-limit-max-attempts"
                            v-model.number="settings.rate_limit_max_attempts"
                            type="number"
                            min="5"
                            max="50"
                            class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                        />
                        <p
                            class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"
                        >
                            Maximum attempts allowed within the rate limit
                            window (default: 10)
                        </p>
                    </div>
                </div>
            </div>

            <!-- IP Whitelist/Blacklist -->
            <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                <h3
                    class="mb-6 text-lg font-medium text-neutral-900 dark:text-white"
                >
                    IP Access Control
                </h3>
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div>
                        <label
                            for="ip-whitelist"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                        >
                            IP Whitelist
                        </label>
                        <textarea
                            id="ip-whitelist"
                            v-model="settings.ip_whitelist"
                            rows="4"
                            class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                            placeholder="192.168.1.0/24&#10;10.0.0.1&#10;Allow specific IPs/ranges"
                        ></textarea>
                        <p
                            class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"
                        >
                            One IP address or CIDR range per line. Leave empty
                            to allow all.
                        </p>
                    </div>

                    <div>
                        <label
                            for="ip-blacklist"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                        >
                            IP Blacklist
                        </label>
                        <textarea
                            id="ip-blacklist"
                            v-model="settings.ip_blacklist"
                            rows="4"
                            class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                            placeholder="192.168.1.100&#10;10.0.0.0/8&#10;Block specific IPs/ranges"
                        ></textarea>
                        <p
                            class="mt-1 text-sm text-neutral-500 dark:text-neutral-400"
                        >
                            One IP address or CIDR range per line. These IPs
                            will be blocked.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const loading = ref(false);
const activeTab = ref('captcha');

const tabs = [
    { id: 'captcha', name: 'CAPTCHA' },
    { id: 'honeypot', name: 'Honeypot' },
    { id: 'access', name: 'Access' },
    { id: 'roles', name: 'Roles' },
];

const settings = ref({
    // CAPTCHA defaults
    captcha_enabled: false,
    captcha_provider: 'recaptcha',
    recaptcha_site_key: '',
    recaptcha_secret_key: '',
    recaptcha_version: 'v2',
    hcaptcha_site_key: '',
    hcaptcha_secret_key: '',
    turnstile_site_key: '',
    turnstile_secret_key: '',
    captcha_login: false,
    captcha_register: true,
    captcha_contact: true,
    captcha_comments: false,

    // Honeypot defaults
    honeypot_enabled: true,
    honeypot_field_name: 'my_name',
    honeypot_time_limit: 3,
    honeypot_register: true,
    honeypot_contact: true,
    honeypot_comments: true,
    honeypot_newsletter: true,

    // Access control defaults
    max_login_attempts: 5,
    lockout_duration: 15,
    password_min_length: 8,
    require_strong_passwords: true,
    force_password_reset: false,
    session_timeout_enabled: false,
    session_timeout: 120,
    ip_whitelist: '',
    ip_blacklist: '',

    // Login attempt tracking defaults
    max_attempts_before_block: 3,
    cross_username_attack_threshold: 5,
    rate_limit_window_minutes: 15,
    rate_limit_max_attempts: 10,
});

const fetchSettings = async () => {
    try {
        loading.value = true;
        router.get(
            '/admin/security',
            {},
            {
                preserveState: true,
                onSuccess: (page) => {
                    const data =
                        (page.props &&
                            (page.props.settings ?? page.props.data)) ??
                        page.props ??
                        {};
                    // merge returned settings into local state
                    Object.assign(settings.value, data);
                },
                onError: (page) => {
                    console.error(
                        'Failed to fetch security settings (Inertia):',
                        page,
                    );
                },
            },
        );
    } catch (error) {
        console.error('Failed to fetch security settings:', error);
    } finally {
        loading.value = false;
    }
};

const saveSettings = async () => {
    try {
        loading.value = true;
        router.put('/admin/security', settings.value, {
            preserveState: true,
            onSuccess: () => {
                console.log('Security settings saved successfully');
            },
            onError: (page) => {
                console.error(
                    'Failed to save security settings (Inertia):',
                    page,
                );
            },
        });
    } catch (error) {
        console.error('Failed to save security settings:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchSettings();
});
</script>
