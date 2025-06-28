<template>
  <div>
    <div v-if="drawnCards.length < cards.length" class="mb-4">
      <button
        @click="drawCard"
        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition"
      >
        🃏 Draw a Card
      </button>
    </div>

    <div v-if="drawnCards.length === cards.length" class="text-green-600 font-bold">
      🎉 All cards drawn!
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
      <div
        v-for="(card, index) in drawnCards"
        :key="index"
        class="bg-white p-4 rounded-lg shadow-md transition transform hover:scale-105"
      >
        <div class="text-xl font-bold">{{ card.label }} {{ card.suit }}</div>
        <div class="text-sm text-gray-600 mt-1">Action: {{ card.action_text }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({ game: Object })

const cards = ref([])
const drawnCards = ref([])

function shuffle(array) {
  return array
    .map((a) => ({ sort: Math.random(), value: a }))
    .sort((a, b) => a.sort - b.sort)
    .map((a) => a.value)
}

onMounted(async () => {
  const res = await axios.get(`/games/${props.game.id}/cards`)
  cards.value = shuffle(res.data)
})

function drawCard() {
  if (drawnCards.value.length < cards.value.length) {
    drawnCards.value.push(cards.value[drawnCards.value.length])
  }
}
</script>
