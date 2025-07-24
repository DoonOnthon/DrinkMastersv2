<template>
  <div class="p-6 max-w-xl mx-auto">
    <h1 class="text-3xl font-bold mb-4">{{ game.title }}</h1>
    <p class="mb-6 text-gray-600">{{ game.description }}</p>

    <div class="mb-4">
      <h2 class="font-semibold text-lg mb-2">How would you like to play?</h2>

      <form @submit.prevent="hostGame">
        <label class="block mb-2 text-sm text-gray-700">Select Mode:</label>

        <label class="inline-flex items-center mr-4">
          <input type="radio" value="host" v-model="mode" class="mr-1" />
          Host-led (stream or control all cards)
        </label>

        <label class="inline-flex items-center">
          <input type="radio" value="multiplayer" v-model="mode" class="mr-1" />
          Multiplayer (each player picks their card)
        </label>

        <button
          type="submit"
          class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
          :disabled="!mode"
        >
          🚀 Start Game
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({ game: Object })
const mode = ref(null)

function hostGame() {
  router.post(`/games/${props.game.id}/play-session`, { mode: mode.value })
}
</script>
