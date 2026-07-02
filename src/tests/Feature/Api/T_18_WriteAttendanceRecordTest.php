<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceRecord;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class T_18_WriteAttendanceRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_POSTで勤怠が作成される()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        Sanctum::actingAs($user);

        $data = [
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => 'Test comment',
        ];

        $postResponse = $this->postJson('/api/v1/attendance-records', $data);

        $postResponse->assertStatus(201);

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00',
            'comment' => 'Test comment',
        ]);
    }

    public function test_バリデーションエラー時に422と日本語エラーメッセージが返る_required()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        Sanctum::actingAs($user);

        $data = [
            'user_id' => $user->id,
            'date' => null,
            'clock_in' => null,
            'clock_out' => '18:00:00',
            'comment' => 'Test comment',
        ];

        $postResponse = $this->postJson('/api/v1/attendance-records', $data);

        $postResponse->assertStatus(422);
        $postResponse->assertJsonValidationErrors([
            'date' => '勤怠日は必須です。',
            'clock_in' => '出勤時刻は必須です。',
        ]);
    }

    public function test_バリデーションエラー時に422と日本語エラーメッセージが返る_date_format()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        Sanctum::actingAs($user);

        $data = [
            'user_id' => $user->id,
            'date' => '2026/06/24',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'comment' => 'Test comment',
        ];

        $postResponse = $this->postJson('/api/v1/attendance-records', $data);

        $postResponse->assertStatus(422);
        $postResponse->assertJsonValidationErrors([
            'date' => '勤怠日は YYYY-MM-DD 形式で指定してください。',
            'clock_in' => '出勤時刻は HH:MM:SS 形式で指定してください。',
            'clock_out' => '退勤時刻は HH:MM:SS 形式で指定してください。',
        ]);
    }

    public function test_バリデーションエラー時に422と日本語エラーメッセージが返る_unique()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        Sanctum::actingAs($user);

        $data = [
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => 'Test comment',
        ];

        $postResponse = $this->postJson('/api/v1/attendance-records', $data);

        $postResponse->assertStatus(422);
        $postResponse->assertJsonValidationErrors([
            'date' => 'この日付の勤怠は既に登録されています。',
        ]);
    }

    public function test_バリデーションエラー時に422と日本語エラーメッセージが返る_max()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        Sanctum::actingAs($user);

        $data = [
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00',
            'comment' => str_repeat('a', 256),
        ];

        $postResponse = $this->postJson('/api/v1/attendance-records', $data);

        $postResponse->assertStatus(422);
        $postResponse->assertJsonValidationErrors([
            'comment' => '備考は 255 文字以内で入力してください。',
        ]);
    }

    public function test_PUTで勤怠が更新される_既存勤怠に対してPUTで更新データを送信()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        Sanctum::actingAs($user);

        $data = [
            'date' => '2026-06-24',
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
            'comment' => 'Test comment',
        ];

        $putResponse = $this->putJson("/api/v1/attendance-records/{$attendanceRecord->id}", $data);

        $putResponse->assertStatus(200);

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:30:00',
            'clock_out' => '2026-06-24 18:30:00',
            'comment' => 'Test comment',
        ]);
    }

    public function test_PUTで勤怠が更新される_存在しないIDに対してPUTを実行()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $attendanceRecord = AttendanceRecord::create([
            'id' => 1,
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        Sanctum::actingAs($user);

        $data = [
            'date' => '2026-06-24',
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
            'comment' => 'Test comment',
        ];

        $putResponse = $this->putJson("/api/v1/attendance-records/2", $data);

        $putResponse->assertStatus(404);
        $putResponse->assertJson([
            'error' => '勤怠情報が見つかりませんでした。',
        ]);
    }

    public function test_DELETEで勤怠が削除される_既存勤怠に対してDELETEを送信()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        Sanctum::actingAs($user);

        $deleteResponse = $this->deleteJson("/api/v1/attendance-records/{$attendanceRecord->id}");

        $deleteResponse->assertStatus(204);

        $this->assertDatabaseMissing('attendance_records', [
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00',
        ]);
    }

    public function test_DELETEで勤怠が更新される_存在しないIDに対してDELETEを実行()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $attendanceRecord = AttendanceRecord::create([
            'id' => 1,
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        Sanctum::actingAs($user);

        $deleteResponse = $this->deleteJson("/api/v1/attendance-records/2");

        $deleteResponse->assertStatus(404);
        $deleteResponse->assertJson([
            'error' => '勤怠情報が見つかりませんでした。',
        ]);
    }
}
