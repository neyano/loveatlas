<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * プロフィール画面 (タブ: 投稿/お気に入り/訪問)
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $tab = $request->query('tab', 'posts');

        $data = match ($tab) {
            'favorites' => [
                'items' => $user->favorites()
                    ->with(['work:id,title,type', 'location:id,name'])
                    ->latest('favorites.created_at')
                    ->paginate(12),
            ],
            'visits' => [
                'items' => $user->visits()
                    ->with('location:id,name,latitude,longitude')
                    ->latest('visited_at')
                    ->paginate(12),
            ],
            default => [
                'items' => $user->quotes()
                    ->with(['work:id,title,type', 'location:id,name'])
                    ->latest()
                    ->paginate(12),
            ],
        };

        // 統計情報
        $stats = [
            'posts_count' => $user->quotes()->count(),
            'favorites_count' => $user->favorites()->count(),
            'visits_count' => $user->visits()->count(),
        ];

        return view('profile.index', [
            'user' => $user,
            'tab' => $tab,
            'items' => $data['items'],
            'stats' => $stats,
        ]);
    }

    /**
     * プロフィール設定画面
     */
    public function settings(Request $request): View
    {
        return view('profile.settings', [
            'user' => $request->user(),
        ]);
    }

    /**
     * プロフィール更新
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();
        $user->display_name = $validated['display_name'];
        $user->bio = $validated['bio'] ?? $user->bio;

        if ($request->hasFile('avatar')) {
            $imageService = app(ImageService::class);
            $user->avatar_path = $imageService->storeAvatar($request->file('avatar'));
        }

        $user->save();

        return redirect()->route('profile.settings')
            ->with('success', 'プロフィールを更新しました');
    }

    /**
     * 公開プロフィール
     */
    public function show(User $user): View
    {
        $quotes = $user->quotes()
            ->where('status', 'approved')
            ->with(['work:id,title,type', 'location:id,name'])
            ->latest()
            ->paginate(12);

        $stats = [
            'posts_count' => $user->quotes()->where('status', 'approved')->count(),
        ];

        return view('users.show', [
            'profileUser' => $user,
            'quotes' => $quotes,
            'stats' => $stats,
        ]);
    }
}
