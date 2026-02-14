<?php

use App\Http\Controllers\ExploreController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| LoveAtlas - Blade ページルーティング
| Vue コンポーネントは各 Blade テンプレート内の #app に自動マウント
|
*/

// --- 公開ページ ---
Route::get('/', function () {
    return view('home');
})->name('home');

// 検索・Explore
Route::get('/search', [SearchController::class, 'view'])->name('search');
Route::get('/explore', [ExploreController::class, 'index'])->name('explore');

// TODO: Step 4 - セリフ閲覧
// Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');

// TODO: Step 7 - 作品
// Route::get('/works', [WorkController::class, 'index'])->name('works.index');
// Route::get('/works/{work}', [WorkController::class, 'show'])->name('works.show');
// Route::get('/tags/{tag:slug}', [TagController::class, 'show'])->name('tags.show');

// --- 認証ページ (Breeze) ---
require __DIR__.'/auth.php';

// --- 要認証ページ ---
Route::middleware('auth')->group(function () {
    // 訪問記録追加
    Route::get('/locations/{location}/visits/create', [VisitController::class, 'create'])
        ->name('visits.create');

    // TODO: Step 5 - セリフ投稿
    // Route::get('/quotes/new', [QuoteController::class, 'create'])->name('quotes.create');

    // TODO: Step 7 - プロフィール
    // Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    // Route::get('/profile/favorites', [ProfileController::class, 'favorites'])->name('profile.favorites');
    // Route::get('/profile/visits', [ProfileController::class, 'visits'])->name('profile.visits');
    // Route::get('/profile/posts', [ProfileController::class, 'posts'])->name('profile.posts');
    // Route::get('/profile/settings', [ProfileController::class, 'settings'])->name('profile.settings');
    // Route::get('/users/{user}', [ProfileController::class, 'show'])->name('users.show');
});

// --- 管理者ページ ---
// Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
//     // TODO: Step 8 - 管理画面
//     // Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
//     // Route::get('/quotes', [Admin\QuoteController::class, 'index'])->name('quotes.index');
//     // Route::get('/reports', [Admin\ReportController::class, 'index'])->name('reports.index');
//     // Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
//     // Route::get('/works', [Admin\WorkController::class, 'index'])->name('works.index');
//     // Route::get('/stats', [Admin\StatsController::class, 'index'])->name('stats.index');
// });
