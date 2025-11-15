<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';

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
  if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    darkMode.value = true;
    document.documentElement.classList.add('dark');
  }
};

initDarkMode();
</script>

<template>
  <header class="sticky top-0 z-50 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm border-b border-gray-200 dark:border-gray-800 transition-colors">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16 md:h-20">
        <!-- Logo and Site Name -->
        <div class="flex items-center">
          <Link :href="route('home')" class="flex items-center space-x-3 group">
            <div v-if="logo" class="flex-shrink-0">
              <img :src="logo" :alt="siteName" class="h-8 w-auto md:h-10" />
            </div>
            <div v-else class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-xl">{{ siteName.charAt(0) }}</span>
            </div>
            <span class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
              {{ siteName }}
            </span>
          </Link>
        </div>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center space-x-1">
          <template v-for="item in navigation" :key="item.id">
            <div v-if="item.children && item.children.length > 0" class="relative group">
              <button class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition-all duration-200 font-medium flex items-center">
                {{ item.title }}
                <Icon icon="ph:caret-down" class="w-4 h-4 ml-1" />
              </button>
              <!-- Dropdown -->
              <div class="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                <Link
                  v-for="child in item.children"
                  :key="child.id"
                  :href="child.url"
                  :target="child.target || '_self'"
                  class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 first:rounded-t-lg last:rounded-b-lg transition-colors"
                >
                  {{ child.title }}
                </Link>
              </div>
            </div>
            <Link
              v-else
              :href="item.url"
              :target="item.target || '_self'"
              class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition-all duration-200 font-medium"
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
            class="p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            aria-label="Toggle dark mode"
          >
            <Icon v-if="darkMode" icon="ph:sun-bold" class="w-5 h-5 md:w-6 md:h-6" />
            <Icon v-else icon="ph:moon-bold" class="w-5 h-5 md:w-6 md:h-6" />
          </button>

          <!-- User Menu / Auth Buttons -->
          <template v-if="user">
            <Link
              :href="route('admin.dashboard.index')"
              class="hidden md:inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors"
            >
              <Icon icon="ph:gauge" class="w-5 h-5 mr-2" />
              Dashboard
            </Link>
          </template>
          <template v-else>
            <Link
              :href="route('login')"
              class="hidden md:inline-flex px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium transition-colors"
            >
              Sign In
            </Link>
            <Link
              :href="route('register')"
              class="hidden md:inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors"
            >
              Get Started
            </Link>
          </template>

          <!-- Mobile Menu Button -->
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="md:hidden p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            aria-label="Toggle menu"
          >
            <Icon v-if="mobileMenuOpen" icon="ph:x-bold" class="w-6 h-6" />
            <Icon v-else icon="ph:list-bold" class="w-6 h-6" />
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
        <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-200 dark:border-gray-800 py-4">
          <nav class="flex flex-col space-y-1">
            <template v-for="item in navigation" :key="item.id">
              <!-- Parent Item -->
              <div v-if="item.children && item.children.length > 0">
                <div class="px-4 py-3 text-gray-700 dark:text-gray-300 font-semibold text-sm">
                  {{ item.title }}
                </div>
                <Link
                  v-for="child in item.children"
                  :key="child.id"
                  :href="child.url"
                  :target="child.target || '_self'"
                  class="px-6 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg transition-colors block"
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
                class="px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg transition-colors font-medium"
                @click="mobileMenuOpen = false"
              >
                {{ item.title }}
              </Link>
            </template>

            <div class="border-t border-gray-200 dark:border-gray-800 my-2"></div>

            <template v-if="user">
              <Link
                :href="route('admin.dashboard.index')"
                class="px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg transition-colors font-medium"
                @click="mobileMenuOpen = false"
              >
                <Icon icon="ph:gauge" class="w-5 h-5 mr-2 inline" />
                Dashboard
              </Link>
            </template>
            <template v-else>
              <Link
                :href="route('login')"
                class="px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg transition-colors font-medium"
                @click="mobileMenuOpen = false"
              >
                Sign In
              </Link>
              <Link
                :href="route('register')"
                class="px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors text-center"
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
