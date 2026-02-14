# LoveAtlas - バッチ処理・運用設計書

## 1. 環境構成

### 1.1 開発環境

| 項目 | 内容 |
|------|------|
| OS | Windows 11 Pro |
| Webサーバー | Apache 2.4 (XAMPP同梱) |
| PHP | 8.2.12 (ZTS, x64) |
| DB | MariaDB 10.4.32 |
| フレームワーク | Laravel 11.x |
| フロントエンド | Vue 3 (Composition API) + Vite |
| パス | `C:\xampp-php8.2.12\xampp\htdocs\loveatlas` |
| ドキュメントルート | `loveatlas/public/` |

### 1.2 Apache 設定

```apache
# httpd-vhosts.conf (推奨)
<VirtualHost *:80>
    ServerName loveatlas.local
    DocumentRoot "C:/xampp-php8.2.12/xampp/htdocs/loveatlas/public"

    <Directory "C:/xampp-php8.2.12/xampp/htdocs/loveatlas/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

```
# hosts ファイル (C:\Windows\System32\drivers\etc\hosts)
127.0.0.1   loveatlas.local
```

### 1.3 MariaDB 設定

```ini
# my.ini に追加 (日本語全文検索対応)
[mysqld]
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
innodb_ft_min_token_size = 1
```

---

## 2. セットアップ手順

### 2.1 初回セットアップ

```bash
# 1. プロジェクトディレクトリに移動
cd C:\xampp-php8.2.12\xampp\htdocs\loveatlas

# 2. Composer 依存をインストール
composer install

# 3. npm 依存をインストール
npm install

# 4. 環境設定ファイルを作成
cp .env.example .env

# 5. アプリケーションキーを生成
php artisan key:generate

# 6. .env を編集 (DB接続情報)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=loveatlas
# DB_USERNAME=root
# DB_PASSWORD=

# 7. データベース作成
mysql -u root -e "CREATE DATABASE loveatlas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 8. マイグレーション実行 + シーダー
php artisan migrate --seed

# 9. ストレージのシンボリックリンク作成
php artisan storage:link

# 10. Vite 開発サーバー起動
npm run dev

# 11. Laravel 開発サーバー起動 (Apache を使わない場合)
php artisan serve

# 12. ブラウザで確認
# http://loveatlas.local/ (Apache) or http://localhost:8000/ (artisan serve)
```

### 2.2 .env テンプレート

```env
APP_NAME=LoveAtlas
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://loveatlas.local

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=loveatlas
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120

SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173,loveatlas.local

VITE_APP_NAME="${APP_NAME}"
```

### 2.3 よく使う Artisan コマンド

```bash
# マイグレーション
php artisan migrate                    # マイグレーション実行
php artisan migrate:rollback           # 直前のマイグレーションを戻す
php artisan migrate:fresh --seed       # DB再作成 + シーダー実行
php artisan migrate:status             # マイグレーション状態確認

# シーダー
php artisan db:seed                    # 全シーダー実行
php artisan db:seed --class=QuoteSeeder  # 個別実行

# キャッシュ
php artisan config:cache               # 設定キャッシュ
php artisan route:cache                # ルートキャッシュ
php artisan view:cache                 # ビューキャッシュ
php artisan cache:clear                # キャッシュクリア
php artisan config:clear               # 設定キャッシュクリア
php artisan optimize:clear             # 全キャッシュクリア

# 開発ツール
php artisan make:controller Api/QuoteController   # コントローラー作成
php artisan make:model Quote -mfs                 # モデル+マイグレーション+ファクトリ+シーダー
php artisan make:request StoreQuoteRequest        # FormRequest 作成
php artisan make:policy QuotePolicy --model=Quote # Policy 作成
php artisan make:observer VoteObserver --model=Vote # Observer 作成
php artisan make:command SyncLikesCount           # カスタムコマンド作成

# テスト
php artisan test                       # テスト実行
php artisan test --filter=QuoteTest    # 特定テスト実行

# Vite
npm run dev                            # 開発サーバー起動
npm run build                          # 本番ビルド
```

---

## 3. バッチ処理

### 3.1 バッチ一覧

| ID | バッチ名 | Artisan コマンド | 実行タイミング | 概要 |
|----|---------|-----------------|-------------|------|
| B-001 | いいね数同期 | `app:sync-likes-count` | 日次 (深夜) | votes テーブルから quotes.likes_count を再計算 |
| B-002 | タグ使用数同期 | `app:sync-tag-usage` | 日次 (深夜) | quote_tag テーブルから tags.usage_count を再計算 |
| B-003 | 古いセッション削除 | `session:gc` | 日次 | 期限切れセッションファイルの削除 |
| B-004 | ログクリア | `log:clear` | 週次 | 古いログファイルの削除 |
| B-005 | DBバックアップ | `app:db-backup` | 週次 | mysqldump でバックアップ取得 |

### 3.2 B-001: いいね数同期コマンド

```php
// app/Console/Commands/SyncLikesCount.php
class SyncLikesCount extends Command
{
    protected $signature = 'app:sync-likes-count';
    protected $description = 'votes テーブルから quotes.likes_count を再計算';

    public function handle(): int
    {
        $updated = DB::statement("
            UPDATE quotes q
            SET q.likes_count = (
                SELECT COUNT(*) FROM votes v
                WHERE v.quote_id = q.id AND v.value = 1
            )
        ");

        $this->info('likes_count の同期が完了しました。');
        return Command::SUCCESS;
    }
}
```

### 3.3 B-002: タグ使用数同期コマンド

```php
// app/Console/Commands/SyncTagUsage.php
class SyncTagUsage extends Command
{
    protected $signature = 'app:sync-tag-usage';
    protected $description = 'quote_tag テーブルから tags.usage_count を再計算';

    public function handle(): int
    {
        DB::statement("
            UPDATE tags t
            SET t.usage_count = (
                SELECT COUNT(*) FROM quote_tag qt
                WHERE qt.tag_id = t.id
            )
        ");

        $this->info('usage_count の同期が完了しました。');
        return Command::SUCCESS;
    }
}
```

### 3.4 B-005: DBバックアップコマンド

```php
// app/Console/Commands/DbBackup.php
class DbBackup extends Command
{
    protected $signature = 'app:db-backup';
    protected $description = 'データベースのバックアップを取得';

    public function handle(): int
    {
        $backupDir = storage_path('backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'loveatlas_' . date('Ymd_His') . '.sql';
        $path = "{$backupDir}/{$filename}";

        $command = sprintf(
            'mysqldump -u%s %s %s > %s',
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password') ? '-p' . config('database.connections.mysql.password') : '',
            config('database.connections.mysql.database'),
            $path
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            $this->info("バックアップ完了: {$filename}");
        } else {
            $this->error('バックアップに失敗しました。');
            return Command::FAILURE;
        }

        // 30日以上前のバックアップを削除
        $files = glob("{$backupDir}/*.sql");
        foreach ($files as $file) {
            if (filemtime($file) < strtotime('-30 days')) {
                unlink($file);
                $this->info('古いバックアップを削除: ' . basename($file));
            }
        }

        return Command::SUCCESS;
    }
}
```

### 3.5 Laravel タスクスケジューラ

```php
// routes/console.php (Laravel 11)
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:sync-likes-count')->daily()->at('03:00');
Schedule::command('app:sync-tag-usage')->daily()->at('03:10');
Schedule::command('app:db-backup')->weekly()->sundays()->at('04:00');
```

```bash
# Windows タスクスケジューラで毎分実行を登録
# タスクスケジューラ → 新しいタスク → 操作:
# プログラム: C:\xampp-php8.2.12\xampp\php\php.exe
# 引数: C:\xampp-php8.2.12\xampp\htdocs\loveatlas\artisan schedule:run
# トリガー: 毎分
```

---

## 4. ログ設計

### 4.1 Laravel ログ設定

```php
// config/logging.php
'channels' => [
    'stack' => [
        'driver'   => 'stack',
        'channels' => ['single'],
    ],
    'single' => [
        'driver' => 'single',
        'path'   => storage_path('logs/laravel.log'),
        'level'  => env('LOG_LEVEL', 'debug'),
    ],
    'daily' => [
        'driver' => 'daily',
        'path'   => storage_path('logs/laravel.log'),
        'level'  => env('LOG_LEVEL', 'debug'),
        'days'   => 14,
    ],
],
```

### 4.2 ログ種別

| ログ | パス | 内容 |
|------|------|------|
| アプリケーションログ | storage/logs/laravel.log | Laravel のログ (Log ファサード) |
| PHPエラーログ | storage/logs/php_errors.log | PHP のエラー・警告 |
| アクセスログ | Apache の access.log | HTTPリクエストログ (Apache管理) |

### 4.3 ログの使用例

```php
use Illuminate\Support\Facades\Log;

// エラー
Log::error('セリフ投稿失敗', [
    'work_id' => $workId,
    'user_id' => auth()->id(),
    'error'   => $exception->getMessage(),
]);

// 情報
Log::info('ユーザーログイン', [
    'user_id' => $user->id,
    'ip'      => request()->ip(),
]);

// 警告
Log::warning('レート制限超過', [
    'ip'     => request()->ip(),
    'action' => 'login',
]);
```

### 4.4 ログレベル

| レベル | 用途 |
|--------|------|
| ERROR | 処理が失敗した場合 (DB接続エラー、例外等) |
| WARNING | 注意が必要な状態 (レート制限超過、不正アクセス試行) |
| INFO | 正常な重要イベント (ユーザー登録、ログイン、セリフ投稿) |
| DEBUG | 開発時のデバッグ情報 (本番では無効化) |

---

## 5. 監視項目

### 5.1 手動チェック (開発フェーズ)

| 項目 | 確認方法 | 頻度 |
|------|---------|------|
| Apache 動作確認 | XAMPP コントロールパネル | 開発時 |
| MariaDB 動作確認 | XAMPP コントロールパネル | 開発時 |
| エラーログ確認 | `storage/logs/laravel.log` の確認 | 日次 |
| ディスク容量 | storage/app/public/uploads/ のサイズ確認 | 週次 |
| DB サイズ | phpMyAdmin で確認 | 月次 |
| Vite ビルド確認 | `npm run build` が正常完了すること | リリース前 |

### 5.2 Artisan による確認コマンド

```bash
# ルート一覧確認
php artisan route:list

# 設定確認
php artisan config:show database

# マイグレーション状態
php artisan migrate:status

# キュー状態 (将来使用時)
php artisan queue:monitor
```

### 5.3 アラート条件 (将来の本番運用時)

| 条件 | 対応 |
|------|------|
| Apache/MariaDB プロセスダウン | サービス再起動 |
| ディスク使用率 90% 以上 | 古いログ・バックアップの削除 |
| ERROR ログが1時間に10件以上 | 原因調査 |

---

## 6. データメンテナンス

### 6.1 データベース保守

| 作業 | 頻度 | コマンド |
|------|------|---------|
| テーブル最適化 | 月次 | `OPTIMIZE TABLE quotes, votes, favorites;` |
| FULLTEXT インデックス再構築 | 月次 | マイグレーションで実行 |
| 統計情報更新 | 月次 | `ANALYZE TABLE quotes, works, locations, users;` |

### 6.2 画像ファイル管理

| 作業 | 頻度 | 内容 |
|------|------|------|
| 孤立画像の検出 | 月次 | DBに参照されていない uploads/ 内の画像を検出 |
| 孤立画像の削除 | 手動 | 検出した孤立画像を確認の上で削除 |
| ディスク使用量確認 | 月次 | storage/app/public/uploads/ の合計サイズ確認 |

### 6.3 キャッシュ管理

```bash
# 全キャッシュクリア
php artisan optimize:clear

# ビューキャッシュのみクリア (Blade テンプレート変更時)
php artisan view:clear

# 設定キャッシュ再生成 (本番デプロイ時)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 7. デプロイ手順

### 7.1 開発環境でのデプロイ

```bash
# 1. 最新コードを取得
git pull origin main

# 2. Composer 依存を更新
composer install --no-dev

# 3. npm 依存を更新・ビルド
npm install
npm run build

# 4. マイグレーション実行
php artisan migrate

# 5. キャッシュ再生成
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. 動作確認
php artisan test
```

---

## 8. 障害対応

### 8.1 障害レベル定義

| レベル | 状態 | 対応 |
|--------|------|------|
| 軽微 | 一部機能が動作しない (例: 画像アップロード失敗) | 次回開発時に修正 |
| 中程度 | 主要機能に影響 (例: 検索が動作しない) | 当日中に修正 |
| 重大 | サービス全体が停止 (例: DB接続不可) | 即時対応 |

### 8.2 復旧手順

**Apache が起動しない場合:**
```
1. XAMPP コントロールパネルで Apache を再起動
2. ポート80が他プロセスに使用されていないか確認 (netstat -ano | findstr :80)
3. httpd.conf、.htaccess の構文エラーを確認
```

**MariaDB が起動しない場合:**
```
1. XAMPP コントロールパネルで MariaDB を再起動
2. storage/logs/laravel.log で DB接続エラーを確認
3. .env の DB接続設定を確認
4. 最悪の場合、最新バックアップからリストア
```

**Laravel エラー (500) の場合:**
```
1. storage/logs/laravel.log でエラー内容を確認
2. php artisan optimize:clear でキャッシュクリア
3. composer dump-autoload でオートロード再生成
4. .env の APP_DEBUG=true にして詳細エラーを確認
```

**DBリストア手順:**
```bash
mysql -u root loveatlas < storage/backups/loveatlas_YYYYMMDD_HHMMSS.sql
```
