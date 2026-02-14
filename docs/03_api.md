# LoveAtlas - REST API 仕様

## 1. 共通仕様

| 項目 | 内容 |
|------|------|
| ベースパス | `/api/v1/` |
| レスポンス形式 | JSON |
| 認証方式 | Laravel Sanctum (SPA認証 / Cookie ベース) |
| CSRF保護 | Laravel 標準 (`/sanctum/csrf-cookie` で Cookie 取得) |
| 文字エンコーディング | UTF-8 |
| ルーティング | `routes/api.php` で定義 |

### 認証フロー (SPA)

```
1. GET  /sanctum/csrf-cookie     → XSRF-TOKEN Cookie を取得
2. POST /api/v1/auth/login       → セッション認証
3. 以降のリクエストで Cookie が自動送信される
```

axios を使用する場合、`withCredentials: true` と `withXSRFToken: true` を設定することで CSRF トークンが自動付与される。

```javascript
// resources/js/bootstrap.js
import axios from 'axios';
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
```

### 成功レスポンス

```json
{
    "success": true,
    "data": { ... },
    "meta": {
        "page": 1,
        "per_page": 20,
        "total": 150
    }
}
```

### エラーレスポンス

```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "入力内容に誤りがあります",
        "details": {
            "quote_text": ["セリフは必須です"]
        }
    }
}
```

### エラーコード一覧

| コード | HTTPステータス | 説明 |
|--------|---------------|------|
| VALIDATION_ERROR | 422 | バリデーションエラー |
| UNAUTHORIZED | 401 | 未認証 |
| FORBIDDEN | 403 | 権限不足 |
| NOT_FOUND | 404 | リソースが見つからない |
| RATE_LIMIT_EXCEEDED | 429 | レート制限超過 |
| INTERNAL_ERROR | 500 | サーバー内部エラー |

### ルーティング定義

```php
// routes/api.php
Route::prefix('v1')->group(function () {

    // 認証
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    // 公開API
    Route::get('/quotes', [QuoteController::class, 'index']);
    Route::get('/quotes/{quote}', [QuoteController::class, 'show']);
    Route::get('/map/quotes', [MapController::class, 'quotes']);
    Route::get('/map/clusters', [MapController::class, 'clusters']);
    Route::get('/locations/{location}', [LocationController::class, 'show']);
    Route::get('/locations/{location}/quotes', [LocationController::class, 'quotes']);
    Route::get('/search', [SearchController::class, 'index']);
    Route::get('/works', [WorkController::class, 'index']);
    Route::get('/works/{work}', [WorkController::class, 'show']);
    Route::get('/works/search', [WorkController::class, 'search']);

    // 認証必須API
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/quotes', [QuoteController::class, 'store']);
        Route::put('/quotes/{quote}', [QuoteController::class, 'update']);
        Route::delete('/quotes/{quote}', [QuoteController::class, 'destroy']);
        Route::post('/quotes/{quote}/vote', [VoteController::class, 'toggle']);
        Route::apiResource('favorites', FavoriteController::class)->only(['index', 'store', 'destroy']);
        Route::apiResource('visits', VisitController::class);
        Route::post('/works', [WorkController::class, 'store']);
    });

    // 管理者API
    Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('/quotes/pending', [Admin\QuoteController::class, 'pending']);
        Route::put('/quotes/{quote}/approve', [Admin\QuoteController::class, 'approve']);
        Route::put('/quotes/{quote}/reject', [Admin\QuoteController::class, 'reject']);
        Route::get('/reports', [Admin\ReportController::class, 'index']);
        Route::put('/reports/{report}', [Admin\ReportController::class, 'update']);
        Route::get('/stats', [Admin\StatsController::class, 'index']);
        Route::put('/users/{user}/role', [Admin\UserController::class, 'updateRole']);
        Route::put('/users/{user}/ban', [Admin\UserController::class, 'ban']);
    });
});
```

---

## 2. 認証 API

### POST `/api/v1/auth/register` - ユーザー登録

**FormRequest:** `App\Http\Requests\Auth\RegisterRequest`

**リクエスト:**
```json
{
    "username": "tanaka_taro",
    "email": "tanaka@example.com",
    "password": "securePassword123",
    "password_confirmation": "securePassword123",
    "display_name": "田中太郎"
}
```

**バリデーション:**
| フィールド | ルール |
|-----------|--------|
| username | required, string, min:3, max:50, regex:/^[a-zA-Z0-9_]+$/, unique:users |
| email | required, email, max:255, unique:users |
| password | required, string, min:8, confirmed, regex:/(?=.*[a-zA-Z])(?=.*[0-9])/ |
| display_name | required, string, max:100 |

**レスポンス (201):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "username": "tanaka_taro",
            "display_name": "田中太郎"
        }
    }
}
```

### POST `/api/v1/auth/login` - ログイン

**FormRequest:** `App\Http\Requests\Auth\LoginRequest`

**リクエスト:**
```json
{
    "login": "tanaka@example.com",
    "password": "securePassword123"
}
```

**レスポンス (200):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "username": "tanaka_taro",
            "display_name": "田中太郎",
            "role": "user",
            "avatar_path": null
        }
    }
}
```

### POST `/api/v1/auth/logout` - ログアウト [要認証]

**レスポンス (200):**
```json
{
    "success": true,
    "data": {
        "message": "ログアウトしました"
    }
}
```

### GET `/api/v1/auth/me` - 現在のユーザー情報 [要認証]

**レスポンス (200):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "username": "tanaka_taro",
            "display_name": "田中太郎",
            "email": "tanaka@example.com",
            "role": "user",
            "avatar_path": null,
            "bio": null,
            "created_at": "2026-02-14T10:00:00+09:00"
        }
    }
}
```

### POST `/api/v1/auth/forgot-password` - パスワードリセット要求

Laravel Breeze のパスワードリセット機能を利用。

**リクエスト:**
```json
{
    "email": "tanaka@example.com"
}
```

### POST `/api/v1/auth/reset-password` - パスワードリセット実行

**リクエスト:**
```json
{
    "token": "reset_token_string",
    "email": "tanaka@example.com",
    "password": "newSecurePassword456",
    "password_confirmation": "newSecurePassword456"
}
```

---

## 3. セリフ API

### GET `/api/v1/quotes` - セリフ一覧

**クエリパラメータ:**
| パラメータ | 型 | デフォルト | 説明 |
|-----------|-----|-----------|------|
| page | int | 1 | ページ番号 |
| per_page | int | 20 | 1ページあたり件数 (最大100) |
| sort | string | "newest" | ソート順: newest / popular / random |
| work_id | int | - | 作品IDでフィルタ |
| tag | string | - | タグスラッグでフィルタ |

**コントローラー実装例:**

```php
// app/Http/Controllers/Api/QuoteController.php
public function index(Request $request)
{
    $quotes = Quote::approved()
        ->with(['work:id,title,type', 'location:id,name,latitude,longitude', 'user:id,display_name,avatar_path'])
        ->when($request->work_id, fn ($q, $id) => $q->where('work_id', $id))
        ->when($request->tag, fn ($q, $tag) => $q->whereHas('tags', fn ($t) => $t->where('slug', $tag)))
        ->when($request->sort === 'popular', fn ($q) => $q->orderByDesc('likes_count'))
        ->when($request->sort !== 'popular', fn ($q) => $q->latest())
        ->paginate($request->input('per_page', 20));

    return response()->json(['success' => true, 'data' => $quotes]);
}
```

**レスポンス (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "quote_text": "生きろ。",
            "character_name": "アシタカ",
            "work": {
                "id": 1,
                "title": "もののけ姫",
                "type": "anime"
            },
            "location": {
                "id": 1,
                "name": "屋久島",
                "latitude": 30.35580000,
                "longitude": 130.50690000
            },
            "user": {
                "id": 1,
                "display_name": "田中太郎",
                "avatar_path": null
            },
            "likes_count": 42,
            "is_liked": false,
            "is_favorited": false,
            "created_at": "2026-02-14T10:00:00+09:00"
        }
    ],
    "meta": {
        "page": 1,
        "per_page": 20,
        "total": 150
    }
}
```

### GET `/api/v1/quotes/{quote}` - セリフ詳細

**レスポンス (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "quote_text": "生きろ。",
        "character_name": "アシタカ",
        "scene_description": "タタラ場の戦いの後、サンに向かって言うセリフ",
        "episode_info": null,
        "language": "ja",
        "photo_path": null,
        "status": "approved",
        "likes_count": 42,
        "is_liked": false,
        "is_favorited": false,
        "work": {
            "id": 1,
            "title": "もののけ姫",
            "type": "anime",
            "year": 1997,
            "poster_path": "/storage/works/mononoke.webp"
        },
        "location": {
            "id": 1,
            "name": "屋久島",
            "latitude": 30.35580000,
            "longitude": 130.50690000,
            "country": "日本",
            "region": "鹿児島県",
            "address": "鹿児島県熊毛郡屋久島町"
        },
        "user": {
            "id": 1,
            "display_name": "田中太郎",
            "avatar_path": null
        },
        "tags": [
            { "id": 1, "name": "感動", "slug": "kando" },
            { "id": 2, "name": "自然", "slug": "shizen" }
        ],
        "created_at": "2026-02-14T10:00:00+09:00"
    }
}
```

### POST `/api/v1/quotes` - セリフ投稿 [要認証]

**FormRequest:** `App\Http\Requests\StoreQuoteRequest`

**リクエスト:**
```json
{
    "quote_text": "生きろ。",
    "work_id": 1,
    "character_name": "アシタカ",
    "scene_description": "タタラ場の戦いの後、サンに向かって言うセリフ",
    "episode_info": null,
    "language": "ja",
    "location": {
        "name": "屋久島",
        "latitude": 30.35580000,
        "longitude": 130.50690000,
        "address": "鹿児島県熊毛郡屋久島町"
    },
    "tags": [1, 2]
}
```

**バリデーション (FormRequest):**

```php
// app/Http/Requests/StoreQuoteRequest.php
public function rules(): array
{
    return [
        'quote_text'         => ['required', 'string', 'max:2000'],
        'work_id'            => ['required', 'exists:works,id'],
        'character_name'     => ['nullable', 'string', 'max:200'],
        'scene_description'  => ['nullable', 'string', 'max:5000'],
        'episode_info'       => ['nullable', 'string', 'max:100'],
        'language'           => ['nullable', 'string', 'max:10'],
        'location.name'      => ['required', 'string', 'max:255'],
        'location.latitude'  => ['required', 'numeric', 'between:-90,90'],
        'location.longitude' => ['required', 'numeric', 'between:-180,180'],
        'location.address'   => ['nullable', 'string', 'max:500'],
        'tags'               => ['nullable', 'array', 'max:10'],
        'tags.*'             => ['integer', 'exists:tags,id'],
        'photo'              => ['nullable', 'image', 'max:5120'],
    ];
}

public function authorize(): bool
{
    return true; // auth:sanctum ミドルウェアで認証済み
}
```

**レスポンス (201):**
```json
{
    "success": true,
    "data": {
        "id": 2,
        "quote_text": "生きろ。",
        "status": "pending",
        "message": "投稿が完了しました。管理者の承認後に公開されます。"
    }
}
```

### PUT `/api/v1/quotes/{quote}` - セリフ編集 [要認証, 投稿者のみ]

**認可:** `QuotePolicy@update` で投稿者のみ許可。

リクエストボディはPOSTと同じ形式。部分更新可能。

### DELETE `/api/v1/quotes/{quote}` - セリフ削除 [要認証, 投稿者 or 管理者]

**認可:** `QuotePolicy@delete` で投稿者または管理者のみ許可。

**レスポンス (200):**
```json
{
    "success": true,
    "data": {
        "message": "セリフを削除しました"
    }
}
```

### POST `/api/v1/quotes/{quote}/vote` - いいねトグル [要認証]

**レスポンス (200):**
```json
{
    "success": true,
    "data": {
        "liked": true,
        "likes_count": 43
    }
}
```

**実装:** `VoteObserver` が `Vote` の作成/削除時に `quotes.likes_count` を自動同期。

---

## 4. 地図データ API

### GET `/api/v1/map/quotes` - バウンディングボックス内セリフ取得

**クエリパラメータ:**
| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| north | float | YES | 北端の緯度 |
| south | float | YES | 南端の緯度 |
| east | float | YES | 東端の経度 |
| west | float | YES | 西端の経度 |
| zoom | int | NO | ズームレベル (データ量調整用) |
| work_type | string | NO | 作品タイプでフィルタ |
| tag | string | NO | タグでフィルタ |

**コントローラー実装例:**

```php
// app/Http/Controllers/Api/MapController.php
public function quotes(Request $request)
{
    $request->validate([
        'north' => 'required|numeric|between:-90,90',
        'south' => 'required|numeric|between:-90,90',
        'east'  => 'required|numeric|between:-180,180',
        'west'  => 'required|numeric|between:-180,180',
    ]);

    $quotes = Quote::approved()
        ->inBoundingBox($request->north, $request->south, $request->east, $request->west)
        ->with(['work:id,title,type', 'location:id,name,latitude,longitude'])
        ->select('id', 'quote_text', 'character_name', 'work_id', 'location_id', 'likes_count')
        ->limit(500)
        ->get();

    return response()->json(['success' => true, 'data' => $quotes]);
}
```

**レスポンス (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "latitude": 30.35580000,
            "longitude": 130.50690000,
            "quote_text": "生きろ。",
            "character_name": "アシタカ",
            "work_title": "もののけ姫",
            "work_type": "anime",
            "likes_count": 42,
            "location_name": "屋久島"
        }
    ]
}
```

**注意:** ズームレベルが低い場合は返すデータ量を制限し、クラスタリングされた集約データを返す。

### GET `/api/v1/map/clusters` - クラスタリングデータ

**クエリパラメータ:** north, south, east, west, zoom

**レスポンス (200):**
```json
{
    "success": true,
    "data": [
        {
            "latitude": 35.68000000,
            "longitude": 139.76000000,
            "count": 15,
            "top_quote_id": 5
        }
    ]
}
```

### GET `/api/v1/locations/{location}` - 場所詳細

**レスポンス (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "屋久島",
        "description": "世界自然遺産。もののけ姫の森のモデル。",
        "latitude": 30.35580000,
        "longitude": 130.50690000,
        "country": "日本",
        "region": "鹿児島県",
        "city": "屋久島町",
        "address": "鹿児島県熊毛郡屋久島町",
        "quotes_count": 3,
        "recent_quotes": [ ... ]
    }
}
```

### GET `/api/v1/locations/{location}/quotes` - 場所のセリフ一覧

**クエリパラメータ:** page, per_page, sort

---

## 5. お気に入り API [要認証]

### GET `/api/v1/favorites` - お気に入り一覧

**クエリパラメータ:** page, per_page

**レスポンス (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "quote_text": "生きろ。",
            "character_name": "アシタカ",
            "work": { "id": 1, "title": "もののけ姫", "type": "anime" },
            "location": { "id": 1, "name": "屋久島" },
            "is_favorited": true,
            "favorited_at": "2026-02-14T12:00:00+09:00"
        }
    ],
    "meta": { "page": 1, "per_page": 20, "total": 5 }
}
```

### POST `/api/v1/favorites` - お気に入り追加

**リクエスト:**
```json
{
    "quote_id": 1
}
```

### DELETE `/api/v1/favorites/{quote_id}` - お気に入り解除

---

## 6. 訪問記録 API [要認証]

### GET `/api/v1/visits` - 訪問記録一覧

**クエリパラメータ:** page, per_page

### POST `/api/v1/visits` - 訪問記録追加

**FormRequest:** `App\Http\Requests\StoreVisitRequest`

**リクエスト:**
```json
{
    "location_id": 1,
    "visited_at": "2026-02-14",
    "note": "苔むす森が美しかった",
    "rating": 5
}
```

**バリデーション:**
| フィールド | ルール |
|-----------|--------|
| location_id | required, exists:locations,id |
| visited_at | required, date, before_or_equal:today |
| note | nullable, string, max:5000 |
| rating | nullable, integer, between:1,5 |

### PUT `/api/v1/visits/{visit}` - 訪問記録更新

### DELETE `/api/v1/visits/{visit}` - 訪問記録削除

---

## 7. 検索 API

### GET `/api/v1/search` - 横断検索

**クエリパラメータ:**
| パラメータ | 型 | デフォルト | 説明 |
|-----------|-----|-----------|------|
| q | string | (必須) | 検索文字列 |
| type | string | "all" | 検索対象: all / quotes / works / locations |
| page | int | 1 | ページ番号 |
| per_page | int | 20 | 1ページあたり件数 |

**レスポンス (200):**
```json
{
    "success": true,
    "data": {
        "quotes": [
            { "id": 1, "quote_text": "生きろ。", "character_name": "アシタカ", "work_title": "もののけ姫" }
        ],
        "works": [
            { "id": 1, "title": "もののけ姫", "type": "anime", "year": 1997 }
        ],
        "locations": [
            { "id": 1, "name": "屋久島", "latitude": 30.35580000, "longitude": 130.50690000, "quotes_count": 3 }
        ]
    },
    "meta": {
        "total_quotes": 1,
        "total_works": 1,
        "total_locations": 1
    }
}
```

---

## 8. 作品 API

### GET `/api/v1/works` - 作品一覧

**クエリパラメータ:** type, page, per_page, sort(name/newest/popular)

### GET `/api/v1/works/{work}` - 作品詳細

### POST `/api/v1/works` - 作品登録 [要認証]

**リクエスト:**
```json
{
    "title": "もののけ姫",
    "title_original": "Princess Mononoke",
    "type": "anime",
    "year": 1997,
    "country": "日本",
    "description": "宮崎駿監督によるスタジオジブリの長編アニメーション映画"
}
```

### GET `/api/v1/works/search?q=` - 作品検索 (オートコンプリート用)

**レスポンス (200):**
```json
{
    "success": true,
    "data": [
        { "id": 1, "title": "もののけ姫", "type": "anime", "year": 1997 }
    ]
}
```

---

## 9. 管理者 API [要管理者権限]

ミドルウェア `auth:sanctum` + `admin` (カスタムミドルウェア) を適用。

### GET `/api/v1/admin/quotes/pending` - 承認待ちセリフ一覧

### PUT `/api/v1/admin/quotes/{quote}/approve` - セリフ承認

**レスポンス (200):**
```json
{
    "success": true,
    "data": {
        "id": 2,
        "status": "approved",
        "message": "セリフを承認しました"
    }
}
```

### PUT `/api/v1/admin/quotes/{quote}/reject` - セリフ拒否

**リクエスト:**
```json
{
    "reason": "内容が不適切です"
}
```

### GET `/api/v1/admin/reports` - 通報一覧

**クエリパラメータ:** status(open/reviewed/resolved/dismissed), page, per_page

### PUT `/api/v1/admin/reports/{report}` - 通報処理

**リクエスト:**
```json
{
    "status": "resolved",
    "action": "セリフを削除しました"
}
```

### GET `/api/v1/admin/stats` - 統計情報

**レスポンス (200):**
```json
{
    "success": true,
    "data": {
        "total_users": 150,
        "total_quotes": 500,
        "total_works": 80,
        "total_locations": 200,
        "pending_quotes": 12,
        "open_reports": 3,
        "quotes_today": 5,
        "registrations_today": 2
    }
}
```

### PUT `/api/v1/admin/users/{user}/role` - ユーザー権限変更

**リクエスト:**
```json
{
    "role": "moderator"
}
```

### PUT `/api/v1/admin/users/{user}/ban` - ユーザーBAN

**リクエスト:**
```json
{
    "reason": "スパム行為"
}
```
