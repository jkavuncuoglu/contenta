import js from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';
import { defineConfig } from 'eslint/config';
import globals from 'globals';
import tseslint from 'typescript-eslint';
import prettier from 'eslint-config-prettier';

export default defineConfig([
    {
        ignores: [
            '**/node_modules/**',
            '**/vendor/**',
            '**/public/build/**',
            '**/storage/**',
            '**/bootstrap/cache/**',
            '**/*.config.cjs',
            '**/public/build/**',
            '**/public/coverage/**',
            'postcss.config.cjs',
        ],
    },
    // Base JS/TS/Vue files
    {
        files: ['**/*.{js,mjs,cjs,ts,mts,cts,vue}'],
        plugins: { js },
        extends: [js.configs.recommended, prettier],
        languageOptions: {
            globals: { ...globals.browser, ...globals.node },
            parser: tseslint.parser,
            parserOptions: {
                ecmaVersion: 'latest',
                sourceType: 'module',
            },
        },
        rules: {
            'no-unused-vars': 'off',
            '@typescript-eslint/no-unused-vars': [
                'warn',
                { argsIgnorePattern: '^_', varsIgnorePattern: '^_' },
            ],
        },
    },
    // Config files - node environment
    {
        files: ['*.config.js', 'tailwind.config.js'],
        languageOptions: { globals: globals.node },
    },
    // TypeScript specific recommended settings
    ...tseslint.configs.recommended,
    // Vue essential rules
    pluginVue.configs['flat/essential'],
    // Vue + TS overrides
    {
        files: ['**/*.{vue,ts,tsx}'],
        languageOptions: { parserOptions: { parser: tseslint.parser } },
        rules: {
            'vue/multi-word-component-names': 'off',
            '@typescript-eslint/no-explicit-any': 'off',
        },
    },
]);
