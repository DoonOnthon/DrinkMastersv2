<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\AdminUserController;

use App\Models\User;
use App\Models\Game;
use App\Models\Activity;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');

    Route::resource('games', GameController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Admin/Dashboard', [
            'users' => User::count(),
            'games' => Game::count(),
            'xpTotal' => User::sum('xp'),
        ]);
    })->name('dashboard');

    Route::get('/users', function (Request $request) {
        $search = $request->input('search');

        $users = User::query()
            ->when(
                $search,
                fn($query) =>
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            )
            ->select('id', 'name', 'email', 'xp', 'reports_count', 'is_admin', 'is_moderator', 'is_manager', 'is_suspended', 'created_at', 'last_login_at') // ✅ ADDED last_login_at
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'filters' => [
                'search' => $search,
            ],
        ]);
    })->name('users.index');

    Route::post('/users/{user}/toggle-role', function (Request $request, User $user) {
        $role = $request->input('role');

        if (!in_array($role, ['admin', 'moderator', 'manager'])) {
            abort(400, 'Invalid role');
        }

        if ($role === 'admin' && $user->id === auth()->id()) {
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

    Route::get('/users/{user}/activity', function (User $user) {
        $activities = $user->activities()
            ->latest()
            ->take(20)
            ->get(['id', 'description', 'created_at']);

        return Inertia::render('Admin/UserActivity', [
            'user' => $user->only('id', 'name', 'email'),
            'activities' => $activities,
        ]);
    })->name('users.activity');
});

require __DIR__ . '/auth.php';
