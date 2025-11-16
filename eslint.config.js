import js from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';
import { defineConfig } from 'eslint/config';
import globals from 'globals';
import tseslint from 'typescript-eslint';

export default defineConfig([
    {
        ignores: [
            '**/node_modules/**',
            '**/vendor/**',
            '**/public/build/**',
            '**/storage/**',
            '**/bootstrap/cache/**',
            '**/*.config.cjs',
            '**/build/**',
            'postcss.config.cjs',
        ],
    },
    {
        files: ['**/*.{js,mjs,cjs,ts,mts,cts,vue}'],
        plugins: { js },
        extends: ['js/recommended'],
        languageOptions: { globals: globals.browser },
    },
    {
        files: ['*.config.js', 'tailwind.config.js'],
        languageOptions: { globals: globals.node },
    },
    tseslint.configs.recommended,
    pluginVue.configs['flat/essential'],
    {
        files: ['**/*.{vue,ts,tsx}'],
        languageOptions: { parserOptions: { parser: tseslint.parser } },
        rules: {
            'vue/multi-word-component-names': 'off',
            '@typescript-eslint/no-explicit-any': 'off',
        },
    },
]);
