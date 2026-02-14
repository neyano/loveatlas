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
}
