<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class T_12_IndexAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        $knownDate = Carbon::create(2026, 6, 24, 18, 30, 0, 'Asia/Tokyo');
        Carbon::setTestNow($knownDate);

        $this->seed(RoleSeeder::class);

        $staff1 = User::factory()->staff()->create([
            'name' => 'Test User1',
        ]);

        $attendanceRecord1 = AttendanceRecord::create([
            'user_id' => $staff1->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00'
        ]);

        BreakRecord :: create([
            'attendance_record_id' => $attendanceRecord1->id,
            'break_in' => '2026-06-24 12:00:00',
            'break_out' => '2026-06-24 13:00:00',
        ]);

        $staff2 = User::factory()->staff()->create([
            'name' => 'Test User2',
        ]);

        $attendanceRecord2 = AttendanceRecord::create([
            'user_id' => $staff2->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:30:00',
            'clock_out' => '2026-06-24 18:30:00'
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord2->id,
            'break_in' => '2026-06-24 12:30:00',
            'break_out' => '2026-06-24 13:00:00',
        ]);

        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/attendance/list');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['Test User1', '09:00', '18:00', '1:00', '8:00']);
        $response->assertSeeInOrder(['Test User2', '09:30', '18:30', '0:30', '8:30']);

        Carbon::setTestNow();
    }

    public function test_遷移した際に現在の日付が表示される()
    {
        $knownDate = Carbon::create(2026, 6, 24, 9, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($knownDate);

        $this->seed(RoleSeeder::class);

        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/attendance/list');
        $response->assertStatus(200);

        $response->assertSee('2026/06/24');

        Carbon::setTestNow();
    }

    public function test_「前日」を押下した時に前の日の勤怠情報が表示される()
    {
        $knownDate = Carbon::create(2026, 6, 24, 9, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($knownDate);

        $this->seed(RoleSeeder::class);

        $staff1 = User::factory()->staff()->create([
            'name' => 'Test User1',
        ]);

        $attendanceRecord1 = AttendanceRecord::create([
            'user_id' => $staff1->id,
            'date' => '2026-06-23',
            'clock_in' => '2026-06-23 09:00:00',
            'clock_out' => '2026-06-23 18:00:00'
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord1->id,
            'break_in' => '2026-06-23 12:00:00',
            'break_out' => '2026-06-23 13:00:00',
        ]);

        $staff2 = User::factory()->staff()->create([
            'name' => 'Test User2',
        ]);

        $attendanceRecord2 = AttendanceRecord::create([
            'user_id' => $staff2->id,
            'date' => '2026-06-23',
            'clock_in' => '2026-06-23 09:30:00',
            'clock_out' => '2026-06-23 18:30:00'
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord2->id,
            'break_in' => '2026-06-23 12:30:00',
            'break_out' => '2026-06-23 13:00:00',
        ]);

        $admin = User::factory()->admin()->create();

        $today = Carbon::parse('2026-06-24');

        $url = route('adminAttendance.index', [
            'date' => $today->copy()->subDay()->format('Y-m-d')
        ]);

        $response = $this->actingAs($admin)->get($url);
        $response->assertStatus(200);

        $response->assertSee('2026/06/23');

        $response->assertSeeInOrder(['Test User1', '09:00', '18:00', '1:00', '8:00']);
        $response->assertSeeInOrder(['Test User2', '09:30', '18:30', '0:30', '8:30']);

        Carbon::setTestNow();
    }

    public function test_「翌日」を押下した時に次の日の勤怠情報が表示される()
    {
        $knownDate = Carbon::create(2026, 6, 24, 9, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($knownDate);

        $this->seed(RoleSeeder::class);

        $staff1 = User::factory()->staff()->create([
            'name' => 'Test User1',
        ]);

        $attendanceRecord1 = AttendanceRecord::create([
            'user_id' => $staff1->id,
            'date' => '2026-06-25',
            'clock_in' => '2026-06-25 09:00:00',
            'clock_out' => '2026-06-25 18:00:00'
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord1->id,
            'break_in' => '2026-06-25 12:00:00',
            'break_out' => '2026-06-25 13:00:00',
        ]);

        $staff2 = User::factory()->staff()->create([
            'name' => 'Test User2',
        ]);

        $attendanceRecord2 = AttendanceRecord::create([
            'user_id' => $staff2->id,
            'date' => '2026-06-25',
            'clock_in' => '2026-06-25 09:30:00',
            'clock_out' => '2026-06-25 18:30:00'
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord2->id,
            'break_in' => '2026-06-25 12:30:00',
            'break_out' => '2026-06-25 13:00:00',
        ]);

        $admin = User::factory()->admin()->create();

        $today = Carbon::parse('2026-06-24');

        $url = route('adminAttendance.index', [
            'date' => $today->copy()->addDay()->format('Y-m-d')
        ]);

        $response = $this->actingAs($admin)->get($url);
        $response->assertStatus(200);

        $response->assertSee('2026/06/25');

        $response->assertSeeInOrder(['Test User1', '09:00', '18:00', '1:00', '8:00']);
        $response->assertSeeInOrder(['Test User2', '09:30', '18:30', '0:30', '8:30']);

        Carbon::setTestNow();
    }
}
