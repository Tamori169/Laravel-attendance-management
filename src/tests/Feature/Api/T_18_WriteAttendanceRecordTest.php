<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceRecord;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class T_18_WriteAttendanceRecordTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->user = User::factory()->staff()->create();

        Sanctum::actingAs($this->user, ['*']);
    }

    public function test_POSTで勤怠が作成される()
    {
        $response = $this->postJson('/api/v1/attendance-records', $this->validPayload());

        $response->assertStatus(201);

        $this->assertDatabaseHas('attendance_records', $this->storedPayload());
    }

    public function test_バリデーションエラー時に422と日本語エラーメッセージが返る_required()
    {
        $response = $this->postJson('/api/v1/attendance-records', $this->validPayload([
            'date' => null,
            'clock_in' => null,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'date' => '勤怠日は必須です。',
            'clock_in' => '出勤時刻は必須です。',
        ]);
    }

    public function test_バリデーションエラー時に422と日本語エラーメッセージが返る_date_format()
    {
        $response = $this->postJson('/api/v1/attendance-records', $this->validPayload([
            'date' => '2026/06/24',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'date' => '勤怠日は YYYY-MM-DD 形式で指定してください。',
            'clock_in' => '出勤時刻は HH:MM:SS 形式で指定してください。',
            'clock_out' => '退勤時刻は HH:MM:SS 形式で指定してください。',
        ]);
    }

    public function test_バリデーションエラー時に422と日本語エラーメッセージが返る_unique()
    {
        $this->createAttendanceRecord();

        $response = $this->postJson('/api/v1/attendance-records', $this->validPayload());

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'date' => 'この日付の勤怠は既に登録されています。',
        ]);
    }

    public function test_バリデーションエラー時に422と日本語エラーメッセージが返る_max()
    {
        $response = $this->postJson('/api/v1/attendance-records', $this->validPayload([
            'comment' => str_repeat('a', 256),
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'comment' => '備考は 255 文字以内で入力してください。',
        ]);
    }

    public function test_PUTで勤怠が更新される_既存勤怠に対してPUTで更新データを送信()
    {
        $attendanceRecord = $this->createAttendanceRecord();

        $response = $this->putJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}",
            $this->updatePayload()
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('attendance_records', $this->storedUpdatePayload());
    }

    public function test_PUTで勤怠が更新される_存在しないIDに対してPUTを実行()
    {
        $this->createAttendanceRecord(['id' => 1]);

        $response = $this->putJson('/api/v1/attendance-records/2', $this->updatePayload());

        $response->assertStatus(404);
        $response->assertJson([
            'error' => '勤怠情報が見つかりませんでした。',
        ]);
    }

    public function test_DELETEで勤怠が削除される_既存勤怠に対してDELETEを送信()
    {
        $attendanceRecord = $this->createAttendanceRecord();

        $response = $this->deleteJson("/api/v1/attendance-records/{$attendanceRecord->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('attendance_records', $this->storedPayload([
            'comment' => null,
        ]));
    }

    public function test_DELETEで勤怠が更新される_存在しないIDに対してDELETEを実行()
    {
        $this->createAttendanceRecord(['id' => 1]);

        $response = $this->deleteJson('/api/v1/attendance-records/2');

        $response->assertStatus(404);
        $response->assertJson([
            'error' => '勤怠情報が見つかりませんでした。',
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'user_id' => $this->user->id,
            'date' => '2026-06-24',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => 'Test comment',
        ], $overrides);
    }

    private function updatePayload(array $overrides = []): array
    {
        return array_merge([
            'date' => '2026-06-24',
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
            'comment' => 'Test comment',
        ], $overrides);
    }

    private function storedPayload(array $overrides = []): array
    {
        return array_merge([
            'user_id' => $this->user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00',
            'comment' => 'Test comment',
        ], $overrides);
    }

    private function storedUpdatePayload(): array
    {
        return $this->storedPayload([
            'clock_in' => '2026-06-24 09:30:00',
            'clock_out' => '2026-06-24 18:30:00',
        ]);
    }

    private function createAttendanceRecord(array $overrides = []): AttendanceRecord
    {
        return AttendanceRecord::create(array_merge([
            'user_id' => $this->user->id,
            'date' => '2026-06-24',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ], $overrides));
    }
}
