<?php

namespace Tests\Feature\Staff;

use App\Models\AttendanceRecord;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class T_08_ClockOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_退勤ボタンが正しく機能する()
    {
        $knownDate = Carbon::create(2026, 6, 24, 9, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($knownDate);

        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => $knownDate->format('Y-m-d'),
            'clock_in' => $knownDate->format('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['attendance-buttons__clock_out']);
        $response->assertSee('退勤');

        Carbon::setTestNow(now()->addMinutes(30));

        $postResponse = $this->actingAs($user)->patch('/attendance/clock_out');

        $postResponse->assertRedirect('/attendance');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('退勤済');

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 09:30:00',
        ]);

        Carbon::setTestNow();
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        $knownDate = Carbon::create(2026, 6, 24, 9, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($knownDate);

        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        $postResponse = $this->actingAs($user)->post('/attendance/clock_in');

        $postResponse->assertRedirect('/attendance');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        Carbon::setTestNow(now()->addMinutes(30));

        $postResponse = $this->actingAs($user)->patch('/attendance/clock_out');

        $postResponse->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 09:30:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-06');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['06/24', '09:00', '09:30']);

        Carbon::setTestNow();
    }
}