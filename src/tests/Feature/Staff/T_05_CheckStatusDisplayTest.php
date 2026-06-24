<?php

namespace Tests\Feature\Staff;

use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class T_05_CheckStatusDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤務外の場合、勤怠ステータスが正しく表示される()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('勤務外');
    }

    public function test_出勤中の場合、勤怠ステータスが正しく表示される()
    {
        $knownDate = Carbon::create(2026, 6, 24, 9, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($knownDate);

        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => $knownDate->format('Y-m-d'),
            'clock_in' => $knownDate->format('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('出勤中');

        Carbon::setTestNow();
    }

    public function test_休憩中の場合、勤怠ステータスが正しく表示される()
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

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => $knownDate->copy()->addMinutes(30)->format('Y-m-d H:i:s'),
        ]);

        Carbon::setTestNow(now()->addMinutes(30));

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('休憩中');

        Carbon::setTestNow();
    }

    public function test_退勤済の場合、勤怠ステータスが正しく表示される()
    {
        $knownDate = Carbon::create(2026, 6, 24, 9, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($knownDate);

        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => $knownDate->format('Y-m-d'),
            'clock_in' => $knownDate->format('Y-m-d H:i:s'),
            'clock_out' => $knownDate->copy()->addMinutes(30)->format('Y-m-d H:i:s'),
        ]);

        Carbon::setTestNow(now()->addMinutes(30));

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('退勤済');

        Carbon::setTestNow();
    }
}
