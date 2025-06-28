<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  users: Object,
  filters: Object,
})

const currentUserId = usePage().props.auth.user.id
const search = ref(props.filters.search || '')

function updateSearch() {
  router.get(route('admin.users.index'), { search: search.value }, { preserveState: true, replace: true })
}

function goTo(url) {
  router.visit(url, { preserveState: true })
}

function toggleRole(userId, role) {
  router.post(route('admin.users.toggleRole', { user: userId }), { role }, {
    preserveScroll: true,
  })
}

function toggleSuspension(userId) {
  router.post(route('admin.users.toggleSuspend', { user: userId }), {}, {
    preserveScroll: true,
  })
}

const showSuspendModal = ref(false)
const userToSuspend = ref(null)

function confirmSuspend(user) {
  userToSuspend.value = user
  showSuspendModal.value = true
}

function performSuspend() {
  toggleSuspension(userToSuspend.value.id)
  showSuspendModal.value = false
}
</script>

<template>
  <div class="p-6">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">👥 Users</h1>

    <div class="mb-4">
      <input
        type="text"
        v-model="search"
        @input="updateSearch"
        placeholder="Search by name or email..."
        class="w-full sm:w-1/3 px-4 py-2 border rounded-lg shadow-sm focus:ring-indigo-500"
      />
    </div>

    <div class="overflow-x-auto bg-white rounded-xl shadow">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-4 py-3 text-left font-semibold text-gray-700">Name</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-700">Email</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-700">XP</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-700">Roles</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-700">Reports</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-700">Joined</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-700">Last Login</th>
            <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users.data" :key="user.id" class="border-t">
            <td class="px-4 py-2 font-medium">{{ user.name }}</td>
            <td class="px-4 py-2 text-gray-600">{{ user.email }}</td>
            <td class="px-4 py-2 font-mono">{{ user.xp }}</td>
            <td class="px-4 py-2 space-x-1">
              <span
                v-if="user.is_admin"
                class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full"
              >Admin</span>
              <span
                v-if="user.is_moderator"
                class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full"
              >Moderator</span>
              <span
                v-if="user.is_manager"
                class="bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded-full"
              >Manager</span>
            </td>
            <td class="px-4 py-2 text-center">
              <span :class="user.reports_count > 0 ? 'text-red-500 font-bold' : 'text-gray-400'">
                {{ user.reports_count }}
              </span>
            </td>
            <td class="px-4 py-2">
              <span
                class="inline-block px-2 py-1 rounded-full text-xs font-semibold"
                :class="user.is_suspended ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-500'"
              >
                {{ user.is_suspended ? 'Suspended' : 'Active' }}
              </span>
            </td>
            <td class="px-4 py-2 text-gray-500">
              {{ new Date(user.created_at).toLocaleDateString() }}
            </td>
            <td class="px-4 py-2 text-gray-500">
              {{ user.last_login_at ? new Date(user.last_login_at).toLocaleString() : 'Never' }}
            </td>
            <td class="px-4 py-2 space-y-1">
              <button
                class="block text-xs text-indigo-600 hover:underline"
                v-if="user.id !== currentUserId"
                @click="toggleRole(user.id, 'admin')"
              >
                {{ user.is_admin ? 'Remove Admin' : 'Make Admin' }}
              </button>
              <button
                class="block text-xs text-blue-600 hover:underline"
                @click="toggleRole(user.id, 'moderator')"
              >
                {{ user.is_moderator ? 'Remove Mod' : 'Make Mod' }}
              </button>
              <button
                class="block text-xs text-yellow-600 hover:underline"
                @click="toggleRole(user.id, 'manager')"
              >
                {{ user.is_manager ? 'Remove Manager' : 'Make Manager' }}
              </button>
              <button
                class="block text-xs text-red-600 hover:underline"
                @click="confirmSuspend(user)"
              >
                {{ user.is_suspended ? 'Unsuspend' : 'Suspend' }}
              </button>
              <button
                class="block text-xs text-gray-600 hover:underline"
                @click="goTo(route('admin.users.activity', { user: user.id }))"
              >
                View Activity
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-6 flex justify-between items-center">
      <p class="text-sm text-gray-500">
        Showing {{ users.data.length }} of {{ users.total }} users
      </p>
      <div class="flex space-x-2">
        <button
          v-if="users.prev_page_url"
          @click="goTo(users.prev_page_url)"
          class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-sm"
        >
          Previous
        </button>
        <button
          v-if="users.next_page_url"
          @click="goTo(users.next_page_url)"
          class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-sm"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Suspend Confirmation Modal -->
    <div
      v-if="showSuspendModal"
      class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Confirm Suspension</h2>
        <p class="text-sm text-gray-600 mb-6">
          Are you sure you want to {{ userToSuspend?.is_suspended ? 'unsuspend' : 'suspend' }} {{ userToSuspend?.name }}?
        </p>
        <div class="flex justify-end space-x-2">
          <button
            @click="showSuspendModal = false"
            class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-sm"
          >
            Cancel
          </button>
          <button
            @click="performSuspend"
            class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700 text-sm"
          >
            Yes, Confirm
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
