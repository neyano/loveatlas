# LoveAtlas - データベース設計

## 1. ER関係図

```
users ──1:N──> quotes         (ユーザーがセリフを投稿)
users ──1:N──> favorites      (ユーザーがセリフをお気に入り)
users ──1:N──> visits         (ユーザーが場所を訪問)
users ──1:N──> votes          (ユーザーがセリフにいいね)
users ──1:N──> reports        (ユーザーがセリフを通報)
works ──1:N──> quotes         (作品にセリフが紐づく)
locations ──1:N──> quotes     (場所にセリフが紐づく)
locations ──1:N──> visits     (場所に訪問記録が紐づく)
quotes ──1:N──> favorites     (セリフがお気に入りされる)
quotes ──1:N──> votes         (セリフにいいねされる)
quotes <──N:M──> tags         (セリフとタグの多対多: quote_tags)
```

---

## 2. テーブル定義

### 2.1 `users` - ユーザー

| カラム | 型 | 制約 | 説明 |
|--------|-----|------|------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| username | VARCHAR(50) | NOT NULL, UNIQUE | ユーザー名 |
| email | VARCHAR(255) | NOT NULL, UNIQUE | メールアドレス |
| password | VARCHAR(255) | NOT NULL | bcryptハッシュ |
| display_name | VARCHAR(100) | NOT NULL | 表示名 |
| avatar_path | VARCHAR(255) | NULL | アバター画像パス |
| bio | TEXT | NULL | 自己紹介 |
| role | VARCHAR(20) | NOT NULL, DEFAULT 'user' | 権限 (user/moderator/admin) |
| is_active | BOOLEAN | NOT NULL, DEFAULT true | 有効フラグ |
| email_verified_at | TIMESTAMP | NULL | メール認証日時 (Laravel標準) |
| remember_token | VARCHAR(100) | NULL | ログイン記憶トークン (Laravel標準) |
| created_at | TIMESTAMP | NULL | |
| updated_at | TIMESTAMP | NULL | |

### 2.2 `works` - 作品 (映画/アニメ/ドラマ等)

| カラム | 型 | 制約 | 説明 |
|--------|-----|------|------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| title | VARCHAR(255) | NOT NULL | 作品名 |
| title_original | VARCHAR(255) | NULL | 原題 |
| type | VARCHAR(20) | NOT NULL | 作品タイプ (movie/anime/drama/novel/game/other) |
| year | SMALLINT UNSIGNED | NULL | 公開/放送年 |
| country | VARCHAR(100) | NULL | 国 |
| description | TEXT | NULL | 概要 |
| poster_path | VARCHAR(255) | NULL | ポスター画像パス |
| external_url | VARCHAR(500) | NULL | 外部リンク (Wikipedia等) |
| submitted_by | BIGINT UNSIGNED | FK→users, NULL | 投稿者 |
| is_approved | BOOLEAN | NOT NULL, DEFAULT false | 管理者承認フラグ |
| created_at | TIMESTAMP | NULL | |
| updated_at | TIMESTAMP | NULL | |

### 2.3 `locations` - 場所

| カラム | 型 | 制約 | 説明 |
|--------|-----|------|------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(255) | NOT NULL | 場所名 |
| description | TEXT | NULL | 説明 |
| latitude | DECIMAL(10,8) | NOT NULL | 緯度 (-90 ~ +90) |
| longitude | DECIMAL(11,8) | NOT NULL | 経度 (-180 ~ +180) |
| country | VARCHAR(100) | NULL | 国 |
| region | VARCHAR(255) | NULL | 都道府県/州 |
| city | VARCHAR(255) | NULL | 市区町村 |
| address | VARCHAR(500) | NULL | 詳細住所 |
| place_id | VARCHAR(255) | NULL | 外部地図サービスID (将来用) |
| created_at | TIMESTAMP | NULL | |
| updated_at | TIMESTAMP | NULL | |

### 2.4 `quotes` - 名セリフ (中核テーブル)

| カラム | 型 | 制約 | 説明 |
|--------|-----|------|------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| user_id | BIGINT UNSIGNED | NOT NULL, FK→users | 投稿者 |
| work_id | BIGINT UNSIGNED | NOT NULL, FK→works | 作品 |
| location_id | BIGINT UNSIGNED | NOT NULL, FK→locations | 場所 |
| quote_text | TEXT | NOT NULL | セリフ本文 |
| character_name | VARCHAR(200) | NULL | キャラクター名 |
| scene_description | TEXT | NULL | シーンの説明 |
| episode_info | VARCHAR(100) | NULL | 話数・シーズン情報 |
| language | VARCHAR(10) | NOT NULL, DEFAULT 'ja' | セリフの言語 |
| photo_path | VARCHAR(255) | NULL | 関連画像パス |
| status | VARCHAR(20) | NOT NULL, DEFAULT 'pending' | 承認状態 (pending/approved/rejected) |
| likes_count | INT UNSIGNED | NOT NULL, DEFAULT 0 | いいね数 (非正規化) |
| created_at | TIMESTAMP | NULL | |
| updated_at | TIMESTAMP | NULL | |

### 2.5 `tags` - タグ

| カラム | 型 | 制約 | 説明 |
|--------|-----|------|------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(50) | NOT NULL, UNIQUE | タグ名 |
| slug | VARCHAR(50) | NOT NULL, UNIQUE | URL用スラッグ |
| category | VARCHAR(20) | NOT NULL, DEFAULT 'theme' | カテゴリ (genre/mood/theme/scene_type) |
| usage_count | INT UNSIGNED | NOT NULL, DEFAULT 0 | 使用回数 (非正規化) |
| created_at | TIMESTAMP | NULL | |
| updated_at | TIMESTAMP | NULL | |

### 2.6 `quote_tag` - セリフ-タグ中間テーブル

| カラム | 型 | 制約 | 説明 |
|--------|-----|------|------|
| quote_id | BIGINT UNSIGNED | FK→quotes | |
| tag_id | BIGINT UNSIGNED | FK→tags | |

**注:** Laravel の命名規約に従い、中間テーブル名は単数形アルファベット順 `quote_tag`。

### 2.7 `favorites` - お気に入り

| カラム | 型 | 制約 | 説明 |
|--------|-----|------|------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| user_id | BIGINT UNSIGNED | NOT NULL, FK→users | |
| quote_id | BIGINT UNSIGNED | NOT NULL, FK→quotes | |
| created_at | TIMESTAMP | NULL | |
| updated_at | TIMESTAMP | NULL | |

**制約:** UNIQUE(user_id, quote_id)

### 2.8 `visits` - 訪問記録

| カラム | 型 | 制約 | 説明 |
|--------|-----|------|------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| user_id | BIGINT UNSIGNED | NOT NULL, FK→users | |
| location_id | BIGINT UNSIGNED | NOT NULL, FK→locations | |
| visited_at | DATE | NOT NULL | 訪問日 |
| note | TEXT | NULL | メモ |
| photo_path | VARCHAR(255) | NULL | 訪問時の写真 |
| rating | TINYINT UNSIGNED | NULL | 1-5の評価 |
| created_at | TIMESTAMP | NULL | |
| updated_at | TIMESTAMP | NULL | |

**制約:** UNIQUE(user_id, location_id, visited_at)

### 2.9 `votes` - いいね

| カラム | 型 | 制約 | 説明 |
|--------|-----|------|------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| user_id | BIGINT UNSIGNED | NOT NULL, FK→users | |
| quote_id | BIGINT UNSIGNED | NOT NULL, FK→quotes | |
| value | TINYINT | NOT NULL, DEFAULT 1 | 1=いいね |
| created_at | TIMESTAMP | NULL | |
| updated_at | TIMESTAMP | NULL | |

**制約:** UNIQUE(user_id, quote_id)

### 2.10 `reports` - 通報

| カラム | 型 | 制約 | 説明 |
|--------|-----|------|------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| reporter_id | BIGINT UNSIGNED | NOT NULL, FK→users | 通報者 |
| quote_id | BIGINT UNSIGNED | NOT NULL, FK→quotes | 対象セリフ |
| reason | VARCHAR(20) | NOT NULL | 理由 (spam/inappropriate/wrong_info/copyright/other) |
| description | TEXT | NULL | 詳細説明 |
| status | VARCHAR(20) | NOT NULL, DEFAULT 'open' | 処理状態 (open/reviewed/resolved/dismissed) |
| reviewed_by | BIGINT UNSIGNED | FK→users, NULL | 対応者 |
| resolved_at | TIMESTAMP | NULL | 解決日時 |
| created_at | TIMESTAMP | NULL | |
| updated_at | TIMESTAMP | NULL | |

---

## 3. Laravel Migration

### 3.1 マイグレーションファイル一覧

```
database/migrations/
├── 0001_01_01_000000_create_users_table.php          ← Laravel 標準 (カスタマイズ)
├── 0001_01_01_000001_create_cache_table.php           ← Laravel 標準
├── 0001_01_01_000002_create_jobs_table.php            ← Laravel 標準
├── 2026_02_14_000001_create_works_table.php
├── 2026_02_14_000002_create_locations_table.php
├── 2026_02_14_000003_create_quotes_table.php
├── 2026_02_14_000004_create_tags_table.php
├── 2026_02_14_000005_create_quote_tag_table.php
├── 2026_02_14_000006_create_favorites_table.php
├── 2026_02_14_000007_create_visits_table.php
├── 2026_02_14_000008_create_votes_table.php
└── 2026_02_14_000009_create_reports_table.php
```

### 3.2 マイグレーション例

```php
// database/migrations/2026_02_14_000001_create_works_table.php
public function up(): void
{
    Schema::create('works', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('title_original')->nullable();
        $table->string('type', 20);            // movie, anime, drama, novel, game, other
        $table->smallInteger('year')->unsigned()->nullable();
        $table->string('country', 100)->nullable();
        $table->text('description')->nullable();
        $table->string('poster_path')->nullable();
        $table->string('external_url', 500)->nullable();
        $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
        $table->boolean('is_approved')->default(false);
        $table->timestamps();

        $table->index('type');
        $table->index('is_approved');
        $table->fullText(['title', 'title_original']);
    });
}
```

```php
// database/migrations/2026_02_14_000003_create_quotes_table.php
public function up(): void
{
    Schema::create('quotes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('work_id')->constrained()->cascadeOnDelete();
        $table->foreignId('location_id')->constrained()->cascadeOnDelete();
        $table->text('quote_text');
        $table->string('character_name', 200)->nullable();
        $table->text('scene_description')->nullable();
        $table->string('episode_info', 100)->nullable();
        $table->string('language', 10)->default('ja');
        $table->string('photo_path')->nullable();
        $table->string('status', 20)->default('pending');   // pending, approved, rejected
        $table->unsignedInteger('likes_count')->default(0);
        $table->timestamps();

        $table->index('status');
        $table->index(['likes_count']);
        $table->fullText(['quote_text', 'character_name', 'scene_description']);
    });
}
```

---

## 4. Eloquent モデル・リレーション

### 4.1 リレーション定義

```php
// app/Models/User.php
class User extends Authenticatable
{
    public function quotes(): HasMany { ... }
    public function favorites(): BelongsToMany { ... }   // quotes via favorites テーブル
    public function votes(): HasMany { ... }
    public function visits(): HasMany { ... }
}

// app/Models/Quote.php
class Quote extends Model
{
    public function user(): BelongsTo { ... }
    public function work(): BelongsTo { ... }
    public function location(): BelongsTo { ... }
    public function tags(): BelongsToMany { ... }
    public function voters(): BelongsToMany { ... }       // users via votes テーブル
    public function favoritedBy(): BelongsToMany { ... }  // users via favorites テーブル

    // スコープ
    public function scopeApproved(Builder $query): void { ... }
    public function scopeInBoundingBox(Builder $query, float $n, float $s, float $e, float $w): void { ... }
}

// app/Models/Work.php
class Work extends Model
{
    public function quotes(): HasMany { ... }
    public function submitter(): BelongsTo { ... }        // users
}

// app/Models/Location.php
class Location extends Model
{
    public function quotes(): HasMany { ... }
    public function visits(): HasMany { ... }
}

// app/Models/Tag.php
class Tag extends Model
{
    public function quotes(): BelongsToMany { ... }
}
```

### 4.2 クエリスコープ例

```php
// Quote モデル - バウンディングボックス検索
public function scopeInBoundingBox(Builder $query, float $n, float $s, float $e, float $w): void
{
    $query->whereHas('location', function ($q) use ($n, $s, $e, $w) {
        $q->whereBetween('latitude', [$s, $n])
          ->whereBetween('longitude', [$w, $e]);
    });
}

// Quote モデル - 承認済みのみ
public function scopeApproved(Builder $query): void
{
    $query->where('status', 'approved');
}
```

---

## 5. Seeder

### 5.1 Seeder 一覧

```
database/seeders/
├── DatabaseSeeder.php          ← メインSeeder
├── AdminUserSeeder.php         ← 管理者ユーザー
├── WorkSeeder.php              ← サンプル作品データ
├── LocationSeeder.php          ← サンプル場所データ
├── QuoteSeeder.php             ← サンプル名言データ
└── TagSeeder.php               ← 初期タグデータ
```

### 5.2 実行

```bash
php artisan db:seed              # 全Seeder実行
php artisan db:seed --class=QuoteSeeder  # 個別実行
php artisan migrate:fresh --seed  # DB再作成 + Seed
```

---

## 6. 設計判断の説明

### `locations` を独立テーブルにした理由

同じ場所に複数のセリフが紐づく可能性が高い。場所情報の正規化により訪問記録の管理も一貫性を持たせられる。Eloquent のリレーションで `$location->quotes` のように直感的にアクセスできる。

### `likes_count` の非正規化

地図上にピンを表示する際、セリフの人気度でソートやフィルタリングを行う。毎回 `withCount('voters')` を呼ぶよりもキャッシュ済みカウンタの方がマップ表示のパフォーマンスが良い。Laravel の Observer で votes の作成/削除時に自動同期する。

### ENUM の代わりに VARCHAR を使用

Laravel の Migration では ENUM も使用可能だが、カラム変更時の制約があるため VARCHAR + バリデーションルールの組み合わせを採用。バリデーションは `Rule::in(['pending', 'approved', 'rejected'])` で行う。

### FULLTEXT INDEX の活用

MariaDB 10.4 は InnoDB の FULLTEXT をサポート。Laravel の `whereFullText()` メソッドで利用可能。日本語全文検索には `ngram` パーサーの設定が必要になる可能性があるため、Phase 2 で Laravel Scout + Meilisearch への移行も視野に入れる。
