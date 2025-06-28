<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { usePage } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  user: Object,
  activities: Array,
})
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4 text-gray-800">📝 Activity Log</h1>

    <div class="mb-6 text-sm text-gray-600">
      <p><strong>User:</strong> {{ user.name }} ({{ user.email }})</p>
      <p><strong>ID:</strong> {{ user.id }}</p>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left font-semibold text-gray-700">Description</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-700">Timestamp</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="activities.length === 0">
            <td colspan="2" class="px-4 py-4 text-center text-gray-400">No activity found.</td>
          </tr>
          <tr v-for="activity in activities" :key="activity.id" class="border-t">
            <td class="px-4 py-2">{{ activity.description }}</td>
            <td class="px-4 py-2 text-gray-500">
              {{ new Date(activity.created_at).toLocaleString() }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-6">
      <a href="#" onclick="history.back()" class="text-sm text-blue-600 hover:underline">← Back</a>
    </div>
  </div>
</template>
