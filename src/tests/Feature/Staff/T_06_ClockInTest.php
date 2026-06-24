<?php

namespace Tests\Feature\Staff;

use App\Models\AttendanceRecord;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class T_06_ClockInTest extends TestCase
{
    use RefreshDatabase;

    public function test_出勤ボタンが正しく機能する()
    {
        $knownDate = Carbon::create(2026, 6, 24, 9, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($knownDate);

        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['attendance-buttons__clock_in']);
        $response->assertSee('出勤');

        $postResponse = $this->actingAs($user)->post('/attendance/clock_in');

        $postResponse->assertRedirect('/attendance');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('出勤中');

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
        ]);

        Carbon::setTestNow();
    }

    public function test_出勤は一日一回のみできる()
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

        // 退勤済ステータスの場合、ヘッダーに「今月の出勤一覧」が表示されてしまうため、クラス名のみでチェック
        $response->assertDontSee('attendance-buttons__clock_in');
        $response->assertSee('お疲れ様でした。');

        Carbon::setTestNow();
    }

    public function test_出勤時刻が勤怠一覧画面で確認できる()
    {
        $knownDate = Carbon::create(2026, 6, 24, 9, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($knownDate);

        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['attendance-buttons__clock_in']);
        $response->assertSee('出勤');

        $postResponse = $this->actingAs($user)->post('/attendance/clock_in');

        $postResponse->assertRedirect('/attendance');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('出勤中');

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-06');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['06/24', '09:00']);

        Carbon::setTestNow();
    }
}
