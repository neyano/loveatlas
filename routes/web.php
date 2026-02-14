<?php

use App\Http\Controllers\Admin;
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

// TODO: Step 4 - セリフ閲覧
// Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');

// TODO: Step 7 - 作品・検索・Explore
// Route::get('/works', [WorkController::class, 'index'])->name('works.index');
// Route::get('/works/{work}', [WorkController::class, 'show'])->name('works.show');
// Route::get('/search', [SearchController::class, 'index'])->name('search');
// Route::get('/tags/{tag:slug}', [TagController::class, 'show'])->name('tags.show');
// Route::get('/explore', [ExploreController::class, 'index'])->name('explore');

// --- 認証ページ (Breeze) ---
require __DIR__.'/auth.php';

// --- 要認証ページ ---
// Route::middleware('auth')->group(function () {
//     // TODO: Step 5 - セリフ投稿
//     // Route::get('/quotes/new', [QuoteController::class, 'create'])->name('quotes.create');
//
//     // TODO: Step 7 - プロフィール
//     // Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
//     // Route::get('/profile/favorites', [ProfileController::class, 'favorites'])->name('profile.favorites');
//     // Route::get('/profile/visits', [ProfileController::class, 'visits'])->name('profile.visits');
//     // Route::get('/profile/posts', [ProfileController::class, 'posts'])->name('profile.posts');
//     // Route::get('/profile/settings', [ProfileController::class, 'settings'])->name('profile.settings');
//     // Route::get('/users/{user}', [ProfileController::class, 'show'])->name('users.show');
// });

// --- 管理者ページ (Group E) ---
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () { return view('admin.dashboard'); })->name('dashboard');
    Route::get('/quotes', function () { return view('admin.quotes.index'); })->name('quotes.index');
    Route::get('/reports', function () { return view('admin.reports.index'); })->name('reports.index');
    Route::get('/users', function () { return view('admin.users.index'); })->name('users.index');
});
