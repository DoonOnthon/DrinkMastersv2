<template>
  <div class="p-6">
    <h1 class="text-3xl font-bold mb-4">{{ game.title }}</h1>
    <p class="text-gray-600 mb-4">{{ game.description }}</p>

    <CardGame v-if="game.type === 'card'" :game="game" />
    <p v-else class="text-gray-400">This game type isn't supported yet.</p>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'
import CardGame from '@/Components/CardGame.vue'

const props = defineProps({ game: Object })
</script>
