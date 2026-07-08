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

    private Carbon $knownDate;
    private User $staff1;
    private User $staff2;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->knownDate = Carbon::create(2026, 6, 24, 18, 30, 0, 'Asia/Tokyo');
        Carbon::setTestNow($this->knownDate);

        $this->seed(RoleSeeder::class);
        $this->staff1 = User::factory()->staff()->create();
        $this->staff2 = User::factory()->staff()->create();
        $this->admin = User::factory()->admin()->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        $attendanceRecord1 = AttendanceRecord::create([
            'user_id' => $this->staff1->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00'
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord1->id,
            'break_in' => '2026-06-24 12:00:00',
            'break_out' => '2026-06-24 13:00:00',
        ]);

        $attendanceRecord2 = AttendanceRecord::create([
            'user_id' => $this->staff2->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:30:00',
            'clock_out' => '2026-06-24 18:30:00'
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord2->id,
            'break_in' => '2026-06-24 12:30:00',
            'break_out' => '2026-06-24 13:00:00',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/attendance/list');
        $response->assertStatus(200);

        $response->assertSeeInOrder([$this->staff1->name, '09:00', '18:00', '1:00', '8:00']);
        $response->assertSeeInOrder([$this->staff2->name, '09:30', '18:30', '0:30', '8:30']);
    }

    public function test_遷移した際に現在の日付が表示される()
    {
        $response = $this->actingAs($this->admin)->get('/admin/attendance/list');
        $response->assertStatus(200);

        $response->assertSee('2026/06/24');
    }

    public function test_「前日」を押下した時に前の日の勤怠情報が表示される()
    {
        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $this->staff1->id,
            'date' => '2026-06-23',
            'clock_in' => '2026-06-23 09:00:00',
            'clock_out' => '2026-06-23 18:00:00'
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '2026-06-23 12:00:00',
            'break_out' => '2026-06-23 13:00:00',
        ]);

        $url = route('adminAttendance.index', [
            'date' => $this->knownDate->copy()->subDay()->format('Y-m-d')
        ]);

        $response = $this->actingAs($this->admin)->get($url);
        $response->assertStatus(200);

        $response->assertSee('2026/06/23');

        $response->assertSeeInOrder([$this->staff1->name, '09:00', '18:00', '1:00', '8:00']);
    }

    public function test_「翌日」を押下した時に次の日の勤怠情報が表示される()
    {
        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $this->staff1->id,
            'date' => '2026-06-25',
            'clock_in' => '2026-06-25 09:00:00',
            'clock_out' => '2026-06-25 18:00:00'
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '2026-06-25 12:00:00',
            'break_out' => '2026-06-25 13:00:00',
        ]);

        $url = route('adminAttendance.index', [
            'date' => $this->knownDate->copy()->addDay()->format('Y-m-d')
        ]);

        $response = $this->actingAs($this->admin)->get($url);
        $response->assertStatus(200);

        $response->assertSee('2026/06/25');

        $response->assertSeeInOrder([$this->staff1->name, '09:00', '18:00', '1:00', '8:00']);
    }
}
