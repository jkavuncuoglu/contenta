<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { computed } from 'vue'

interface ConflictPost {
  id: number
  content: string
  scheduled_at: string
  source_type: string
  account: {
    platform: string
    platform_username: string
  }
}

interface Props {
  conflicts: ConflictPost[]
}

const props = defineProps<Props>()

const formattedConflicts = computed(() => {
  return props.conflicts.map((conflict) => {
    const scheduledDate = new Date(conflict.scheduled_at)
    return {
      ...conflict,
      formattedTime: scheduledDate.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
      }),
      contentPreview:
        conflict.content.length > 80 ? conflict.content.substring(0, 80) + '...' : conflict.content,
    }
  })
})

const conflictCount = computed(() => props.conflicts.length)
</script>

<template>
  <div class="rounded-lg border border-yellow-300 bg-yellow-50 p-4">
    <div class="flex items-start">
      <div class="flex-shrink-0">
        <Icon icon="mdi:alert" class="h-6 w-6 text-yellow-600" />
      </div>
      <div class="ml-3 flex-1">
        <h3 class="text-sm font-medium text-yellow-800">
          Scheduling Conflict{{ conflictCount > 1 ? 's' : '' }} Detected
        </h3>
        <div class="mt-2 text-sm text-yellow-700">
          <p class="mb-3">
            {{ conflictCount }} post{{ conflictCount > 1 ? 's are' : ' is' }} already scheduled within 15
            minutes of your selected time. Publishing multiple posts too close together may reduce
            engagement.
          </p>

          <div class="space-y-2">
            <div
              v-for="conflict in formattedConflicts"
              :key="conflict.id"
              class="rounded-md border border-yellow-200 bg-white p-3"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <div class="flex items-center gap-2">
                    <Icon
                      icon="material-symbols-light:schedule"
                      class="h-4 w-4 text-yellow-600"
                    />
                    <span class="font-medium text-yellow-900">{{ conflict.formattedTime }}</span>
                    <span
                      class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800"
                    >
                      {{
                        conflict.source_type === 'manual' ? 'Manual' : 'Auto-generated'
                      }}
                    </span>
                  </div>
                  <p class="mt-1 text-sm text-neutral-700">{{ conflict.contentPreview }}</p>
                </div>
                <a
                  :href="`/admin/social-media/posts/${conflict.id}/edit`"
                  class="ml-3 text-sm font-medium text-blue-600 hover:text-blue-700"
                  target="_blank"
                >
                  View
                </a>
              </div>
            </div>
          </div>

          <div class="mt-3 rounded-md bg-yellow-100 p-3">
            <p class="text-sm font-medium text-yellow-800">
              <Icon icon="mdi:lightbulb-outline" class="mb-0.5 mr-1 inline h-4 w-4" />
              Recommendation:
            </p>
            <ul class="ml-5 mt-1 list-disc space-y-1 text-sm text-yellow-700">
              <li>Consider spacing posts at least 30 minutes apart</li>
              <li>Adjust your scheduled time to avoid overlaps</li>
              <li>You can still proceed if you want to post at this time</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
