# LoveAtlas - テスト設計書

## 1. テスト方針

Laravel の標準テスト機能 (PHPUnit + Feature テスト) を活用し、主要な API エンドポイントと認証フローの自動テストを作成する。フロントエンド (Vue) は手動テストを中心に行う。

---

## 2. テスト種別

| 種別 | 対象 | ツール |
|------|------|--------|
| Feature テスト | API エンドポイント、認証フロー | PHPUnit (Laravel Feature Tests) |
| Unit テスト | Service クラス、Model スコープ | PHPUnit (Laravel Unit Tests) |
| バリデーションテスト | FormRequest のルール | PHPUnit |
| セキュリティテスト | 認証・認可・CSRF | PHPUnit |
| レスポンシブテスト | 各画面サイズでの表示 | ブラウザの開発者ツール (手動) |
| E2Eテスト | ユーザー操作フロー全体 | ブラウザ手動テスト (将来 Playwright 導入) |

---

## 3. テスト環境設定

### 3.1 phpunit.xml

```xml
<!-- phpunit.xml -->
<phpunit>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
    </php>
</phpunit>
```

### 3.2 テストの基本構造

```php
// tests/Feature/Api/QuoteApiTest.php
namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Quote;
use App\Models\Work;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteApiTest extends TestCase
{
    use RefreshDatabase;

    // ...
}
```

### 3.3 Factory パターン

```php
// database/factories/QuoteFactory.php
class QuoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'work_id'        => Work::factory(),
            'location_id'    => Location::factory(),
            'quote_text'     => $this->faker->realText(200),
            'character_name' => $this->faker->name(),
            'status'         => 'approved',
            'likes_count'    => 0,
            'language'       => 'ja',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }
}
```

---

## 4. Feature テスト

### 4.1 認証テスト

```php
// tests/Feature/Api/AuthTest.php
class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_ユーザー登録_正常(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'username'              => 'tanaka_taro',
            'email'                 => 'tanaka@example.com',
            'password'              => 'Password123',
            'password_confirmation' => 'Password123',
            'display_name'          => '田中太郎',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.username', 'tanaka_taro');

        $this->assertDatabaseHas('users', ['username' => 'tanaka_taro']);
    }

    public function test_ユーザー登録_既存メール(): void
    {
        User::factory()->create(['email' => 'tanaka@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'username'              => 'tanaka_taro',
            'email'                 => 'tanaka@example.com',
            'password'              => 'Password123',
            'password_confirmation' => 'Password123',
            'display_name'          => '田中太郎',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_ログイン_正常(): void
    {
        $user = User::factory()->create([
            'email'    => 'tanaka@example.com',
            'password' => bcrypt('Password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => 'tanaka@example.com',
            'password' => 'Password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_ログイン_パスワード不正(): void
    {
        User::factory()->create([
            'email'    => 'tanaka@example.com',
            'password' => bcrypt('Password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => 'tanaka@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_ログアウト(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
    }

    public function test_未認証でme取得_401(): void
    {
        $response = $this->getJson('/api/v1/auth/me');
        $response->assertStatus(401);
    }
}
```

### 4.2 セリフ API テスト

```php
// tests/Feature/Api/QuoteApiTest.php
class QuoteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_セリフ一覧_正常(): void
    {
        Quote::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/quotes');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(5, 'data');
    }

    public function test_セリフ一覧_未承認は非表示(): void
    {
        Quote::factory()->create(['status' => 'approved']);
        Quote::factory()->create(['status' => 'pending']);

        $response = $this->getJson('/api/v1/quotes');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_セリフ詳細_正常(): void
    {
        $quote = Quote::factory()->create();

        $response = $this->getJson("/api/v1/quotes/{$quote->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $quote->id);
    }

    public function test_セリフ投稿_正常(): void
    {
        $user = User::factory()->create();
        $work = Work::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/quotes', [
                'quote_text'     => 'テストセリフ',
                'work_id'        => $work->id,
                'character_name' => 'テストキャラ',
                'location'       => [
                    'name'      => 'テスト場所',
                    'latitude'  => 35.6812,
                    'longitude' => 139.7671,
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_セリフ投稿_未認証_401(): void
    {
        $response = $this->postJson('/api/v1/quotes', [
            'quote_text' => 'テスト',
        ]);

        $response->assertStatus(401);
    }

    public function test_セリフ投稿_バリデーションエラー(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/quotes', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quote_text', 'work_id', 'location.name']);
    }

    public function test_セリフ削除_投稿者のみ(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $quote = Quote::factory()->create(['user_id' => $user->id]);

        // 他人は削除不可
        $this->actingAs($other)
            ->deleteJson("/api/v1/quotes/{$quote->id}")
            ->assertStatus(403);

        // 投稿者は削除可
        $this->actingAs($user)
            ->deleteJson("/api/v1/quotes/{$quote->id}")
            ->assertStatus(200);
    }

    public function test_いいねトグル(): void
    {
        $user = User::factory()->create();
        $quote = Quote::factory()->create();

        // いいね追加
        $response = $this->actingAs($user)
            ->postJson("/api/v1/quotes/{$quote->id}/vote");

        $response->assertStatus(200)
            ->assertJsonPath('data.liked', true);

        // いいね解除
        $response = $this->actingAs($user)
            ->postJson("/api/v1/quotes/{$quote->id}/vote");

        $response->assertStatus(200)
            ->assertJsonPath('data.liked', false);
    }
}
```

### 4.3 地図 API テスト

```php
// tests/Feature/Api/MapApiTest.php
class MapApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_バウンディングボックス取得(): void
    {
        $location = Location::factory()->create([
            'latitude'  => 35.6812,
            'longitude' => 139.7671,
        ]);
        Quote::factory()->create([
            'location_id' => $location->id,
            'status'      => 'approved',
        ]);

        $response = $this->getJson('/api/v1/map/quotes?' . http_build_query([
            'north' => 36.0,
            'south' => 35.0,
            'east'  => 140.0,
            'west'  => 139.0,
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_範囲外のセリフは返さない(): void
    {
        $location = Location::factory()->create([
            'latitude'  => 43.0,  // 北海道
            'longitude' => 141.0,
        ]);
        Quote::factory()->create([
            'location_id' => $location->id,
            'status'      => 'approved',
        ]);

        $response = $this->getJson('/api/v1/map/quotes?' . http_build_query([
            'north' => 36.0,   // 東京周辺
            'south' => 35.0,
            'east'  => 140.0,
            'west'  => 139.0,
        ]));

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_パラメータ不足_422(): void
    {
        $response = $this->getJson('/api/v1/map/quotes');

        $response->assertStatus(422);
    }
}
```

### 4.4 管理者 API テスト

```php
// tests/Feature/Api/AdminTest.php
class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_管理者がセリフ承認(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $quote = Quote::factory()->pending()->create();

        $response = $this->actingAs($admin)
            ->putJson("/api/v1/admin/quotes/{$quote->id}/approve");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('quotes', [
            'id'     => $quote->id,
            'status' => 'approved',
        ]);
    }

    public function test_一般ユーザーは管理画面にアクセス不可(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/admin/quotes/pending');

        $response->assertStatus(403);
    }

    public function test_未認証は管理画面にアクセス不可(): void
    {
        $response = $this->getJson('/api/v1/admin/quotes/pending');

        $response->assertStatus(401);
    }
}
```

---

## 5. セキュリティテスト

```php
// tests/Feature/SecurityTest.php
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_SQLインジェクション_防御(): void
    {
        $user = User::factory()->create();
        $work = Work::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/quotes', [
                'quote_text'     => "'; DROP TABLE quotes; --",
                'work_id'        => $work->id,
                'character_name' => 'テスト',
                'location'       => [
                    'name'      => 'テスト場所',
                    'latitude'  => 35.6812,
                    'longitude' => 139.7671,
                ],
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseCount('quotes', 1);
    }

    public function test_XSS_エスケープ(): void
    {
        $quote = Quote::factory()->create([
            'quote_text' => '<script>alert("XSS")</script>',
        ]);

        $response = $this->getJson("/api/v1/quotes/{$quote->id}");

        $response->assertStatus(200);
        // JSON レスポンスでは自動エスケープされないが、
        // Blade テンプレートの {{ }} で安全にエスケープされる
        $response->assertJsonPath('data.quote_text', '<script>alert("XSS")</script>');
    }

    public function test_権限昇格_防御(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/admin/stats');

        $response->assertStatus(403);
    }

    public function test_他人のセリフ編集_防御(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $quote = Quote::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)
            ->putJson("/api/v1/quotes/{$quote->id}", [
                'quote_text' => '改ざんされたセリフ',
            ]);

        $response->assertStatus(403);
    }
}
```

---

## 6. 手動テスト項目

### 6.1 地図機能

| # | テスト項目 | 期待結果 | 結果 |
|---|-----------|---------|------|
| T-001 | ホームページ読込 | Leaflet地図が表示される | |
| T-002 | 初期表示 | 日本全体が見える位置・ズームで表示 | |
| T-003 | 地図をドラッグして移動 | 新しい表示範囲のピンが読み込まれる | |
| T-004 | ズームイン | クラスタが分解されて個別ピンが表示される | |
| T-005 | ズームアウト | ピンがクラスタにまとまる | |
| T-006 | ピンクリック | ポップアップ表示 (セリフ冒頭、作品名、いいね数) | |
| T-007 | ポップアップ「詳細→」 | セリフ詳細ページに遷移 | |
| T-008 | ピンの色分け | 映画=青, アニメ=赤, ドラマ=緑 で表示 | |

### 6.2 セリフ投稿 (Vue コンポーネント)

| # | テスト項目 | 期待結果 | 結果 |
|---|-----------|---------|------|
| T-009 | 正常な入力で投稿 | 投稿成功、「承認待ち」メッセージ表示 | |
| T-010 | 作品をオートコンプリートで選択 | 既存作品が候補表示される | |
| T-011 | 地図クリックで場所選択 | 緯度経度が取得されフォームに反映 | |
| T-012 | 住所検索で場所選択 | Nominatimで検索、地図にピン表示 | |
| T-013 | 画像を添付して投稿 | 画像がWebPに変換されアップロードされる | |

### 6.3 レスポンシブテスト

| # | 画面 | デバイス | 確認項目 |
|---|------|---------|---------|
| R-001 | ホーム | iPhone SE (375px) | 地図全画面、ボトムシート表示 |
| R-002 | ホーム | iPhone 14 (390px) | 同上 |
| R-003 | ホーム | iPad (768px) | 地図上部、セリフ一覧下部 |
| R-004 | ホーム | デスクトップ (1280px) | サイドパネル+地図の分割表示 |
| R-005 | セリフ詳細 | iPhone SE | コンテンツが折り返し表示 |
| R-006 | セリフ投稿 | iPhone SE | 地図の高さ適切、フォームスクロール可能 |
| R-007 | ログイン | iPhone SE | フォームが画面内に収まる |
| R-008 | 管理画面 | iPad | サイドナビ + コンテンツの分割 |

---

## 7. テスト実行コマンド

```bash
# 全テスト実行
php artisan test

# 特定テストクラスを実行
php artisan test --filter=AuthTest

# 特定テストメソッドを実行
php artisan test --filter=test_ユーザー登録_正常

# カバレッジレポート付き (要 Xdebug)
php artisan test --coverage

# 並列実行 (Laravel 11)
php artisan test --parallel
```

---

## 8. CI/CD (将来計画)

```yaml
# .github/workflows/test.yml (将来導入時)
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - run: composer install
      - run: cp .env.example .env
      - run: php artisan key:generate
      - run: php artisan test
```
