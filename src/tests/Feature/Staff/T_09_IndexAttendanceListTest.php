<?php

namespace Tests\Feature\Staff;

use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class T_09_IndexAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    private Carbon $knownDate;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->knownDate = Carbon::create(2026, 6, 24, 9, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($this->knownDate);

        $this->seed(RoleSeeder::class);
        $this->user = User::factory()->staff()->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_自分が行った勤怠情報が全て表示されている()
    {
        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $this->user->id,
            'date' => '2026-06-23',
            'clock_in' => '2026-06-23 09:00:00',
            'clock_out' => '2026-06-23 18:00:00'
        ]);

        $attendanceRecord2 = AttendanceRecord::create([
            'user_id' => $this->user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:30:00'
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '2026-06-23 12:00:00',
            'break_out' => '2026-06-23 13:00:00',
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord2->id,
            'break_in' => '2026-06-24 12:00:00',
            'break_out' => '2026-06-24 12:30:00',
        ]);

        $response = $this->actingAs($this->user)->get('/attendance/list');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['06/23', '09:00', '18:00', '1:00', '8:00']);
        $response->assertSeeInOrder(['06/24', '09:00', '18:30', '0:30', '9:00']);
    }

    public function test_勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        $response = $this->actingAs($this->user)->get('/attendance/list');
        $response->assertStatus(200);

        $response->assertSee('2026/06');
    }

    public function test_「前月」を押下した時に表示月の前月の情報が表示される()
    {
        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $this->user->id,
            'date' => '2026-05-24',
            'clock_in' => '2026-05-24 09:00:00',
            'clock_out' => '2026-05-24 18:00:00'
        ]);

        $attendanceRecord2 = AttendanceRecord::create([
            'user_id' => $this->user->id,
            'date' => '2026-05-25',
            'clock_in' => '2026-05-25 09:00:00',
            'clock_out' => '2026-05-25 18:30:00'
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '2026-05-24 12:00:00',
            'break_out' => '2026-05-24 13:00:00',
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord2->id,
            'break_in' => '2026-05-25 12:00:00',
            'break_out' => '2026-05-25 12:30:00',
        ]);

        $url = route('staffAttendance.index', [
            'month' => $this->knownDate->copy()->subMonth()->format('Y-m')
        ]);

        $response = $this->actingAs($this->user)->get($url);
        $response->assertStatus(200);

        $response->assertSeeInOrder(['05/24', '09:00', '18:00', '1:00', '8:00']);
        $response->assertSeeInOrder(['05/25', '09:00', '18:30', '0:30', '9:00']);
    }

    public function test_「翌月」を押下した時に表示月の翌月の情報が表示される()
    {
        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $this->user->id,
            'date' => '2026-07-24',
            'clock_in' => '2026-07-24 09:00:00',
            'clock_out' => '2026-07-24 18:00:00'
        ]);

        $attendanceRecord2 = AttendanceRecord::create([
            'user_id' => $this->user->id,
            'date' => '2026-07-25',
            'clock_in' => '2026-07-25 09:00:00',
            'clock_out' => '2026-07-25 18:30:00'
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '2026-07-24 12:00:00',
            'break_out' => '2026-07-24 13:00:00',
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord2->id,
            'break_in' => '2026-07-25 12:00:00',
            'break_out' => '2026-07-25 12:30:00',
        ]);

        $url = route('staffAttendance.index', [
            'month' => $this->knownDate->copy()->addMonth()->format('Y-m')
        ]);

        $response = $this->actingAs($this->user)->get($url);
        $response->assertStatus(200);

        $response->assertSeeInOrder(['07/24', '09:00', '18:00', '1:00', '8:00']);
        $response->assertSeeInOrder(['07/25', '09:00', '18:30', '0:30', '9:00']);
    }

    public function test_「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $this->user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00'
        ]);

        $response = $this->actingAs($this->user)->get('/attendance/list');
        $response->assertStatus(200);

        $url = route('staffAttendance.show', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($this->user)->get($url);
        $response->assertStatus(200);

        $response->assertSee('勤怠詳細');
    }
}
