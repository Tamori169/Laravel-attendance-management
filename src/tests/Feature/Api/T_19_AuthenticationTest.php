<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceRecord;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class T_19_AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_未認証時に書き込み系APIで401が返る_POST()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $data = [
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => 'Test comment',
        ];

        $postResponse = $this->postJson('/api/v1/attendance-records', $data);

        $postResponse->assertStatus(401);
        $postResponse->assertJson([
            'message' => 'Unauthenticated.',
        ]);

        $this->assertDatabaseMissing('attendance_records', [
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00',
            'comment' => 'Test comment',
        ]);
    }

    public function test_未認証時に書き込み系APIで401が返る_PUT()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00',
        ]);

        $data = [
            'date' => '2026-06-24',
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
            'comment' => 'Test comment',
        ];

        $putResponse = $this->putJson("/api/v1/attendance-records/{$attendanceRecord->id}", $data);

        $putResponse->assertStatus(401);
        $putResponse->assertJson([
            'message' => 'Unauthenticated.',
        ]);

        $this->assertDatabaseMissing('attendance_records', [
            'clock_in' => '2026-06-24 09:30:00',
            'clock_out' => '2026-06-24 18:30:00',
            'comment' => 'Test comment',
        ]);
    }

    public function test_未認証時に書き込み系APIで401が返る_DELETE()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00',
        ]);

        $deleteResponse = $this->deleteJson("/api/v1/attendance-records/{$attendanceRecord->id}");

        $deleteResponse->assertStatus(401);
        $deleteResponse->assertJson([
            'message' => 'Unauthenticated.',
        ]);

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00',
        ]);
    }

    public function test_認証済みユーザーは自分の勤怠を更新できる()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        Sanctum::actingAs($user, ['*']);

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

    public function test_認証済みユーザーは自分の勤怠を削除できる()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        Sanctum::actingAs($user, ['*']);

        $deleteResponse = $this->deleteJson("/api/v1/attendance-records/{$attendanceRecord->id}");

        $deleteResponse->assertStatus(204);

        $this->assertDatabaseMissing('attendance_records', [
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00',
        ]);
    }

    public function test_他ユーザーの勤怠を更新しようとすると403が返る()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();
        $user2 = User::factory()->staff()->create();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user2->id,
            'date' => '2026-06-24',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        Sanctum::actingAs($user, ['*']);

        $data = [
            'date' => '2026-06-24',
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
            'comment' => 'Test comment',
        ];

        $putResponse = $this->putJson("/api/v1/attendance-records/{$attendanceRecord->id}", $data);

        $putResponse->assertStatus(403);
        $putResponse->assertJson([
            'error' => 'この操作を実行する権限がありません。',
        ]);
    }

    public function test_他ユーザーの勤怠を削除しようとすると403が返る()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();
        $user2 = User::factory()->staff()->create();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user2->id,
            'date' => '2026-06-24',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        Sanctum::actingAs($user, ['*']);

        $deleteResponse = $this->deleteJson("/api/v1/attendance-records/{$attendanceRecord->id}");

        $deleteResponse->assertStatus(403);
        $deleteResponse->assertJson([
            'error' => 'この操作を実行する権限がありません。',
        ]);
    }
}
