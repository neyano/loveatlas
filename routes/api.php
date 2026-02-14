<?php

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

    // 検索 API (公開)
    // TODO: Step 7
    // Route::get('/search', [Api\SearchController::class, 'index']);

    // 要認証 API
    // Route::middleware('auth:sanctum')->group(function () {
    //     // セリフ投稿・編集
    //     // Route::post('/quotes', [Api\QuoteController::class, 'store']);
    //     // Route::put('/quotes/{quote}', [Api\QuoteController::class, 'update']);
    //     // Route::delete('/quotes/{quote}', [Api\QuoteController::class, 'destroy']);
    //     // Route::post('/quotes/{quote}/vote', [Api\VoteController::class, 'toggle']);

    //     // お気に入り
    //     // Route::get('/favorites', [Api\FavoriteController::class, 'index']);
    //     // Route::post('/favorites', [Api\FavoriteController::class, 'store']);
    //     // Route::delete('/favorites/{quote}', [Api\FavoriteController::class, 'destroy']);

    //     // 訪問記録
    //     // Route::apiResource('visits', Api\VisitController::class);

    //     // 作品登録
    //     // Route::post('/works', [Api\WorkController::class, 'store']);

    //     // 通報
    //     // Route::post('/reports', [Api\ReportController::class, 'store']);
    // });

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
