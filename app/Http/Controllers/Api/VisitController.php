<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVisitRequest;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VisitController extends Controller
{
    /**
     * 訪問記録一覧
     * GET /api/v1/visits
     */
    public function index(Request $request): JsonResponse
    {
        $visits = $request->user()
            ->visits()
            ->with('location')
            ->orderByDesc('visited_at')
            ->paginate(20);

        return response()->json($visits);
    }

    /**
     * 訪問記録追加
     * POST /api/v1/visits
     */
    public function store(StoreVisitRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('visits', 'public');
        }

        $visit = Visit::create([
            'user_id' => $request->user()->id,
            'location_id' => $validated['location_id'],
            'visited_at' => $validated['visited_at'],
            'note' => $validated['notes'] ?? null,
            'rating' => $validated['rating'] ?? null,
            'photo_path' => $photoPath,
        ]);

        $visit->load('location');

        return response()->json($visit, 201);
    }

    /**
     * 訪問記録更新
     * PUT /api/v1/visits/{visit}
     */
    public function update(Request $request, Visit $visit): JsonResponse
    {
        $this->authorize('update', $visit);

        $validated = $request->validate([
            'visited_at' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $visit->visited_at = $validated['visited_at'] ?? $visit->visited_at;
        $visit->note = $validated['notes'] ?? $visit->note;
        $visit->rating = $validated['rating'] ?? $visit->rating;

        if ($request->hasFile('photo')) {
            if ($visit->photo_path) {
                Storage::disk('public')->delete($visit->photo_path);
            }
            $visit->photo_path = $request->file('photo')->store('visits', 'public');
        }

        $visit->save();
        $visit->load('location');

        return response()->json($visit);
    }

    /**
     * 訪問記録削除
     * DELETE /api/v1/visits/{visit}
     */
    public function destroy(Visit $visit): JsonResponse
    {
        $this->authorize('delete', $visit);

        if ($visit->photo_path) {
            Storage::disk('public')->delete($visit->photo_path);
        }

        $visit->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
