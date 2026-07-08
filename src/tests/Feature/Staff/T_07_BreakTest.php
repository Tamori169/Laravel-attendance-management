<?php

namespace Tests\Feature\Staff;

use App\Models\AttendanceRecord;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class T_07_BreakTest extends TestCase
{
    use RefreshDatabase;

    private Carbon $knownDate;
    private User $user;
    private AttendanceRecord $attendanceRecord;

    protected function setUp(): void
    {
        parent::setUp();

        $this->knownDate = Carbon::create(2026, 6, 24, 9, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($this->knownDate);

        $this->seed(RoleSeeder::class);
        $this->user = User::factory()->staff()->create();
        $this->attendanceRecord = AttendanceRecord::create([
            'user_id' => $this->user->id,
            'date' => $this->knownDate->format('Y-m-d'),
            'clock_in' => $this->knownDate->format('Y-m-d H:i:s'),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_休憩ボタンが正しく機能する()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['attendance-buttons__break_in']);
        $response->assertSee('休憩入');

        Carbon::setTestNow($this->knownDate->copy()->addMinutes(30));

        $postResponse = $this->actingAs($this->user)->post('/attendance/break_in');

        $postResponse->assertRedirect('/attendance');

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('休憩中');

        $this->assertDatabaseHas('break_records', [
            'attendance_record_id' => $this->attendanceRecord->id,
            'break_in' => '2026-06-24 09:30:00',
        ]);
    }

    public function test_休憩は一日に何回でもできる()
    {

        Carbon::setTestNow($this->knownDate->copy()->addMinutes(30));

        $postResponse = $this->actingAs($this->user)->post('/attendance/break_in');

        $postResponse->assertRedirect('/attendance');

        Carbon::setTestNow($this->knownDate->copy()->addMinutes(60));

        $patchResponse = $this->actingAs($this->user)->patch('/attendance/break_out');

        $patchResponse->assertRedirect('/attendance');

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['attendance-buttons__break_in']);
        $response->assertSee('休憩入');

        $this->assertDatabaseHas('break_records', [
            'attendance_record_id' => $this->attendanceRecord->id,
            'break_in' => '2026-06-24 09:30:00',
            'break_out' => '2026-06-24 10:00:00',
        ]);
    }

    public function test_休憩戻ボタンが正しく機能する()
    {
        Carbon::setTestNow($this->knownDate->copy()->addMinutes(30));

        $postResponse = $this->actingAs($this->user)->post('/attendance/break_in');

        $postResponse->assertRedirect('/attendance');

        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertSeeInOrder(['attendance-buttons__break_out']);
        $response->assertSee('休憩戻');

        Carbon::setTestNow($this->knownDate->copy()->addMinutes(60));

        $postResponse = $this->actingAs($this->user)->patch('/attendance/break_out');

        $postResponse->assertRedirect('/attendance');

        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertSee('出勤中');

        $this->assertDatabaseHas('break_records', [
            'attendance_record_id' => $this->attendanceRecord->id,
            'break_in' => '2026-06-24 09:30:00',
            'break_out' => '2026-06-24 10:00:00',
        ]);
    }

    public function test_休憩戻は一日に何回でもできる()
    {
        Carbon::setTestNow($this->knownDate->copy()->addMinutes(30));

        $postResponse = $this->actingAs($this->user)->post('/attendance/break_in');

        $postResponse->assertRedirect('/attendance');

        Carbon::setTestNow($this->knownDate->copy()->addMinutes(60));

        $postResponse = $this->actingAs($this->user)->patch('/attendance/break_out');

        $postResponse->assertRedirect('/attendance');

        Carbon::setTestNow($this->knownDate->copy()->addMinutes(90));

        $postResponse = $this->actingAs($this->user)->post('/attendance/break_in');

        $postResponse->assertRedirect('/attendance');

        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertSeeInOrder(['attendance-buttons__break_out']);
        $response->assertSee('休憩戻');

        $this->assertDatabaseHas('break_records', [
            'attendance_record_id' => $this->attendanceRecord->id,
            'break_in' => '2026-06-24 09:30:00',
            'break_out' => '2026-06-24 10:00:00',
        ]);

        $this->assertDatabaseHas('break_records', [
            'attendance_record_id' => $this->attendanceRecord->id,
            'break_in' => '2026-06-24 10:30:00',
        ]);
    }

    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow($this->knownDate->copy()->addMinutes(30));

        $postResponse = $this->actingAs($this->user)->post('/attendance/break_in');

        $postResponse->assertRedirect('/attendance');

        Carbon::setTestNow($this->knownDate->copy()->addMinutes(60));

        $postResponse = $this->actingAs($this->user)->patch('/attendance/break_out');

        $postResponse->assertRedirect('/attendance');

        $response = $this->actingAs($this->user)->get('/attendance/list?month=2026-06');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['06/24', '09:00', '0:30']);
    }
}
