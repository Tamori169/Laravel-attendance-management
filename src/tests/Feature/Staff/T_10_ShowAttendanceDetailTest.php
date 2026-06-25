<?php

namespace Tests\Feature\Staff;

use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class T_10_ShowAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠詳細画面の「名前」がログインユーザーの氏名になっている()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create([
            'name' => 'Test User',
        ]);

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00'
        ]);

        $url = route('staffAttendance.show', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($user)->get($url);
        $response->assertStatus(200);

        $response->assertSeeInOrder(['名前', 'Test User']);
    }

    public function test_勤怠詳細画面の「日付」が選択した日付になっている()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create([
            'name' => 'Test User',
        ]);

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00'
        ]);

        $url = route('staffAttendance.show', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($user)->get($url);
        $response->assertStatus(200);

        $response->assertSeeInOrder(['日付', '2026年', '6月24日']);
    }

    public function test_「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create([
            'name' => 'Test User',
        ]);

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00'
        ]);

        $url = route('staffAttendance.show', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($user)->get($url);
        $response->assertStatus(200);

        $response->assertSeeInOrder(['出勤・退勤', '09:00', '18:00']);
    }

    public function test_「休憩」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create([
            'name' => 'Test User',
        ]);

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00'
        ]);

        BreakRecord :: create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '12:00',
            'break_out' => '12:30',
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '13:00',
            'break_out' => '13:30',
        ]);

        $url = route('staffAttendance.show', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($user)->get($url);
        $response->assertStatus(200);

        $response->assertSeeInOrder(['休憩', '12:00', '12:30']);
        $response->assertSeeInOrder(['休憩2', '13:00', '13:30']);
    }
}
