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

    public function test_勤務外の場合、勤怠ステータスが正しく表示される()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('勤務外');
    }

    public function test_出勤中の場合、勤怠ステータスが正しく表示される()
    {
        AttendanceRecord::create([
            'user_id' => $this->user->id,
            'date' => $this->knownDate->format('Y-m-d'),
            'clock_in' => $this->knownDate->format('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('出勤中');
    }

    public function test_休憩中の場合、勤怠ステータスが正しく表示される()
    {
        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $this->user->id,
            'date' => $this->knownDate->format('Y-m-d'),
            'clock_in' => $this->knownDate->format('Y-m-d H:i:s'),
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => $this->knownDate->copy()->addMinutes(30)->format('Y-m-d H:i:s'),
        ]);

        Carbon::setTestNow($this->knownDate->copy()->addMinutes(30));

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('休憩中');
    }

    public function test_退勤済の場合、勤怠ステータスが正しく表示される()
    {
        AttendanceRecord::create([
            'user_id' => $this->user->id,
            'date' => $this->knownDate->format('Y-m-d'),
            'clock_in' => $this->knownDate->format('Y-m-d H:i:s'),
            'clock_out' => $this->knownDate->copy()->addMinutes(30)->format('Y-m-d H:i:s'),
        ]);

        Carbon::setTestNow($this->knownDate->copy()->addMinutes(30));

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('退勤済');
    }
}
