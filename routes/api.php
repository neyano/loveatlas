<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Api;
use App\Http\Controllers\Api\AuthController;
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

// --- 認証ユーザー情報 (Laravel デフォルト) ---
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// --- v1 API ---
Route::prefix('v1')->group(function () {

    // ===== グループ B: 認証 API =====
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:password-reset');
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
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
  
    // 検索 API (公開)
    // TODO: Step 7
    // Route::get('/search', [Api\SearchController::class, 'index']);

    // 要認証 API
    Route::middleware('auth:sanctum')->group(function () {
        // セリフ投稿・編集 (TODO: Group C)
        // Route::post('/quotes', [Api\QuoteController::class, 'store']);
        // Route::put('/quotes/{quote}', [Api\QuoteController::class, 'update']);
        // Route::delete('/quotes/{quote}', [Api\QuoteController::class, 'destroy']);
        // Route::post('/quotes/{quote}/vote', [Api\VoteController::class, 'toggle']);

        // お気に入り (TODO: Group D)
        // Route::get('/favorites', [Api\FavoriteController::class, 'index']);
        // Route::post('/favorites', [Api\FavoriteController::class, 'store']);
        // Route::delete('/favorites/{quote}', [Api\FavoriteController::class, 'destroy']);

        // 訪問記録 (TODO: Group D)
        // Route::apiResource('visits', Api\VisitController::class);

        // 作品登録 (TODO: Group C)
        // Route::post('/works', [Api\WorkController::class, 'store']);

        // 通報 (Group E)
        Route::post('/reports', [Api\ReportController::class, 'store']);
    });

    // 管理者 API (Group E)
    // TODO: ログイン機能統合後に middleware(['auth:sanctum', 'admin']) に戻す
    Route::prefix('admin')->group(function () {
        Route::get('/quotes/pending', [Admin\QuoteController::class, 'pending']);
        Route::put('/quotes/{quote}/approve', [Admin\QuoteController::class, 'approve']);
        Route::put('/quotes/{quote}/reject', [Admin\QuoteController::class, 'reject']);
        Route::get('/reports', [Admin\ReportController::class, 'index']);
        Route::put('/reports/{report}', [Admin\ReportController::class, 'update']);
        Route::get('/stats', [Admin\StatsController::class, 'index']);
        Route::get('/users', [Admin\UserController::class, 'index']);
        Route::put('/users/{user}/role', [Admin\UserController::class, 'updateRole']);
        Route::put('/users/{user}/ban', [Admin\UserController::class, 'ban']);
        Route::get('/works', [Admin\WorkController::class, 'index']);
        Route::post('/works', [Admin\WorkController::class, 'store']);
        Route::put('/works/{work}', [Admin\WorkController::class, 'update']);
        Route::delete('/works/{work}', [Admin\WorkController::class, 'destroy']);
        Route::put('/works/{work}/approve', [Admin\WorkController::class, 'approve']);
        Route::get('/works/{work}/quotes', [Admin\WorkController::class, 'quotes']);
    });

});
