<template>
  <div class="shortcodes-library">
    <div class="mb-4">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search shortcodes..."
        class="w-full rounded-lg border border-neutral-300 bg-white px-4 py-2 text-neutral-900 focus:border-blue-500 focus:outline-none dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
      />
    </div>

    <div class="space-y-2">
      <div
        v-for="shortcode in filteredShortcodes"
        :key="shortcode.tag"
        class="cursor-pointer rounded-lg border border-neutral-200 bg-white p-4 transition-all hover:border-blue-500 hover:shadow-md dark:border-neutral-700 dark:bg-neutral-900 dark:hover:border-blue-400"
        @click="insertShortcode(shortcode)"
      >
        <div class="mb-2 flex items-center justify-between">
          <h3 class="font-semibold text-neutral-900 dark:text-white">{{ shortcode.name }}</h3>
          <span class="rounded bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
            {{ shortcode.category }}
          </span>
        </div>
        <p class="mb-3 text-sm text-neutral-600 dark:text-neutral-400">{{ shortcode.description }}</p>
        <pre class="overflow-x-auto rounded bg-neutral-50 p-2 text-xs text-neutral-800 dark:bg-neutral-800 dark:text-neutral-300">{{
          shortcode.template
        }}</pre>
      </div>
    </div>

    <div v-if="filteredShortcodes.length === 0" class="py-12 text-center text-neutral-500 dark:text-neutral-400">
      No shortcodes found matching "{{ searchQuery }}"
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'

interface Shortcode {
  tag: string
  name: string
  description: string
  category: string
  template: string
}

const emit = defineEmits<{
  insert: [template: string]
}>()

const searchQuery = ref('')

const shortcodes: Shortcode[] = [
  {
    tag: 'hero',
    name: 'Hero Section',
    description: 'Large header section with title, subtitle, and call-to-action',
    category: 'Layout',
    template: `[#hero title="Welcome" subtitle="Get Started"]{
Your description here

[#button url="/about"]{Learn More}[/#button]
}[/#hero]`,
  },
  {
    tag: 'features',
    name: 'Features Grid',
    description: 'Display features in a responsive grid layout',
    category: 'Content',
    template: `[#features title="Our Features" columns="3"]{
[#feature icon="⚡" title="Feature 1"]{
Description of feature 1
}[/#feature]

[#feature icon="🔒" title="Feature 2"]{
Description of feature 2
}[/#feature]
}[/#features]`,
  },
  {
    tag: 'cta',
    name: 'Call to Action',
    description: 'Eye-catching call-to-action section',
    category: 'Marketing',
    template: `[#cta title="Ready to Get Started?" button-text="Sign Up" button-url="/signup"]{
Join thousands of satisfied customers today.
}[/#cta]`,
  },
  {
    tag: 'pricing',
    name: 'Pricing Table',
    description: 'Display pricing tiers with features',
    category: 'Marketing',
    template: `[#pricing title="Choose Your Plan"]{
[#plan name="Starter" price="$9" period="/month" button-text="Get Started" button-url="/signup"]{
- 5 pages
- Basic support
- Community access
}[/#plan]

[#plan name="Pro" price="$29" period="/month" highlighted="true" button-text="Try Free" button-url="/trial"]{
- Unlimited pages
- Priority support
- Advanced features
}[/#plan]
}[/#pricing]`,
  },
  {
    tag: 'faq',
    name: 'FAQ Section',
    description: 'Frequently asked questions in accordion format',
    category: 'Content',
    template: `[#faq title="Frequently Asked Questions"]{
[#faq-item question="What is this?"]{
Your answer here
}[/#faq-item]

[#faq-item question="How does it work?"]{
Explanation here
}[/#faq-item]
}[/#faq]`,
  },
  {
    tag: 'stats',
    name: 'Statistics',
    description: 'Display key numbers and metrics',
    category: 'Content',
    template: `[#stats title="Our Impact"]{
[#stat value="10,000+" label="Users"][/#stat]
[#stat value="99.9%" label="Uptime"][/#stat]
[#stat value="4.9/5" label="Rating"][/#stat]
}[/#stats]`,
  },
  {
    tag: 'team',
    name: 'Team Section',
    description: 'Showcase team members with photos and bios',
    category: 'Content',
    template: `[#team title="Meet Our Team" columns="3"]{
[#member name="John Doe" role="CEO" image="/team/john.jpg"]{
Bio information here
}[/#member]
}[/#team]`,
  },
  {
    tag: 'testimonials',
    name: 'Testimonials',
    description: 'Customer testimonials and reviews',
    category: 'Marketing',
    template: `[#testimonials title="What Our Customers Say"]{
[#testimonial author="Jane Smith" role="CEO, Company"]{
"This product changed everything for us!"
}[/#testimonial]
}[/#testimonials]`,
  },
  {
    tag: 'text',
    name: 'Text Block',
    description: 'Rich text content with markdown support',
    category: 'Content',
    template: `[#text]{
# Heading

Your **markdown** content here.

- List item 1
- List item 2
}[/#text]`,
  },
  {
    tag: 'image',
    name: 'Image',
    description: 'Responsive image with caption',
    category: 'Media',
    template: `[#image src="/path/to/image.jpg" alt="Description" caption="Image caption" /]`,
  },
  {
    tag: 'video',
    name: 'Video Embed',
    description: 'Embed video from URL',
    category: 'Media',
    template: `[#video url="https://www.youtube.com/watch?v=..." /]`,
  },
  {
    tag: 'button',
    name: 'Button',
    description: 'Styled button with link',
    category: 'UI Elements',
    template: `[#button url="/action" variant="primary"]{Click Me}[/#button]`,
  },
  {
    tag: 'columns',
    name: 'Column Layout',
    description: 'Multi-column responsive layout',
    category: 'Layout',
    template: `[#columns count="2"]{
[#column]{
Left column content
}[/#column]

[#column]{
Right column content
}[/#column]
}[/#columns]`,
  },
  {
    tag: 'contact-form',
    name: 'Contact Form',
    description: 'Simple contact form',
    category: 'Forms',
    template: `[#contact-form title="Get in Touch" description="We'd love to hear from you"][/#contact-form]`,
  },
]

const filteredShortcodes = computed(() => {
  if (!searchQuery.value) return shortcodes

  const query = searchQuery.value.toLowerCase()
  return shortcodes.filter(
    (shortcode) =>
      shortcode.name.toLowerCase().includes(query) ||
      shortcode.description.toLowerCase().includes(query) ||
      shortcode.tag.toLowerCase().includes(query) ||
      shortcode.category.toLowerCase().includes(query),
  )
})

function insertShortcode(shortcode: Shortcode) {
  emit('insert', shortcode.template)
}
</script>

<style scoped>
pre {
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  white-space: pre-wrap;
  word-wrap: break-word;
}
</style>
