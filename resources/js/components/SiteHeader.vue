<script setup lang="ts">
import { home, login, register } from '@/routes';
import adminDashboard from '@/routes/admin/dashboard';
import { Icon } from '@iconify/vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface MenuItem {
    id: number;
    title: string;
    url: string;
    target?: string;
    children?: MenuItem[];
}

interface Props {
    navigation?: MenuItem[];
    logo?: string;
    siteName?: string;
}

withDefaults(defineProps<Props>(), {
    navigation: () => [],
    siteName: 'Contenta',
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const mobileMenuOpen = ref(false);
const darkMode = ref(false);

const toggleDarkMode = () => {
    darkMode.value = !darkMode.value;
    if (darkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

// Initialize dark mode from localStorage
const initDarkMode = () => {
    const savedTheme = localStorage.getItem('theme');
    if (
        savedTheme === 'dark' ||
        (!savedTheme &&
            window.matchMedia('(prefers-color-scheme: dark)').matches)
    ) {
        darkMode.value = true;
        document.documentElement.classList.add('dark');
    }
};

initDarkMode();
</script>

<template>
    <header
        class="sticky top-0 z-50 border-b border-neutral-200 bg-white/95 backdrop-blur-sm transition-colors dark:border-neutral-800 dark:bg-neutral-900/95"
    >
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between md:h-20">
                <!-- Logo and Site Name -->
                <div class="flex items-center">
                    <Link
                        :href="home.url()"
                        class="group flex items-center space-x-3"
                    >
                        <div v-if="logo" class="flex-shrink-0">
                            <img
                                :src="logo"
                                :alt="siteName"
                                class="h-8 w-auto md:h-10"
                            />
                        </div>
                        <div
                            v-else
                            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-blue-600 to-purple-600"
                        >
                            <span class="text-xl font-bold text-white">{{
                                siteName.charAt(0)
                            }}</span>
                        </div>
                        <span
                            class="text-xl font-bold text-neutral-900 transition-colors group-hover:text-blue-600 md:text-2xl dark:text-white dark:group-hover:text-blue-400"
                        >
                            {{ siteName }}
                        </span>
                    </Link>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden items-center space-x-1 md:flex">
                    <template v-for="item in navigation" :key="item.id">
                        <div
                            v-if="item.children && item.children.length > 0"
                            class="group relative"
                        >
                            <button
                                class="flex items-center rounded-lg px-4 py-2 font-medium text-neutral-700 transition-all duration-200 hover:bg-neutral-50 hover:text-blue-600 dark:text-neutral-300 dark:hover:bg-neutral-800 dark:hover:text-blue-400"
                            >
                                {{ item.title }}
                                <Icon
                                    icon="ph:caret-down"
                                    class="ml-1 h-4 w-4"
                                />
                            </button>
                            <!-- Dropdown -->
                            <div
                                class="invisible absolute left-0 mt-2 w-48 rounded-lg border border-neutral-200 bg-white opacity-0 shadow-lg transition-all duration-200 group-hover:visible group-hover:opacity-100 dark:border-neutral-700 dark:bg-neutral-800"
                            >
                                <Link
                                    v-for="child in item.children"
                                    :key="child.id"
                                    :href="child.url"
                                    :target="child.target || '_self'"
                                    class="block px-4 py-2 text-neutral-700 transition-colors first:rounded-t-lg last:rounded-b-lg hover:bg-neutral-50 hover:text-blue-600 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:hover:text-blue-400"
                                >
                                    {{ child.title }}
                                </Link>
                            </div>
                        </div>
                        <Link
                            v-else
                            :href="item.url"
                            :target="item.target || '_self'"
                            class="rounded-lg px-4 py-2 font-medium text-neutral-700 transition-all duration-200 hover:bg-neutral-50 hover:text-blue-600 dark:text-neutral-300 dark:hover:bg-neutral-800 dark:hover:text-blue-400"
                        >
                            {{ item.title }}
                        </Link>
                    </template>
                </nav>

                <!-- Right Side Actions -->
                <div class="flex items-center space-x-2 md:space-x-4">
                    <!-- Dark Mode Toggle -->
                    <button
                        @click="toggleDarkMode"
                        class="rounded-lg p-2 text-neutral-600 transition-colors hover:bg-neutral-100 dark:text-neutral-400 dark:hover:bg-neutral-800"
                        aria-label="Toggle dark mode"
                    >
                        <Icon
                            v-if="darkMode"
                            icon="ph:sun-bold"
                            class="h-5 w-5 md:h-6 md:w-6"
                        />
                        <Icon
                            v-else
                            icon="ph:moon-bold"
                            class="h-5 w-5 md:h-6 md:w-6"
                        />
                    </button>

                    <!-- User Menu / Auth Buttons -->
                    <template v-if="user">
                        <Link
                            :href="adminDashboard.index.url()"
                            class="hidden items-center rounded-lg bg-blue-600 px-4 py-2 font-medium text-white transition-colors hover:bg-blue-700 md:inline-flex"
                        >
                            <Icon icon="ph:gauge" class="mr-2 h-5 w-5" />
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="login.url()"
                            class="hidden px-4 py-2 font-medium text-neutral-700 transition-colors hover:text-blue-600 md:inline-flex dark:text-neutral-300 dark:hover:text-blue-400"
                        >
                            Sign In
                        </Link>
                        <Link
                            :href="register.url()"
                            class="hidden items-center rounded-lg bg-blue-600 px-4 py-2 font-medium text-white transition-colors hover:bg-blue-700 md:inline-flex"
                        >
                            Get Started
                        </Link>
                    </template>

                    <!-- Mobile Menu Button -->
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="rounded-lg p-2 text-neutral-600 transition-colors hover:bg-neutral-100 md:hidden dark:text-neutral-400 dark:hover:bg-neutral-800"
                        aria-label="Toggle menu"
                    >
                        <Icon
                            v-if="mobileMenuOpen"
                            icon="ph:x-bold"
                            class="h-6 w-6"
                        />
                        <Icon v-else icon="ph:list-bold" class="h-6 w-6" />
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-1"
            >
                <div
                    v-if="mobileMenuOpen"
                    class="border-t border-neutral-200 py-4 md:hidden dark:border-neutral-800"
                >
                    <nav class="flex flex-col space-y-1">
                        <template v-for="item in navigation" :key="item.id">
                            <!-- Parent Item -->
                            <div
                                v-if="item.children && item.children.length > 0"
                            >
                                <div
                                    class="px-4 py-3 text-sm font-semibold text-neutral-700 dark:text-neutral-300"
                                >
                                    {{ item.title }}
                                </div>
                                <Link
                                    v-for="child in item.children"
                                    :key="child.id"
                                    :href="child.url"
                                    :target="child.target || '_self'"
                                    class="block rounded-lg px-6 py-2 text-neutral-600 transition-colors hover:bg-neutral-50 hover:text-blue-600 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-blue-400"
                                    @click="mobileMenuOpen = false"
                                >
                                    {{ child.title }}
                                </Link>
                            </div>
                            <!-- Single Item -->
                            <Link
                                v-else
                                :href="item.url"
                                :target="item.target || '_self'"
                                class="rounded-lg px-4 py-3 font-medium text-neutral-700 transition-colors hover:bg-neutral-50 hover:text-blue-600 dark:text-neutral-300 dark:hover:bg-neutral-800 dark:hover:text-blue-400"
                                @click="mobileMenuOpen = false"
                            >
                                {{ item.title }}
                            </Link>
                        </template>

                        <div
                            class="my-2 border-t border-neutral-200 dark:border-neutral-800"
                        ></div>

                        <template v-if="user">
                            <Link
                                :href="adminDashboard.index.url()"
                                class="rounded-lg px-4 py-3 font-medium text-neutral-700 transition-colors hover:bg-neutral-50 hover:text-blue-600 dark:text-neutral-300 dark:hover:bg-neutral-800 dark:hover:text-blue-400"
                                @click="mobileMenuOpen = false"
                            >
                                <Icon
                                    icon="ph:gauge"
                                    class="mr-2 inline h-5 w-5"
                                />
                                Dashboard
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="login.url()"
                                class="rounded-lg px-4 py-3 font-medium text-neutral-700 transition-colors hover:bg-neutral-50 hover:text-blue-600 dark:text-neutral-300 dark:hover:bg-neutral-800 dark:hover:text-blue-400"
                                @click="mobileMenuOpen = false"
                            >
                                Sign In
                            </Link>
                            <Link
                                :href="register.url()"
                                class="rounded-lg bg-blue-600 px-4 py-3 text-center font-medium text-white transition-colors hover:bg-blue-700"
                                @click="mobileMenuOpen = false"
                            >
                                Get Started
                            </Link>
                        </template>
                    </nav>
                </div>
            </Transition>
        </div>
    </header>
</template>
