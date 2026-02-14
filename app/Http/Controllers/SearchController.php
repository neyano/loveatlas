<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Quote;
use App\Models\Work;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * 検索結果ページ
     * GET /search?q=keyword
     */
    public function view(Request $request)
    {
        $keyword = $request->query('q', '');
        $results = [
            'quotes' => collect(),
            'works' => collect(),
            'locations' => collect(),
        ];

        if (! empty(trim($keyword))) {
            $searchTerm = '%' . trim($keyword) . '%';

            $results['quotes'] = Quote::approved()
                ->where(function ($query) use ($searchTerm) {
                    $query->where('quote_text', 'like', $searchTerm)
                        ->orWhere('character_name', 'like', $searchTerm)
                        ->orWhere('scene_description', 'like', $searchTerm);
                })
                ->with(['work', 'location', 'user'])
                ->limit(10)
                ->get();

            $results['works'] = Work::where('title', 'like', $searchTerm)
                ->withCount('quotes')
                ->limit(10)
                ->get();

            $results['locations'] = Location::where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', $searchTerm)
                    ->orWhere('address', 'like', $searchTerm);
            })
                ->withCount('quotes')
                ->limit(10)
                ->get();
        }

        return view('search.index', [
            'keyword' => $keyword,
            'results' => $results,
        ]);
    }
}
