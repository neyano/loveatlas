<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\ProfileController;
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

// --- 認証ページ (Breeze API ルート) ---
require __DIR__.'/auth.php';

// --- 認証ページ (ビュー表示) ---
Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login.show');
    Route::view('/register', 'auth.register')->name('register.show');
    Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
});

// --- 要認証ページ ---
Route::middleware('auth')->group(function () {
    // 訪問記録追加
    Route::get('/locations/{location}/visits/create', [VisitController::class, 'create'])
        ->name('visits.create');

    // プロフィール (グループ B)
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/settings', [ProfileController::class, 'settings'])->name('profile.settings');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // TODO: Step 5 - セリフ投稿
    // Route::get('/quotes/new', [QuoteController::class, 'create'])->name('quotes.create');

    // TODO: Step 7 - プロフィール (追加分があればここに)
});

// --- 公開プロフィール ---
Route::get('/users/{user}', [ProfileController::class, 'show'])->name('users.show');

// --- 管理者ページ (Group E) ---
// TODO: ログイン機能統合後に middleware(['auth', 'admin']) に戻す
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () { return view('admin.dashboard'); })->name('dashboard');
    Route::get('/quotes', function () { return view('admin.quotes.index'); })->name('quotes.index');
    Route::get('/reports', function () { return view('admin.reports.index'); })->name('reports.index');
    Route::get('/users', function () { return view('admin.users.index'); })->name('users.index');
    Route::get('/works', function () { return view('admin.works.index'); })->name('works.index');
});
