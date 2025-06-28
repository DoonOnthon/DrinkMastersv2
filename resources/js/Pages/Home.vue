<template>
  <div class="relative min-h-screen bg-gradient-to-br from-purple-100 to-pink-100 flex items-center justify-center px-6 py-12 overflow-hidden">

    <!-- Animated background blobs -->
    <div class="absolute inset-0 overflow-hidden z-0">
      <div class="absolute w-72 h-72 bg-purple-300 opacity-30 rounded-full blur-3xl top-0 left-0 animate-blob animation-delay-2000"></div>
      <div class="absolute w-80 h-80 bg-pink-300 opacity-30 rounded-full blur-3xl top-1/2 left-1/2 animate-blob"></div>
    </div>

    <!-- Foreground content -->
    <div class="z-10 text-center">
      <div class="text-7xl mb-4 animate-bounce">🍻</div>
      <h1 class="text-5xl font-extrabold text-gray-800 mb-2">DrinkMasters</h1>
      <p class="text-lg text-gray-700 mb-8">The ultimate hub for drinking games, XP, and party chaos.</p>

      <div class="flex justify-center gap-4 flex-wrap">
        <Link
          href="/games"
          class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-full transition-all shadow-md"
        >
          🎲 Explore Games
        </Link>

        <Link
          :href="`/games/${randomGameId}`"
          class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 px-6 rounded-full transition-all shadow-md"
        >
          🔀 Random Game
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({
  layout: AppLayout
})

const gameIds = [1, 2, 3] // Replace with real IDs later
const randomGameId = computed(() => {
  return gameIds[Math.floor(Math.random() * gameIds.length)]
})
</script>


<style scoped>
@keyframes blob {
  0%, 100% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -20px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 30px) scale(0.9);
  }
}

.animate-blob {
  animation: blob 8s infinite ease-in-out;
}

.animation-delay-2000 {
  animation-delay: 2s;
}
</style>
