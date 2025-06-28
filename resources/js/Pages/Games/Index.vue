<template>
  <div class="min-h-screen bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto">
      <div class="mb-8">
        <h1 class="text-4xl font-extrabold text-gray-800">🎲 DrinkMasters Games</h1>
        <p class="text-gray-600">Browse our collection of drinking games by type</p>
      </div>

      <div class="mb-6 flex flex-wrap gap-2">
        <button
          v-for="t in types"
          :key="t"
          @click="filterType = t"
          :class="[
            'px-4 py-2 rounded-full text-sm font-semibold transition',
            filterType === t
              ? 'bg-blue-600 text-white'
              : 'bg-white border border-blue-600 text-blue-600 hover:bg-blue-100'
          ]"
        >
          {{ t === 'all' ? 'All' : t.charAt(0).toUpperCase() + t.slice(1) }}
        </button>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="(game, index) in filteredGames"
          :key="game.id"
          class="bg-white p-6 rounded-2xl shadow-lg transform transition-all duration-500 opacity-0 animate-fade-in"
          :style="`animation-delay: ${index * 100}ms`"
        >
          <div class="flex items-center justify-between mb-2">
            <h2 class="text-xl font-bold text-gray-800">{{ game.title }}</h2>
            <span class="text-2xl">
              {{ icons[game.type] }}
            </span>
          </div>
          <p class="text-gray-500 text-sm mb-2 capitalize">{{ game.type }}</p>
          <p class="text-gray-600 text-sm">{{ game.description }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
defineOptions({ layout: AppLayout })

const props = defineProps({ games: Array })

import { ref, computed } from 'vue'

const filterType = ref('all')
const types = ['all', 'card', 'movie', 'video']
const icons = {
  card: '🃏',
  movie: '🎬',
  video: '🎮',
}

const filteredGames = computed(() => {
  const safeGames = props.games || []
  return filterType.value === 'all'
    ? safeGames
    : safeGames.filter((g) => g.type === filterType.value)
})
</script>


<style scoped>
@keyframes fade-in {
  0% {
    opacity: 0;
    transform: translateY(10px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.6s ease-out forwards;
}
</style>
