# LoveAtlas - 5人並行開発 機能グループ分け

## 1. 前提

### 1.1 共通基盤 (全員着手前に完了させる)

以下は全グループの前提となるため、**開発開始前にリーダー or 全員で共同作業**する。

| # | タスク | 成果物 |
|---|--------|--------|
| 0-1 | Laravel プロジェクト作成・初期設定 | composer, npm, .env, vite.config.js |
| 0-2 | 全マイグレーション作成・実行 | 9テーブル (users~reports) |
| 0-3 | 全 Eloquent モデル・リレーション定義 | User, Quote, Work, Location, Tag, Favorite, Visit, Vote, Report |
| 0-4 | Factory・Seeder 作成 | サンプルデータ投入 |
| 0-5 | 共通 Blade レイアウト | `layouts/app.blade.php` (ヘッダー・フッター枠) |
| 0-6 | Vue エントリーポイント | `app.js` (createApp, Pinia 初期化) |
| 0-7 | CSS 基盤 | CSS Variables、リセット、共通ユーティリティ |
| 0-8 | routes/api.php のスケルトン | ルートグループの枠だけ定義 |

**見積もり: 1〜2日**

### 1.2 依存関係図

```
              ┌──────────────────────────────────────────┐
              │     Step 0: 共通基盤 (全員/リーダー)       │
              │  Migration, Model, Layout, Vue init, CSS  │
              └──────┬───────┬───────┬───────┬───────┬────┘
                     │       │       │       │       │
              ┌──────▼──┐ ┌──▼────┐ ┌▼──────┐ ┌▼─────┐ ┌▼──────┐
              │  A: 地図 │ │B: 認証│ │C: セリ│ │D: イン│ │E: 管理│
              │         │ │      │ │フ・作品│ │タラク │ │      │
              └─────────┘ └──┬───┘ └──┬────┘ └──┬───┘ └──────┘
                             │        │         │
                             ▼        ▼         ▼
                     認証に依存する機能は B 完了後に結合テスト
```

**ポイント:** 各グループは Model / Migration を共有するが、Controller・Vue コンポーネント・Blade は独立して開発可能。認証 (B) が必要な部分は、開発中は `actingAs()` やダミー認証で回避できる。

---

## 2. グループ分け

### グループ A: 地図・位置情報 (Map & Geo)

**担当機能:**

| ID | 機能名 | 概要 |
|----|--------|------|
| F-001 | 地図表示 | Leaflet + OSM によるインタラクティブ地図 |
| F-002 | ピン表示 | 作品タイプ別の色分けマーカー |
| F-003 | クラスタリング | Leaflet.markercluster によるズーム連動クラスタ |
| F-004 | ポップアップ表示 | ピンクリックでセリフ概要ポップアップ |
| F-005 | バウンディングボックス取得 | 表示範囲内のデータのみ API 取得 |

**担当ファイル:**

```
Backend:
  app/Http/Controllers/Api/MapController.php       ← バウンディングボックスAPI, クラスタAPI
  app/Services/GeocodingService.php                ← Nominatim API 連携

Frontend:
  resources/js/components/MapView.vue              ← Leaflet 地図本体
  resources/js/components/SidePanel.vue            ← PC サイドパネル (セリフ一覧)
  resources/js/components/BottomSheet.vue           ← モバイル ボトムシート
  resources/js/components/FilterPanel.vue           ← 作品タイプ・タグフィルター
  resources/js/composables/useMap.js               ← Leaflet インスタンス管理
  resources/js/composables/useDebounce.js          ← デバウンス処理
  resources/js/stores/map.js                       ← 地図状態 (Pinia)
  resources/css/components/map.css                 ← 地図関連 CSS

Blade:
  resources/views/home.blade.php                   ← ホーム画面 (地図ビュー)
```

**API エンドポイント:**

```
GET /api/v1/map/quotes          ← バウンディングボックス内セリフ
GET /api/v1/map/clusters        ← クラスタリングデータ
GET /api/v1/locations/{id}      ← 場所詳細
GET /api/v1/locations/{id}/quotes ← 場所のセリフ一覧
```

**他グループとの接点:**
- Quote モデルの `scopeApproved`, `scopeInBoundingBox` スコープを定義 → C が投稿した Quote データを表示
- ポップアップから C のセリフ詳細ページへリンク
- D の FilterPanel (タグ・作品タイプ) と連携

**作業量: ★★★★☆** (Leaflet 統合が技術的に重い)

---

### グループ B: 認証・プロフィール (Auth & Profile)

**担当機能:**

| ID | 機能名 | 概要 |
|----|--------|------|
| F-006 | ユーザー登録 | メール + パスワードによるアカウント作成 |
| F-007 | ログイン/ログアウト | Sanctum SPA 認証 |
| F-008 | パスワードリセット | メールによるリセット |
| F-018 | プロフィール | 自分の投稿・お気に入り・訪問記録 |
| F-019 | 公開プロフィール | 他ユーザーの投稿一覧 |

**担当ファイル:**

```
Backend:
  app/Http/Controllers/Api/AuthController.php       ← register, login, logout, me
  app/Http/Controllers/ProfileController.php        ← プロフィール表示・編集
  app/Http/Requests/Auth/RegisterRequest.php
  app/Http/Requests/Auth/LoginRequest.php
  app/Http/Middleware/SecurityHeaders.php            ← セキュリティヘッダー
  app/Services/ImageService.php                     ← アバター画像処理 (共有)
  config/sanctum.php                                ← Sanctum 設定
  config/cors.php                                   ← CORS 設定

Frontend:
  resources/js/components/LoginForm.vue
  resources/js/components/RegisterForm.vue
  resources/js/stores/auth.js                       ← 認証状態 (Pinia)
  resources/js/components/HeaderUser.vue             ← ヘッダーのユーザー表示

Blade:
  resources/views/auth/login.blade.php
  resources/views/auth/register.blade.php
  resources/views/auth/forgot-password.blade.php
  resources/views/profile/index.blade.php           ← タブ: 投稿/お気に入り/訪問
  resources/views/profile/settings.blade.php
  resources/views/users/show.blade.php              ← 公開プロフィール
```

**API エンドポイント:**

```
POST /api/v1/auth/register
POST /api/v1/auth/login
POST /api/v1/auth/logout
GET  /api/v1/auth/me
POST /api/v1/auth/forgot-password
POST /api/v1/auth/reset-password
```

**他グループとの接点:**
- `auth:sanctum` ミドルウェアを全グループが使用
- `auth.js` (Pinia store) を全 Vue コンポーネントが参照
- プロフィール画面は D のお気に入り・訪問記録データを表示

**作業量: ★★★☆☆** (Breeze がスカフォールドの大半を提供)

---

### グループ C: セリフ・作品 (Quote & Work)

**担当機能:**

| ID | 機能名 | 概要 |
|----|--------|------|
| F-009 | セリフ投稿 | セリフ・作品・場所を紐付けて投稿 |
| F-010 | セリフ閲覧 | セリフ詳細ページ |
| F-011 | セリフ編集/削除 | 投稿者によるセリフの CRUD |
| F-016 | 作品ページ | 作品詳細 + セリフ一覧 |
| F-017 | 作品登録 | 投稿時の新規作品登録 |
| F-020 | タグ機能 | セリフへのタグ付け・タグ別一覧 |

**担当ファイル:**

```
Backend:
  app/Http/Controllers/Api/QuoteController.php      ← index, show, store, update, destroy
  app/Http/Controllers/Api/WorkController.php       ← index, show, store, search
  app/Http/Requests/StoreQuoteRequest.php
  app/Http/Requests/UpdateQuoteRequest.php
  app/Policies/QuotePolicy.php                      ← 編集・削除の認可
  app/Services/ImageService.php                     ← セリフ画像処理 (B と共有)

Frontend:
  resources/js/components/QuoteForm.vue              ← 投稿・編集フォーム
  resources/js/components/QuoteCard.vue              ← セリフカード (一覧用)
  resources/js/components/WorkAutocomplete.vue       ← 作品オートコンプリート
  resources/js/components/TagInput.vue               ← タグ入力コンポーネント
  resources/js/components/QuoteDetail.vue            ← 詳細ページ内 Vue 部品

Blade:
  resources/views/quotes/show.blade.php             ← セリフ詳細ページ
  resources/views/quotes/create.blade.php           ← セリフ投稿ページ
  resources/views/quotes/edit.blade.php             ← セリフ編集ページ
  resources/views/works/index.blade.php             ← 作品一覧
  resources/views/works/show.blade.php              ← 作品詳細
  resources/views/tags/show.blade.php               ← タグ別セリフ一覧
```

**API エンドポイント:**

```
GET    /api/v1/quotes
GET    /api/v1/quotes/{id}
POST   /api/v1/quotes
PUT    /api/v1/quotes/{id}
DELETE /api/v1/quotes/{id}
GET    /api/v1/works
GET    /api/v1/works/{id}
POST   /api/v1/works
GET    /api/v1/works/search
```

**他グループとの接点:**
- A の地図がこのグループのセリフデータを表示
- D のいいね・お気に入りボタンがセリフ詳細ページに表示される
- E の管理画面がセリフの承認/拒否を行う
- 投稿フォーム内に A の地図コンポーネント (位置選択用) を埋め込み → A と連携

**作業量: ★★★★☆** (投稿フォームが複雑、Policy・バリデーション多い)

---

### グループ D: インタラクション・探索 (Interaction & Discovery)

**担当機能:**

| ID | 機能名 | 概要 |
|----|--------|------|
| F-012 | いいね | セリフへのいいねトグル |
| F-013 | お気に入り | セリフのブックマーク |
| F-014 | 訪問記録 | 場所の訪問記録 (日付・メモ・評価) |
| F-015 | 検索 | セリフ・作品・場所の横断検索 |
| F-021 | Explore | 人気セリフ・新着セリフのブラウズ |

**担当ファイル:**

```
Backend:
  app/Http/Controllers/Api/VoteController.php       ← いいねトグル
  app/Http/Controllers/Api/FavoriteController.php   ← お気に入り CRUD
  app/Http/Controllers/Api/VisitController.php      ← 訪問記録 CRUD
  app/Http/Controllers/Api/SearchController.php     ← 横断検索
  app/Http/Controllers/ExploreController.php        ← Explore ページ
  app/Http/Requests/StoreVisitRequest.php
  app/Observers/VoteObserver.php                    ← likes_count 自動同期

Frontend:
  resources/js/components/LikeButton.vue             ← いいねボタン
  resources/js/components/FavoriteButton.vue          ← お気に入りボタン
  resources/js/components/SearchBar.vue              ← ヘッダー検索バー
  resources/js/components/VisitForm.vue              ← 訪問記録フォーム
  resources/js/composables/useQuotes.js              ← いいね・お気に入り状態

Blade:
  resources/views/search/index.blade.php            ← 検索結果ページ
  resources/views/explore/index.blade.php           ← Explore ページ
  resources/views/visits/create.blade.php           ← 訪問記録登録
```

**API エンドポイント:**

```
POST   /api/v1/quotes/{id}/vote
GET    /api/v1/favorites
POST   /api/v1/favorites
DELETE /api/v1/favorites/{id}
GET    /api/v1/visits
POST   /api/v1/visits
PUT    /api/v1/visits/{id}
DELETE /api/v1/visits/{id}
GET    /api/v1/search
```

**他グループとの接点:**
- LikeButton / FavoriteButton は C のセリフ詳細ページや A のポップアップに埋め込まれる
- SearchBar は共通レイアウトのヘッダーに配置 → 全画面で表示
- VoteObserver は Quote モデルの likes_count を更新 → A の表示に影響
- B のプロフィール画面にお気に入り・訪問記録タブのデータを提供

**作業量: ★★★☆☆** (個々の API はシンプルだが数が多い)

---

### グループ E: 管理機能 (Admin & Moderation)

**担当機能:**

| ID | 機能名 | 概要 |
|----|--------|------|
| F-022 | セリフ承認 | 管理者による投稿セリフの承認/拒否 |
| F-023 | 通報機能 | 不適切セリフの通報と処理 |
| F-024 | 管理ダッシュボード | 統計情報・承認待ち・通報の管理画面 |
| F-025 | ユーザー管理 | 権限変更・BAN 処理 |

**追加担当 (横断的品質):**

| タスク | 概要 |
|--------|------|
| レスポンシブ調整 | 全画面のモバイル・タブレット対応の最終調整 |
| CSS テーマ仕上げ | カラーパレット・フォント・アイコンの統一 |
| Feature テスト | PHPUnit テスト作成 (認証・API・管理) |
| エラーページ | 404, 403, 500 ページ |

**担当ファイル:**

```
Backend:
  app/Http/Controllers/Api/Admin/QuoteController.php  ← 承認/拒否
  app/Http/Controllers/Api/Admin/ReportController.php ← 通報管理
  app/Http/Controllers/Api/Admin/StatsController.php  ← 統計
  app/Http/Controllers/Api/Admin/UserController.php   ← ユーザー管理
  app/Http/Controllers/Api/ReportController.php       ← 通報投稿 (ユーザー側)
  app/Http/Middleware/AdminMiddleware.php              ← 管理者権限チェック
  app/Console/Commands/SyncLikesCount.php             ← バッチコマンド
  app/Console/Commands/SyncTagUsage.php

Frontend:
  resources/js/components/ReportButton.vue            ← 通報ボタン
  resources/js/components/ToastNotification.vue       ← トースト通知 (共有)
  resources/js/components/ModalDialog.vue             ← モーダル (共有)

Blade:
  resources/views/layouts/admin.blade.php             ← 管理画面レイアウト
  resources/views/admin/dashboard.blade.php           ← ダッシュボード
  resources/views/admin/quotes/index.blade.php        ← セリフ承認管理
  resources/views/admin/reports/index.blade.php       ← 通報管理
  resources/views/admin/users/index.blade.php         ← ユーザー管理
  resources/views/errors/404.blade.php
  resources/views/errors/403.blade.php
  resources/views/errors/500.blade.php

Test:
  tests/Feature/Api/AuthTest.php
  tests/Feature/Api/QuoteApiTest.php
  tests/Feature/Api/MapApiTest.php
  tests/Feature/Api/AdminTest.php
  tests/Feature/SecurityTest.php
```

**API エンドポイント:**

```
POST /api/v1/reports                         ← 通報投稿 (ユーザー側)
GET  /api/v1/admin/quotes/pending
PUT  /api/v1/admin/quotes/{id}/approve
PUT  /api/v1/admin/quotes/{id}/reject
GET  /api/v1/admin/reports
PUT  /api/v1/admin/reports/{id}
GET  /api/v1/admin/stats
PUT  /api/v1/admin/users/{id}/role
PUT  /api/v1/admin/users/{id}/ban
```

**他グループとの接点:**
- AdminMiddleware を管理者ルート全体に適用
- 通報ボタンは C のセリフ詳細ページに配置
- 承認すると C のセリフが `approved` になり、A の地図に表示される
- テストは全グループの API をカバー

**作業量: ★★★☆☆** (管理 CRUD はシンプル + テスト・仕上げで調整)

---

## 3. 作業量サマリー

| グループ | 担当者 | 機能数 | API 数 | Vue 数 | Blade 数 | 作業量 |
|---------|--------|--------|--------|--------|---------|--------|
| A: 地図 | 担当者1 | 5 | 4 | 5 | 1 | ★★★★☆ |
| B: 認証 | 担当者2 | 5 | 6 | 3 | 5 | ★★★☆☆ |
| C: セリフ | 担当者3 | 6 | 9 | 5 | 6 | ★★★★☆ |
| D: インタラクション | 担当者4 | 5 | 9 | 5 | 3 | ★★★☆☆ |
| E: 管理 | 担当者5 | 4+横断 | 9 | 3 | 7 | ★★★☆☆ |

---

## 4. 開発フロー

```
Week 1 (Day 1-2):
  全員 → Step 0 共通基盤構築

Week 1-2 (Day 3~):
  A → Leaflet 地図表示・ピン・クラスタリング
  B → Sanctum 認証・登録・ログイン
  C → セリフ CRUD API・作品 API
  D → いいね・お気に入り・訪問記録 API
  E → 管理画面レイアウト・承認 API

Week 2-3:
  A → ポップアップ・フィルター・レスポンシブ (BottomSheet)
  B → プロフィール画面・アバターアップロード
  C → 投稿フォーム (Vue)・作品ページ・タグ機能
  D → 検索・Explore ページ
  E → 通報・ダッシュボード・ユーザー管理

Week 3-4:
  全員 → 結合テスト・バグ修正
  E → Feature テスト・CSS 仕上げ・レスポンシブ最終調整
```

---

## 5. コンポーネント共有ルール

### 5.1 共有コンポーネント (変更時は全員に通知)

| コンポーネント | 主担当 | 利用者 |
|--------------|--------|--------|
| `layouts/app.blade.php` | 共通 | 全員 |
| `auth.js` (Pinia store) | B | A, C, D, E |
| `map.js` (Pinia store) | A | C (投稿フォームの位置選択) |
| `QuoteCard.vue` | C | A (ポップアップ), D (Explore), B (プロフィール) |
| `LikeButton.vue` | D | C (セリフ詳細), A (ポップアップ) |
| `FavoriteButton.vue` | D | C (セリフ詳細) |
| `SearchBar.vue` | D | 共通レイアウト (全員) |
| `ToastNotification.vue` | E | 全員 |
| `ModalDialog.vue` | E | 全員 |
| `ImageService.php` | B (アバター) / C (セリフ画像) | 共有 |

### 5.2 Git ブランチ戦略

```
main
├── feature/group-a-map          ← A: 地図
├── feature/group-b-auth         ← B: 認証・プロフィール
├── feature/group-c-quotes       ← C: セリフ・作品
├── feature/group-d-interaction  ← D: インタラクション・探索
└── feature/group-e-admin        ← E: 管理・テスト
```

- 各グループは自分のブランチで開発
- 共有コンポーネントの変更は `main` にマージ後、各ブランチに取り込み
- 結合テスト期間に全ブランチを `main` へマージ

### 5.3 コンフリクト回避のルール

1. **routes/api.php** は各グループが自分の prefix 内のみ編集
2. **app.js** への Vue コンポーネント登録は各自のグループコメントブロック内に記述
3. **Model への変更** (スコープ追加等) は事前に Slack/チャットで共有
4. **CSS は BEM 命名規則** を厳守し、コンポーネントごとにスコープ化
