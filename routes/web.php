<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\PlaySessionController;

use App\Models\User;
use App\Models\Game;
use App\Models\Activity;

// 🏠 Public features
Route::get('/', fn() => Inertia::render('Home'))->name('home');

// 🌐 Public play session access (no login needed)
Route::get('/play/{code}', [PlaySessionController::class, 'show'])->name('play.session.show');
Route::post('/play/{code}/join', [PlaySessionController::class, 'join'])->name('play.session.join');
Route::get('/api/play/{code}', [PlaySessionController::class, 'api'])->name('play.session.api');
Route::post('/play/{code}/next-turn', [PlaySessionController::class, 'nextTurn'])->name('play.session.next-turn');
Route::post('/play/{code}/draw', [PlaySessionController::class, 'draw'])->name('play.session.draw');
Route::post('/play/{code}/leave', [PlaySessionController::class, 'leave'])->name('play.session.leave');
Route::delete('/play/{code}', [PlaySessionController::class, 'destroy'])->name('play.session.destroy');
Route::get('/games/{id}/cards', [GameController::class, 'cards'])->name('games.cards');

// 👤 Logged-in user features (with email verification)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');

    // 🎲 Game browsing/playing
    Route::resource('games', GameController::class);
    Route::get('/games/{id}/play', [GameController::class, 'play'])->name('games.play');

    // 🧑 Profile management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 🧑‍💼 Host a play session
    Route::post('/games/{game}/play-session', [PlaySessionController::class, 'store'])->name('play.session.store');
});

// 🔐 Admin-only routes
Route::middleware(['auth', 'verified', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::get('/users', function (Request $request) {
        $search = $request->input('search');

        $users = User::query()
            ->when(
                $search,
                fn($query) =>
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            )
            ->select('id', 'name', 'email', 'xp', 'reports_count', 'is_admin', 'is_moderator', 'is_manager', 'is_suspended', 'created_at', 'last_login_at')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'filters' => ['search' => $search],
        ]);
    })->name('users.index');

    Route::get('/users/{user}/activity', function (Request $request, User $user) {
        $activities = $user->activities()
            ->when($request->date, fn($query, $date) => $query->whereDate('created_at', $date))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/UserActivity', [
            'user' => $user->only('id', 'name', 'email'),
            'activities' => $activities,
            'filters' => ['date' => $request->date],
        ]);
    })->name('users.activity');

    Route::get('/users/{user}/activity/export', function (User $user, Request $request) {
        $filename = "activity_user_{$user->id}.csv";

        $activities = $user->activities()
            ->when($request->date, fn($query, $date) => $query->whereDate('created_at', $date))
            ->latest()
            ->get(['description', 'created_at', 'type']);

        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""];

        $callback = function () use ($activities) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Description', 'Type', 'Created At']);

            foreach ($activities as $activity) {
                fputcsv($handle, [
                    $activity->description,
                    $activity->type,
                    $activity->created_at->toDateTimeString(),
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    })->name('users.activity.export');

    Route::post('/users/{user}/toggle-role', function (Request $request, User $user) {
        $role = $request->input('role');

        if (!in_array($role, ['admin', 'moderator', 'manager'])) {
            abort(400, 'Invalid role');
        }

        if ($role === 'admin' && $user->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot demote yourself.']);
        }

        $column = "is_{$role}";
        $user->$column = !$user->$column;
        $user->save();

        return back();
    })->name('users.toggleRole');

    Route::post('/users/{user}/toggle-suspend', function (User $user) {
        $user->is_suspended = !$user->is_suspended;
        $user->save();

        return back();
    })->name('users.toggleSuspend');
});

require __DIR__ . '/auth.php';
