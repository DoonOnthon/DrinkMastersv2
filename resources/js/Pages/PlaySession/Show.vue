<template>
    <component :is="layout" :auth="auth">
        <div v-if="session?.game" class="min-h-screen bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900 text-white overflow-hidden">
            <!-- 🎮 Game Header -->
            <div class="text-center pt-6 pb-4">
                <h1 class="text-4xl font-black mb-2 bg-gradient-to-r from-yellow-400 to-pink-500 bg-clip-text text-transparent">
                    🎮 {{ session.game.title }}
                </h1>
                <div class="inline-block bg-white/10 backdrop-blur-sm rounded-full px-6 py-2">
                    <span class="text-lg font-mono">{{ session.code }}</span>
                </div>
                <div class="mt-2">
                    <span class="inline-block px-4 py-1 rounded-full text-sm font-bold"
                          :class="session.state?.mode === 'host' ? 'bg-orange-500' : 'bg-blue-500'">
                        {{ session.state?.mode === 'host' ? '👑 Host-led' : '🎯 Multiplayer' }}
                    </span>
                </div>
            </div>

            <!-- 🚨 XP Incentive Banner (NO PULSING) -->
            <div v-if="session.state?.mode === 'multiplayer' && !auth?.user"
                 class="mx-6 mb-4 bg-gradient-to-r from-yellow-400 to-orange-500 text-black p-3 rounded-2xl text-center font-bold shadow-2xl">
                <div class="text-lg mb-2">⚡ EARN XP & CLIMB THE LEADERBOARD! ⚡</div>
                <a href="/login" class="inline-block bg-black text-yellow-400 px-4 py-1 rounded-full hover:scale-105 transition-transform text-sm">
                    🔥 Login Now 🔥
                </a>
            </div>

            <!-- 🙋 Player Management - BEFORE first card OR compact top-left -->
            <div v-if="session.state?.mode === 'host' && isHost">
                <!-- Show prominently BEFORE any cards are drawn -->
                <div v-if="!hasAnyCardsBeenDrawn" class="px-6 mb-4">
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border-2 border-dashed border-yellow-400/50">
                        <h4 class="font-bold mb-3 text-center text-lg">🎯 Add Players to Start</h4>
                        <div class="flex gap-3">
                            <input v-model="playerName" placeholder="Player name"
                                   class="flex-1 bg-white/20 border border-white/30 px-4 py-2 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-yellow-400" />
                            <button @click="addPlayer" :disabled="!playerName.trim()"
                                    class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-2 rounded-xl font-bold hover:scale-105 transition-transform disabled:opacity-50 disabled:cursor-not-allowed">
                                ➕ Add
                            </button>
                        </div>
                        <div v-if="session.players?.length === 0" class="text-center mt-3 text-yellow-400 text-sm">
                            Add at least one player to begin
                        </div>
                    </div>
                </div>

                <!-- Show compactly in top-left AFTER cards are drawn -->
                <div v-else class="fixed top-4 left-4 z-50">
                    <div v-if="!showPlayerManager" class="relative">
                        <button @click="showPlayerManager = true"
                                class="bg-gradient-to-r from-green-500/80 to-emerald-600/80 backdrop-blur-sm text-white px-3 py-2 rounded-lg font-bold hover:scale-105 transition-transform shadow-lg">
                            ➕ Add Player
                        </button>
                    </div>

                    <div v-else class="bg-white/10 backdrop-blur-lg rounded-2xl p-4 shadow-2xl border border-white/20 min-w-64">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-bold">➕ Add Player</h4>
                            <button @click="showPlayerManager = false" class="text-white/60 hover:text-white text-xl">✕</button>
                        </div>
                        <div class="flex gap-2">
                            <input v-model="playerName" placeholder="Player name"
                                   class="flex-1 bg-white/20 border border-white/30 px-3 py-2 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-yellow-400 text-sm" />
                            <button @click="addPlayer" :disabled="!playerName.trim()"
                                    class="bg-gradient-to-r from-green-500 to-emerald-600 px-4 py-2 rounded-lg font-bold hover:scale-105 transition-transform disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                                ➕
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Multiplayer Join -->
            <div v-if="session.state?.mode === 'multiplayer' && !hasJoined && auth?.user" class="px-6 mb-4">
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-4">
                    <h4 class="font-bold mb-3 text-lg">🎮 Join the Game!</h4>
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

            <!-- 🎯 Turn Indicator (COMPACT) -->
            <div v-if="session.players?.length > 0" class="px-6 mb-4">
                <div class="text-center mb-3">
                    <div class="inline-block bg-gradient-to-r from-blue-500 to-purple-600 px-6 py-2 rounded-full shadow-xl">
                        <span class="text-lg font-black">🎯 Turn {{ turn + 1 }} of {{ session.players.length }}</span>
                    </div>
                </div>

                <!-- SMALLER Turn Circle -->
                <div class="flex justify-center">
                    <div class="turn-circle-compact">
                        <div v-for="(player, index) in orderedPlayers" :key="player.id"
                             class="turn-player-compact"
                             :class="{
                                'active': player.turn_order === turn,
                                'my-turn': player.user_id === auth?.user?.id && player.turn_order === turn,
                                'host-mode': session.state?.mode === 'host'
                             }"
                             :style="getPlayerPositionCompact(index, orderedPlayers.length)">
                            <div class="turn-avatar-compact">
                                {{ player.name[0].toUpperCase() }}
                            </div>
                            <div class="turn-name-compact">{{ player.name }}</div>
                        </div>

                        <!-- Center Turn Info -->
                        <div class="turn-center-compact">
                            <div class="text-lg">🎯</div>
                            <div class="font-bold text-sm">{{ currentPlayer?.name || 'Unknown' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Host/Player Status (COMPACT) -->
                <div class="text-center mt-3">
                    <div class="mt-tiny-move">
                    <div v-if="session.state?.mode === 'host' && isHost" class="inline-block bg-gradient-to-r from-orange-400 to-red-500 text-black px-4 py-1 rounded-full font-bold text-sm">
                        👑 You control for {{ currentPlayer?.name || 'Unknown' }}
                    </div>
                    <div v-else-if="session.state?.mode === 'multiplayer' && isMyTurn" class="inline-block bg-gradient-to-r from-green-400 to-emerald-500 text-black px-4 py-1 rounded-full font-bold text-sm">
                        ✨ YOUR TURN! ✨
                    </div>
                    </div>
                </div>
            </div>


            <!-- 🃏 Card Drawing Area (FIXED LAYOUT) -->
            <div class="px-6">
                <!-- Draw Pile (ALWAYS IN SAME POSITION) -->
                <div class="flex justify-center items-center gap-6">
                    <!-- Draw Pile Container -->
                    <div class="draw-pile-container">
                        <!-- Main draw pile -->
                        <div v-if="canDraw" @click="drawCard" class="draw-pile"
                             :class="{ 'clickable': !justDrewCard, 'dimmed': justDrewCard }">
                            <div class="card-stack card-3" :class="{ 'shake-animation': isDrawing }"></div>
                            <div class="card-stack card-2" :class="{ 'shake-animation': isDrawing }"></div>
                            <div class="card-stack card-1" :class="{
                                'drawing-animation': isDrawing,
                                'card-being-drawn': isCardFlying,
                                'card-just-drawn': isCardFlying
                            }">
                                <!-- Show the actual card content when not flying -->
                                <div v-if="!isCardFlying" class="card-content">
                                    <div class="text-4xl mb-2">🃏</div>
                                    <div class="text-sm font-bold">{{ isDrawing ? 'DRAWING...' : 'DRAW CARD' }}</div>
                                    <div class="text-xs opacity-75">{{ cardsRemaining }} left</div>
                                </div>

                                <!-- Show the drawn card when flying -->
                                <div v-else class="card-content mystery-card-compact">
                                    <div class="mystery-pattern-compact"></div>
                                    <div class="mystery-content-compact">
                                        <div class="text-2xl mb-2">🎭</div>
                                        <div class="text-sm font-bold">Mystery Card</div>
                                    </div>
                                </div>
                            </div>
                            <div class="draw-glow" :class="{ 'super-glow': isDrawing }"></div>
                        </div>

                        <!-- Disabled state -->
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

                    <!-- Manual Skip (Emergency) -->
                    <div v-if="session.state?.mode === 'host' && isHost && session.players?.length > 1"
                         class="flex flex-col items-center">
                        <button @click="advanceTurn"
                                class="bg-gradient-to-r from-gray-500 to-gray-600 text-white px-3 py-2 rounded-lg font-bold hover:scale-105 transition-transform shadow-lg opacity-75 text-sm">
                            <div class="text-sm">🔄</div>
                            <div class="text-xs">Skip</div>
                        </button>
                    </div>
                </div>

                <!-- ✨ FLOATING CARD DISPLAY (APPEARS ABOVE DECK) -->
                <div v-if="justDrewCard && lastDrawnCard"
                     class="floating-card-overlay">
                    <div class="floating-card-container">
                        <div class="text-center mb-3">
                            <h3 class="text-lg font-bold mb-1">Card Drawn!</h3>
                            <p class="text-xs opacity-90">Drawn by: <strong>{{ lastDrawnBy }}</strong></p>
                        </div>

                        <div class="flex justify-center">
                            <!-- ✨ Card that flies from deck position -->
                            <div class="drawn-card-container-compact"
                                 :class="{ 'card-flying': isCardFlying, 'card-landed': hasCardLanded }"
                                 @click="!flippedCurrent && (flippedCurrent = true)">

                                <div class="drawn-card-compact" :class="{ 'flipped': flippedCurrent }">
                                    <!-- Card Back -->
                                    <div class="card-face card-back">
                                        <div class="mystery-card-compact">
                                            <div class="mystery-pattern-compact"></div>
                                            <div class="mystery-content-compact">
                                                <div class="text-2xl mb-2">🎭</div>
                                                <div class="text-sm font-bold">Mystery Card</div>
                                                <div class="text-xs opacity-75" v-if="!flippedCurrent">
                                                    {{ flipCountdown > 0 ? `Auto-flip in ${flipCountdown}s` : 'Tap to reveal' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card Front -->
                                    <div class="card-face card-front">
                                        <div class="revealed-card-compact">
                                            <div class="card-header-compact">
                                                <div class="card-suit-compact">{{ lastDrawnCard.suit }}</div>
                                                <div class="card-label-compact">{{ lastDrawnCard.label }}</div>
                                            </div>
                                            <div class="card-action-compact">
                                                {{ lastDrawnCard.action_text }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ✨ Subtle glow when card lands -->
                                <div v-if="hasCardLanded" class="card-landing-glow"></div>
                            </div>
                        </div>

                        <div v-if="flippedCurrent" class="text-center mt-3">
                            <button @click="finalizeCard" class="finalize-button-compact">
                                <span class="text-lg">🍻</span>
                                <span class="ml-2">Done Drinking! Next Turn</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🧩 Card History (COMPACT, BOTTOM) -->
            <div v-if="drawHistory.length > 0" class="px-6 py-4 mt-6">
                <div class="text-center mb-3">
                    <button @click="showHistory = !showHistory"
                            class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-bold hover:scale-105 transition-transform">
                        📚 Card History ({{ drawHistory.length }}) {{ showHistory ? '▼' : '▲' }}
                    </button>
                </div>

                <div v-if="showHistory" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 max-h-48 overflow-y-auto">
                    <div v-for="(entry, index) in drawHistory" :key="`history-${index}`" class="history-card-compact">
                        <div class="history-header-compact">
                            <span class="history-suit-compact">{{ entry.card.suit }}</span>
                            <span class="history-label-compact">{{ entry.card.label }}</span>
                        </div>
                        <div class="history-player-compact">{{ entry.playerName }}</div>
                        <div class="history-action-compact">{{ entry.card.action_text }}</div>
                    </div>
                </div>
            </div>

            <!-- Game Status Messages (COMPACT) -->
            <div v-if="session.completed_at" class="mx-6 mt-4 bg-gradient-to-r from-green-600 to-emerald-700 text-white p-3 rounded-xl text-center">
                <div class="text-lg mb-1">🎉 Game Completed! 🎉</div>
                <div class="text-xs opacity-90">Session will be automatically cleaned up in 1 hour</div>
                <div v-if="isHost" class="mt-2">
                    <button @click="endSession" class="bg-white text-green-600 px-3 py-1 rounded-full font-bold hover:scale-105 transition-transform text-sm">
                        End Session Now
                    </button>
                </div>
            </div>

            <div v-if="isDeckFinished && !session.completed_at" class="mx-6 mt-4 bg-gradient-to-r from-purple-600 to-pink-700 text-white p-3 rounded-xl text-center">
                <div class="text-lg mb-1">🃏 All Cards Drawn! 🃏</div>
                <div class="text-xs opacity-90">The deck is complete!</div>
                <div v-if="isHost" class="mt-2">
                    <button @click="endSession" class="bg-white text-purple-600 px-3 py-1 rounded-full font-bold hover:scale-105 transition-transform text-sm">
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
    </component>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
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
const showPlayerManager = ref(false)
const showHistory = ref(false)

// ✨ Animation state variables
const isDrawing = ref(false)
const isCardFlying = ref(false)
const hasCardLanded = ref(false)
const showParticles = ref(false)
const flipCountdown = ref(0)
let flipTimer = null

// ✅ Check if any cards have been drawn
const hasAnyCardsBeenDrawn = computed(() => {
    return (session.value?.state?.drawn?.length || 0) > 0
})

const cardsRemaining = computed(() => {
    const totalCards = cards.value.length
    const drawnCount = session.value?.state?.drawn?.length || 0
    return Math.max(0, totalCards - drawnCount)
})

const orderedPlayers = computed(() => {
    return session.value?.players?.slice().sort((a, b) => a.turn_order - b.turn_order) || []
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

const isDeckFinished = computed(() => {
    const totalCards = cards.value.length
    const drawnCount = session.value?.state?.drawn?.length || 0
    return drawnCount >= totalCards
})

// ✅ Compact position calculation
const getPlayerPositionCompact = (index, total) => {
    const angle = (index * 360) / total - 90
    const radius = 80
    const x = Math.cos(angle * Math.PI / 180) * radius
    const y = Math.sin(angle * Math.PI / 180) * radius

    return {
        transform: `translate(${x}px, ${y}px)`,
        zIndex: orderedPlayers.value[index]?.turn_order === turn.value ? 10 : 1
    }
}

// ✨ Particle animation helper
const getParticleStyle = (index) => {
    const angle = (index * 30) * Math.PI / 180
    const distance = 100 + Math.random() * 50
    const x = Math.cos(angle) * distance
    const y = Math.sin(angle) * distance

    return {
        '--particle-x': `${x}px`,
        '--particle-y': `${y}px`,
        '--delay': `${index * 0.1}s`,
        animationDelay: `${index * 0.1}s`
    }
}

// ✅ Enhanced addPlayer function
const addPlayer = async () => {
    if (!playerName.value.trim()) return

    try {
        await axios.post(`/play/${props.session.code}/join`, { name: playerName.value })
        playerName.value = ''
        showPlayerManager.value = false
        await fetchSession()
    } catch (e) {
        alert(e.response?.data?.error || 'Error adding player')
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
        console.log('🔍 Fetching cards for game:', props.session.game.id)
        const res = await axios.get(`/games/${props.session.game.id}/cards`)
        console.log('🔍 Raw response:', res.data)

        cards.value = res.data

        console.log('📇 Loaded cards:', cards.value.length)
        console.log('📇 First card:', cards.value[0])
        console.log('📇 Cards remaining calculation:', cardsRemaining.value)
    } catch (error) {
        console.error('❌ Failed to fetch cards:', error)
        console.error('❌ Error response:', error.response)
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

// ✨ Enhanced draw card function with PROPER deck-to-center animation
const drawCard = async () => {
    if (!canDraw.value || isDrawing.value) return

    try {
        // 🎬 Start drawing animation
        isDrawing.value = true
        isCardFlying.value = false
        hasCardLanded.value = false
        flippedCurrent.value = false
        flipCountdown.value = 0

        console.log('🎯 Before draw - Current turn:', turn.value, 'Current player:', currentPlayer.value?.name)

        // 🎬 Wait for deck shake animation
        await new Promise(resolve => setTimeout(resolve, 800))

        // Draw the card from backend
        const drawRes = await axios.post(`/play/${session.value.code}/draw`)
        console.log('🃏 Draw response:', drawRes.data)

        // ✅ FIX: Get the drawn card correctly
        const drawnIndexes = drawRes.data.drawn
        const newCardIndex = drawnIndexes[drawnIndexes.length - 1]

        // The newCardIndex is now the index in the cards array
        lastDrawnCard.value = cards.value[newCardIndex]
        lastDrawnBy.value = currentPlayer.value?.name || (isHost.value ? 'Host' : 'Someone')

        console.log('🃏 Drew card:', lastDrawnCard.value)

        // 🎬 Start the flying animation - card flies FROM deck position
        isDrawing.value = false
        isCardFlying.value = true

        // 🎬 After flying animation completes, show the landed card
        setTimeout(() => {
            isCardFlying.value = false
            justDrewCard.value = true
            hasCardLanded.value = true
        }, 800) // Match the cardFlyFromDeck animation duration

        // Refresh session after animation
        await fetchSession()

        // 🎬 Auto-flip countdown
        setTimeout(() => {
            if (!flippedCurrent.value) {
                flipCountdown.value = 3
                flipTimer = setInterval(() => {
                    flipCountdown.value--
                    if (flipCountdown.value <= 0) {
                        clearInterval(flipTimer)
                        flippedCurrent.value = true
                    }
                }, 1000)
            }
        }, 1500) // Start countdown after card has landed

    } catch (e) {
        isDrawing.value = false
        isCardFlying.value = false
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

// Clean up timer when component unmounts
onUnmounted(() => {
    if (flipTimer) clearInterval(flipTimer)
})

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

/* ✅ COMPACT STYLES */
.turn-circle-compact {
    @apply relative w-48 h-48 flex items-center justify-center;
}

.turn-player-compact {
    @apply absolute transition-all duration-300 ease-in-out;
}

.turn-player-compact.active {
    @apply scale-110;
}

.turn-avatar-compact {
    @apply w-12 h-12 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-sm font-bold text-white shadow-lg transition-all duration-300;
}

.turn-player-compact.active .turn-avatar-compact {
    @apply bg-gradient-to-br from-yellow-400 to-orange-500 ring-2 ring-white/50;
}

.turn-name-compact {
    @apply text-center text-xs font-bold mt-1;
}

.turn-center-compact {
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
    @apply absolute inset-0 bg-gradient-to-br from-yellow-400/30 to-pink-500/30 rounded-2xl blur-xl -z-10;
    transition: all 0.3s ease;
}

.clickable .draw-glow {
    @apply opacity-100;
}

.clickable:hover .draw-glow {
    @apply opacity-100 scale-110;
    background: linear-gradient(45deg,
        rgba(251, 191, 36, 0.5),
        rgba(249, 158, 11, 0.5),
        rgba(239, 68, 68, 0.5)
    );
}

/* Drawn Card */
.drawn-card-container {
    @apply relative w-80 h-96;
    perspective: 1000px;
}

.drawn-card-container-compact {
    @apply relative w-48 h-64;
    perspective: 1000px;
}

.drawn-card, .drawn-card-compact {
    @apply relative w-full h-full cursor-pointer;
    transform-style: preserve-3d;
    transition: transform 0.7s;
}

.drawn-card.flipped, .drawn-card-compact.flipped {
    transform: rotateY(180deg);
    transition: transform 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.card-face {
    @apply absolute w-full h-full rounded-3xl overflow-hidden shadow-2xl;
    backface-visibility: hidden;
}

.card-back {
    @apply bg-gradient-to-br from-purple-800 to-indigo-900;
}

.card-front {
    @apply bg-white;
    transform: rotateY(180deg);
}

/* ✅ COMPACT CARD STYLES */
.mystery-card-compact {
    @apply relative h-full flex flex-col items-center justify-center text-white p-4;
    background: linear-gradient(45deg, #6b46c1, #7c3aed, #8b5cf6, #a855f7, #c084fc, #8b5cf6, #7c3aed, #6b46c1);
    background-size: 800% 800%;
    animation: mysteryGradient 3s ease infinite;
}

.mystery-pattern-compact {
    @apply absolute inset-0 opacity-20;
    background-image: repeating-linear-gradient(45deg, transparent, transparent 8px, rgba(255,255,255,0.1) 8px, rgba(255,255,255,0.1) 16px);
}

.mystery-content-compact {
    @apply relative z-10 text-center;
}

.revealed-card-compact {
    @apply h-full p-4 text-gray-800 relative overflow-hidden;
    background: linear-gradient(135deg, #fbbf24, #f59e0b, #d97706);
}

/* Finalize Button */
.finalize-button {
    @apply bg-gradient-to-r from-pink-500 to-rose-600 text-white px-8 py-4 rounded-full text-xl font-bold shadow-2xl hover:scale-105 transition-transform;
}

.finalize-button-compact {
    @apply bg-gradient-to-r from-pink-500 to-rose-600 text-white px-6 py-3 rounded-full text-lg font-bold shadow-xl hover:scale-105 transition-transform;
}

/* History Cards */
.history-card {
    @apply bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20 hover:scale-105 transition-transform;
}

.history-card-compact {
    @apply bg-white/10 backdrop-blur-sm rounded-lg p-3 border border-white/20 hover:scale-105 transition-transform;
}

.history-card-content {
    @apply space-y-2;
}

.history-header {
    @apply flex justify-between items-center;
}

.history-header-compact {
    @apply flex justify-between items-center mb-1;
}

.history-suit {
    @apply text-sm font-bold bg-white/20 px-2 py-1 rounded;
}

.history-suit-compact {
    @apply text-xs font-bold bg-white/20 px-2 py-1 rounded;
}

.history-label {
    @apply font-bold;
}

.history-label-compact {
    @apply font-bold text-sm;
}

.history-player {
    @apply text-xs text-yellow-400 font-bold;
}

.history-player-compact {
    @apply text-xs text-yellow-400 font-bold mb-1;
}

.history-action {
    @apply text-sm opacity-90 leading-relaxed;
}

.history-action-compact {
    @apply text-xs opacity-90 leading-relaxed;
}

/* ✨ CARD DRAWING ANIMATIONS */
@keyframes shake {
    0%, 100% { transform: translateX(0) rotate(0deg); }
    25% { transform: translateX(-2px) rotate(-1deg); }
    75% { transform: translateX(2px) rotate(1deg); }
}

@keyframes cardFly {
    0% {
        transform: translateY(200px) translateX(0) scale(0.6) rotate(-5deg);
        opacity: 0;
    }
    20% {
        transform: translateY(100px) translateX(0) scale(0.8) rotate(-2deg);
        opacity: 0.5;
    }
    100% {
        transform: translateY(0) translateX(0) scale(1) rotate(0deg);
        opacity: 1;
    }
}

@keyframes cardFlyFromDeck {
    0% {
        transform: translateY(0) translateX(0) scale(1) rotate(0deg);
        opacity: 1;
    }
    25% {
        transform: translateY(-30px) translateX(30px) scale(1.1) rotate(8deg);
        opacity: 1;
    }
    50% {
        transform: translateY(-60px) translateX(15px) scale(1.05) rotate(4deg);
        opacity: 1;
    }
    75% {
        transform: translateY(-80px) translateX(-10px) scale(0.9) rotate(-2deg);
        opacity: 0.8;
    }
    100% {
        transform: translateY(-100px) translateX(0) scale(0.8) rotate(0deg);
        opacity: 0;
    }
}

@keyframes cardLand {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

@keyframes superGlow {
    0%, 100% {
        opacity: 0.5;
        transform: scale(1);
    }
    50% {
        opacity: 1;
        transform: scale(1.2);
    }
}

@keyframes drawingPulse {
    0%, 100% {
        transform: scale(1) rotate(0deg);
        box-shadow: 0 0 20px rgba(251, 191, 36, 0.5);
    }
    50% {
        transform: scale(1.05) rotate(2deg);
        box-shadow: 0 0 40px rgba(251, 191, 36, 0.8);
    }
}

@keyframes particleExplode {
    0% {
        transform: translate(0, 0) scale(1);
        opacity: 1;
    }
    100% {
        transform: translate(var(--particle-x), var(--particle-y)) scale(0);
        opacity: 0;
    }
}

@keyframes landingGlow {
    0% {
        opacity: 0;
        transform: scale(0.8);
    }
    50% {
        opacity: 0.5;
        transform: scale(1.1);
    }
    100% {
        opacity: 0;
        transform: scale(1.2);
    }
}

/* ✨ ANIMATION CLASSES */
.shake-animation {
    animation: shake 0.5s ease-in-out infinite;
}

.drawing-animation {
    animation: drawingPulse 1s ease-in-out infinite;
}

.super-glow {
    animation: superGlow 0.5s ease-in-out infinite;
}

.card-flying {
    animation: cardFly 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    z-index: 100;
}

.card-landed {
    animation: cardLand 0.4s ease-out;
}

.magic-particles {
    @apply absolute inset-0 pointer-events-none;
    z-index: 200;
}

.particle {
    @apply absolute top-1/2 left-1/2 text-2xl;
    animation: particleExplode 2s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
}

.card-landing-glow {
    @apply absolute inset-0 rounded-3xl;
    background: radial-gradient(circle, rgba(251, 191, 36, 0.2) 0%, transparent 70%);
    animation: landingGlow 1s ease-out;
    pointer-events: none;
    z-index: -1;
}

/* ✨ FLOATING CARD OVERLAY */
.floating-card-overlay {
    @apply fixed inset-0 z-50 flex items-center justify-center;
    background: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(4px);
    animation: overlayFadeIn 0.3s ease-out;
}

.floating-card-container {
    @apply relative z-50;
    animation: cardDropIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

/* ✨ ENHANCED CARD FLYING ANIMATION */
.card-just-drawn {
    animation: cardFlyFromDeck 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    z-index: 100;
}

.card-being-drawn {
    @apply opacity-30;
    transition: opacity 0.3s ease;
}

.draw-pile.dimmed {
    @apply opacity-40;
    transition: opacity 0.3s ease;
}

/* ✨ OVERLAY ANIMATIONS */
@keyframes overlayFadeIn {
    0% {
        opacity: 0;
        backdrop-filter: blur(0px);
    }
    100% {
        opacity: 1;
        backdrop-filter: blur(4px);
    }
}

@keyframes cardDropIn {
    0% {
        transform: translateY(-100px) scale(0.8);
        opacity: 0;
    }
    100% {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
}

/* ✨ Card appearance animation in overlay */
.card-flying {
    animation: cardAppearInOverlay 0.3s ease-out forwards;
    z-index: 100;
}

@keyframes cardAppearInOverlay {
    0% {
        transform: translateY(-20px) scale(0.9);
        opacity: 0;
    }
    100% {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
}
.mt-tiny-move {
  margin-top: 2.5rem; /* or whatever you prefer, like 0.4rem */
}

/* ✨ Missing compact card styles */
.card-header-compact {
    @apply flex justify-between items-start mb-3;
}

.card-suit-compact {
    @apply text-lg font-bold bg-white/20 px-2 py-1 rounded-full;
}

.card-label-compact {
    @apply text-xl font-black;
}

.card-action-compact {
    @apply text-sm leading-relaxed font-medium bg-white/10 backdrop-blur-sm rounded-xl p-3;
}

/* ✨ MYSTERY CARD GRADIENT ANIMATION */
@keyframes mysteryGradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
</style>
