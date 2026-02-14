<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Quote;
use App\Models\Work;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * 横断検索
     * GET /api/v1/search?q=keyword&type=all
     */
    public function index(Request $request): JsonResponse
    {
        $keyword = $request->query('q', '');
        $type = $request->query('type', 'all');

        if (empty(trim($keyword))) {
            return response()->json([
                'quotes' => [],
                'works' => [],
                'locations' => [],
            ]);
        }

        $searchTerm = '%' . trim($keyword) . '%';
        $quotes = [];
        $works = [];
        $locations = [];

        if (in_array($type, ['all', 'quotes'])) {
            $quotes = Quote::approved()
                ->where(function ($query) use ($searchTerm) {
                    $query->where('quote_text', 'like', $searchTerm)
                        ->orWhere('character_name', 'like', $searchTerm)
                        ->orWhere('scene_description', 'like', $searchTerm);
                })
                ->with(['work', 'location'])
                ->limit(10)
                ->get();
        }

        if (in_array($type, ['all', 'works'])) {
            $works = Work::where('title', 'like', $searchTerm)
                ->withCount('quotes')
                ->limit(10)
                ->get();
        }

        if (in_array($type, ['all', 'locations'])) {
            $locations = Location::where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', $searchTerm)
                    ->orWhere('address', 'like', $searchTerm);
            })
                ->withCount('quotes')
                ->limit(10)
                ->get();
        }

        return response()->json([
            'quotes' => $quotes,
            'works' => $works,
            'locations' => $locations,
        ]);
    }
}
