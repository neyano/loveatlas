<?php

namespace App\Http\Controllers;

use App\Models\Location;

class VisitController extends Controller
{
    /**
     * 訪問記録追加フォーム
     * GET /locations/{location}/visits/create
     */
    public function create(Location $location)
    {
        return view('visits.create', [
            'location' => $location,
        ]);
    }
}
