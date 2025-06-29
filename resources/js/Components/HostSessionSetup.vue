<template>
  <div class="p-4 border rounded-lg shadow bg-white max-w-md mx-auto">
    <h2 class="text-xl font-bold mb-4">🎮 Host Game Setup</h2>

    <label class="block mb-2 text-gray-700">Choose Mode:</label>
    <div class="mb-4">
      <label class="inline-flex items-center space-x-2">
        <input type="radio" v-model="mode" value="host" />
        <span>Host-led (stream/presenter)</span>
      </label>
      <label class="inline-flex items-center space-x-2 ml-4">
        <input type="radio" v-model="mode" value="multiplayer" />
        <span>Multiplayer (each player interacts)</span>
      </label>
    </div>

    <button
      :disabled="!mode"
      @click="startSession"
      class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50"
    >
      🚀 Start Game
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'

const props = defineProps({ game: Object })
const mode = ref(null)

const startSession = async () => {
  await router.post(`/games/${props.game.id}/play-session`, {
    mode: mode.value,
  })
}
</script>
