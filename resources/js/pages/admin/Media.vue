<template>
  <Head title="Media" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Media</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Manage your media files and uploads
          </p>
        </div>
        <button
          @click="uploadFile"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
        >
          <Icon icon="material-symbols-light:add" class="w-4 h-4 mr-2" />
          Upload Media
        </button>
      </div>

      <!-- Flash Messages -->
      <div v-if="flashMessage?.success" class="bg-green-50 border border-green-200 rounded-md p-4">
        <div class="flex">
          <Icon icon="material-symbols-light:check-circle" class="h-5 w-5 text-green-400" />
          <div class="ml-3">
            <p class="text-sm text-green-800">{{ flashMessage.success }}</p>
          </div>
        </div>
      </div>

      <div v-if="flashMessage?.error" class="bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
          <Icon icon="material-symbols-light:error" class="h-5 w-5 text-red-400" />
          <div class="ml-3">
            <p class="text-sm text-red-800">{{ flashMessage.error }}</p>
          </div>
        </div>
      </div>

      <!-- Media Grid -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div v-if="!props.media.length" class="p-12 text-center">
          <Icon icon="material-symbols-light:image" class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No media files</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by uploading your first media file.</p>
        </div>

        <div v-else class="p-6">
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div
              v-for="item in props.media"
              :key="item.id"
              class="group relative aspect-square bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden hover:shadow-lg transition-shadow"
            >
              <img
                v-if="item.type === 'image'"
                :src="item.url"
                :alt="item.name"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center">
                <Icon icon="material-symbols-light:description" class="w-8 h-8 text-gray-400" />
              </div>

              <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-opacity flex items-center justify-center opacity-0 group-hover:opacity-100">
                <div class="flex space-x-2">
                  <button
                    @click="viewMedia(item)"
                    class="p-2 bg-white rounded-full text-gray-700 hover:bg-gray-100"
                  >
                    <Icon icon="material-symbols-light:visibility" class="w-4 h-4" />
                  </button>
                  <button
                    @click="deleteMedia(item)"
                    class="p-2 bg-white rounded-full text-red-600 hover:bg-gray-100"
                  >
                    <Icon icon="material-symbols-light:delete" class="w-4 h-4" />
                  </button>
                </div>
              </div>

              <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black to-transparent">
                <p class="text-white text-xs truncate">{{ item.name }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Icon } from '@iconify/vue';
import { withDefaults, computed } from 'vue';

interface MediaItem {
  id: number;
  name: string;
  url: string;
  type: 'image' | 'document' | 'video' | 'audio';
  size: number;
  created_at: string;
}

interface Props {
  media: MediaItem[];
}

const props = withDefaults(defineProps<Props>(), {
  media: () => [],
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Media', href: '/admin/media' },
];

const page = usePage();
const flashMessage = computed(() => page.props.flash);

const uploadFile = () => {
  const input = document.createElement('input');
  input.type = 'file';
  input.multiple = true;
  input.accept = 'image/*,application/pdf,application/msword,text/plain';

  input.onchange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const files = target.files;
    if (files && files.length > 0) {
      uploadFiles(Array.from(files));
    }
  };

  input.click();
};

const uploadFiles = async (files: File[]) => {
  for (const file of files) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('collection', 'uploads');

    router.post('/admin/media', formData, {
      forceFormData: true,
      preserveState: false,
      preserveScroll: false,
    });
  }
};

const viewMedia = (item: MediaItem) => {
  window.open(item.url, '_blank');
};

const deleteMedia = (item: MediaItem) => {
  if (confirm(`Are you sure you want to delete "${item.name}"?`)) {
    router.delete(`/admin/media/${item.id}`, {
      preserveState: false,
      preserveScroll: false,
    });
  }
};
</script>
