# LoveAtlas - 開発環境セットアップガイド

## 1. 前提条件

| ソフトウェア | バージョン | 備考 |
|-------------|-----------|------|
| PHP | 8.2.x | XAMPP 同梱 |
| MariaDB | 10.4.x | XAMPP 同梱 |
| Composer | 2.x | PHP パッケージ管理 |
| Node.js | 18.x 以上 | npm 同梱 |
| Git | 2.x | |

### XAMPP の設定

1. XAMPP コントロールパネルで **Apache** と **MySQL** を起動
2. Apache は開発時は不要（`php artisan serve` を使用）
3. MySQL (MariaDB) はポート **3306** で起動していること

---

## 2. 初回セットアップ

### 2.1 リポジトリのクローン

```bash
cd c:\xampp-php8.2.12\xampp\htdocs
git clone https://github.com/neyano/loveatlas.git
cd loveatlas
```

### 2.2 PHP 依存パッケージのインストール

```bash
composer install
```

### 2.3 Node.js 依存パッケージのインストール

```bash
npm install
```

### 2.4 環境設定ファイルの作成

```bash
cp .env.example .env
php artisan key:generate
```

`.env` を開き、以下を確認・編集:

```dotenv
APP_NAME=LoveAtlas
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000

APP_LOCALE=ja
APP_FAKER_LOCALE=ja_JP

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=loveatlas
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173,loveatlas.local
```

### 2.5 データベースの作成

phpMyAdmin (http://localhost/phpmyadmin) または CLI で作成:

```sql
CREATE DATABASE loveatlas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2.6 マイグレーション & シードデータ投入

```bash
php artisan migrate --seed
```

これにより以下が作成されます:
- 全テーブル (users, works, locations, quotes, tags 等)
- 管理者アカウント (`admin@loveatlas.local` / `password`)
- サンプル作品 10件、場所 11件、名言 13件、タグ 20件

---

## 3. 開発サーバーの起動

**2つのターミナルを開き、それぞれ実行してください。**

### ターミナル 1 - Laravel 開発サーバー

```bash
cd c:\xampp-php8.2.12\xampp\htdocs\loveatlas
php artisan serve
```

→ http://localhost:8000 で起動

### ターミナル 2 - Vite 開発サーバー (HMR)

```bash
cd c:\xampp-php8.2.12\xampp\htdocs\loveatlas
npm run dev
```

→ http://localhost:5173 で Vite が起動 (HMR: ホットモジュールリプレイスメント)

### アクセス

- **アプリ:** http://localhost:8000
- **phpMyAdmin:** http://localhost/phpmyadmin

---

## 4. よく使うコマンド

### Laravel (Artisan)

```bash
# マイグレーション
php artisan migrate                    # 未実行のマイグレーションを適用
php artisan migrate:fresh --seed       # DB 再作成 + シードデータ投入
php artisan migrate:status             # マイグレーション状態確認

# キャッシュ
php artisan cache:clear                # キャッシュクリア
php artisan config:clear               # 設定キャッシュクリア
php artisan route:clear                # ルートキャッシュクリア

# ルート確認
php artisan route:list                 # 登録ルート一覧

# Tinker (REPL)
php artisan tinker                     # 対話式 PHP コンソール

# テスト
php artisan test                       # PHPUnit テスト実行
php artisan test --filter=AuthTest     # 特定テスト実行
```

### npm

```bash
npm run dev                            # Vite 開発サーバー (HMR)
npm run build                          # 本番ビルド
```

### Git

```bash
git status                             # 変更状態の確認
git pull origin main                   # 最新コードの取得
git push origin main                   # コードのプッシュ
```

---

## 5. 管理者アカウント

| 項目 | 値 |
|------|-----|
| メール | `admin@loveatlas.local` |
| パスワード | `password` |
| 権限 | `admin` |

**注意:** 本番環境では必ずパスワードを変更してください。

---

## 6. プロジェクト構成

```
loveatlas/
├── app/
│   ├── Http/Controllers/Auth/    ← Breeze 認証コントローラー
│   ├── Http/Middleware/          ← SecurityHeaders, AdminMiddleware
│   ├── Models/                   ← Eloquent モデル (9個)
│   ├── Observers/                ← VoteObserver
│   └── Providers/                ← AppServiceProvider
├── bootstrap/app.php             ← ミドルウェア・ルート設定
├── config/                       ← Laravel 設定ファイル
├── database/
│   ├── factories/                ← テスト用ファクトリ (5個)
│   ├── migrations/               ← DBマイグレーション (13個)
│   └── seeders/                  ← シードデータ (5個)
├── docs/                         ← 設計書
├── public/                       ← ドキュメントルート
├── resources/
│   ├── css/app.css               ← CSS 基盤 (変数・リセット・BEM)
│   ├── js/
│   │   ├── app.js                ← Vue アプリケーション初期化
│   │   ├── api.js                ← axios API クライアント
│   │   ├── stores/               ← Pinia ストア (auth, map)
│   │   └── composables/          ← Vue composables (useMap, useDebounce)
│   └── views/
│       ├── layouts/              ← Blade レイアウト (app, admin, minimal)
│       └── home.blade.php        ← ホーム画面
├── routes/
│   ├── web.php                   ← Web ルート
│   ├── api.php                   ← API ルート
│   └── auth.php                  ← Breeze 認証ルート
├── tests/                        ← PHPUnit テスト
├── vite.config.js                ← Vite 設定 (Vue プラグイン)
├── composer.json                 ← PHP 依存関係
└── package.json                  ← Node.js 依存関係
```

---

## 7. トラブルシューティング

### `php artisan serve` でエラーが出る

```bash
# 設定キャッシュをクリア
php artisan config:clear
php artisan cache:clear
```

### DB 接続エラー

1. XAMPP で MySQL が起動しているか確認
2. `.env` の `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` を確認
3. `loveatlas` データベースが存在するか確認

### Vite でアセットが読み込めない

1. `npm run dev` が起動しているか確認
2. Blade テンプレートに `@vite()` ディレクティブがあるか確認
3. `npm install` を再実行

### マイグレーションエラー

```bash
# DB を完全に再作成
php artisan migrate:fresh --seed
```

### composer install でエラー

```bash
# PHP 拡張の確認 (fileinfo, mbstring, openssl が必要)
php -m
```

---

## 8. 開発フロー

1. `git pull origin main` で最新コードを取得
2. `composer install` と `npm install` で依存関係を更新
3. `php artisan migrate` で DB を更新
4. ターミナル 2つで `php artisan serve` と `npm run dev` を起動
5. 開発 → テスト → コミット → プッシュ
