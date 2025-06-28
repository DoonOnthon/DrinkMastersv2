<template>
  <div>
    <h1 class="text-3xl font-bold mb-4">📊 Admin Dashboard</h1>
    <p class="text-gray-600 mb-8">Welcome back, Admin!</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="stat-card">
        <h2 class="text-2xl font-bold text-indigo-600">{{ users }}</h2>
        <p class="text-sm text-gray-500">Total Users</p>
      </div>
      <div class="stat-card">
        <h2 class="text-2xl font-bold text-green-600">{{ games }}</h2>
        <p class="text-sm text-gray-500">Games Played</p>
      </div>
      <div class="stat-card">
        <h2 class="text-2xl font-bold text-yellow-600">{{ xpTotal }}</h2>
        <p class="text-sm text-gray-500">Total XP Earned</p>
      </div>
      <div class="stat-card">
        <h2 class="text-2xl font-bold text-red-600">{{ activePlayers }}</h2>
        <p class="text-sm text-gray-500">Active Players</p>
      </div>
    </div>

    <div class="bg-white dark:bg-gray-900 shadow rounded-xl p-6">
      <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">📈 XP Earned Over Time</h3>
      <LineChart :data="xpData" />
    </div>
  </div>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import LineChart from '@/Components/Charts/LineChart.vue'

defineOptions({ layout: AdminLayout })

defineProps({
  users: Number,
  games: Number,
  xpTotal: Number,
  activePlayers: Number,
  xpData: {
    type: Array,
    default: () => [],
  }
})
</script>

<style scoped>
.stat-card {
  @apply bg-white dark:bg-gray-800 text-center p-6 rounded-xl shadow transition-transform hover:scale-105;
}
</style>
