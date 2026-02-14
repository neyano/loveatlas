<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Models\Report;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function store(StoreReportRequest $request): JsonResponse
    {
        $report = Report::create([
            'reporter_id' => $request->user()->id,
            'quote_id' => $request->input('quote_id'),
            'reason' => $request->input('reason'),
            'description' => $request->input('description'),
        ]);

        return response()->json([
            'message' => '通報を受け付けました。',
            'report' => $report,
        ], 201);
    }
}
