<script setup lang="ts">
import FeaturesBlockEditable from './FeaturesBlockEditable.vue';

interface Column {
    title: string;
    content: string;
}

interface Props {
    config: {
        title?: string;
        subtitle?: string;
        columns?: Column[];
        numColumns?: 2 | 3 | 4;
        backgroundColor?: string;
    };
    editMode?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    config: () => ({
        title: 'Our Features',
        subtitle: 'Everything you need to succeed',
        columns: [
            {
                title: 'Fast Performance',
                content: '<p>Lightning-fast page loads with optimized code and efficient caching.</p>',
            },
            {
                title: 'Secure by Default',
                content: '<p>Built-in security features to protect your data and users.</p>',
            },
            {
                title: 'Easy to Use',
                content: '<p>Intuitive interface that makes content management simple and enjoyable.</p>',
            },
        ],
        numColumns: 3,
        backgroundColor: 'bg-white dark:bg-gray-900',
    }),
    editMode: false,
});

const emit = defineEmits<{
    (e: 'update:config', config: any): void;
}>();
</script>

<template>
    <FeaturesBlockEditable
        :config="config"
        :edit-mode="editMode"
        @update:config="emit('update:config', $event)"
    />
</template>
