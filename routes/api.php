<?php

use App\Http\Controllers\Api\FavoriteController as ApiFavoriteController;
use App\Http\Controllers\Api\SearchController as ApiSearchController;
use App\Http\Controllers\Api\VisitController as ApiVisitController;
use App\Http\Controllers\Api\VoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| LoveAtlas - REST API (prefix: /api)
| Sanctum セッション認証 (SPA)
|
*/

// --- 認証ユーザー情報 ---
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// --- v1 API ---
Route::prefix('v1')->group(function () {

    // 認証 API
    Route::prefix('auth')->group(function () {
        Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
            return $request->user();
        });
    });

    // 検索 API (公開)
    Route::get('/search', [ApiSearchController::class, 'index']);

    // 要認証 API
    Route::middleware('auth:sanctum')->group(function () {
        // いいね
        Route::post('/quotes/{quote}/vote', [VoteController::class, 'toggle']);

        // お気に入り
        Route::get('/favorites', [ApiFavoriteController::class, 'index']);
        Route::post('/favorites', [ApiFavoriteController::class, 'store']);
        Route::delete('/favorites/{favorite}', [ApiFavoriteController::class, 'destroy']);

        // 訪問記録
        Route::get('/visits', [ApiVisitController::class, 'index']);
        Route::post('/visits', [ApiVisitController::class, 'store']);
        Route::put('/visits/{visit}', [ApiVisitController::class, 'update']);
        Route::delete('/visits/{visit}', [ApiVisitController::class, 'destroy']);
    });

    // 地図データ API (公開)
    // TODO: Step 4
    // Route::get('/map/quotes', [Api\MapController::class, 'quotes']);
    // Route::get('/locations/{location}', [Api\LocationController::class, 'show']);
    // Route::get('/locations/{location}/quotes', [Api\LocationController::class, 'quotes']);

    // セリフ API (公開)
    // TODO: Step 4
    // Route::get('/quotes', [Api\QuoteController::class, 'index']);
    // Route::get('/quotes/{quote}', [Api\QuoteController::class, 'show']);

    // 作品 API (公開)
    // TODO: Step 5
    // Route::get('/works', [Api\WorkController::class, 'index']);
    // Route::get('/works/search', [Api\WorkController::class, 'search']);

    // 管理者 API
    // Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    //     // Route::get('/quotes/pending', [Api\Admin\QuoteController::class, 'pending']);
    //     // Route::put('/quotes/{quote}/approve', [Api\Admin\QuoteController::class, 'approve']);
    //     // Route::put('/quotes/{quote}/reject', [Api\Admin\QuoteController::class, 'reject']);
    //     // Route::get('/reports', [Api\Admin\ReportController::class, 'index']);
    //     // Route::put('/reports/{report}', [Api\Admin\ReportController::class, 'update']);
    //     // Route::get('/stats', [Api\Admin\StatsController::class, 'index']);
    // });
});
