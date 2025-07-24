# DrinkMasters - Game Hosting Platform

## Project Overview

DrinkMasters is a web application built with Laravel that allows users to host and play card-based drinking games with friends. The platform supports two distinct game modes and encourages user registration through XP incentives.

## Game Modes

### 1. Host-Led Mode 🎮
- **Purpose**: For streaming/presenting games over Discord, Zoom, etc.
- **Host Control**: Only the host (must be logged in) can add players and control the game
- **Player Experience**: Players watch the stream and follow along
- **Authentication**: Host must be logged in, players are anonymous
- **XP Earning**: ❌ No XP earned (anonymous play)
- **Use Case**: Perfect for content creators or casual group hangouts

### 2. Multiplayer Mode 👥
- **Purpose**: Interactive gameplay where each player participates directly
- **Player Control**: Each player takes turns and draws their own cards
- **Authentication**: ✅ All players must log in to join
- **XP Earning**: ✅ Players earn XP for participation and completion
- **Use Case**: Competitive gaming with progression tracking

## User Incentive Strategy

### Account Creation Motivation
- **Clear XP messaging**: Prominent banners showing "Login to earn XP!"
- **Feature limitations**: Guest players see what they're missing
- **Social features**: Leaderboards, achievements only for registered users
- **Progress tracking**: Game history and statistics for logged-in players

## Session Management Issues to Fix

### Current Problems
1. **Guest joining flow**: Unclear how guests can participate
2. **Mode switching**: Players might not understand the difference
3. **Session persistence**: Need better handling of disconnects
4. **Turn management**: Ensure proper turn order in multiplayer mode

### Priority Fixes Needed

#### 1. Join Flow Improvements
```php
// In PlaySessionController@join - need clearer logic
public function join(Request $request, string $code)
{
    $session = PlaySession::with('players')->where('code', $code)->firstOrFail();
    $name = trim($request->input('name'));
    $mode = $session->state['mode'];

    // Host-led: Anyone can be added by host
    if ($mode === 'host') {
        if ($session->host_user_id !== auth()->id()) {
            return response()->json(['error' => 'Only the host can add players'], 403);
        }
        // Add as anonymous player
    }
    
    // Multiplayer: Must be logged in
    if ($mode === 'multiplayer') {
        if (!auth()->check()) {
            return response()->json([
                'error' => 'Login required for multiplayer mode',
                'requires_auth' => true
            ], 401);
        }
        // Add as authenticated player
    }
}
```

#### 2. Session State Management
```php
// Enhanced session state structure
'state' => [
    'mode' => 'host|multiplayer',
    'status' => 'waiting|playing|completed',
    'drawn' => [],
    'turn' => 0,
    'deck' => $cards,
    'max_players' => 8,
    'created_at' => now(),
]
```

#### 3. Frontend UX Improvements
- **Mode selection**: Clear explanation of differences
- **Join barriers**: Show login prompt for multiplayer sessions
- **XP teasers**: Display potential XP earnings before login
- **Guest limitations**: Show what features require accounts

## Technical Architecture

### Core Controllers

#### [`PlaySessionController`](app/Http/Controllers/PlaySessionController.php)
**Key Methods:**
- `store()`: Creates session with mode validation
- `show()`: Renders game interface with mode-specific UI
- `join()`: Handles mode-specific joining logic
- `draw()`: Manages card drawing with turn validation
- `nextTurn()`: Advances turn order safely
- `api()`: Provides real-time session state

**Critical Logic:**
```php
// Mode validation on creation
if (!in_array($mode, ['host', 'multiplayer'])) {
    return back()->withErrors(['mode' => 'Invalid game mode']);
}

// Authentication checks
if ($mode === 'multiplayer' && !auth()->check()) {
    return redirect()->route('login')
        ->with('message', 'Login required for multiplayer mode');
}
```

### Database Schema

#### Play Sessions Table
```sql
CREATE TABLE play_sessions (
    id BIGINT PRIMARY KEY,
    game_id BIGINT NOT NULL,
    host_user_id BIGINT NULL,           -- NULL for guest hosts
    code VARCHAR(6) UNIQUE NOT NULL,    -- Join code
    is_public BOOLEAN DEFAULT true,
    state JSON NOT NULL,                -- Game state
    expires_at TIMESTAMP NULL,          -- Session expiration
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (game_id) REFERENCES games(id),
    FOREIGN KEY (host_user_id) REFERENCES users(id)
);
```

#### Session Players Table
```sql
CREATE TABLE session_players (
    id BIGINT PRIMARY KEY,
    play_session_id BIGINT NOT NULL,
    user_id BIGINT NULL,                -- NULL for anonymous players
    name VARCHAR(255) NOT NULL,
    turn_order INTEGER NOT NULL,
    is_active BOOLEAN DEFAULT true,
    joined_at TIMESTAMP DEFAULT NOW(),
    
    FOREIGN KEY (play_session_id) REFERENCES play_sessions(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## API Endpoints

### Session Management
- `POST /games/{game}/play-session` - Create new session (auth required)
- `GET /play/{code}` - View session (public)
- `GET /api/play/{code}` - Session state polling (public)
- `POST /play/{code}/join` - Join session (mode-dependent auth)

### Game Actions
- `POST /play/{code}/draw` - Draw card (turn-based)
- `POST /play/{code}/next-turn` - Advance turn
- `POST /play/{code}/leave` - Leave session
- `DELETE /play/{code}` - End session (host only)

## Frontend Components

### [`PlaySession/Show.vue`](resources/js/Pages/PlaySession/Show.vue)
**Key Features:**
- Mode-aware UI rendering
- XP incentive banners for guests
- Real-time session polling
- Turn indicator and controls
- Login prompts for multiplayer features

### [`Games/Play.vue`](resources/js/Pages/Games/Play.vue)
**Responsibilities:**
- Clear mode explanation
- XP earning potential display
- Authentication flow integration
- Session creation with validation

## UX Improvements Needed

### 1. Mode Selection Screen
```vue
<template>
  <div class="mode-selection">
    <div class="mode-card">
      <h3>Host-Led Mode</h3>
      <p>Perfect for streaming to friends</p>
      <ul>
        <li>✅ Easy setup</li>
        <li>❌ No XP earned</li>
        <li>❌ No progress tracking</li>
      </ul>
    </div>
    
    <div class="mode-card featured">
      <h3>Multiplayer Mode</h3>
      <p>Everyone plays and earns XP!</p>
      <ul>
        <li>✅ Earn XP and level up</li>
        <li>✅ Track your progress</li>
        <li>✅ Compete with friends</li>
      </ul>
      <button v-if="!$page.props.auth.user">
        Login to Unlock Multiplayer
      </button>
    </div>
  </div>
</template>
```

### 2. Session Join Flow
- **Guest joining multiplayer**: Clear "Login Required" message
- **XP preview**: Show how much XP they could earn
- **Quick registration**: Streamlined signup process
- **Guest mode explanation**: What they can/can't do

### 3. In-Game XP Teasers
- **Turn notifications**: "Login to earn XP for this turn!"
- **Game completion**: "You could have earned 50 XP - Sign up now!"
- **Leaderboard teasers**: Show where they'd rank if registered

## Security & Validation

### Session Security
- Rate limiting on session creation
- Code uniqueness validation
- Session expiration handling
- Player count limits

### Input Validation
- Player name sanitization
- Mode validation
- Turn order verification
- Card draw validation

This documentation focuses on fixing the core session management while keeping XP as a clear incentive for registration without implementing the full XP system yet.
