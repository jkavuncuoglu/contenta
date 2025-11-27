<template>
    <div class="block-renderer">
        <!-- Hero Block -->
        <HeroBlock
            v-if="section.type === 'hero'"
            :config="config"
        />

        <!-- Features Block -->
        <FeaturesBlock
            v-else-if="section.type === 'features'"
            :config="config"
            :edit-mode="editMode"
            @update:config="emit('update:config', $event)"
        />

        <!-- Contact Form Block -->
        <ContactFormBlock
            v-else-if="section.type === 'contact-form'"
            :config="config"
        />

        <!-- CTA Block -->
        <CTABlock
            v-else-if="section.type === 'cta'"
            :config="config"
        />

        <!-- FAQ Block -->
        <FAQBlock
            v-else-if="section.type === 'faq'"
            :config="config"
        />

        <!-- Stats Block -->
        <StatsBlock
            v-else-if="section.type === 'stats'"
            :config="config"
        />

        <!-- Legal Text Block -->
        <LegalTextBlock
            v-else-if="section.type === 'legal-text'"
            :config="config"
        />

        <!-- Team Block -->
        <TeamBlock
            v-else-if="section.type === 'team'"
            :config="config"
        />

        <!-- Pricing Block -->
        <PricingBlock
            v-else-if="section.type === 'pricing'"
            :config="config"
        />

        <!-- Text Block -->
        <div v-else-if="section.type === 'text-block'" class="text-block">
            <div class="px-6 py-8">
                <div class="mx-auto max-w-4xl">
                    <h2
                        v-if="config.title"
                        class="mb-6 text-3xl font-bold text-gray-900 dark:text-white"
                        :class="getAlignmentClass(config.alignment)"
                    >
                        {{ config.title }}
                    </h2>
                    <div
                        class="prose max-w-none"
                        :class="[
                            getAlignmentClass(config.alignment),
                            getFontSizeClass(config.font_size),
                            getMaxWidthClass(config.max_width),
                        ]"
                        v-html="config.content || 'Your content goes here...'"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Image Block -->
        <div v-else-if="section.type === 'image-block'" class="image-block">
            <div class="px-6 py-8">
                <div class="mx-auto max-w-4xl">
                    <figure :class="getAlignmentClass(config.alignment)">
                        <div
                            v-if="config.src"
                            class="inline-block"
                            :class="{
                                'mx-auto': config.alignment === 'center',
                            }"
                        >
                            <img
                                :src="config.src"
                                :alt="config.alt || ''"
                                class="h-auto max-w-full rounded-lg shadow-lg"
                                :class="[
                                    getBorderRadiusClass(config.border_radius),
                                    getShadowClass(config.shadow),
                                    getMaxWidthClass(config.max_width),
                                ]"
                            />
                        </div>
                        <div
                            v-else
                            class="inline-block rounded-lg bg-gray-200 p-8 text-center dark:bg-gray-700"
                            :class="{
                                'mx-auto': config.alignment === 'center',
                            }"
                        >
                            <svg
                                class="mx-auto h-12 w-12 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                ></path>
                            </svg>
                            <p class="mt-2 text-gray-500 dark:text-gray-400">
                                No image selected
                            </p>
                        </div>
                        <figcaption
                            v-if="config.caption"
                            class="mt-4 text-sm text-gray-600 dark:text-gray-400"
                            :class="getAlignmentClass(config.alignment)"
                        >
                            {{ config.caption }}
                        </figcaption>
                    </figure>
                </div>
            </div>
        </div>

        <!-- Two Column Block -->
        <div
            v-else-if="section.type === 'two-column-block'"
            class="two-column-block"
        >
            <div class="px-6 py-8">
                <div class="mx-auto max-w-6xl">
                    <div class="grid grid-cols-2 gap-6">
                        <div
                            v-html="
                                config.left_content || 'Left column content'
                            "
                        ></div>
                        <div
                            v-html="
                                config.right_content || 'Right column content'
                            "
                        ></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Container Block -->
        <div
            v-else-if="section.type === 'container-block'"
            class="container-block"
        >
            <div
                class="px-6 py-8"
                :class="{
                    'bg-white dark:bg-gray-800': config.background === 'white',
                    'bg-gray-100 dark:bg-gray-700':
                        config.background === 'gray',
                    'bg-blue-600 text-white': config.background === 'primary',
                }"
            >
                <div
                    class="mx-auto max-w-4xl"
                    v-html="config.content || 'Container content'"
                ></div>
            </div>
        </div>

        <!-- Heading Block -->
        <div v-else-if="section.type === 'heading-block'" class="heading-block">
            <div class="px-6 py-4">
                <div class="mx-auto max-w-4xl">
                    <component
                        :is="config.level || 'h2'"
                        class="font-bold text-gray-900 dark:text-white"
                        :class="[
                            getAlignmentClass(config.alignment),
                            `text-${config.size || '2xl'}`,
                        ]"
                    >
                        {{ config.text || 'Heading Text' }}
                    </component>
                </div>
            </div>
        </div>

        <!-- Quote Block -->
        <div v-else-if="section.type === 'quote-block'" class="quote-block">
            <div class="px-6 py-8">
                <div class="mx-auto max-w-4xl">
                    <blockquote
                        class="border-l-4 border-blue-500 py-4 pl-6 text-gray-700 italic dark:text-gray-300"
                    >
                        <p class="text-lg">
                            {{ config.quote || 'Your quote goes here...' }}
                        </p>
                        <footer
                            v-if="config.author"
                            class="mt-4 text-sm text-gray-600 dark:text-gray-400"
                        >
                            — {{ config.author
                            }}<span v-if="config.cite"
                                >, {{ config.cite }}</span
                            >
                        </footer>
                    </blockquote>
                </div>
            </div>
        </div>

        <!-- List Block -->
        <div v-else-if="section.type === 'list-block'" class="list-block">
            <div class="px-6 py-8">
                <div class="mx-auto max-w-4xl">
                    <h3
                        v-if="config.title"
                        class="mb-4 text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ config.title }}
                    </h3>
                    <ul
                        v-if="config.style !== 'numbered'"
                        class="space-y-2 text-gray-700 dark:text-gray-300"
                    >
                        <li
                            v-for="(item, idx) in getListItems(config.items)"
                            :key="idx"
                            class="flex items-start"
                        >
                            <svg
                                class="mt-0.5 mr-2 h-5 w-5 text-blue-500"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            {{ item }}
                        </li>
                    </ul>
                    <ol
                        v-else
                        class="list-inside list-decimal space-y-2 text-gray-700 dark:text-gray-300"
                    >
                        <li
                            v-for="(item, idx) in getListItems(config.items)"
                            :key="idx"
                        >
                            {{ item }}
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Video Block -->
        <div v-else-if="section.type === 'video-block'" class="video-block">
            <div class="px-6 py-8">
                <div class="mx-auto max-w-4xl">
                    <h3
                        v-if="config.title"
                        class="mb-4 text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ config.title }}
                    </h3>
                    <div
                        class="flex aspect-video items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700"
                    >
                        <svg
                            class="h-16 w-16 text-gray-400"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"
                            />
                        </svg>
                    </div>
                    <p
                        v-if="config.description"
                        class="mt-4 text-gray-600 dark:text-gray-400"
                    >
                        {{ config.description }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Gallery Block -->
        <div v-else-if="section.type === 'gallery-block'" class="gallery-block">
            <div class="px-6 py-8">
                <div class="mx-auto max-w-6xl">
                    <h3
                        v-if="config.title"
                        class="mb-6 text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ config.title }}
                    </h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div
                            v-for="i in 6"
                            :key="i"
                            class="aspect-square rounded-lg bg-gray-200 dark:bg-gray-700"
                        ></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Form Block -->
        <div
            v-else-if="section.type === 'contact-form-block'"
            class="contact-form-block"
        >
            <div class="px-6 py-8">
                <div class="mx-auto max-w-2xl">
                    <h3
                        class="mb-4 text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ config.title || 'Contact Us' }}
                    </h3>
                    <p
                        v-if="config.description"
                        class="mb-6 text-gray-600 dark:text-gray-400"
                    >
                        {{ config.description }}
                    </p>
                    <div class="space-y-4">
                        <input
                            type="text"
                            placeholder="Name"
                            class="w-full rounded-md border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                        <input
                            type="email"
                            placeholder="Email"
                            class="w-full rounded-md border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                        <input
                            v-if="config.show_phone"
                            type="tel"
                            placeholder="Phone"
                            class="w-full rounded-md border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                        <input
                            v-if="config.show_subject"
                            type="text"
                            placeholder="Subject"
                            class="w-full rounded-md border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                        <textarea
                            placeholder="Message"
                            rows="4"
                            class="w-full rounded-md border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        ></textarea>
                        <button
                            class="rounded-md bg-blue-600 px-6 py-3 text-white hover:bg-blue-700"
                        >
                            {{ config.submit_text || 'Send Message' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Newsletter Block -->
        <div
            v-else-if="section.type === 'newsletter-block'"
            class="newsletter-block"
        >
            <div class="bg-blue-50 px-6 py-8 dark:bg-blue-900/20">
                <div class="mx-auto max-w-2xl text-center">
                    <h3
                        class="mb-2 text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ config.title || 'Subscribe to Our Newsletter' }}
                    </h3>
                    <p class="mb-6 text-gray-600 dark:text-gray-400">
                        {{ config.description }}
                    </p>
                    <div class="mx-auto flex max-w-md gap-2">
                        <input
                            v-if="config.show_name"
                            type="text"
                            placeholder="Name"
                            class="flex-1 rounded-md border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                        <input
                            type="email"
                            placeholder="Email"
                            class="flex-1 rounded-md border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                        <button
                            class="rounded-md bg-blue-600 px-6 py-2 text-white hover:bg-blue-700"
                        >
                            {{ config.button_text || 'Subscribe' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Button Block -->
        <div v-else-if="section.type === 'button-block'" class="button-block">
            <div class="px-6 py-6">
                <div
                    class="mx-auto max-w-4xl"
                    :class="getAlignmentClass(config.alignment)"
                >
                    <button
                        class="rounded-md px-6 py-3 font-medium transition-colors"
                        :class="{
                            'bg-blue-600 text-white hover:bg-blue-700':
                                config.style === 'primary',
                            'bg-gray-600 text-white hover:bg-gray-700':
                                config.style === 'secondary',
                            'border-2 border-blue-600 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20':
                                config.style === 'outline',
                            'text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20':
                                config.style === 'ghost',
                        }"
                    >
                        {{ config.text || 'Click Here' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- CTA Block -->
        <div v-else-if="section.type === 'cta-block'" class="cta-block">
            <div
                class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-16 text-white"
            >
                <div class="mx-auto max-w-4xl text-center">
                    <h2 class="mb-4 text-4xl font-bold">
                        {{ config.title || 'Ready to Get Started?' }}
                    </h2>
                    <p
                        v-if="config.description"
                        class="mb-8 text-xl opacity-90"
                    >
                        {{ config.description }}
                    </p>
                    <div class="flex justify-center gap-4">
                        <button
                            class="rounded-lg bg-white px-8 py-3 font-bold text-blue-600 hover:bg-gray-100"
                        >
                            {{ config.button_text || 'Get Started' }}
                        </button>
                        <button
                            v-if="config.secondary_button_text"
                            class="rounded-lg border-2 border-white px-8 py-3 font-bold text-white hover:bg-white/10"
                        >
                            {{ config.secondary_button_text }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breadcrumb Block -->
        <div
            v-else-if="section.type === 'breadcrumb-block'"
            class="breadcrumb-block"
        >
            <div class="px-6 py-4">
                <div class="mx-auto max-w-4xl">
                    <nav class="flex text-sm text-gray-600 dark:text-gray-400">
                        <a href="#" class="hover:text-blue-600">Home</a>
                        <span class="mx-2">/</span>
                        <span class="text-gray-900 dark:text-white"
                            >Current Page</span
                        >
                    </nav>
                </div>
            </div>
        </div>

        <!-- Unknown Block Type -->
        <div v-else class="unknown-block">
            <div
                class="rounded-lg border-2 border-dashed border-gray-300 bg-gray-100 p-8 text-center dark:border-gray-600 dark:bg-gray-700"
            >
                <svg
                    class="mx-auto h-12 w-12 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
                    ></path>
                </svg>
                <h3
                    class="mt-2 text-sm font-medium text-gray-900 dark:text-white"
                >
                    Unknown Block
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Block type "{{ section.type }}" not found
                </p>
            </div>
        </div>

        <!-- Block Info Overlay (in edit mode) -->
        <div v-if="!previewMode" class="absolute top-2 left-2 z-20">
            <span
                class="inline-flex items-center rounded-md bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800"
            >
                {{ getBlockName() }}
            </span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import HeroBlock from './blocks/HeroBlock.vue';
import FeaturesBlock from './blocks/FeaturesBlock.vue';
import ContactFormBlock from './blocks/ContactFormBlock.vue';
import CTABlock from './blocks/CTABlock.vue';
import FAQBlock from './blocks/FAQBlock.vue';
import StatsBlock from './blocks/StatsBlock.vue';
import LegalTextBlock from './blocks/LegalTextBlock.vue';
import TeamBlock from './blocks/TeamBlock.vue';
import PricingBlock from './blocks/PricingBlock.vue';

interface Block {
    id: number;
    name: string;
    type: string;
}

interface Section {
    id: string;
    type: string;
    config: Record<string, any>;
}

interface Props {
    section: Section;
    availableBlocks: Block[];
    previewMode: boolean;
    editMode?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    previewMode: false,
    editMode: false,
});

const emit = defineEmits<{
    (e: 'update:config', config: Record<string, any>): void;
}>();

const config = computed(() => props.section.config || {});

const getBlockName = () => {
    const block = props.availableBlocks.find(
        (b) => b.type === props.section.type,
    );
    return block?.name || props.section.type;
};

const getAlignmentClass = (alignment: string) => {
    switch (alignment) {
        case 'center':
            return 'text-center';
        case 'right':
            return 'text-right';
        case 'left':
        default:
            return 'text-left';
    }
};

const getFontSizeClass = (fontSize: string) => {
    switch (fontSize) {
        case 'sm':
            return 'text-sm';
        case 'lg':
            return 'text-lg';
        case 'xl':
            return 'text-xl';
        case '2xl':
            return 'text-2xl';
        case 'base':
        default:
            return 'text-base';
    }
};

const getMaxWidthClass = (maxWidth: string) => {
    switch (maxWidth) {
        case 'sm':
            return 'max-w-sm';
        case 'md':
            return 'max-w-md';
        case 'lg':
            return 'max-w-lg';
        case 'xl':
            return 'max-w-xl';
        case '2xl':
            return 'max-w-2xl';
        case '4xl':
            return 'max-w-4xl';
        case '6xl':
            return 'max-w-6xl';
        case 'full':
            return 'max-w-full';
        default:
            return 'max-w-4xl';
    }
};

const getBorderRadiusClass = (borderRadius: string) => {
    switch (borderRadius) {
        case 'none':
            return 'rounded-none';
        case 'sm':
            return 'rounded-sm';
        case 'md':
            return 'rounded-md';
        case 'xl':
            return 'rounded-xl';
        case 'full':
            return 'rounded-full';
        case 'lg':
        default:
            return 'rounded-lg';
    }
};

const getShadowClass = (shadow: string) => {
    switch (shadow) {
        case 'none':
            return 'shadow-none';
        case 'sm':
            return 'shadow-sm';
        case 'md':
            return 'shadow-md';
        case 'xl':
            return 'shadow-xl';
        case '2xl':
            return 'shadow-2xl';
        case 'lg':
        default:
            return 'shadow-lg';
    }
};

const getListItems = (items: string | string[]) => {
    if (Array.isArray(items)) {
        return items;
    }
    if (typeof items === 'string') {
        return items
            .split(',')
            .map((item) => item.trim())
            .filter((item) => item);
    }
    return [];
};
</script>

<style scoped>
.block-renderer {
    position: relative;
}

.prose {
    color: inherit;
}

.prose h1,
.prose h2,
.prose h3,
.prose h4,
.prose h5,
.prose h6 {
    color: inherit;
}

.prose p {
    color: inherit;
}
</style>
