<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::withCount(['quotes', 'votes', 'visits']);

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 20));

        return response()->json($users);
    }

    public function updateRole(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role' => ['required', Rule::in(['user', 'moderator', 'admin'])],
        ]);

        $user->update(['role' => $request->input('role')]);

        return response()->json([
            'message' => '権限を変更しました。',
            'user' => $user,
        ]);
    }

    public function ban(User $user): JsonResponse
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? '有効化' : 'BAN';

        return response()->json([
            'message' => "ユーザーを{$status}しました。",
            'user' => $user,
        ]);
    }
}
