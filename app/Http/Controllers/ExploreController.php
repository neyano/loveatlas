<?php

namespace App\Http\Controllers;

use App\Models\Quote;

class ExploreController extends Controller
{
    /**
     * Explore ページ
     * GET /explore
     */
    public function index()
    {
        $popular = Quote::approved()
            ->with(['work', 'location', 'user'])
            ->orderByDesc('likes_count')
            ->limit(12)
            ->get();

        $recent = Quote::approved()
            ->with(['work', 'location', 'user'])
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        return view('explore.index', [
            'popular' => $popular,
            'recent' => $recent,
        ]);
    }
}
