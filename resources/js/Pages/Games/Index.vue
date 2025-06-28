<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Games</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <transition-group name="fade" tag="div">
        <div
          v-for="game in games"
          :key="game.id"
          class="bg-white p-4 rounded-2xl shadow hover:scale-105 transition-transform duration-300"
        >
          <h2 class="text-lg font-semibold">{{ game.title }}</h2>
          <p class="text-sm text-gray-600 capitalize">{{ game.type }}</p>
        </div>
      </transition-group>
    </div>
  </div>
</template>

<script setup>
defineProps({ games: Array })
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>
