<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function pending(Request $request): JsonResponse
    {
        $quotes = Quote::where('status', 'pending')
            ->with(['user:id,username,display_name', 'work:id,title,type', 'location:id,name'])
            ->orderBy('created_at', 'asc')
            ->paginate($request->integer('per_page', 20));

        return response()->json($quotes);
    }

    public function approve(Quote $quote): JsonResponse
    {
        $quote->update(['status' => 'approved']);

        return response()->json([
            'message' => 'セリフを承認しました。',
            'quote' => $quote->load(['user:id,username', 'work:id,title']),
        ]);
    }

    public function reject(Request $request, Quote $quote): JsonResponse
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $quote->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'セリフを拒否しました。',
        ]);
    }
}
