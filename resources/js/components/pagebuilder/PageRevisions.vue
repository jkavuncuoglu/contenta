<template>
  <div class="page-revisions w-full">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Loading revisions...</p>
      </div>
    </div>

    <!-- Revisions Content -->
    <div v-else-if="revisions.length > 0" class="space-y-6 w-full">
      <!-- Revision Timeline Slider -->
      <div class="revision-slider bg-gray-50 dark:bg-gray-900 rounded-lg p-6 border border-gray-200 dark:border-gray-700 w-full">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Revision Timeline</h4>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              {{ displayedRevisions.length }} {{ displayedRevisions.length === 1 ? 'revision' : 'revisions' }}
              <span v-if="!showAllRevisions && revisions.length > 10"> (showing oldest to newest)</span>
            </p>
          </div>
          <button
            v-if="revisions.length > 10"
            @click="toggleShowAll"
            class="text-xs px-3 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
          >
            {{ showAllRevisions ? 'Show Latest 10' : `Show All ${revisions.length}` }}
          </button>
        </div>

        <!-- Slider -->
        <div class="relative py-4">
          <input
            type="range"
            v-model="selectedRevisionIndex"
            :min="0"
            :max="displayedRevisions.length - 1"
            class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer slider"
            @input="onSliderChange"
          />
          <!-- Revision Points -->
          <div class="flex justify-between mt-2 px-1">
            <div
              v-for="(revision, index) in displayedRevisions"
              :key="revision.id"
              class="flex flex-col items-center"
              :style="{ width: `${100 / displayedRevisions.length}%` }"
            >
              <div
                class="w-3 h-3 rounded-full transition-all"
                :class="index === selectedRevisionIndex
                  ? 'bg-blue-600 ring-4 ring-blue-200 dark:ring-blue-900 scale-125'
                  : 'bg-gray-400 dark:bg-gray-600'"
              ></div>
              <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">#{{ revision.revision_number }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Revision Preview -->
      <div v-if="selectedRevisionData" class="revision-preview bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden w-full">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center space-x-3 mb-3">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Preview: Revision #{{ selectedRevision?.revision_number }}</h4>
              </div>
              <div v-if="selectedRevision" class="space-y-2">
                <div class="flex items-center space-x-3">
                  <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-semibold text-blue-600 dark:text-blue-300">#{{ selectedRevision.revision_number }}</span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <h5 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                      {{ selectedRevision.title }}
                    </h5>
                    <div class="flex items-center space-x-2">
                      <span class="text-xs text-gray-500 dark:text-gray-400">
                        by {{ selectedRevision.user }}
                      </span>
                      <span class="text-gray-300 dark:text-gray-600">•</span>
                      <span class="text-xs text-gray-500 dark:text-gray-400" :title="selectedRevision.created_at">
                        {{ selectedRevision.created_at_human }}
                      </span>
                    </div>
                  </div>
                </div>
                <p v-if="selectedRevision.reason" class="text-xs text-gray-600 dark:text-gray-400 italic ml-11">
                  {{ selectedRevision.reason }}
                </p>
              </div>
            </div>
            <button
              v-if="selectedRevision"
              @click="restoreRevision(selectedRevision)"
              :disabled="restoring === selectedRevision.id"
              class="ml-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex-shrink-0"
            >
              <svg v-if="restoring !== selectedRevision.id" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              <div v-else class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
              {{ restoring === selectedRevision.id ? 'Restoring...' : 'Restore This Version' }}
            </button>
          </div>
        </div>
        <div class="p-6 bg-gray-50 dark:bg-gray-900 max-h-96 overflow-auto">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="transform scale-90 origin-top">
              <BlockRenderer
                v-for="section in selectedRevisionData.data?.sections || []"
                :key="section.id"
                :section="section"
                :available-blocks="availableBlocks"
                :preview-mode="true"
              />
              <div v-if="!selectedRevisionData.data?.sections?.length" class="p-12 text-center text-gray-500 dark:text-gray-400">
                <p class="text-sm">This revision has no content blocks</p>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No revisions yet</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Revisions will appear here as you save changes to the page.
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import BlockRenderer from './BlockRenderer.vue'

interface Revision {
  id: number
  revision_number: number
  title: string
  user: string
  reason?: string
  created_at: string
  created_at_human: string
}

interface RevisionData {
  id: number
  revision_number: number
  title: string
  slug: string
  data: {
    sections: any[]
  }
  status: string
  meta_title?: string
  meta_description?: string
  meta_keywords?: string
  schema_data?: any
}

interface Props {
  pageId: number
  availableBlocks: any[]
}

const props = defineProps<Props>()

const revisions = ref<Revision[]>([])
const loading = ref(true)
const restoring = ref<number | null>(null)
const showAllRevisions = ref(false)
const selectedRevisionIndex = ref(0)
const selectedRevisionData = ref<RevisionData | null>(null)
const loadingRevisionData = ref(false)

// Computed property for displayed revisions (limited to 10 unless "Show All" is clicked)
// Reversed to show oldest first (left) to newest last (right)
const displayedRevisions = computed(() => {
  let revs = []
  if (showAllRevisions.value || revisions.value.length <= 10) {
    revs = revisions.value
  } else {
    revs = revisions.value.slice(0, 10)
  }
  // Reverse to show oldest (left) to newest (right)
  return [...revs].reverse()
})

// Computed property for selected revision
const selectedRevision = computed(() => {
  return displayedRevisions.value[selectedRevisionIndex.value] || null
})

const fetchRevisions = async () => {
  try {
    loading.value = true
    const response = await fetch(`/admin/page-builder/api/pages/${props.pageId}/revisions`, {
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })

    if (response.ok) {
      revisions.value = await response.json()
      // Select the last (most recent) revision by default - now on the right side
      if (revisions.value.length > 0) {
        selectedRevisionIndex.value = displayedRevisions.value.length - 1
        await fetchRevisionData(displayedRevisions.value[displayedRevisions.value.length - 1].id)
      }
    } else {
      console.error('Failed to fetch revisions')
    }
  } catch (error) {
    console.error('Error fetching revisions:', error)
  } finally {
    loading.value = false
  }
}

const fetchRevisionData = async (revisionId: number) => {
  try {
    loadingRevisionData.value = true
    const response = await fetch(`/admin/page-builder/api/pages/${props.pageId}/revisions/${revisionId}`, {
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })

    if (response.ok) {
      selectedRevisionData.value = await response.json()
    } else {
      console.error('Failed to fetch revision data')
      selectedRevisionData.value = null
    }
  } catch (error) {
    console.error('Error fetching revision data:', error)
    selectedRevisionData.value = null
  } finally {
    loadingRevisionData.value = false
  }
}

const onSliderChange = () => {
  const revision = selectedRevision.value
  if (revision) {
    fetchRevisionData(revision.id)
  }
}

const toggleShowAll = () => {
  showAllRevisions.value = !showAllRevisions.value
  // If we're hiding revisions, make sure the selected index is within bounds
  if (!showAllRevisions.value && selectedRevisionIndex.value >= 10) {
    // Select the last item (newest, rightmost)
    selectedRevisionIndex.value = displayedRevisions.value.length - 1
    const lastRevision = displayedRevisions.value[displayedRevisions.value.length - 1]
    if (lastRevision) {
      fetchRevisionData(lastRevision.id)
    }
  }
}

const restoreRevision = async (revision: Revision) => {
  if (!confirm(`Are you sure you want to restore to revision #${revision.revision_number}? This will create a new revision with the restored content.`)) {
    return
  }

  try {
    restoring.value = revision.id
    const response = await fetch(`/admin/page-builder/api/pages/${props.pageId}/revisions/${revision.id}/restore`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })

    if (response.ok) {
      // Reload the page to show the restored content
      router.reload()
    } else {
      const error = await response.json()
      alert('Failed to restore revision: ' + (error.error || 'Unknown error'))
    }
  } catch (error) {
    console.error('Error restoring revision:', error)
    alert('Failed to restore revision')
  } finally {
    restoring.value = null
  }
}

onMounted(() => {
  fetchRevisions()
})

// Expose method to refresh revisions from parent
defineExpose({
  fetchRevisions
})
</script>

<style scoped>
.revision-item {
  transition: all 0.2s ease;
}

.revision-item:hover {
  transform: translateY(-1px);
}

/* Custom slider styling */
.slider {
  -webkit-appearance: none;
  appearance: none;
}

.slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #2563eb;
  cursor: pointer;
  border: 3px solid white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.slider::-moz-range-thumb {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #2563eb;
  cursor: pointer;
  border: 3px solid white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.slider::-webkit-slider-thumb:hover {
  background: #1d4ed8;
  transform: scale(1.1);
}

.slider::-moz-range-thumb:hover {
  background: #1d4ed8;
  transform: scale(1.1);
}
</style>
