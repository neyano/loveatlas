# LoveAtlas - 実装ロードマップ

## 1. フェーズ概要

```
Phase 1: MVP ──────────────────────────> リリース可能な最小構成
Phase 2: エンゲージメント強化 ──────────> ユーザー定着・成長機能
Phase 3: 拡張・スケール ───────────────> 多言語・AI・パフォーマンス
```

---

## 2. Phase 1: MVP

### Step 1 - Laravel プロジェクト基盤構築

| # | タスク | 成果物 |
|---|--------|--------|
| 1-1 | Laravel プロジェクト作成 | `composer create-project laravel/laravel loveatlas` |
| 1-2 | .env 設定 (DB接続、APP_URL等) | `.env` |
| 1-3 | Vite + Vue 3 セットアップ | `npm install vue @vitejs/plugin-vue`, `vite.config.js` |
| 1-4 | Pinia / axios / Leaflet インストール | `npm install pinia axios leaflet leaflet.markercluster` |
| 1-5 | Laravel Breeze インストール (API用) | `composer require laravel/breeze`, `php artisan breeze:install` |
| 1-6 | Intervention Image インストール | `composer require intervention/image` |
| 1-7 | 共通 Blade レイアウト作成 | `resources/views/layouts/app.blade.php` |
| 1-8 | Vue アプリケーション初期化 | `resources/js/app.js` (createApp, Pinia, 共通コンポーネント) |
| 1-9 | CSS 基盤 (変数、リセット、BEM) | `resources/css/app.css` |
| 1-10 | SecurityHeaders ミドルウェア | `app/Http/Middleware/SecurityHeaders.php` |

**完了条件:** `php artisan serve` でブラウザに空ページが表示される。`npm run dev` で Vite が起動。

### Step 2 - データベース・モデル

| # | タスク | 成果物 |
|---|--------|--------|
| 2-1 | マイグレーション: users テーブルカスタマイズ | username, display_name, avatar_path, bio, role, is_active 追加 |
| 2-2 | マイグレーション: works | `php artisan make:migration create_works_table` |
| 2-3 | マイグレーション: locations | `php artisan make:migration create_locations_table` |
| 2-4 | マイグレーション: quotes | `php artisan make:migration create_quotes_table` |
| 2-5 | マイグレーション: tags, quote_tag | タグ + 中間テーブル |
| 2-6 | マイグレーション: favorites, visits, votes, reports | インタラクション系テーブル |
| 2-7 | Eloquent モデル (9個) | User, Work, Location, Quote, Tag, Favorite, Visit, Vote, Report |
| 2-8 | リレーション定義 | 各モデルの belongsTo, hasMany, belongsToMany |
| 2-9 | VoteObserver | likes_count 自動同期 |
| 2-10 | Seeder: AdminUserSeeder | 管理者アカウント |
| 2-11 | Seeder: WorkSeeder, LocationSeeder, QuoteSeeder, TagSeeder | サンプルデータ |
| 2-12 | マイグレーション実行・確認 | `php artisan migrate --seed` |

**完了条件:** 全テーブルが作成され、サンプルデータが投入されている。`php artisan tinker` でリレーションが確認できる。

### Step 3 - 認証機能

| # | タスク | 成果物 |
|---|--------|--------|
| 3-1 | Sanctum 設定 | `config/sanctum.php` (stateful domains) |
| 3-2 | CORS 設定 | `config/cors.php` (supports_credentials: true) |
| 3-3 | AuthController (API) | register, login, logout, me |
| 3-4 | RegisterRequest / LoginRequest | FormRequest バリデーション |
| 3-5 | AdminMiddleware | 管理者権限チェック |
| 3-6 | RateLimiter 設定 | AppServiceProvider (login, api, quote-post) |
| 3-7 | Vue: LoginForm.vue / RegisterForm.vue | 認証フォームコンポーネント |
| 3-8 | Pinia: auth store | ログイン状態管理 |
| 3-9 | Blade: auth ページ | login.blade.php, register.blade.php |
| 3-10 | ヘッダーにユーザー表示 | ログイン/ユーザー名の切り替え |

**完了条件:** ユーザー登録 → ログイン → API 認証 → ログアウトが動作する。

### Step 4 - 地図表示・セリフ閲覧

| # | タスク | 成果物 |
|---|--------|--------|
| 4-1 | Blade: ホーム画面 | `resources/views/home.blade.php` |
| 4-2 | Vue: MapView.vue | Leaflet 初期化、OSM タイル設定 |
| 4-3 | Vue: SidePanel.vue | サイドパネル (セリフ一覧) |
| 4-4 | Vue: BottomSheet.vue | モバイル用ボトムシート |
| 4-5 | Composable: useMap.js | Leaflet インスタンス管理、マーカー描画 |
| 4-6 | MapController (API) | バウンディングボックス取得 API |
| 4-7 | Quote スコープ | scopeApproved, scopeInBoundingBox |
| 4-8 | ピン表示 (色分け) | work.type に応じたマーカーアイコン |
| 4-9 | Leaflet.markercluster 導入 | クラスタリング表示 |
| 4-10 | ポップアップ表示 | ピンクリックでセリフ概要表示 |
| 4-11 | Blade: セリフ詳細ページ | `resources/views/quotes/show.blade.php` |
| 4-12 | QuoteController (API) | index, show |
| 4-13 | Composable: useDebounce.js | 地図移動時のAPI呼び出しデバウンス |

**完了条件:** 地図上にサンプルセリフのピンが表示される。ピンクリックでポップアップ → 詳細ページに遷移できる。

### Step 5 - セリフ投稿

| # | タスク | 成果物 |
|---|--------|--------|
| 5-1 | Vue: QuoteForm.vue | セリフ投稿フォーム (地図位置選択、作品検索) |
| 5-2 | Vue: WorkAutocomplete.vue | 作品オートコンプリート |
| 5-3 | Blade: 投稿ページ | `resources/views/quotes/create.blade.php` |
| 5-4 | StoreQuoteRequest | FormRequest バリデーション |
| 5-5 | QuoteController::store | セリフ投稿 API |
| 5-6 | WorkController (API) | 作品一覧・検索・登録 |
| 5-7 | GeocodingService | Nominatim API 連携 (住所→緯度経度) |
| 5-8 | ImageService | Intervention Image で画像リサイズ・WebP 変換 |
| 5-9 | QuotePolicy | 編集・削除の認可 |

**完了条件:** ログインユーザーがセリフを投稿できる。投稿後は承認待ち状態。

### Step 6 - インタラクション機能

| # | タスク | 成果物 |
|---|--------|--------|
| 6-1 | VoteController (API) | いいねトグル API |
| 6-2 | FavoriteController (API) | お気に入り API |
| 6-3 | VisitController (API) | 訪問記録 API |
| 6-4 | Vue: LikeButton.vue | いいねボタン (即時 UI 反映) |
| 6-5 | Vue: FavoriteButton.vue | お気に入りボタン |
| 6-6 | Blade: 訪問記録ページ | 訪問登録フォーム |
| 6-7 | Composable: useQuotes.js | いいね・お気に入り状態管理 |

**完了条件:** いいね・お気に入り・訪問記録の追加/解除が動作する。

### Step 7 - 検索・ブラウズ・プロフィール

| # | タスク | 成果物 |
|---|--------|--------|
| 7-1 | SearchController (API) | 横断検索 API (FULLTEXT) |
| 7-2 | Vue: SearchBar.vue | ヘッダー検索バー |
| 7-3 | Blade: 検索結果ページ | `resources/views/search/index.blade.php` |
| 7-4 | Blade: 作品一覧・詳細 | `resources/views/works/` |
| 7-5 | Blade: Explore ページ | 人気・新着セリフ一覧 |
| 7-6 | Blade: タグ一覧ページ | タグ別セリフ表示 |
| 7-7 | ProfileController | 投稿・お気に入り・訪問記録タブ |
| 7-8 | Blade: プロフィールページ | `resources/views/profile/` |
| 7-9 | プロフィール設定 | 表示名・アバター・自己紹介の編集 |
| 7-10 | 公開プロフィール | 他ユーザーの投稿閲覧 |

**完了条件:** 検索・ブラウズ・プロフィール機能が一通り動作する。

### Step 8 - 管理画面・仕上げ

| # | タスク | 成果物 |
|---|--------|--------|
| 8-1 | Blade: 管理レイアウト | `resources/views/layouts/admin.blade.php` |
| 8-2 | Admin\QuoteController | セリフ承認/拒否 API |
| 8-3 | Admin\ReportController | 通報管理 |
| 8-4 | Admin\UserController | ユーザー管理 (権限変更・BAN) |
| 8-5 | Admin\StatsController | 統計ダッシュボード |
| 8-6 | Vue: FilterPanel.vue | 地図フィルタパネル |
| 8-7 | レスポンシブ調整 | モバイルのボトムシート、タブレット対応 |
| 8-8 | CSS テーマ仕上げ | カラーパレット・フォント・アイコン整備 |
| 8-9 | パフォーマンス調整 | Eager Loading、DB インデックス確認 |
| 8-10 | Feature テスト作成 | PHPUnit テスト (認証、API、管理) |

**完了条件:** MVP として全機能が動作し、PC・モバイル両方で使用可能。テストがパスする。

---

## 3. Phase 2: エンゲージメント強化

| # | 機能 | 概要 |
|---|------|------|
| P2-1 | SNSシェア | セリフをTwitter/LINE等にシェアするボタン |
| P2-2 | ユーザーフォロー | 他ユーザーをフォローし、投稿を追いかける |
| P2-3 | アクティビティフィード | フォロー中ユーザーの投稿・いいねを時系列表示 |
| P2-4 | コレクション | 「ジブリの聖地巡礼」等テーマ別のセリフリスト作成 |
| P2-5 | バッジ | 訪問数・投稿数に応じたバッジ付与 (例: 10箇所訪問→「旅人」) |
| P2-6 | ランキング | 月間人気セリフ・アクティブ投稿者ランキング |
| P2-7 | ルート表示 | 複数聖地を巡るルート提案 (地図上に経路表示) |
| P2-8 | ヒートマップ | セリフの密度をヒートマップで可視化 |
| P2-9 | 通知システム | Laravel Notification (いいね・フォロー時のアプリ内通知) |
| P2-10 | パスワードリセット | Laravel Breeze のメール送信機能 |
| P2-11 | メール認証 | `MustVerifyEmail` インターフェース実装 |

---

## 4. Phase 3: 拡張・スケール

| # | 機能 | 概要 |
|---|------|------|
| P3-1 | 多言語対応 (i18n) | Laravel Localization + Vue I18n |
| P3-2 | AI自動タグ付け | 投稿セリフのジャンル・ムード自動分類 |
| P3-3 | AI類似セリフ推薦 | 閲覧中セリフに類似したセリフを推薦 |
| P3-4 | TMDB API連携 | 映画・アニメの作品情報を外部APIから自動取得 |
| P3-5 | PWA対応 | オフライン閲覧、プッシュ通知、ホーム画面追加 |
| P3-6 | Redis キャッシュ | Laravel Cache (API レスポンス、セッション) |
| P3-7 | CDN導入 | 静的ファイル・画像の配信高速化 |
| P3-8 | SPATIAL INDEX | MariaDB の空間インデックスで地図クエリ高速化 |
| P3-9 | 全文検索エンジン | Laravel Scout + Meilisearch で日本語検索精度向上 |
| P3-10 | OGP対応 | シェア時にセリフ・作品画像がプレビュー表示 |
