<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Work;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $works = Work::withCount('quotes')
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json($works);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:movie,anime,drama,novel,game,other',
            'title_original' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:2100',
            'country' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:2000',
            'external_url' => 'nullable|url|max:500',
        ]);

        $work = Work::create($validated);

        return response()->json([
            'message' => '作品を登録しました。',
            'data' => $work,
        ], 201);
    }

    public function update(Request $request, Work $work): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|in:movie,anime,drama,novel,game,other',
            'title_original' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:2100',
            'country' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:2000',
            'external_url' => 'nullable|url|max:500',
        ]);

        $work->update($validated);

        return response()->json([
            'message' => '作品を更新しました。',
            'data' => $work,
        ]);
    }

    public function destroy(Work $work): JsonResponse
    {
        if ($work->quotes()->exists()) {
            return response()->json([
                'message' => 'セリフが紐づいているため削除できません。',
            ], 409);
        }

        $work->delete();

        return response()->json([
            'message' => '作品を削除しました。',
        ]);
    }

    public function quotes(Work $work): JsonResponse
    {
        $quotes = $work->quotes()
            ->with(['user:id,username,display_name', 'location:id,name'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $quotes]);
    }

    public function approve(Work $work): JsonResponse
    {
        $work->update(['is_approved' => !$work->is_approved]);

        return response()->json([
            'message' => $work->is_approved ? '承認しました。' : '承認を取り消しました。',
            'data' => $work,
        ]);
    }
}
