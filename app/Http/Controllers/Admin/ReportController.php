<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Report::with([
            'reporter:id,username,display_name',
            'quote:id,quote_text,status',
            'reviewer:id,username',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $reports = $query->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 20));

        return response()->json($reports);
    }

    public function update(Request $request, Report $report): JsonResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(['reviewed', 'resolved', 'dismissed'])],
        ]);

        $report->update([
            'status' => $request->input('status'),
            'reviewed_by' => $request->user()->id,
            'resolved_at' => in_array($request->input('status'), ['resolved', 'dismissed']) ? now() : null,
        ]);

        return response()->json([
            'message' => '通報を処理しました。',
            'report' => $report->fresh(['reporter:id,username', 'reviewer:id,username']),
        ]);
    }
}
