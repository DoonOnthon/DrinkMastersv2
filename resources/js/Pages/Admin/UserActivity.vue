<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
dayjs.extend(relativeTime)

defineOptions({ layout: AdminLayout })

const props = defineProps({
  user: Object,
  activities: Object,
  filters: Object,
})

const selectedDate = ref(props.filters.date || '')
const selectedType = ref(props.filters.type || '')

function updateFilters(page = 1) {
  router.get(route('admin.users.activity', { user: props.user.id }), {
    date: selectedDate.value,
    type: selectedType.value,
    page,
  }, {
    preserveState: true,
    replace: true,
  })
}

function downloadCsv() {
  window.location = route('admin.users.activity.export', {
    user: props.user.id,
    date: selectedDate.value || undefined,
    type: selectedType.value || undefined,
  })
}
</script>

<template>
  <div class="p-6 animate-fadeIn">
    <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white">📝 Activity Log</h1>

    <div class="mb-4 text-sm text-gray-600 dark:text-gray-300">
      <p><strong>User:</strong> {{ user.name }} ({{ user.email }})</p>
      <p><strong>ID:</strong> {{ user.id }}</p>
    </div>

    <div class="flex items-center flex-wrap gap-4 mb-6">
      <input
        type="date"
        v-model="selectedDate"
        @change="updateFilters"
        class="border rounded px-3 py-2 text-sm dark:bg-gray-800 dark:text-white"
      />
      <select
        v-model="selectedType"
        @change="updateFilters"
        class="border rounded px-3 py-2 text-sm dark:bg-gray-800 dark:text-white"
      >
        <option value="">All Types</option>
        <option value="system">System</option>
        <option value="user">User</option>
      </select>
      <button @click="downloadCsv" class="px-4 py-2 bg-gray-800 text-white rounded text-sm hover:bg-gray-700">
        Download CSV
      </button>
    </div>

    <div class="bg-white dark:bg-gray-900 shadow rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
        <thead class="bg-gray-50 dark:bg-gray-800">
          <tr>
            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Type</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Description</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Time</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="activities.data.length === 0">
            <td colspan="3" class="px-4 py-4 text-center text-gray-400 dark:text-gray-500">No activity found.</td>
          </tr>
          <tr v-for="activity in activities.data" :key="activity.id" class="border-t dark:border-gray-800">
            <td class="px-4 py-2">
              <span
                class="px-2 py-1 rounded-full text-xs font-medium"
                :class="{
                  'bg-type-system/10 text-type-system': activity.type === 'system',
                  'bg-type-user/10 text-type-user': activity.type === 'user',
                }"
              >
                {{ activity.type }}
              </span>
            </td>
            <td class="px-4 py-2 text-gray-700 dark:text-gray-100">{{ activity.description }}</td>
            <td class="px-4 py-2 text-gray-500 dark:text-gray-400">
              {{ dayjs(activity.created_at).fromNow() }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex justify-between items-center text-sm text-gray-500 dark:text-gray-400">
      <span>
        Showing {{ activities.from }}–{{ activities.to }} of {{ activities.total }} activities
      </span>
      <div class="space-x-1">
        <button
          v-for="page in activities.last_page"
          :key="page"
          @click="updateFilters(page)"
          class="px-3 py-1 rounded border text-sm"
          :class="page === activities.current_page
            ? 'bg-gray-800 text-white'
            : 'bg-gray-200 text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600'"
        >
          {{ page }}
        </button>
      </div>
    </div>

    <div class="mt-6">
      <a href="#" onclick="history.back()" class="text-sm text-blue-600 hover:underline dark:text-blue-400">← Back</a>
    </div>
  </div>
</template>
