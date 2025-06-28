<template>
  <div>
    <h1 class="text-3xl font-bold mb-4">Dashboard</h1>
    <p class="text-gray-600 mb-8">Welcome back, Admin!</p>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
      <div class="stat-card">
        <h2 class="text-xl font-bold text-indigo-600">{{ users }}</h2>
        <p class="text-sm text-gray-500">Users</p>
      </div>
      <div class="stat-card">
        <h2 class="text-xl font-bold text-indigo-600">{{ games }}</h2>
        <p class="text-sm text-gray-500">Games</p>
      </div>
      <div class="stat-card">
        <h2 class="text-xl font-bold text-indigo-600">{{ xpTotal }}</h2>
        <p class="text-sm text-gray-500">Total XP Earned</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
defineOptions({ layout: AdminLayout })

defineProps({
  users: Number,
  games: Number,
  xpTotal: Number,
})
</script>

<style scoped>
.stat-card {
  @apply bg-white shadow p-6 rounded-xl text-center;
}
</style>
