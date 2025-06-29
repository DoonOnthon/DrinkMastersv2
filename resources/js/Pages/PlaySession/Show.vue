<template>
  <div v-if="session?.game" class="p-6 max-w-4xl mx-auto space-y-6">
    <!-- 🎮 Game Info -->
    <div class="text-center">
      <h1 class="text-3xl font-extrabold mb-1">🎮 {{ session.game.title }}</h1>
      <p class="text-sm text-gray-500">Session Code: <strong>{{ session.code }}</strong></p>
    </div>

    <!-- 🎯 Game Mode -->
    <div class="text-center text-blue-700 font-medium">
      Mode:
      <span class="font-bold">
        <template v-if="session.state?.mode === 'host'">Host-led</template>
        <template v-else-if="session.state?.mode === 'multiplayer'">Multiplayer</template>
        <template v-else>Unknown</template>
      </span>
    </div>

    <!-- 👥 Players -->
    <div>
      <h2 class="text-lg font-semibold mb-2">👥 Players:</h2>
      <ul class="list-disc pl-6 text-gray-800 space-y-1">
        <li v-for="p in session.players" :key="p.id">{{ p.name }}</li>
      </ul>
    </div>

    <!-- 🙋‍♂️ Join -->
    <div v-if="!hasJoined" class="flex items-center space-x-2">
      <input
        v-model="playerName"
        placeholder="Your name"
        class="border border-gray-300 px-3 py-2 rounded shadow-sm focus:outline-none focus:ring focus:border-blue-400"
      />
      <button
        @click="join"
        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded"
      >
        ✅ Join
      </button>
    </div>

    <!-- 🔄 Turn -->
    <div class="text-lg text-blue-800">
      🎯 It’s <strong>{{ currentPlayer?.name }}</strong>’s turn.
    </div>
    <div v-if="isMyTurn" class="text-green-600 font-semibold">✅ It’s your turn!</div>

    <!-- 🃏 Draw -->
    <div v-if="canDraw">
      <button
        @click="drawCard"
        class="bg-purple-700 hover:bg-purple-800 text-white px-4 py-2 rounded-lg transition"
      >
        🃏 Draw a Card
      </button>
    </div>

    <!-- 🧩 Drawn Cards -->
    <div v-if="drawnCards.length" class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div
        v-for="(card, index) in drawnCards"
        :key="index"
        class="bg-white p-4 rounded-lg shadow text-center"
      >
        <div class="text-xl font-bold">{{ card.label }} {{ card.suit }}</div>
        <div class="text-sm text-gray-600 mt-1">Action: {{ card.action_text }}</div>
      </div>
    </div>
  </div>

  <div v-else class="p-6 text-center text-gray-500">
    Loading session...
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({ session: Object })

const session = ref(null)
const cards = ref([])
const playerName = ref('')
const hasJoined = ref(false)
const turn = ref(0)

const fetchSession = async () => {
  const { data } = await axios.get(`/api/play/${props.session.code}`)
  session.value = data
  turn.value = data.state?.turn ?? 0
}

const fetchCards = async () => {
  const res = await axios.get(`/games/${props.session.game.id}/cards`)
  const fullDeck = res.data

  const deckOrder = session.value.state?.deck || []
  cards.value = deckOrder.map(id => fullDeck.find(c => c.id === id))
}


const join = async () => {
  if (!playerName.value.trim()) return

  await axios.post(`/play/${props.session.code}/join`, {
    name: playerName.value,
  })

  localStorage.setItem(`name:${props.session.code}`, playerName.value)
  hasJoined.value = true
  await fetchSession()
}

async function drawCard() {
  if (!canDraw.value) return

  try {
    await axios.post(`/play/${session.value.code}/draw`)
    await axios.post(`/play/${session.value.code}/next-turn`)
    await fetchSession()
  } catch (e) {
    console.error('❌ Draw failed:', e.response?.data || e.message)
    alert('Something went wrong: ' + (e.response?.data?.error || e.message))
  }
}


onMounted(async () => {
  playerName.value = localStorage.getItem(`name:${props.session.code}`) || ''

  await fetchSession()
  await fetchCards()

  hasJoined.value = session.value.players.some(p => p.name === playerName.value)

  setInterval(fetchSession, 3000)
})

const currentPlayer = computed(() =>
  session.value?.players?.find(p => p.turn_order === turn.value)
)

const myName = computed(() =>
  playerName.value || localStorage.getItem(`name:${session.value?.code}`) || ''
)

const isMyTurn = computed(() =>
  currentPlayer.value?.name === myName.value
)

const canDraw = computed(() => {
  const mode = session.value?.state?.mode
  return mode === 'host' || (mode === 'multiplayer' && isMyTurn.value)
})

const drawnCards = computed(() => {
  const drawn = session.value?.state?.drawn ?? []
  return drawn
    .map(i => cards.value[i])
    .filter(card => card !== undefined) // ✅ Prevent render crash
})

</script>
