<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::query()
            ->when($search, fn ($query) =>
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
            )
            ->select('id', 'name', 'email', 'xp', 'is_admin', 'is_moderator', 'is_manager', 'is_suspended', 'created_at', 'last_login_at', 'reports_count')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'filters' => ['search' => $search],
        ]);
    }

    public function updateRoles(Request $request, User $user)
    {
        if ($user->id === Auth::id() && !$request->boolean('is_admin')) {
            return Redirect::back()->with('error', 'You cannot remove your own admin rights.');
        }

        $user->update([
            'is_admin' => $request->boolean('is_admin'),
            'is_moderator' => $request->boolean('is_moderator'),
            'is_manager' => $request->boolean('is_manager'),
        ]);

        return back();
    }

    public function toggleSuspension(User $user)
    {
        $user->is_suspended = !$user->is_suspended;
        $user->save();

        return back();
    }
}
