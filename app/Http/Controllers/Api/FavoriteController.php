<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * お気に入り一覧
     * GET /api/v1/favorites
     */
    public function index(Request $request): JsonResponse
    {
        $favorites = Favorite::where('user_id', $request->user()->id)
            ->with(['quote.work', 'quote.location'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($favorites);
    }

    /**
     * お気に入り追加
     * POST /api/v1/favorites
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quote_id' => ['required', 'exists:quotes,id'],
        ]);

        $favorite = Favorite::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'quote_id' => $validated['quote_id'],
            ]
        );

        $favorite->load(['quote.work', 'quote.location']);

        return response()->json($favorite, 201);
    }

    /**
     * お気に入り削除
     * DELETE /api/v1/favorites/{favorite}
     */
    public function destroy(Favorite $favorite): JsonResponse
    {
        $this->authorize('delete', $favorite);

        $favorite->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
