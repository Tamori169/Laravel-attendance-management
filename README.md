# アプリケーション名

Coachtech　勤怠管理アプリ

## アプリケーション概要

- 一般ユーザ：勤怠情報の登録および一覧、修正の申請が行えます。
- 管理者：全スタッフおよび各勤怠情報の一覧、修正の承認が行えます。

## 使用技術(実行環境)

- PHP 8.1.34
- Laravel 8.83.8
- MySQL 8.0.26
- Docker
- Docker Compose
- phpMyAdmin
- MailHog
- Git
- GitHub

## 環境構築

### 1. リポジトリをクローン

```
git clone https://github.com/Tamori169/Laravel-attendance-management.git
cd Laravel-attendance-management
```

### 2. Dockerコンテナを作成・起動

```
docker-compose up -d --build
```

### 3. PHPコンテナに入る

```
docker-compose exec php bash
```

### 4. Composerパッケージをインストール

```
composer install
```
※ Laravel Sanctum は導入済みのため、追加で `composer require laravel/sanctum` を実行する必要はありません。

### 5. .envファイルを作成

```
cp .env.example .env
```

### 6. アプリケーションキーを作成

```
php artisan key:generate
```

### 7. 環境変数の設定

詳細は「環境変数」の項目を参照

### 8. データベースマイグレーション

```
php artisan migrate
```

### 9. シーディング実行

```
php artisan db:seed
```

### "The stream or file could not be opened"エラーが発生した場合

srcディレクトリにあるstorageディレクトリに権限を設定

```
chmod -R 777 storage
```

## 環境変数

`.env.example` をもとに `.env` を作成し、以下の項目を設定してください。

- `APP_KEY`
  - `php artisan key:generate` で生成
- `DB_HOST=mysql`
- `DB_DATABASE=laravel_db`
- `DB_USERNAME=laravel_user`
- `DB_PASSWORD=laravel_pass`
- `MAIL_FROM_ADDRESS=test@example.com`

## ER図

![ER図](ER.drawio.png)

## URL一覧

### 1. 一般ユーザーがアクセス可能なページ一覧

- 認証不要
  - 会員登録画面（トップ画面）：`http://localhost/register`
  - ログイン画面：`http://localhost/login`
  - メール認証誘導画面：`http://localhost/email/verify`
- 認証要
  - 出勤登録画面：`http://localhost/attendance`
  - 勤怠一覧画面：`http://localhost/attendance/list`
  - 勤怠詳細画面：`http://localhost/attendance/detail/{id}`
  - 申請一覧画面：`http://localhost/attendance/stamp_correction_request/list`
  - マイ勤怠レポート画面：`http://localhost/attendance/report`

### 2. 管理者がアクセス可能なページ一覧

- 認証不要
  - ログイン画面：`http://localhost/login`
- 認証要
  - 勤怠一覧画面（トップ画面）：`http://localhost/admin/attendance/list`
  - 勤怠詳細画面：`http://localhost/admin/attendance/{id}`
  - スタッフ一覧画面：`http://localhost/admin/staff/list`
  - スタッフ別勤怠一覧画面：`http://localhost/admin/attendance/staff/{id}`
  - 申請一覧画面：`http://localhost/stamp_correction_request/list`
  - 修正申請承認画面：`http://localhost/stamp_correction_request/approve/{attendance_correct_request_id}`

### 3. DB管理画面

- phpMyAdmin：`http://localhost:8080`

## MailHog設定

本アプリではメール認証機能の確認に MailHog を使用しています。  
Docker起動後、MailHog は `http://localhost:8025` で確認が可能です。

`.env` への設定内容は下記の通りです。

```env
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_FROM_ADDRESS=test@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

## 機能テスト実行手順

`config/database.php` にはテスト用DB接続設定を記述済みです。  
以下の手順でテスト用DBと環境ファイルを作成後、テストを実行してください。

### 1. MySQLにログイン

```
cd Laravel-attendance-management
docker-compose exec mysql bash
```

### 2. rootにアクセス

```
mysql -u root -p
```
パスワードは `root` を入力してください。

### 3. テスト用DBを作成

```
CREATE DATABASE laravel_attendance_management_test;
```

### 4. MySQLを終了

```
exit
```

### 5. PHPコンテナに入る

```
docker-compose exec php bash
```

### 6. .env.testingを作成

```
cp .env .env.testing
```

### 7. 環境変数設定

```.env.testing
APP_ENV=testing
DB_DATABASE=laravel_attendance_management_test
DB_USERNAME=root
DB_PASSWORD=root
```

### 8. 設定キャッシュをクリア

```
php artisan config:clear
php artisan cache:clear
```

### 9. データベースマイグレーション

```
php artisan migrate --env=testing
```

### 10. テスト実行

```
php artisan test
```

## テストユーザー

### 1. ユーザー情報一覧

シーディングにより、下記３名の動作確認用テストユーザーが作成されます。  
各ユーザーの認証状況および権限は下記の通りです。  
また、シーディングにより一般ユーザーは6ヶ月分の勤怠情報が作成されます。


テストユーザー1

- ユーザ名：ユーザー１
- メールアドレス：user1@example.com
- パスワード：password
- メール認証：済
- 権限：一般ユーザー

テストユーザー2

- ユーザ名：ユーザー２
- メールアドレス：user2@example.com
- パスワード：password
- メール認証：済
- 権限：一般ユーザー

テストユーザー３

- ユーザ名：ユーザー３
- メールアドレス：user3@example.com
- パスワード：password
- メール認証：未済（権限上メール認証は不要）
- 権限：管理者


## API仕様

本アプリケーションでは、勤怠情報を取得・登録・更新・削除するAPIを実装しています。  
APIのバージョンは `v1` です。

### エンドポイント一覧

| メソッド | URL | 説明 | 認証 |
| --- | --- | --- | --- |
| GET | `/api/v1/attendance-records` | 勤怠情報一覧を取得 | 不要 |
| GET | `/api/v1/attendance-records/{attendanceRecord}` | 勤怠情報詳細を取得 | 不要 |
| POST | `/api/v1/attendance-records` | 勤怠情報を登録 | 必要 |
| PUT/PATCH | `/api/v1/attendance-records/{attendanceRecord}` | 勤怠情報を更新 | 必要 |
| DELETE | `/api/v1/attendance-records/{attendanceRecord}` | 勤怠情報を削除 | 必要 |

### 認証・認可

書き込み系APIでは Laravel Sanctum によるBearerトークン認証を使用しています。  
更新・削除では `AttendanceRecordPolicy` により、本人または管理者のみ操作可能です。  
本アプリケーションではAPI用のログイン・トークン発行エンドポイントは用意していません。  
機能テストでは `Sanctum::actingAs($user, ['*'])` を使用して認証済みユーザーとしてリクエストを実行しています。

### リクエスト例

#### 勤怠一覧取得

```http
GET /api/v1/attendance-records?user_id=1&month=2026-07&per_page=20
```

| パラメータ | 説明 | 例 |
| --- | --- | --- |
| `user_id` | ユーザーIDで絞り込み | `1` |
| `date` | 日付で絞り込み | `2026-07-03` |
| `month` | 月で絞り込み | `2026-07` |
| `per_page` | 1ページあたりの取得件数 | `20` |

#### 勤怠登録・更新

```json
{
  "date": "2026-07-03",
  "clock_in": "09:00",
  "clock_out": "18:00",
  "comment": "通常勤務"
}
```

### レスポンス例

```json
{
  "data": {
    "id": 1,
    "user": {
      "id": 1,
      "name": "ユーザー１"
    },
    "date": "2026-07-03",
    "clock_in": "09:00:00",
    "clock_out": "18:00:00",
    "total_time": "08:00",
    "total_break_time": "01:00",
    "comment": "通常勤務",
    "breaks": [
      {
        "id": 1,
        "break_in": "12:00:00",
        "break_out": "13:00:00"
      }
    ]
  }
}
```

### エラーレスポンス例

```json
{
  "error": "勤怠情報が見つかりませんでした。"
}
```

```json
{
  "error": "この操作を実行する権限がありません。"
}
```