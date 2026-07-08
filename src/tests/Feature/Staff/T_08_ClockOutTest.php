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

    public function test_退勤ボタンが正しく機能する()
    {
        AttendanceRecord::create([
            'user_id' => $this->user->id,
            'date' => $this->knownDate->format('Y-m-d'),
            'clock_in' => $this->knownDate->format('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['attendance-buttons__clock_out']);
        $response->assertSee('退勤');

        Carbon::setTestNow($this->knownDate->copy()->addMinutes(30));

        $patchResponse = $this->actingAs($this->user)->patch('/attendance/clock_out');

        $patchResponse->assertRedirect('/attendance');

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('退勤済');

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $this->user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 09:30:00',
        ]);
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        $postResponse = $this->actingAs($this->user)->post('/attendance/clock_in');

        $postResponse->assertRedirect('/attendance');

        Carbon::setTestNow($this->knownDate->copy()->addMinutes(30));

        $postResponse = $this->actingAs($this->user)->patch('/attendance/clock_out');

        $postResponse->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $this->user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 09:30:00',
        ]);

        $response = $this->actingAs($this->user)->get('/attendance/list?month=2026-06');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['06/24', '09:00', '09:30']);
    }
}