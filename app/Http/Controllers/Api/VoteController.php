<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\Vote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    /**
     * いいねのトグル
     * POST /api/v1/quotes/{quote}/vote
     */
    public function toggle(Request $request, Quote $quote): JsonResponse
    {
        $user = $request->user();
        $vote = Vote::where('user_id', $user->id)
            ->where('quote_id', $quote->id)
            ->first();

        if ($vote) {
            $vote->delete();
            $liked = false;
        } else {
            Vote::create([
                'user_id' => $user->id,
                'quote_id' => $quote->id,
                'value' => 1,
            ]);
            $liked = true;
        }

        $quote->refresh();

        return response()->json([
            'liked' => $liked,
            'likes_count' => $quote->likes_count ?? 0,
        ]);
    }
}
