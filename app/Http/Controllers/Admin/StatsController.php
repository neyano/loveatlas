<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\Report;
use App\Models\User;
use App\Models\Visit;
use App\Models\Work;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'users' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
            ],
            'quotes' => [
                'total' => Quote::count(),
                'approved' => Quote::where('status', 'approved')->count(),
                'pending' => Quote::where('status', 'pending')->count(),
                'rejected' => Quote::where('status', 'rejected')->count(),
            ],
            'works' => [
                'total' => Work::count(),
                'approved' => Work::where('is_approved', true)->count(),
            ],
            'reports' => [
                'total' => Report::count(),
                'open' => Report::where('status', 'open')->count(),
            ],
            'visits' => [
                'total' => Visit::count(),
            ],
        ]);
    }
}
