<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <div class="flex min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-md p-6 space-y-4">
      <h2 class="text-xl font-bold text-indigo-600 mb-6">Admin Panel</h2>
      <nav class="space-y-2">
        <Link href="/admin" class="admin-link">📊 Dashboard</Link>
        <Link href="/admin/users" class="admin-link">👥 Users</Link>
        <Link href="/admin/games" class="admin-link">🎮 Games</Link>
        <Link href="/admin/reports" class="admin-link">🚨 Reports</Link>
        <Link href="/" class="text-xs text-gray-400 hover:text-indigo-500 block mt-6">← Back to Site</Link>
      </nav>
    </aside>

    <!-- Main content -->
    <main class="flex-1 p-8 overflow-auto">
      <slot />
    </main>
  </div>
</template>

<style scoped>
.admin-link {
  @apply block px-4 py-2 rounded-lg text-gray-700 hover:bg-indigo-100 hover:text-indigo-700 transition;
}
</style>
