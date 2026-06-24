<?php

namespace Tests\Feature\Staff;

use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class T_07_BreakTest extends TestCase
{
    use RefreshDatabase;

    public function test_休憩ボタンが正しく機能する()
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

        $response->assertSeeInOrder(['attendance-buttons__break_in']);
        $response->assertSee('休憩入');

        Carbon::setTestNow(now()->addMinutes(30));

        $postResponse = $this->actingAs($user)->post('/attendance/break_in');

        $postResponse->assertRedirect('/attendance');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('休憩中');

        $this->assertDatabaseHas('break_records', [
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '2026-06-24 09:30:00',
        ]);

        Carbon::setTestNow();
    }

    public function test_休憩は一日に何回でもできる()
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

        Carbon::setTestNow(now()->addMinutes(30));

        $postResponse = $this->actingAs($user)->post('/attendance/break_in');

        $postResponse->assertRedirect('/attendance');

        Carbon::setTestNow(now()->addMinutes(30));

        $postResponse = $this->actingAs($user)->patch('/attendance/break_out');

        $postResponse->assertRedirect('/attendance');

        $response->assertSeeInOrder(['attendance-buttons__break_in']);
        $response->assertSee('休憩入');

        $this->assertDatabaseHas('break_records', [
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '2026-06-24 09:30:00',
            'break_out' => '2026-06-24 10:00:00',
        ]);

        Carbon::setTestNow();
    }

    public function test_休憩戻ボタンが正しく機能する()
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

        Carbon::setTestNow(now()->addMinutes(30));

        $postResponse = $this->actingAs($user)->post('/attendance/break_in');

        $postResponse->assertRedirect('/attendance');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSeeInOrder(['attendance-buttons__break_out']);
        $response->assertSee('休憩戻');

        Carbon::setTestNow(now()->addMinutes(30));

        $postResponse = $this->actingAs($user)->patch('/attendance/break_out');

        $postResponse->assertRedirect('/attendance');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('出勤中');

        $this->assertDatabaseHas('break_records', [
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '2026-06-24 09:30:00',
            'break_out' => '2026-06-24 10:00:00',
        ]);

        Carbon::setTestNow();
    }

    public function test_休憩戻は一日に何回でもできる()
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

        Carbon::setTestNow(now()->addMinutes(30));

        $postResponse = $this->actingAs($user)->post('/attendance/break_in');

        $postResponse->assertRedirect('/attendance');

        Carbon::setTestNow(now()->addMinutes(30));

        $postResponse = $this->actingAs($user)->patch('/attendance/break_out');

        $postResponse->assertRedirect('/attendance');

        Carbon::setTestNow(now()->addMinutes(30));

        $postResponse = $this->actingAs($user)->post('/attendance/break_in');

        $postResponse->assertRedirect('/attendance');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSeeInOrder(['attendance-buttons__break_out']);
        $response->assertSee('休憩戻');

        $this->assertDatabaseHas('break_records', [
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '2026-06-24 09:30:00',
            'break_out' => '2026-06-24 10:00:00',
        ]);

        $this->assertDatabaseHas('break_records', [
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '2026-06-24 10:30:00',
        ]);

        Carbon::setTestNow();
    }

    public function test_休憩時刻が勤怠一覧画面で確認できる()
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

        Carbon::setTestNow(now()->addMinutes(30));

        $postResponse = $this->actingAs($user)->post('/attendance/break_in');

        $postResponse->assertRedirect('/attendance');

        Carbon::setTestNow(now()->addMinutes(30));

        $postResponse = $this->actingAs($user)->patch('/attendance/break_out');

        $postResponse->assertRedirect('/attendance');

        $this->assertDatabaseHas('break_records', [
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '2026-06-24 09:30:00',
            'break_out' => '2026-06-24 10:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-06');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['06/24', '09:00', '0:30']);

        Carbon::setTestNow();
    }
}
