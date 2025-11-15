<script setup lang="ts">
interface TeamMember {
    name: string;
    role: string;
    bio?: string;
    image?: string;
}

interface Props {
    config: {
        title?: string;
        subtitle?: string;
        members?: TeamMember[];
        columns?: 2 | 3 | 4;
        backgroundColor?: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    config: () => ({
        title: 'Meet Our Team',
        subtitle: 'The people behind our success',
        members: [
            {
                name: 'John Doe',
                role: 'CEO & Founder',
                bio: 'Leading the company with vision and passion.',
                image: '',
            },
        ],
        columns: 3,
        backgroundColor: 'bg-white dark:bg-gray-900',
    }),
});

const gridClass = {
    2: 'md:grid-cols-2',
    3: 'md:grid-cols-3',
    4: 'md:grid-cols-2 lg:grid-cols-4',
}[props.config.columns || 3];
</script>

<template>
    <section class="py-16" :class="config.backgroundColor">
        <div class="container mx-auto px-4">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2
                    class="mb-4 text-3xl font-bold text-gray-900 md:text-4xl dark:text-white"
                >
                    {{ config.title }}
                </h2>
                <p
                    v-if="config.subtitle"
                    class="text-lg text-gray-600 dark:text-gray-300"
                >
                    {{ config.subtitle }}
                </p>
            </div>
            <div
                class="mx-auto grid max-w-6xl grid-cols-1 gap-8"
                :class="gridClass"
            >
                <div
                    v-for="(member, index) in config.members"
                    :key="index"
                    class="text-center"
                >
                    <div
                        class="mx-auto mb-4 flex h-32 w-32 items-center justify-center overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700"
                    >
                        <img
                            v-if="member.image"
                            :src="member.image"
                            :alt="member.name"
                            class="h-full w-full object-cover"
                        />
                        <span
                            v-else
                            class="text-4xl font-bold text-gray-400 dark:text-gray-500"
                        >
                            {{ member.name.charAt(0) }}
                        </span>
                    </div>
                    <h3
                        class="mb-1 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ member.name }}
                    </h3>
                    <p
                        class="mb-2 text-sm font-medium text-blue-600 dark:text-blue-400"
                    >
                        {{ member.role }}
                    </p>
                    <p
                        v-if="member.bio"
                        class="text-gray-600 dark:text-gray-400"
                    >
                        {{ member.bio }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
