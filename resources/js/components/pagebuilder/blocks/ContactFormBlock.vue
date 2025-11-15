<script setup lang="ts">
import { Icon } from '@iconify/vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    config: {
        title?: string;
        description?: string;
        submitUrl?: string;
        successMessage?: string;
        showPhone?: boolean;
        showSubject?: boolean;
        backgroundColor?: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    config: () => ({
        title: 'Get in Touch',
        description:
            "Fill out the form below and we'll get back to you as soon as possible.",
        submitUrl: '/contact',
        successMessage: "Thank you for your message! We'll be in touch soon.",
        showPhone: true,
        showSubject: true,
        backgroundColor: 'bg-gray-50 dark:bg-gray-800',
    }),
});

const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
});

const showSuccess = ref(false);

const submit = () => {
    form.post(props.config.submitUrl || '/contact', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showSuccess.value = true;
            setTimeout(() => {
                showSuccess.value = false;
            }, 5000);
        },
    });
};
</script>

<template>
    <section class="py-16" :class="config.backgroundColor">
        <div class="container mx-auto px-4">
            <div class="mx-auto max-w-3xl">
                <div class="mb-10 text-center">
                    <h2
                        class="mb-4 text-3xl font-bold text-gray-900 md:text-4xl dark:text-white"
                    >
                        {{ config.title }}
                    </h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300">
                        {{ config.description }}
                    </p>
                </div>

                <!-- Success Message -->
                <div
                    v-if="showSuccess"
                    class="mb-6 flex items-start gap-3 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20"
                >
                    <Icon
                        icon="ph:check-circle"
                        class="mt-0.5 h-6 w-6 flex-shrink-0 text-green-600 dark:text-green-400"
                    />
                    <p class="text-green-800 dark:text-green-200">
                        {{ config.successMessage }}
                    </p>
                </div>

                <form
                    @submit.prevent="submit"
                    class="space-y-6 rounded-xl bg-white p-8 shadow-lg dark:bg-gray-900"
                >
                    <!-- Name -->
                    <div>
                        <label
                            for="name"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Name *
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            placeholder="Your name"
                        />
                        <p
                            v-if="form.errors.name"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label
                            for="email"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Email *
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            placeholder="your@email.com"
                        />
                        <p
                            v-if="form.errors.email"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Phone (optional) -->
                    <div v-if="config.showPhone">
                        <label
                            for="phone"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Phone
                        </label>
                        <input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            placeholder="+1 (555) 000-0000"
                        />
                        <p
                            v-if="form.errors.phone"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.phone }}
                        </p>
                    </div>

                    <!-- Subject (optional) -->
                    <div v-if="config.showSubject">
                        <label
                            for="subject"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Subject
                        </label>
                        <input
                            id="subject"
                            v-model="form.subject"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            placeholder="How can we help?"
                        />
                        <p
                            v-if="form.errors.subject"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.subject }}
                        </p>
                    </div>

                    <!-- Message -->
                    <div>
                        <label
                            for="message"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Message *
                        </label>
                        <textarea
                            id="message"
                            v-model="form.message"
                            required
                            rows="5"
                            class="w-full resize-none rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            placeholder="Tell us more about your inquiry..."
                        ></textarea>
                        <p
                            v-if="form.errors.message"
                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                        >
                            {{ form.errors.message }}
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-8 py-3 font-semibold text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span v-if="form.processing">Sending...</span>
                        <span v-else>Send Message</span>
                        <Icon
                            v-if="!form.processing"
                            icon="ph:paper-plane-right"
                            class="h-5 w-5"
                        />
                    </button>
                </form>
            </div>
        </div>
    </section>
</template>
