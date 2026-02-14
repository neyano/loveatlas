# LoveAtlas - アーキテクチャ設計 (Laravel)

## 1. 全体構成

```
┌─────────────────────────────────────────────────────────┐
│  クライアント (ブラウザ)                                   │
│  ┌───────────┬──────────────┬───────────────┐           │
│  │ Leaflet   │ Vue 3        │  CSS          │           │
│  │ (地図)     │ (Vite経由)   │  (Vite経由)    │           │
│  └───────────┴──────────────┴───────────────┘           │
└───────────────────────┬─────────────────────────────────┘
                        │ HTTP (Blade HTML / JSON API)
┌───────────────────────┴─────────────────────────────────┐
│  Apache (XAMPP)                                          │
│  ┌────────────────────────────────────────────┐         │
│  │  Laravel 11.x                               │         │
│  │  ┌──────────┐  ┌────────────┐  ┌────────┐ │         │
│  │  │ Router   │→│ Middleware  │→│Controller│ │         │
│  │  └──────────┘  └────────────┘  └────┬───┘ │         │
│  │                                      │     │         │
│  │  ┌──────────┐  ┌────────────┐  ┌────┴───┐ │         │
│  │  │ Blade    │←│ Service    │←│Eloquent │ │         │
│  │  │ View     │  │ (任意)     │  │ Model   │ │         │
│  │  └──────────┘  └────────────┘  └────────┘ │         │
│  └────────────────────────────────────────────┘         │
└───────────────────────┬─────────────────────────────────┘
                        │ Eloquent (PDO)
┌───────────────────────┴─────────────────────────────────┐
│  MariaDB 10.4                                            │
└─────────────────────────────────────────────────────────┘
```

### リクエストフロー

```
1. ブラウザ → Apache
2. .htaccess でリライト → public/index.php (Laravel エントリーポイント)
3. Laravel Bootstrap (サービスプロバイダ登録、ミドルウェアスタック構築)
4. Router がURLを解析 → routes/web.php or routes/api.php
5. Middleware 実行 (認証、CSRF、レート制限等)
6. Controller がリクエスト処理
   - FormRequest でバリデーション
   - Service 層でビジネスロジック (必要な場合)
   - Eloquent Model でDB操作
7. Web → Blade テンプレートレンダリング
   API → JsonResponse で返却
```

---

## 2. ディレクトリ構成

```
loveatlas/
│
├── app/
│   ├── Console/
│   │   └── Commands/                    ← カスタム Artisan コマンド
│   │       ├── SyncLikesCount.php       ← いいね数同期バッチ
│   │       └── SyncTagUsageCount.php    ← タグ使用数同期バッチ
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── HomeController.php       ← ホーム (地図ビュー)
│   │   │   ├── QuoteController.php      ← セリフ CRUD (Web)
│   │   │   ├── WorkController.php       ← 作品ページ
│   │   │   ├── ProfileController.php    ← ユーザープロフィール
│   │   │   ├── SearchController.php     ← 検索
│   │   │   ├── ExploreController.php    ← 人気・新着ブラウズ
│   │   │   ├── TagController.php        ← タグ別一覧
│   │   │   │
│   │   │   ├── Api/                     ← API コントローラー
│   │   │   │   ├── QuoteApiController.php
│   │   │   │   ├── MapApiController.php
│   │   │   │   ├── FavoriteApiController.php
│   │   │   │   ├── VisitApiController.php
│   │   │   │   ├── VoteApiController.php
│   │   │   │   ├── SearchApiController.php
│   │   │   │   └── WorkApiController.php
│   │   │   │
│   │   │   └── Admin/                   ← 管理画面コントローラー
│   │   │       ├── DashboardController.php
│   │   │       ├── QuoteModController.php
│   │   │       ├── ReportController.php
│   │   │       ├── UserController.php
│   │   │       └── WorkController.php
│   │   │
│   │   ├── Middleware/
│   │   │   └── EnsureUserIsAdmin.php    ← 管理者権限チェック
│   │   │
│   │   └── Requests/                    ← FormRequest (バリデーション)
│   │       ├── StoreQuoteRequest.php
│   │       ├── UpdateQuoteRequest.php
│   │       ├── StoreWorkRequest.php
│   │       ├── StoreVisitRequest.php
│   │       └── StoreReportRequest.php
│   │
│   ├── Models/                          ← Eloquent モデル
│   │   ├── User.php
│   │   ├── Quote.php
│   │   ├── Work.php
│   │   ├── Location.php
│   │   ├── Tag.php
│   │   ├── Favorite.php
│   │   ├── Visit.php
│   │   ├── Vote.php
│   │   └── Report.php
│   │
│   ├── Observers/                       ← モデルオブザーバー
│   │   └── VoteObserver.php             ← いいね作成/削除時に likes_count 同期
│   │
│   ├── Policies/                        ← 認可ポリシー
│   │   ├── QuotePolicy.php
│   │   └── VisitPolicy.php
│   │
│   ├── Services/                        ← ビジネスロジック
│   │   ├── GeocodingService.php         ← Nominatim API ラッパー
│   │   └── SearchService.php            ← 検索ロジック
│   │
│   └── Providers/
│       └── AppServiceProvider.php       ← Observer 登録等
│
├── bootstrap/
│   └── app.php                          ← アプリ初期化、ミドルウェア登録
│
├── config/                              ← Laravel 設定ファイル群
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   ├── filesystems.php                  ← Storage ディスク設定
│   ├── sanctum.php
│   └── ...
│
├── database/
│   ├── migrations/                      ← マイグレーションファイル
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2026_02_14_000001_create_works_table.php
│   │   ├── 2026_02_14_000002_create_locations_table.php
│   │   ├── 2026_02_14_000003_create_quotes_table.php
│   │   ├── 2026_02_14_000004_create_tags_table.php
│   │   ├── 2026_02_14_000005_create_quote_tag_table.php
│   │   ├── 2026_02_14_000006_create_favorites_table.php
│   │   ├── 2026_02_14_000007_create_visits_table.php
│   │   ├── 2026_02_14_000008_create_votes_table.php
│   │   └── 2026_02_14_000009_create_reports_table.php
│   │
│   ├── seeders/                         ← 初期データ
│   │   ├── DatabaseSeeder.php
│   │   ├── AdminUserSeeder.php
│   │   ├── WorkSeeder.php
│   │   ├── LocationSeeder.php
│   │   ├── QuoteSeeder.php
│   │   └── TagSeeder.php
│   │
│   └── factories/                       ← テスト用ファクトリ
│       ├── QuoteFactory.php
│       ├── WorkFactory.php
│       └── LocationFactory.php
│
├── public/                              ← ドキュメントルート
│   ├── index.php                        ← Laravel エントリーポイント
│   ├── .htaccess
│   └── build/                           ← Vite ビルド出力 (自動生成)
│
├── resources/                           ← フロントエンドソース
│   ├── views/                           ← Blade テンプレート
│   │   ├── layouts/
│   │   │   ├── app.blade.php            ← メインレイアウト
│   │   │   ├── admin.blade.php          ← 管理画面レイアウト
│   │   │   └── minimal.blade.php        ← 最小レイアウト (ログイン等)
│   │   │
│   │   ├── components/                  ← Blade コンポーネント
│   │   │   ├── quote-card.blade.php
│   │   │   ├── work-card.blade.php
│   │   │   ├── map-popup.blade.php
│   │   │   └── pagination.blade.php
│   │   │
│   │   ├── home/
│   │   │   └── index.blade.php          ← 地図メインビュー
│   │   ├── auth/                        ← Laravel Breeze が生成
│   │   │   ├── login.blade.php
│   │   │   ├── register.blade.php
│   │   │   ├── forgot-password.blade.php
│   │   │   └── reset-password.blade.php
│   │   ├── quotes/
│   │   │   ├── show.blade.php
│   │   │   ├── create.blade.php
│   │   │   └── edit.blade.php
│   │   ├── works/
│   │   │   ├── index.blade.php
│   │   │   └── show.blade.php
│   │   ├── profile/
│   │   │   ├── show.blade.php
│   │   │   ├── settings.blade.php
│   │   │   ├── favorites.blade.php
│   │   │   ├── visits.blade.php
│   │   │   └── posts.blade.php
│   │   ├── search/
│   │   │   └── results.blade.php
│   │   ├── explore/
│   │   │   └── index.blade.php
│   │   ├── tags/
│   │   │   └── show.blade.php
│   │   ├── admin/
│   │   │   ├── dashboard.blade.php
│   │   │   ├── quotes.blade.php
│   │   │   ├── reports.blade.php
│   │   │   ├── users.blade.php
│   │   │   └── works.blade.php
│   │   └── errors/
│   │       ├── 404.blade.php
│   │       ├── 403.blade.php
│   │       └── 500.blade.php
│   │
│   ├── css/
│   │   ├── app.css                      ← メインCSS (Vite で処理)
│   │   └── map.css                      ← 地図関連
│   │
│   └── js/
│       ├── app.js                       ← Vue アプリ初期化 (Vite エントリーポイント)
│       ├── api.js                       ← axios インスタンス (CSRF自動付与)
│       ├── stores/                      ← Pinia ストア
│       │   ├── auth.js                  ← 認証状態管理
│       │   └── map.js                   ← 地図状態管理 (フィルター、表示範囲)
│       ├── components/                  ← Vue コンポーネント
│       │   ├── MapView.vue              ← Leaflet 地図 (メインコンポーネント)
│       │   ├── MapPopup.vue             ← 地図ポップアップ
│       │   ├── QuoteCard.vue            ← セリフカード
│       │   ├── QuoteForm.vue            ← セリフ投稿フォーム
│       │   ├── SidePanel.vue            ← サイドパネル (PC)
│       │   ├── BottomSheet.vue          ← ボトムシート (モバイル)
│       │   ├── SearchBar.vue            ← 検索バー (オートコンプリート)
│       │   ├── WorkAutocomplete.vue     ← 作品選択オートコンプリート
│       │   ├── LikeButton.vue           ← いいねボタン
│       │   ├── FavoriteButton.vue       ← お気に入りボタン
│       │   ├── FilterPanel.vue          ← フィルターパネル
│       │   ├── ToastNotification.vue    ← トースト通知
│       │   └── ModalDialog.vue          ← モーダル
│       └── composables/                 ← Vue Composables (再利用ロジック)
│           ├── useMap.js                ← Leaflet 操作のカプセル化
│           ├── useQuotes.js             ← セリフ取得・フィルタリング
│           └── useDebounce.js           ← デバウンスユーティリティ
│
├── routes/
│   ├── web.php                          ← Web ルート (Blade 返却)
│   ├── api.php                          ← API ルート (JSON 返却)
│   └── console.php                      ← Artisan コマンドスケジュール
│
├── storage/
│   ├── app/
│   │   └── public/                      ← ユーザーアップロード画像
│   │       ├── avatars/
│   │       ├── quotes/
│   │       └── visits/
│   ├── framework/                       ← キャッシュ・セッション・ビュー
│   └── logs/
│       └── laravel.log
│
├── tests/
│   ├── Feature/                         ← 機能テスト
│   │   ├── Auth/
│   │   ├── Api/
│   │   └── Admin/
│   └── Unit/                            ← 単体テスト
│       ├── Models/
│       └── Services/
│
├── .env                                 ← 環境変数 (Git管理外)
├── .env.example                         ← 環境変数テンプレート
├── artisan                              ← Artisan CLI
├── composer.json
├── package.json                         ← npm 依存 (Vite, Leaflet 等)
├── vite.config.js                       ← Vite 設定
└── .gitignore
```

---

## 3. ルーティング設計

### 3.1 Web ルート (routes/web.php)

```php
use App\Http\Controllers\*;

// 公開ページ
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
Route::get('/works', [WorkController::class, 'index'])->name('works.index');
Route::get('/works/{work}', [WorkController::class, 'show'])->name('works.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/explore', [ExploreController::class, 'index'])->name('explore');
Route::get('/tags/{tag:slug}', [TagController::class, 'show'])->name('tags.show');
Route::get('/users/{user}', [ProfileController::class, 'publicShow'])->name('users.show');

// 認証必須
Route::middleware('auth')->group(function () {
    Route::get('/quotes/create', [QuoteController::class, 'create'])->name('quotes.create');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/favorites', [ProfileController::class, 'favorites']);
    Route::get('/profile/visits', [ProfileController::class, 'visits']);
    Route::get('/profile/posts', [ProfileController::class, 'posts']);
    Route::get('/profile/settings', [ProfileController::class, 'settings']);
});

// 管理者
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/quotes', [Admin\QuoteModController::class, 'index'])->name('quotes');
    Route::get('/reports', [Admin\ReportController::class, 'index'])->name('reports');
    Route::get('/users', [Admin\UserController::class, 'index'])->name('users');
    Route::get('/works', [Admin\WorkController::class, 'index'])->name('works');
});

// Breeze 認証ルート (自動登録)
require __DIR__.'/auth.php';
```

### 3.2 API ルート (routes/api.php)

```php
use App\Http\Controllers\Api\*;

// 認証不要 API
Route::prefix('v1')->group(function () {
    Route::get('/map/quotes', [MapApiController::class, 'quotes']);
    Route::get('/map/clusters', [MapApiController::class, 'clusters']);
    Route::get('/quotes', [QuoteApiController::class, 'index']);
    Route::get('/quotes/{quote}', [QuoteApiController::class, 'show']);
    Route::get('/locations/{location}', [MapApiController::class, 'showLocation']);
    Route::get('/locations/{location}/quotes', [MapApiController::class, 'locationQuotes']);
    Route::get('/works', [WorkApiController::class, 'index']);
    Route::get('/works/{work}', [WorkApiController::class, 'show']);
    Route::get('/works/search', [WorkApiController::class, 'search']);
    Route::get('/search', [SearchApiController::class, 'index']);
});

// 認証必須 API
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('/quotes', [QuoteApiController::class, 'store']);
    Route::put('/quotes/{quote}', [QuoteApiController::class, 'update']);
    Route::delete('/quotes/{quote}', [QuoteApiController::class, 'destroy']);
    Route::post('/quotes/{quote}/vote', [VoteApiController::class, 'toggle']);

    Route::apiResource('favorites', FavoriteApiController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('visits', VisitApiController::class);

    Route::post('/works', [WorkApiController::class, 'store']);
    Route::post('/reports', [ReportApiController::class, 'store']);
});

// 管理者 API
Route::prefix('v1/admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/quotes/pending', [Admin\QuoteModController::class, 'pending']);
    Route::put('/quotes/{quote}/approve', [Admin\QuoteModController::class, 'approve']);
    Route::put('/quotes/{quote}/reject', [Admin\QuoteModController::class, 'reject']);
    Route::get('/reports', [Admin\ReportController::class, 'index']);
    Route::put('/reports/{report}', [Admin\ReportController::class, 'update']);
    Route::get('/stats', [Admin\DashboardController::class, 'stats']);
    Route::put('/users/{user}/role', [Admin\UserController::class, 'updateRole']);
    Route::put('/users/{user}/ban', [Admin\UserController::class, 'ban']);
});
```

---

## 4. 主要クラスの設計

### 4.1 FormRequest (バリデーション)

```php
// app/Http/Requests/StoreQuoteRequest.php
class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth ミドルウェアで認証済み
    }

    public function rules(): array
    {
        return [
            'quote_text'            => ['required', 'string', 'max:2000'],
            'work_id'               => ['required', 'exists:works,id'],
            'character_name'        => ['nullable', 'string', 'max:200'],
            'scene_description'     => ['nullable', 'string', 'max:5000'],
            'episode_info'          => ['nullable', 'string', 'max:100'],
            'language'              => ['nullable', 'string', 'max:10'],
            'location.name'         => ['required', 'string', 'max:255'],
            'location.latitude'     => ['required', 'numeric', 'between:-90,90'],
            'location.longitude'    => ['required', 'numeric', 'between:-180,180'],
            'location.address'      => ['nullable', 'string', 'max:500'],
            'tags'                  => ['nullable', 'array', 'max:10'],
            'tags.*'                => ['integer', 'exists:tags,id'],
            'photo'                 => ['nullable', 'image', 'max:5120'],  // 5MB
        ];
    }
}
```

### 4.2 Policy (認可)

```php
// app/Policies/QuotePolicy.php
class QuotePolicy
{
    public function update(User $user, Quote $quote): bool
    {
        return $user->id === $quote->user_id;
    }

    public function delete(User $user, Quote $quote): bool
    {
        return $user->id === $quote->user_id || $user->isAdmin();
    }
}
```

### 4.3 Observer (イベント)

```php
// app/Observers/VoteObserver.php
class VoteObserver
{
    public function created(Vote $vote): void
    {
        $vote->quote()->increment('likes_count');
    }

    public function deleted(Vote $vote): void
    {
        $vote->quote()->decrement('likes_count');
    }
}
```

### 4.4 Service (外部API連携)

```php
// app/Services/GeocodingService.php
class GeocodingService
{
    public function reverseGeocode(float $lat, float $lng): ?array
    {
        $response = Http::get('https://nominatim.openstreetmap.org/reverse', [
            'lat'    => $lat,
            'lon'    => $lng,
            'format' => 'json',
        ]);

        if ($response->failed()) return null;

        $data = $response->json();
        return [
            'country' => $data['address']['country'] ?? null,
            'region'  => $data['address']['state'] ?? null,
            'city'    => $data['address']['city'] ?? $data['address']['town'] ?? null,
            'address' => $data['display_name'] ?? null,
        ];
    }
}
```

---

## 5. フロントエンド アーキテクチャ (Vue 3)

### 5.1 Vite 設定

```js
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: { '@': '/resources/js' },
    },
});
```

### 5.2 Vue アプリ初期化

```js
// resources/js/app.js
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import axios from 'axios';

// CSRF トークン設定
axios.defaults.headers.common['X-CSRF-TOKEN'] =
    document.querySelector('meta[name="csrf-token"]').content;
axios.defaults.headers.common['Accept'] = 'application/json';

// Vue コンポーネントをグローバル登録 (Blade 埋め込み用)
import MapView from '@/components/MapView.vue';
import QuoteCard from '@/components/QuoteCard.vue';
import QuoteForm from '@/components/QuoteForm.vue';
import SearchBar from '@/components/SearchBar.vue';
import LikeButton from '@/components/LikeButton.vue';
import FavoriteButton from '@/components/FavoriteButton.vue';

const app = createApp({});
app.use(createPinia());

app.component('map-view', MapView);
app.component('quote-card', QuoteCard);
app.component('quote-form', QuoteForm);
app.component('search-bar', SearchBar);
app.component('like-button', LikeButton);
app.component('favorite-button', FavoriteButton);

app.mount('#app');
```

### 5.3 Blade レイアウトでの Vue マウント

```html
<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <div id="app">
        @include('layouts.partials.header')
        <main>@yield('content')</main>
        @include('layouts.partials.footer')
    </div>
    @stack('scripts')
</body>
</html>
```

### 5.4 Blade 内での Vue コンポーネント使用例

```html
<!-- resources/views/home/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="home">
    <map-view
        :initial-center="[36.5, 138.0]"
        :initial-zoom="5"
    ></map-view>

    <quote-card
        v-for="quote in quotes"
        :key="quote.id"
        :quote="quote"
    ></quote-card>
</div>
@endsection
```

### 5.5 Vue コンポーネント設計方針

| 方針 | 説明 |
|------|------|
| Composition API | `<script setup>` 構文を標準使用 |
| Pinia | 認証状態、地図フィルター等のグローバル状態管理 |
| Composables | Leaflet 操作やAPI呼び出しの再利用ロジック |
| Props/Emit | 親子コンポーネント間はProps Down, Events Up |
| Blade 共存 | ページ全体は Blade、インタラクティブ部分を Vue コンポーネント化 |

### 5.6 Composable 例

```js
// resources/js/composables/useMap.js
import { ref, onMounted, onUnmounted } from 'vue';
import L from 'leaflet';
import 'leaflet.markercluster';

export function useMap(containerId, options = {}) {
    const map = ref(null);
    const markers = ref(null);

    onMounted(() => {
        map.value = L.map(containerId).setView(
            options.center || [36.5, 138.0],
            options.zoom || 5
        );
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map.value);

        markers.value = L.markerClusterGroup();
        map.value.addLayer(markers.value);
    });

    onUnmounted(() => { map.value?.remove(); });

    return { map, markers };
}
```

---

## 6. パフォーマンス設計

### 6.1 Eloquent 最適化

| 対策 | 方法 |
|------|------|
| N+1問題回避 | `with()` によるイーガーロード |
| バウンディングボックスクエリ | latitude/longitude 複合インデックス + スコープ |
| カウンタキャッシュ | likes_count を Observer で同期 |
| ページネーション | `->paginate()` / `->simplePaginate()` |
| 不要カラム除外 | `->select()` で必要カラムのみ取得 |

### 6.2 フロントエンド最適化

| 対策 | 方法 |
|------|------|
| アセットバンドル | Vite によるJS/CSSバンドル・ミニファイ |
| デバウンス | 地図の `moveend` イベントを300msデバウンス |
| 画像最適化 | Intervention Image で WebP 変換・リサイズ |
| キャッシュ | Vite のファイルハッシュによるブラウザキャッシュ |
