# LoveAtlas - セキュリティ設計書

## 1. セキュリティ方針

すべてのユーザー入力は信頼しない。Laravel が提供するセキュリティ機構を最大限活用し、フレームワークの規約に従った安全な実装を行う。

---

## 2. 認証設計

### 2.1 認証パッケージ

| 項目 | 仕様 |
|------|------|
| 認証スカフォールド | Laravel Breeze |
| SPA認証 | Laravel Sanctum (Cookie ベース) |
| パスワードハッシュ | bcrypt (Laravel デフォルト: `Hash::make()`) |
| パスワード検証 | `Hash::check()` |
| 最小要件 | 8文字以上、英字+数字の混在必須 |
| 平文保存 | 禁止。ログ出力も禁止 |

### 2.2 セッション管理

```php
// config/session.php
return [
    'driver'          => 'file',       // 開発時はfile、本番はdatabase推奨
    'lifetime'        => 120,          // 2時間
    'expire_on_close' => false,
    'encrypt'         => false,
    'cookie'          => 'loveatlas_session',
    'http_only'       => true,         // JSからCookieアクセス不可
    'secure'          => env('SESSION_SECURE_COOKIE', false), // 本番はtrue
    'same_site'       => 'lax',        // CSRF軽減
];
```

| 項目 | 仕様 |
|------|------|
| セッションID再生成 | Laravel が認証成功時に自動で `session()->regenerate()` を実行 |
| セッション破棄 | ログアウト時に `session()->invalidate()` + `session()->regenerateToken()` |
| タイムアウト | 2時間無操作でセッション期限切れ |
| CSRF トークン | `session()->token()` で自動管理 |

### 2.3 Sanctum SPA 認証フロー

```
[SPA 認証フロー]
1. GET /sanctum/csrf-cookie → XSRF-TOKEN Cookie を取得
2. POST /api/v1/auth/login  → セッション認証 (Cookie 自動セット)
3. 以降の API リクエストで Cookie が自動送信される
4. axios の withCredentials: true で Cookie を自動付与

[パスワードリセットフロー]
1. POST /api/v1/auth/forgot-password  → Laravel Breeze のリセットメール送信
2. メール内リンクからリセットフォームへ
3. POST /api/v1/auth/reset-password   → パスワード更新
```

### 2.4 Sanctum 設定

```php
// config/sanctum.php
return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS',
        'localhost,localhost:5173,loveatlas.local'
    )),
    'expiration' => null,  // セッションベースなので不要
];
```

```php
// config/cors.php
return [
    'paths'                => ['api/*', 'sanctum/csrf-cookie'],
    'supports_credentials' => true,   // Cookie 送信を許可
];
```

---

## 3. 入力バリデーション

### 3.1 Laravel FormRequest

すべての POST/PUT リクエストに対して FormRequest クラスを作成し、コントローラー到達前にバリデーションを実行する。

```php
// app/Http/Requests/StoreQuoteRequest.php
class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quote_text'         => ['required', 'string', 'max:2000'],
            'work_id'            => ['required', 'exists:works,id'],
            'character_name'     => ['nullable', 'string', 'max:200'],
            'scene_description'  => ['nullable', 'string', 'max:5000'],
            'location.name'      => ['required', 'string', 'max:255'],
            'location.latitude'  => ['required', 'numeric', 'between:-90,90'],
            'location.longitude' => ['required', 'numeric', 'between:-180,180'],
            'tags'               => ['nullable', 'array', 'max:10'],
            'tags.*'             => ['integer', 'exists:tags,id'],
            'photo'              => ['nullable', 'image', 'max:5120'], // 5MB
        ];
    }
}
```

### 3.2 バリデーションルール一覧

| フィールド | ルール |
|-----------|--------|
| username | required, string, min:3, max:50, regex:/^[a-zA-Z0-9_]+$/, unique:users |
| email | required, email, max:255, unique:users |
| password | required, string, min:8, confirmed, regex:/(?=.*[a-zA-Z])(?=.*[0-9])/ |
| display_name | required, string, max:100 |
| quote_text | required, string, max:2000 |
| character_name | nullable, string, max:200 |
| scene_description | nullable, string, max:5000 |
| latitude | required, numeric, between:-90,90 |
| longitude | required, numeric, between:-180,180 |
| rating | nullable, integer, between:1,5 |
| tags | nullable, array, max:10 |
| tags.* | integer, exists:tags,id |

---

## 4. SQLインジェクション対策

### 4.1 方針

- **Eloquent ORM を使用**することで、プリペアドステートメントが自動適用される
- 生のSQL文字列結合は全面禁止
- `DB::raw()` を使用する場合は必ずバインディングを使用

### 4.2 Eloquent による安全なクエリ

```php
// Eloquent (安全: 自動的にプリペアドステートメント)
$quotes = Quote::where('work_id', $workId)
    ->where('status', 'approved')
    ->get();

// クエリビルダ (安全: バインディング使用)
$quotes = DB::table('quotes')
    ->where('work_id', '=', $workId)
    ->get();
```

### 4.3 禁止パターン

```php
// 禁止: 文字列結合
DB::select("SELECT * FROM quotes WHERE work_id = $workId");

// 許可: バインディング
DB::select("SELECT * FROM quotes WHERE work_id = ?", [$workId]);
```

### 4.4 LIKE句のエスケープ

```php
// 検索時は Eloquent の where LIKE でバインディング
$quotes = Quote::where('quote_text', 'LIKE', '%' . addcslashes($search, '%_') . '%')->get();
```

---

## 5. XSS (クロスサイトスクリプティング) 対策

### 5.1 Blade テンプレートの自動エスケープ

```blade
{{-- Blade の {{ }} は自動的に htmlspecialchars() を適用 --}}
<p class="quote-text">{{ $quote->quote_text }}</p>
<span class="character">{{ $quote->character_name }}</span>

{{-- 生のHTMLを出力する場合 (信頼できるコンテンツのみ) --}}
{!! $trustedHtml !!}
```

**ルール:**
- ユーザー入力は **必ず** `{{ }}` (ダブルブレース) で出力
- `{!! !!}` はシステム生成の信頼できるHTMLのみに使用
- Vue コンポーネント内では `v-text` または `{{ }}` (Vue テンプレート構文) を使用し、`v-html` は避ける

### 5.2 Content-Security-Policy ヘッダー

```php
// app/Http/Middleware/SecurityHeaders.php
class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; "
            . "script-src 'self'; "
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
            . "font-src 'self' https://fonts.gstatic.com; "
            . "img-src 'self' https://*.tile.openstreetmap.org data:; "
            . "connect-src 'self' https://nominatim.openstreetmap.org; "
        );

        return $response;
    }
}
```

### 5.3 Vue コンポーネントでのサニタイズ

```vue
<template>
  <!-- 安全: Vue のテンプレート構文は自動エスケープ -->
  <p>{{ quote.quote_text }}</p>

  <!-- 禁止: v-html はユーザー入力に使わない -->
  <!-- <p v-html="quote.quote_text"></p> -->
</template>
```

---

## 6. CSRF (クロスサイトリクエストフォージェリ) 対策

### 6.1 Laravel の CSRF 保護

Laravel はデフォルトで `VerifyCsrfToken` ミドルウェアが全 POST/PUT/DELETE リクエストを保護する。

### 6.2 Blade フォームでの使用

```blade
<form method="POST" action="{{ route('quotes.store') }}">
    @csrf
    ...
</form>
```

### 6.3 SPA (axios) での使用

```javascript
// resources/js/bootstrap.js
import axios from 'axios';

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

// Sanctum が XSRF-TOKEN Cookie をセットし、
// axios が自動的に X-XSRF-TOKEN ヘッダーに付与する
```

### 6.4 API ルートの除外

```php
// routes/api.php のルートは Laravel 11 ではデフォルトで
// CSRF 検証が除外されるが、Sanctum SPA 認証では
// Cookie ベースのセッションを使用するため CSRF が有効になる。
```

---

## 7. ファイルアップロードセキュリティ

### 7.1 許可ファイル

| 用途 | 許可拡張子 | 最大サイズ |
|------|-----------|-----------|
| アバター画像 | jpg, jpeg, png, webp | 2MB |
| セリフ関連画像 | jpg, jpeg, png, webp | 5MB |
| 訪問写真 | jpg, jpeg, png, webp | 5MB |

### 7.2 バリデーション (FormRequest)

```php
// アバターアップロード
'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

// セリフ画像アップロード
'photo'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
```

### 7.3 画像処理 (Intervention Image 3.x)

```php
// app/Services/ImageService.php
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    public function store(UploadedFile $file, string $directory): string
    {
        $manager = new ImageManager(new Driver());

        // 画像を再描画 (メタデータ・悪意あるコード除去)
        $image = $manager->read($file->getPathname());

        // リサイズ (アスペクト比維持)
        $image->scaleDown(width: 800);

        // WebP 変換
        $filename = Str::random(32) . '.webp';
        $path = "uploads/{$directory}/{$filename}";
        $image->toWebp(80)->save(storage_path("app/public/{$path}"));

        return $path;
    }
}
```

### 7.4 ストレージ設定

```php
// config/filesystems.php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root'   => storage_path('app/public'),
        'url'    => env('APP_URL') . '/storage',
    ],
],
```

```bash
# シンボリックリンク作成
php artisan storage:link
# → public/storage → storage/app/public
```

### 7.5 画像リサイズ仕様

| 種別 | サイズ | 用途 |
|------|--------|------|
| サムネイル | 200x200px (アスペクト比維持、クロップ) | 一覧表示、アバター |
| 表示用 | 800x600px (アスペクト比維持、縮小のみ) | 詳細ページ |

---

## 8. レート制限

### 8.1 Laravel RateLimiter 設定

```php
// app/Providers/AppServiceProvider.php (boot メソッド)
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

public function boot(): void
{
    // API 全般: 60回/分
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    });

    // ログイン: 5回/分
    RateLimiter::for('login', function (Request $request) {
        return Limit::perMinute(5)->by($request->ip());
    });

    // セリフ投稿: 10回/時
    RateLimiter::for('quote-post', function (Request $request) {
        return Limit::perHour(10)->by($request->user()->id);
    });

    // 作品登録: 5回/時
    RateLimiter::for('work-post', function (Request $request) {
        return Limit::perHour(5)->by($request->user()->id);
    });

    // パスワードリセット: 3回/時
    RateLimiter::for('password-reset', function (Request $request) {
        return Limit::perHour(3)->by($request->ip());
    });
}
```

### 8.2 ルートへの適用

```php
// routes/api.php
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');

Route::post('/quotes', [QuoteController::class, 'store'])
    ->middleware(['auth:sanctum', 'throttle:quote-post']);
```

### 8.3 制限超過時のレスポンス

```
HTTP/1.1 429 Too Many Requests
Retry-After: 60
X-RateLimit-Limit: 5
X-RateLimit-Remaining: 0

{
    "success": false,
    "error": {
        "code": "RATE_LIMIT_EXCEEDED",
        "message": "リクエスト回数の上限に達しました。しばらくしてから再度お試しください。"
    }
}
```

---

## 9. 権限制御 (認可)

### 9.1 ロール定義

| ロール | 権限 |
|--------|------|
| user | セリフ閲覧・投稿・編集(自分のみ)・削除(自分のみ)、いいね、お気に入り、訪問記録 |
| moderator | user の全権限 + セリフ承認/拒否、通報管理 |
| admin | moderator の全権限 + ユーザー管理、権限変更、統計閲覧 |

### 9.2 Laravel Policy

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
        return $user->id === $quote->user_id || $user->role === 'admin';
    }

    public function approve(User $user): bool
    {
        return in_array($user->role, ['moderator', 'admin']);
    }
}
```

### 9.3 コントローラーでの認可

```php
// コントローラー内
public function update(UpdateQuoteRequest $request, Quote $quote)
{
    $this->authorize('update', $quote);
    // ...
}

public function destroy(Quote $quote)
{
    $this->authorize('delete', $quote);
    $quote->delete();
    return response()->json(['success' => true, 'data' => ['message' => 'セリフを削除しました']]);
}
```

### 9.4 管理者ミドルウェア

```php
// app/Http/Middleware/AdminMiddleware.php
class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!in_array($request->user()->role, ['admin', 'moderator'])) {
            abort(403, '管理者権限が必要です');
        }
        return $next($request);
    }
}
```

---

## 10. その他のセキュリティ対策

### 10.1 HTTPセキュリティヘッダー

```php
// app/Http/Middleware/SecurityHeaders.php
class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');

        return $response;
    }
}
```

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
})
```

### 10.2 エラーハンドリング

```php
// .env
APP_DEBUG=true    // 開発環境
APP_DEBUG=false   // 本番環境 (詳細エラーを非表示)
```

Laravel は `APP_DEBUG=false` の場合、ユーザーに詳細なエラーメッセージを表示しない。すべてのエラーは `storage/logs/laravel.log` に記録される。

### 10.3 マスアサインメント保護

```php
// app/Models/Quote.php
class Quote extends Model
{
    protected $fillable = [
        'quote_text', 'work_id', 'location_id', 'character_name',
        'scene_description', 'episode_info', 'language', 'photo_path',
    ];

    // status, likes_count, user_id は fillable に含めない → 直接代入不可
}
```

### 10.4 .env / .gitignore

```
# .gitignore
.env                    # 環境変数 (DB接続情報、APP_KEY等)
storage/logs/           # ログファイル
storage/app/public/uploads/  # ユーザーアップロード画像
vendor/                 # Composer依存
node_modules/           # npm依存
public/build/           # Vite ビルド成果物
```

### 10.5 APP_KEY 管理

```bash
# APP_KEY は初回セットアップ時に生成し、厳重に管理
php artisan key:generate
```

`APP_KEY` はセッションの暗号化、パスワードリセットトークンの署名等に使用される。漏洩した場合はすべてのセッションが無効になる。
