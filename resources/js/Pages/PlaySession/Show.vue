<template>
    <component :is="layout" :auth="auth">
        <div v-if="session?.game" class="min-h-screen bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900 text-white">
            <!-- 🎮 Game Header -->
            <div class="text-center pt-8 pb-6">
                <h1 class="text-5xl font-black mb-2 bg-gradient-to-r from-yellow-400 to-pink-500 bg-clip-text text-transparent">
                    🎮 {{ session.game.title }}
                </h1>
                <div class="inline-block bg-white/10 backdrop-blur-sm rounded-full px-6 py-2">
                    <span class="text-lg font-mono">{{ session.code }}</span>
                </div>
                <div class="mt-3">
                    <span class="inline-block px-4 py-1 rounded-full text-sm font-bold"
                          :class="session.state?.mode === 'host' ? 'bg-orange-500' : 'bg-blue-500'">
                        {{ session.state?.mode === 'host' ? '👑 Host-led' : '🎯 Multiplayer' }}
                    </span>
                </div>
            </div>

            <!-- 🚨 XP Incentive Banner -->
            <div v-if="session.state?.mode === 'multiplayer' && !auth?.user"
                 class="mx-6 mb-6 bg-gradient-to-r from-yellow-400 to-orange-500 text-black p-4 rounded-2xl text-center font-bold shadow-2xl animate-pulse">
                <div class="text-2xl mb-2">⚡ EARN XP & CLIMB THE LEADERBOARD! ⚡</div>
                <a href="/login" class="inline-block bg-black text-yellow-400 px-6 py-2 rounded-full hover:scale-105 transition-transform">
                    🔥 Login Now 🔥
                </a>
            </div>

            <!-- 🎯 Turn Indicator (Enhanced) - NOW FOR BOTH MODES -->
            <div v-if="session.players?.length > 0" class="px-6 mb-6">
                <div class="text-center mb-4">
                    <div class="inline-block bg-gradient-to-r from-blue-500 to-purple-600 px-8 py-3 rounded-full shadow-2xl">
                        <span class="text-2xl font-black">🎯 Turn {{ turn + 1 }} of {{ session.players.length }}</span>
                    </div>
                </div>

                <!-- Turn Circle Visualization -->
                <div class="flex justify-center">
                    <div class="turn-circle">
                        <div v-for="(player, index) in orderedPlayers" :key="player.id"
                             class="turn-player"
                             :class="{
                                'active': player.turn_order === turn,
                                'my-turn': player.user_id === auth?.user?.id && player.turn_order === turn,
                                'host-mode': session.state?.mode === 'host'
                             }"
                             :style="getPlayerPosition(index, orderedPlayers.length)">
                            <div class="turn-avatar">
                                {{ player.name[0].toUpperCase() }}
                            </div>
                            <div class="turn-name">{{ player.name }}</div>
                            <div v-if="player.turn_order === turn" class="turn-indicator">
                                <div class="turn-pulse"></div>
                                <span class="text-xs font-bold">
                                    {{ session.state?.mode === 'host' ? 'FOCUS' : 'TURN' }}
                                </span>
                            </div>
                        </div>

                        <!-- Center Turn Info -->
                        <div class="turn-center">
                            <div class="text-3xl mb-2">🎯</div>
                            <div class="font-bold">{{ currentPlayer?.name || 'Unknown' }}</div>
                            <div class="text-sm opacity-75">
                                {{ session.state?.mode === 'host' ? 'In focus' : "It's your turn!" }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Host Controls -->
                <div v-if="session.state?.mode === 'host' && isHost" class="text-center mt-4">
                    <div class="inline-block bg-gradient-to-r from-orange-400 to-red-500 text-black px-6 py-2 rounded-full font-bold text-lg">
                        👑 You control the game for {{ currentPlayer?.name || 'Unknown' }}
                    </div>
                </div>

                <!-- Player Turn Indicator -->
                <div v-if="session.state?.mode === 'multiplayer' && isMyTurn" class="text-center mt-4">
                    <div class="inline-block bg-gradient-to-r from-green-400 to-emerald-500 text-black px-6 py-2 rounded-full font-bold text-lg animate-bounce">
                        ✨ YOUR TURN! Draw a card! ✨
                    </div>
                </div>
            </div>

            <!-- 🙋 Player Management -->
            <div v-if="session.state?.mode === 'host' && isHost" class="px-6 mb-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4">
                    <h4 class="font-bold mb-3">➕ Add Player</h4>
                    <div class="flex gap-3">
                        <input v-model="playerName" placeholder="Player name"
                               class="flex-1 bg-white/20 border border-white/30 px-4 py-2 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-yellow-400" />
                        <button @click="addPlayer" :disabled="!playerName.trim()"
                                class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-2 rounded-xl font-bold hover:scale-105 transition-transform disabled:opacity-50 disabled:cursor-not-allowed">
                            ➕ Add
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="session.state?.mode === 'multiplayer' && !hasJoined && auth?.user" class="px-6 mb-6">
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-4">
                    <h4 class="font-bold mb-3 text-xl">🎮 Join the Game!</h4>
                    <div class="flex gap-3">
                        <input v-model="playerName" placeholder="Your display name"
                               class="flex-1 bg-white/20 border border-white/30 px-4 py-2 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-yellow-400" />
                        <button @click="join" :disabled="!playerName.trim()"
                                class="bg-gradient-to-r from-yellow-500 to-orange-600 px-8 py-2 rounded-xl font-bold hover:scale-105 transition-transform disabled:opacity-50 disabled:cursor-not-allowed">
                            🚀 JOIN
                        </button>
                    </div>
                </div>
            </div>

            <!-- 🃏 Card Drawing Area -->
            <div class="px-6 mb-8">
                <div class="flex justify-center items-center gap-6">
                    <!-- Draw Pile -->
                    <div class="draw-pile-container">
                        <div v-if="canDraw && !justDrewCard" @click="drawCard" class="draw-pile clickable">
                            <div class="card-stack card-3"></div>
                            <div class="card-stack card-2"></div>
                            <div class="card-stack card-1">
                                <div class="card-content">
                                    <div class="text-4xl mb-2">🃏</div>
                                    <div class="text-sm font-bold">DRAW CARD</div>
                                    <div class="text-xs opacity-75">{{ cardsRemaining }} left</div>
                                </div>
                            </div>
                            <div class="draw-glow"></div>
                        </div>

                        <div v-else-if="justDrewCard" class="draw-pile">
                            <div class="card-stack card-1 drawing">
                                <div class="card-content">
                                    <div class="text-2xl">✨</div>
                                    <div class="text-sm">Drawing...</div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="draw-pile disabled">
                            <div class="card-stack card-1">
                                <div class="card-content">
                                    <div class="text-2xl opacity-50">🚫</div>
                                    <div class="text-xs opacity-75">
                                        <template v-if="session.state?.mode === 'host' && !isHost">Host only</template>
                                        <template v-else-if="session.state?.mode === 'multiplayer' && !isMyTurn">Wait your turn</template>
                                        <template v-else-if="!auth?.user">Login required</template>
                                        <template v-else>Cannot draw</template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Optional: Keep manual button as backup/emergency use -->
                    <div v-if="session.state?.mode === 'host' && isHost && session.players?.length > 1 && !justDrewCard"
                         class="flex flex-col items-center">
                        <button @click="advanceTurn"
                                class="bg-gradient-to-r from-gray-500 to-gray-600 text-white px-4 py-2 rounded-lg font-bold hover:scale-105 transition-transform shadow-lg opacity-75">
                            <div class="text-sm">🔄</div>
                            <div class="text-xs">Skip Turn</div>
                        </button>
                        <div class="text-xs mt-1 opacity-50">Emergency skip</div>
                    </div>
                </div>
            </div>

            <!-- 🎉 Current Card Display -->
            <div v-if="justDrewCard && lastDrawnCard" class="px-6 mb-8">
                <div class="text-center mb-4">
                    <h3 class="text-3xl font-bold mb-2">🎉 Card Drawn!</h3>
                    <p class="text-lg opacity-90">Drawn by: <strong>{{ lastDrawnBy }}</strong></p>
                </div>

                <div class="flex justify-center">
                    <div class="drawn-card-container" @click="!flippedCurrent && (flippedCurrent = true)">
                        <div class="drawn-card" :class="{ 'flipped': flippedCurrent }">
                            <!-- Card Back -->
                            <div class="card-face card-back">
                                <div class="mystery-card">
                                    <div class="mystery-pattern"></div>
                                    <div class="mystery-content">
                                        <div class="text-6xl mb-4">🎭</div>
                                        <div class="text-xl font-bold">Mystery Card</div>
                                        <div class="text-sm opacity-75">Tap to reveal</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Front -->
                            <div class="card-face card-front">
                                <div class="revealed-card">
                                    <div class="card-header">
                                        <div class="card-suit">{{ lastDrawnCard.suit }}</div>
                                        <div class="card-label">{{ lastDrawnCard.label }}</div>
                                    </div>
                                    <div class="card-action">
                                        {{ lastDrawnCard.action_text }}
                                    </div>
                                    <div class="card-sparkles">✨</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="flippedCurrent" class="text-center mt-6">
                    <button @click="finalizeCard" class="finalize-button">
                        <span class="text-2xl">🍻</span>
                        <span class="ml-2">Done Drinking! Next Turn</span>
                    </button>
                </div>
            </div>

            <!-- 🧩 Card History -->
            <div v-if="drawHistory.length > 0" class="px-6 pb-8">
                <h3 class="text-2xl font-bold text-center mb-6">📚 Card History</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <transition-group name="history-card">
                        <div v-for="(entry, index) in drawHistory" :key="`history-${index}`"
                             class="history-card">
                            <div class="history-card-content">
                                <div class="history-header">
                                    <span class="history-suit">{{ entry.card.suit }}</span>
                                    <span class="history-label">{{ entry.card.label }}</span>
                                </div>
                                <div class="history-player">{{ entry.playerName }}</div>
                                <div class="history-action">{{ entry.card.action_text }}</div>
                            </div>
                        </div>
                    </transition-group>
                </div>
            </div>

            <!-- Add after game header -->
            <div v-if="session.completed_at" class="mx-6 mb-6 bg-gradient-to-r from-green-600 to-emerald-700 text-white p-4 rounded-2xl text-center">
                <div class="text-2xl mb-2">🎉 Game Completed! 🎉</div>
                <div class="text-sm opacity-90">Session will be automatically cleaned up in 1 hour</div>
                <div v-if="isHost" class="mt-3">
                    <button @click="endSession" class="bg-white text-green-600 px-4 py-2 rounded-full font-bold hover:scale-105 transition-transform">
                        End Session Now
                    </button>
                </div>
            </div>

            <!-- Add deck finished notification -->
            <div v-if="isDeckFinished && !session.completed_at" class="mx-6 mb-6 bg-gradient-to-r from-purple-600 to-pink-700 text-white p-4 rounded-2xl text-center">
                <div class="text-2xl mb-2">🃏 All Cards Drawn! 🃏</div>
                <div class="text-sm opacity-90">The deck is complete!</div>
                <div v-if="isHost" class="mt-3">
                    <button @click="endSession" class="bg-white text-purple-600 px-4 py-2 rounded-full font-bold hover:scale-105 transition-transform">
                        🏁 End Game
                    </button>
                </div>
            </div>

        </div>

        <div v-else class="min-h-screen bg-gradient-to-br from-purple-900 to-indigo-900 flex items-center justify-center">
            <div class="text-center text-white">
                <div class="text-6xl mb-4 animate-spin">🎮</div>
                <div class="text-2xl font-bold">Loading session...</div>
            </div>
        </div>

        <!-- Debug section -->
        <div v-if="session?.game" class="px-6 mb-4 bg-red-900/20 text-white p-4 rounded">
            <h4 class="font-bold">🐛 Debug Info:</h4>
            <p>Mode: {{ session.state?.mode }} | Turn: {{ turn }} | Players: {{ orderedPlayers.length }}</p>
            <p>Current Player: {{ currentPlayer?.name }} (turn_order: {{ currentPlayer?.turn_order }})</p>
            <p>Is Host: {{ isHost }} | Can Draw: {{ canDraw }}</p>
        </div>
    </component>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

const props = defineProps({
    session: Object,
    auth: Object
})

const layout = computed(() => props.auth?.user ? AuthenticatedLayout : AppLayout)

const session = ref(props.session)
const cards = ref([])
const playerName = ref('')
const hasJoined = ref(false)
const turn = ref(props.session?.state?.turn ?? 0)
const lastDrawnCard = ref(null)
const lastDrawnBy = ref(null)
const justDrewCard = ref(false)
const flippedCurrent = ref(false)
const drawHistory = ref([])

const cardsRemaining = computed(() => {
    const totalCards = cards.value.length
    const drawnCount = session.value?.state?.drawn?.length || 0
    return Math.max(0, totalCards - drawnCount)
})

const orderedPlayers = computed(() => {
    return session.value?.players?.slice().sort((a, b) => a.turn_order - b.turn_order) || []
})

const getPlayerPosition = (index, total) => {
    const angle = (index * 360) / total - 90 // Start from top
    const radius = 120
    const x = Math.cos(angle * Math.PI / 180) * radius
    const y = Math.sin(angle * Math.PI / 180) * radius

    return {
        transform: `translate(${x}px, ${y}px)`,
        // Fix: Use the actual player's turn_order, not the loop index
        zIndex: orderedPlayers.value[index]?.turn_order === turn.value ? 10 : 1
    }
}

const finalizeCard = async () => {
    if (lastDrawnCard.value) {
        drawHistory.value.unshift({
            card: lastDrawnCard.value,
            playerName: lastDrawnBy.value || 'Someone',
        })
    }

    // Clear the current card
    lastDrawnCard.value = null
    lastDrawnBy.value = null
    justDrewCard.value = false
    flippedCurrent.value = false

    // Auto-advance turn in host mode (only if multiple players)
    if (session.value.state?.mode === 'host' && isHost.value && session.value.players?.length > 1) {
        try {
            console.log('🔄 Auto-advancing turn after card completion')
            const turnRes = await axios.post(`/play/${session.value.code}/next-turn`)
            console.log('🔄 Turn response:', turnRes.data)

            await fetchSession()
            console.log('🔄 New turn:', turn.value, 'New player:', currentPlayer.value?.name)
        } catch (error) {
            console.error('❌ Auto turn advancement failed:', error)
            // Don't show alert here, just log the error - the card was still finalized successfully
        }
    }

    // In multiplayer mode, the player's turn should also advance automatically
    if (session.value.state?.mode === 'multiplayer' && isMyTurn.value) {
        try {
            console.log('🔄 Auto-advancing turn after my card completion')
            const turnRes = await axios.post(`/play/${session.value.code}/next-turn`)
            console.log('🔄 Turn response:', turnRes.data)

            await fetchSession()
            console.log('🔄 New turn:', turn.value, 'New player:', currentPlayer.value?.name)
        } catch (error) {
            console.error('❌ Auto turn advancement failed:', error)
            // Don't show alert here either
        }
    }
}

const fetchSession = async () => {
    try {
        const { data } = await axios.get(`/api/play/${props.session.code}`)
        session.value = data
        turn.value = data.state?.turn ?? 0
    } catch (error) {
        console.error('Failed to fetch session:', error)
    }
}

const fetchCards = async () => {
    try {
        const res = await axios.get(`/games/${props.session.game.id}/cards`)
        const fullDeck = res.data
        const deckOrder = session.value.state?.deck || []
        cards.value = deckOrder.map(id => fullDeck.find(c => c.id === id))
    } catch (error) {
        console.error('Failed to fetch cards:', error)
    }
}

const join = async () => {
    if (!playerName.value.trim()) return

    try {
        await axios.post(`/play/${props.session.code}/join`, { name: playerName.value })
        localStorage.setItem(`name:${props.session.code}`, playerName.value)
        hasJoined.value = true
        await fetchSession()
    } catch (error) {
        console.error('Join failed:', error)
        if (error.response?.status === 401) {
            alert('You need to be logged in to join multiplayer sessions!')
            window.location.href = '/login'
        } else {
            alert(error.response?.data?.error || 'Failed to join session')
        }
    }
}

const addPlayer = async () => {
    if (!playerName.value.trim()) return

    try {
        await axios.post(`/play/${props.session.code}/join`, { name: playerName.value })
        playerName.value = ''
        await fetchSession()
    } catch (e) {
        alert(e.response?.data?.error || 'Error adding player')
    }
}

const drawCard = async () => {
    if (!canDraw.value) return

    try {
        justDrewCard.value = true
        flippedCurrent.value = false

        console.log('🎯 Before draw - Current turn:', turn.value, 'Current player:', currentPlayer.value?.name)

        // Draw the card
        const drawRes = await axios.post(`/play/${session.value.code}/draw`)
        console.log('🃏 Draw response:', drawRes.data)

        // Check if game is completed
        if (drawRes.data.completed) {
            console.log('🎉 Game completed!')
        }

        // Refresh session first
        await fetchSession()

        console.log('🎯 After refresh - Turn:', turn.value, 'Player:', currentPlayer.value?.name)

        // Show the drawn card with delay for effect
        setTimeout(() => {
            const drawnIndexes = drawRes.data.drawn
            const newCardIndex = drawnIndexes[drawnIndexes.length - 1]
            lastDrawnCard.value = cards.value[newCardIndex]
            lastDrawnBy.value = currentPlayer.value?.name || (isHost.value ? 'Host' : 'Someone')
        }, 500)

    } catch (e) {
        justDrewCard.value = false
        console.error('❌ Draw failed:', e.response?.data || e.message)
        alert('Something went wrong: ' + (e.response?.data?.error || e.message))
    }
}

// New function for host to advance turns manually
const advanceTurn = async () => {
    if (!isHost.value) return

    try {
        console.log('🔄 Host advancing turn from:', turn.value)
        const turnRes = await axios.post(`/play/${session.value.code}/next-turn`)
        console.log('🔄 Turn response:', turnRes.data)

        await fetchSession()
        console.log('🔄 New turn:', turn.value, 'New player:', currentPlayer.value?.name)
    } catch (error) {
        console.error('❌ Turn advancement failed:', error)
        alert('Failed to advance turn: ' + (error.response?.data?.error || error.message))
    }
}

onMounted(async () => {
    playerName.value = localStorage.getItem(`name:${props.session.code}`) || ''
    await fetchCards()

    if (session.value.state?.mode === 'multiplayer') {
        hasJoined.value = session.value.players.some(p =>
            p.name === playerName.value || p.user_id === props.auth?.user?.id
        )
    }

    setInterval(fetchSession, 2000)
})

const currentPlayer = computed(() =>
    session.value?.players?.find(p => p.turn_order === turn.value)
)

const isMyTurn = computed(() => {
    if (session.value?.state?.mode === 'multiplayer') {
        return currentPlayer.value?.user_id === props.auth?.user?.id
    }
    return false
})

const canDraw = computed(() => {
    const mode = session.value?.state?.mode

    if (mode === 'host') {
        return isHost.value
    } else if (mode === 'multiplayer') {
        return props.auth?.user && isMyTurn.value
    }

    return false
})

const isHost = computed(() =>
    props.auth?.user?.id && props.session?.host_user_id === props.auth.user.id
)

// Add computed property
const isDeckFinished = computed(() => {
    const totalCards = cards.value.length
    const drawnCount = session.value?.state?.drawn?.length || 0
    return drawnCount >= totalCards
})

// Add end session function
const endSession = async () => {
    if (!isHost.value) return

    try {
        await axios.post(`/play/${session.value.code}/end`)
        await fetchSession()
    } catch (error) {
        console.error('Failed to end session:', error)
        alert('Failed to end session: ' + (error.response?.data?.error || error.message))
    }
}
</script>

<style scoped>
/* Player Cards */
.player-card {
    @apply bg-white/10 backdrop-blur-sm rounded-2xl p-4 flex items-center gap-3 border border-white/20;
}

.player-card.current-turn {
    @apply ring-4 ring-yellow-400 bg-yellow-400/20 animate-pulse;
}

.player-card.host-crown {
    @apply bg-gradient-to-r from-yellow-500/20 to-orange-500/20;
}

.player-avatar {
    @apply w-12 h-12 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-xl font-bold text-white;
}

.player-info {
    @apply flex flex-col;
}

/* Turn Circle Visualization */
.turn-circle {
    @apply relative w-80 h-80 flex items-center justify-center;
}

.turn-player {
    @apply absolute transition-all duration-500 ease-in-out;
}

.turn-player.active {
    @apply scale-125;
}

.turn-player.my-turn {
    @apply scale-150;
}

.turn-avatar {
    @apply w-16 h-16 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-xl font-bold text-white shadow-2xl transition-all duration-300;
}

.turn-player.active .turn-avatar {
    @apply bg-gradient-to-br from-yellow-400 to-orange-500 ring-4 ring-white/50;
}

.turn-player.my-turn .turn-avatar {
    @apply bg-gradient-to-br from-green-400 to-emerald-500 ring-4 ring-green-400/50 animate-pulse;
}

.turn-name {
    @apply text-center text-sm font-bold mt-2;
}

.turn-indicator {
    @apply absolute -top-2 -right-2 bg-gradient-to-r from-yellow-400 to-orange-500 text-black text-xs font-bold px-2 py-1 rounded-full;
}

.turn-pulse {
    @apply absolute inset-0 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full animate-ping;
}

.turn-center {
    @apply absolute inset-0 flex flex-col items-center justify-center text-center bg-white/5 backdrop-blur-sm rounded-full border border-white/20;
}

/* Draw Pile */
.draw-pile-container {
    @apply relative;
}

.draw-pile {
    @apply relative w-32 h-44 cursor-pointer transition-all duration-300;
}

.draw-pile.clickable:hover {
    @apply scale-110;
}

.draw-pile.disabled {
    @apply opacity-50 cursor-not-allowed;
}

.card-stack {
    @apply absolute w-full h-full bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl shadow-2xl border-2 border-white/20;
}

.card-3 {
    @apply transform rotate-12 -translate-x-2 translate-y-2;
}

.card-2 {
    @apply transform rotate-6 -translate-x-1 translate-y-1;
}

.card-1 {
    @apply transform rotate-0 z-10;
}

.card-1.drawing {
    @apply animate-pulse bg-gradient-to-br from-yellow-400 to-orange-500;
}

.card-content {
    @apply h-full flex flex-col items-center justify-center text-white font-bold;
}

.draw-glow {
    @apply absolute inset-0 bg-gradient-to-br from-yellow-400/30 to-pink-500/30 rounded-2xl blur-xl -z-10 animate-pulse;
}

.clickable .draw-glow {
    @apply opacity-100;
}

/* Drawn Card */
.drawn-card-container {
    @apply relative w-80 h-96 perspective-1000;
}

.drawn-card {
    @apply relative w-full h-full transform-style-preserve-3d transition-transform duration-700 cursor-pointer;
}

.drawn-card.flipped {
    @apply rotate-y-180;
}

.card-face {
    @apply absolute w-full h-full backface-hidden rounded-3xl overflow-hidden shadow-2xl;
}

.card-back {
    @apply bg-gradient-to-br from-purple-800 to-indigo-900;
}

.card-front {
    @apply bg-white rotate-y-180;
}

.mystery-card {
    @apply relative h-full flex flex-col items-center justify-center text-white p-6;
    background: linear-gradient(45deg, #6b46c1, #7c3aed, #8b5cf6, #a855f7);
    background-size: 400% 400%;
    animation: mysteryGradient 3s ease infinite;
}

.mystery-pattern {
    @apply absolute inset-0 opacity-20;
    background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(255,255,255,0.1) 10px, rgba(255,255,255,0.1) 20px);
}

.mystery-content {
    @apply relative z-10 text-center;
}

.revealed-card {
    @apply h-full p-6 text-gray-800 relative overflow-hidden;
    background: linear-gradient(135deg, #fbbf24, #f59e0b, #d97706);
}

.card-header {
    @apply flex justify-between items-start mb-4;
}

.card-suit {
    @apply text-2xl font-bold bg-white/20 px-3 py-1 rounded-full;
}

.card-label {
    @apply text-3xl font-black;
}

.card-action {
    @apply text-lg leading-relaxed font-medium bg-white/10 backdrop-blur-sm rounded-2xl p-4;
}

.card-sparkles {
    @apply absolute top-4 right-4 text-4xl animate-pulse;
}

/* Finalize Button */
.finalize-button {
    @apply bg-gradient-to-r from-pink-500 to-rose-600 text-white px-8 py-4 rounded-full text-xl font-bold shadow-2xl hover:scale-105 transition-transform;
}

/* History Cards */
.history-card {
    @apply bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20 hover:scale-105 transition-transform;
}

.history-card-content {
    @apply space-y-2;
}

.history-header {
    @apply flex justify-between items-center;
}

.history-suit {
    @apply text-sm font-bold bg-white/20 px-2 py-1 rounded;
}

.history-label {
    @apply font-bold;
}

.history-player {
    @apply text-xs text-yellow-400 font-bold;
}

.history-action {
    @apply text-sm opacity-90 leading-relaxed;
}

/* Animations */
@keyframes mysteryGradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.history-card-enter-active,
.history-card-leave-active {
    transition: all 0.5s ease;
}

.history-card-enter-from,
.history-card-leave-to {
    opacity: 0;
    transform: scale(0.8) translateY(20px);
}

/* Utility Classes */
.perspective-1000 {
    perspective: 1000px;
}

.transform-style-preserve-3d {
    transform-style: preserve-3d;
}

.backface-hidden {
    backface-visibility: hidden;
}

.rotate-y-180 {
    transform: rotateY(180deg);
}

/* Add host-mode specific styling */
.turn-player.host-mode.active .turn-avatar {
    @apply bg-gradient-to-br from-orange-400 to-red-500 ring-4 ring-orange-400/50;
}
</style>
