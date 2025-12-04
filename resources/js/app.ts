import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';
import analyticsPlugin from './plugins/analytics';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

/**
 * Convert a string by replacing dashes/underscores with spaces and
 * capitalizing the first letter. Optionally capitalize the first
 * letter of every word.
 *
 * Examples:
 * ucwords('hello-world') => 'Hello world'
 * ucwords('hello-world', true) => 'Hello World'
 * ucwords('some_value_here', true) => 'Some Value Here'
 *
 * @param value - value to format
 * @param allWords - when true, uppercase first letter of every word
 */
export function ucwords(value: unknown, allWords = false): string {
    if (value === null || value === undefined) return '';

    const s = String(value).replace(/[-_]+/g, ' ').replace(/\s+/g, ' ').trim();

    if (s.length === 0) return '';

    if (allWords) {
        // Uppercase the first letter of every word using a regex replace.
        return s.replace(/\b\w/g, (ch) => ch.toUpperCase());
    }

    // Uppercase only the first non-space character in the string.
    return s.replace(/^(\s*\w)/, (m) => m.toUpperCase());
}

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        // create the Vue app instance so we can register global helpers
        const vueApp = createApp({ render: () => h(App, props) });

        // register Inertia plugin and any other plugins
        vueApp.use(plugin).use(analyticsPlugin);

        // Provide the ucwords helper for Composition API (inject) and
        // attach to globalProperties for Options API / templates as $ucwords
        vueApp.provide('ucwords', ucwords);
        (vueApp.config.globalProperties as any).$ucwords = ucwords;

        vueApp.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
