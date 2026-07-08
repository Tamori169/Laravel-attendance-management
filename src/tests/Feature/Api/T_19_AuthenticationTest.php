<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceRecord;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class T_19_AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->user = User::factory()->staff()->create();
    }

    public function test_未認証時に書き込み系APIで401が返る_POST()
    {
        $response = $this->postJson('/api/v1/attendance-records', $this->validPayload());

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);

        $this->assertDatabaseMissing('attendance_records', $this->storedPayload());
    }

    public function test_未認証時に書き込み系APIで401が返る_PUT()
    {
        $attendanceRecord = $this->createAttendanceRecord();

        $response = $this->putJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}",
            $this->updatePayload()
        );

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);

        $this->assertDatabaseMissing('attendance_records', $this->storedUpdatePayload());
    }

    public function test_未認証時に書き込み系APIで401が返る_DELETE()
    {
        $attendanceRecord = $this->createAttendanceRecord();

        $response = $this->deleteJson("/api/v1/attendance-records/{$attendanceRecord->id}");

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);

        $this->assertDatabaseHas('attendance_records', $this->storedPayload([
            'comment' => null,
        ]));
    }

    public function test_認証済みユーザーは自分の勤怠を更新できる()
    {
        $attendanceRecord = $this->createAttendanceRecord();

        $this->authenticate();

        $response = $this->putJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}",
            $this->updatePayload()
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('attendance_records', $this->storedUpdatePayload());
    }

    public function test_認証済みユーザーは自分の勤怠を削除できる()
    {
        $attendanceRecord = $this->createAttendanceRecord();

        $this->authenticate();

        $response = $this->deleteJson("/api/v1/attendance-records/{$attendanceRecord->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('attendance_records', $this->storedPayload([
            'comment' => null,
        ]));
    }

    public function test_他ユーザーの勤怠を更新しようとすると403が返る()
    {
        $otherUser = User::factory()->staff()->create();
        $attendanceRecord = $this->createAttendanceRecord($otherUser);

        $this->authenticate();

        $response = $this->putJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}",
            $this->updatePayload()
        );

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'この操作を実行する権限がありません。',
        ]);
    }

    public function test_他ユーザーの勤怠を削除しようとすると403が返る()
    {
        $otherUser = User::factory()->staff()->create();
        $attendanceRecord = $this->createAttendanceRecord($otherUser);

        $this->authenticate();

        $response = $this->deleteJson("/api/v1/attendance-records/{$attendanceRecord->id}");

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'この操作を実行する権限がありません。',
        ]);
    }

    private function authenticate(): void
    {
        Sanctum::actingAs($this->user, ['*']);
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

    private function createAttendanceRecord(?User $user = null, array $overrides = []): AttendanceRecord
    {
        $user ??= $this->user;

        return AttendanceRecord::create(array_merge([
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00',
        ], $overrides));
    }
}