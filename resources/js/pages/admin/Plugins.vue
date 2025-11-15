<template>
  <Head title="Plugins" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Plugins</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Manage and configure plugins for your site
          </p>
        </div>
        <div class="flex gap-2">
          <button
            @click="discoverPlugins"
            :disabled="discovering"
            class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50"
          >
            <Icon icon="material-symbols-light:refresh" class="w-4 h-4 mr-2" />
            {{ discovering ? 'Discovering...' : 'Discover' }}
          </button>
          <button
            @click="triggerUpload"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
          >
            <Icon icon="material-symbols-light:add" class="w-4 h-4 mr-2" />
            Upload Plugin
          </button>
        </div>
      </div>

      <!-- Upload Input (Hidden) -->
      <input
        ref="fileInput"
        type="file"
        accept=".zip"
        @change="handleFileUpload"
        class="hidden"
      />

      <!-- Flash Messages -->
      <div v-if="message" class="rounded-md p-4" :class="messageType === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
        <div class="flex">
          <Icon :icon="messageType === 'success' ? 'material-symbols-light:check-circle' : 'material-symbols-light:error'" class="h-5 w-5" :class="messageType === 'success' ? 'text-green-400' : 'text-red-400'" />
          <div class="ml-3">
            <p class="text-sm" :class="messageType === 'success' ? 'text-green-800' : 'text-red-800'">{{ message }}</p>
          </div>
        </div>
      </div>

      <!-- Plugins List -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div v-if="!hasAnyPlugins" class="p-12 text-center">
          <Icon icon="material-symbols-light:extension" class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No plugins found</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by uploading your first plugin.</p>
        </div>

        <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
          <!-- Uninstalled Plugins Section -->
          <div v-if="uninstalledPlugins.length > 0" class="p-6 bg-blue-50 dark:bg-blue-900/10">
            <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-4">
              Available Plugins ({{ uninstalledPlugins.length }})
            </h3>
            <div class="space-y-4">
              <div
                v-for="plugin in uninstalledPlugins"
                :key="plugin.slug"
                class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                      <h4 class="text-base font-semibold text-gray-900 dark:text-white">
                        {{ plugin.name }}
                      </h4>
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                        v{{ plugin.version }}
                      </span>

                      <!-- Plugin Type Badge -->
                      <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getPluginTypeBadge(plugin.plugin_type).class]">
                        <Icon :icon="getPluginTypeBadge(plugin.plugin_type).icon" class="w-3 h-3 mr-1" />
                        {{ getPluginTypeBadge(plugin.plugin_type).label }}
                      </span>

                      <!-- Show Duplicate badge only for duplicates -->
                      <template v-if="plugin.is_duplicate">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300">
                          <Icon icon="material-symbols-light:content-copy" class="w-3 h-3 mr-1" />
                          Duplicate
                        </span>
                      </template>

                      <!-- Show Security Issues badge only for non-duplicates with security issues -->
                      <template v-else-if="!plugin.is_safe">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                          <Icon icon="material-symbols-light:warning" class="w-3 h-3 mr-1" />
                          Security Issues
                        </span>
                      </template>
                    </div>

                    <p v-if="plugin.description" class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                      {{ plugin.description }}
                    </p>

                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                      <span v-if="plugin.author">
                        <Icon icon="material-symbols-light:person" class="w-3 h-3 inline mr-1" />
                        {{ plugin.author }}
                      </span>
                      <span class="inline-flex items-center">
                        <Icon icon="material-symbols-light:folder" class="w-3 h-3 inline mr-1" />
                        {{ plugin.is_duplicate ? `Folder: ${plugin.folder_name}` : 'Not installed' }}
                      </span>
                    </div>

                    <p v-if="plugin.is_duplicate" class="mt-2 text-xs text-orange-700 dark:text-orange-400">
                      This plugin is already installed. This is a duplicate copy in a different folder.
                    </p>
                  </div>

                  <!-- Delete Button for Duplicates, Install Button for New Plugins -->
                  <div class="ml-6">
                    <!-- Only show Delete button if it's a duplicate -->
                    <template v-if="plugin.is_duplicate">
                      <button
                        @click="deleteUninstalledPlugin(plugin)"
                        :disabled="processing[plugin.folder_name]"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md transition-colors disabled:opacity-50"
                        title="Delete duplicate plugin folder"
                      >
                        {{ processing[plugin.folder_name] ? 'Deleting...' : 'Delete' }}
                      </button>
                    </template>

                    <!-- Only show Install/Delete buttons if NOT a duplicate -->
                    <template v-else>
                      <button
                        v-if="plugin.is_safe"
                        @click="installAndEnablePlugin(plugin)"
                        :disabled="processing[plugin.folder_name]"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors disabled:opacity-50"
                      >
                        {{ processing[plugin.folder_name] ? 'Installing...' : 'Install & Enable' }}
                      </button>
                      <button
                        v-else
                        @click="deleteUninstalledPlugin(plugin)"
                        :disabled="processing[plugin.folder_name]"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md transition-colors disabled:opacity-50"
                        title="Delete plugin with security issues"
                      >
                        {{ processing[plugin.folder_name] ? 'Deleting...' : 'Delete' }}
                      </button>
                    </template>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Installed Plugins Section -->
          <div
            v-for="plugin in installedPlugins"
            :key="plugin.id"
            class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-2">
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ plugin.name }}
                  </h3>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                    v{{ plugin.version }}
                  </span>

                  <!-- Plugin Type Badge -->
                  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getPluginTypeBadge(plugin.plugin_type).class]">
                    <Icon :icon="getPluginTypeBadge(plugin.plugin_type).icon" class="w-3 h-3 mr-1" />
                    {{ getPluginTypeBadge(plugin.plugin_type).label }}
                  </span>

                  <span
                    v-if="plugin.is_enabled"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300"
                  >
                    <Icon icon="material-symbols-light:check-circle" class="w-3 h-3 mr-1" />
                    Enabled
                  </span>
                  <span
                    v-if="!plugin.is_verified"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300"
                  >
                    <Icon icon="material-symbols-light:warning" class="w-3 h-3 mr-1" />
                    Unverified
                  </span>
                  <span
                    v-else-if="hasSecurityIssues(plugin)"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300"
                  >
                    <Icon icon="material-symbols-light:security" class="w-3 h-3 mr-1" />
                    Security Warnings
                  </span>
                  <span
                    v-else-if="plugin.is_verified"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                  >
                    <Icon icon="material-symbols-light:verified" class="w-3 h-3 mr-1" />
                    Verified
                  </span>
                </div>

                <p v-if="plugin.description" class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                  {{ plugin.description }}
                </p>

                <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                  <span v-if="plugin.author">
                    <Icon icon="material-symbols-light:person" class="w-3 h-3 inline mr-1" />
                    {{ plugin.author }}
                  </span>
                  <span v-if="plugin.scanned_at">
                    <Icon icon="material-symbols-light:shield" class="w-3 h-3 inline mr-1" />
                    Scanned {{ formatDate(plugin.scanned_at) }}
                  </span>
                </div>

                <!-- Security Results -->
                <div v-if="plugin.scan_results && (plugin.scan_results.threats?.length || plugin.scan_results.warnings?.length)" class="mt-4">
                  <button
                    @click="toggleScanResults(plugin.slug)"
                    class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 flex items-center gap-1"
                  >
                    <Icon :icon="showScanResults[plugin.slug] ? 'material-symbols-light:expand-less' : 'material-symbols-light:expand-more'" class="w-4 h-4" />
                    {{ showScanResults[plugin.slug] ? 'Hide' : 'Show' }} Security Scan Results
                  </button>

                  <div v-if="showScanResults[plugin.slug]" class="mt-2 p-4 bg-gray-50 dark:bg-gray-900 rounded-md text-xs">
                    <div v-if="plugin.scan_results.threats?.length" class="mb-3">
                      <h4 class="font-semibold text-red-600 dark:text-red-400 mb-2">Threats ({{ plugin.scan_results.threats.length }})</h4>
                      <div v-for="(threat, idx) in plugin.scan_results.threats.slice(0, 5)" :key="idx" class="mb-2">
                        <p class="font-mono text-gray-700 dark:text-gray-300">{{ threat.file }}:{{ threat.line }}</p>
                        <p class="text-gray-600 dark:text-gray-400">{{ threat.issue }}</p>
                      </div>
                    </div>
                    <div v-if="plugin.scan_results.warnings?.length">
                      <h4 class="font-semibold text-yellow-600 dark:text-yellow-400 mb-2">Warnings ({{ plugin.scan_results.warnings.length }})</h4>
                      <div v-for="(warning, idx) in plugin.scan_results.warnings.slice(0, 5)" :key="idx" class="mb-2">
                        <p class="font-mono text-gray-700 dark:text-gray-300">{{ warning.file }}:{{ warning.line }}</p>
                        <p class="text-gray-600 dark:text-gray-400">{{ warning.issue }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-2 ml-6">
                <!-- Enable/Disable Toggle -->
                <button
                  v-if="plugin.is_verified && !hasSecurityIssues(plugin)"
                  @click="togglePlugin(plugin)"
                  :disabled="processing[plugin.slug]"
                  class="px-3 py-2 text-sm font-medium rounded-md transition-colors"
                  :class="plugin.is_enabled
                    ? 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900 dark:text-green-300'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300'"
                >
                  {{ plugin.is_enabled ? 'Disable' : 'Enable' }}
                </button>

                <!-- Scan -->
                <button
                  @click="scanPlugin(plugin)"
                  :disabled="processing[plugin.slug]"
                  class="p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white disabled:opacity-50"
                  title="Scan for security issues"
                >
                  <Icon icon="material-symbols-light:shield-person" class="w-5 h-5" />
                </button>

                <!-- Uninstall -->
                <button
                  @click="uninstallPlugin(plugin)"
                  :disabled="processing[plugin.slug] || plugin.is_enabled"
                  class="p-2 text-red-600 hover:text-red-700 dark:text-red-400 disabled:opacity-50"
                  title="Uninstall plugin"
                >
                  <Icon icon="material-symbols-light:delete" class="w-5 h-5" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { Icon } from '@iconify/vue'
import { ref, reactive, computed } from 'vue'

interface ScanResults {
  safe: boolean
  threats: Array<{ file: string; line: number; issue: string }>
  warnings: Array<{ file: string; line: number; issue: string }>
  scanned_files: number
  scanned_at: string
}

interface InstalledPlugin {
  id: number
  slug: string
  name: string
  description: string | null
  version: string
  author: string | null
  author_url: string | null
  plugin_type: 'frontend' | 'admin' | 'universal'
  is_enabled: boolean
  is_verified: boolean
  scanned_at: string | null
  scan_results: ScanResults | null
}

interface UninstalledPlugin {
  folder_name: string
  slug: string
  name: string
  description: string | null
  version: string
  author: string | null
  author_url: string | null
  plugin_type: 'frontend' | 'admin' | 'universal'
  is_safe: boolean
  is_duplicate: boolean
  scan_results: ScanResults
  metadata: Record<string, any>
}

const props = defineProps<{
  installedPlugins: InstalledPlugin[]
  uninstalledPlugins: UninstalledPlugin[]
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Admin', href: '/admin' },
  { title: 'Plugins', href: '/admin/plugins' }
]

const fileInput = ref<HTMLInputElement | null>(null)
const installedPlugins = ref<InstalledPlugin[]>(props.installedPlugins)
const uninstalledPlugins = ref<UninstalledPlugin[]>(props.uninstalledPlugins)
const processing = reactive<Record<string, boolean>>({})
const showScanResults = reactive<Record<string, boolean>>({})
const discovering = ref(false)
const message = ref('')
const messageType = ref<'success' | 'error'>('success')

const hasAnyPlugins = computed(() =>
  installedPlugins.value.length > 0 || uninstalledPlugins.value.length > 0
)

function showMessage(msg: string, type: 'success' | 'error' = 'success') {
  message.value = msg
  messageType.value = type
  setTimeout(() => {
    message.value = ''
  }, 5000)
}

function triggerUpload() {
  fileInput.value?.click()
}

async function handleFileUpload(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return

  const formData = new FormData()
  formData.append('file', file)

  try {
    const response = await fetch('/admin/plugins/api/upload', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: formData,
    })

    const data = await response.json()

    if (data.success) {
      showMessage(data.message, 'success')
      await refreshPlugins()
    } else {
      showMessage(data.message, 'error')
    }
  } catch {
    showMessage('Failed to upload plugin', 'error')
  } finally {
    target.value = ''
  }
}

async function installAndEnablePlugin(plugin: UninstalledPlugin) {
  processing[plugin.folder_name] = true

  try {
    const response = await fetch(`/admin/plugins/api/${plugin.slug}/enable`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })

    const data = await response.json()

    if (data.success) {
      showMessage(data.message, 'success')
      await refreshPlugins()
    } else {
      showMessage(data.message, 'error')
    }
  } catch {
    showMessage('Failed to install plugin', 'error')
  } finally {
    processing[plugin.folder_name] = false
  }
}

async function deleteUninstalledPlugin(plugin: UninstalledPlugin) {
  if (!confirm(`Are you sure you want to delete the duplicate plugin folder "${plugin.folder_name}"? This action cannot be undone.`)) {
    return
  }

  processing[plugin.folder_name] = true

  try {
    const response = await fetch(`/admin/plugins/api/uninstalled/${plugin.folder_name}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })

    const data = await response.json()

    if (data.success) {
      showMessage(data.message, 'success')
      await refreshPlugins()
    } else {
      showMessage(data.message, 'error')
    }
  } catch {
    showMessage('Failed to delete plugin folder', 'error')
  } finally {
    processing[plugin.folder_name] = false
  }
}

async function togglePlugin(plugin: InstalledPlugin) {
  processing[plugin.slug] = true

  try {
    const endpoint = plugin.is_enabled ? 'disable' : 'enable'
    const response = await fetch(`/admin/plugins/api/${plugin.slug}/${endpoint}`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })

    const data = await response.json()

    if (data.success) {
      showMessage(data.message, 'success')
      await refreshPlugins()
    } else {
      showMessage(data.message, 'error')
    }
  } catch {
    showMessage('Failed to toggle plugin', 'error')
  } finally {
    processing[plugin.slug] = false
  }
}

async function scanPlugin(plugin: InstalledPlugin) {
  processing[plugin.slug] = true

  try {
    const response = await fetch(`/admin/plugins/api/${plugin.slug}/scan`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })

    const data = await response.json()

    if (data.success) {
      showMessage(data.message, 'success')
      await refreshPlugins()
    } else {
      showMessage(data.message, 'error')
    }
  } catch {
    showMessage('Failed to scan plugin', 'error')
  } finally {
    processing[plugin.slug] = false
  }
}

async function uninstallPlugin(plugin: InstalledPlugin) {
  if (!confirm(`Are you sure you want to uninstall ${plugin.name}? This action cannot be undone.`)) {
    return
  }

  processing[plugin.slug] = true

  try {
    const response = await fetch(`/admin/plugins/api/${plugin.slug}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })

    const data = await response.json()

    if (data.success) {
      showMessage(data.message, 'success')
      await refreshPlugins()
    } else {
      showMessage(data.message, 'error')
    }
  } catch {
    showMessage('Failed to uninstall plugin', 'error')
  } finally {
    processing[plugin.slug] = false
  }
}

async function discoverPlugins() {
  discovering.value = true

  try {
    const response = await fetch('/admin/plugins/api/discover', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })

    const data = await response.json()

    if (data.success) {
      showMessage(data.message, 'success')
      await refreshPlugins()
    } else {
      showMessage(data.message, 'error')
    }
  } catch {
    showMessage('Failed to discover plugins', 'error')
  } finally {
    discovering.value = false
  }
}

async function refreshPlugins() {
  const response = await fetch('/admin/plugins/api')
  const data = await response.json()
  if (data.success) {
    installedPlugins.value = data.installed
    uninstalledPlugins.value = data.uninstalled
  }
}

function toggleScanResults(pluginSlug: string) {
  showScanResults[pluginSlug] = !showScanResults[pluginSlug]
}

function hasSecurityIssues(plugin: InstalledPlugin): boolean {
  return !!(plugin.scan_results?.threats?.length || plugin.scan_results?.warnings?.length)
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

function getPluginTypeBadge(pluginType: 'frontend' | 'admin' | 'universal') {
  const badges = {
    frontend: {
      label: 'Frontend',
      icon: 'material-symbols-light:web',
      class: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
    },
    admin: {
      label: 'Admin',
      icon: 'material-symbols-light:shield-person',
      class: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300'
    },
    universal: {
      label: 'Universal',
      icon: 'material-symbols-light:language',
      class: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    }
  }
  return badges[pluginType] || badges.universal
}
</script>
