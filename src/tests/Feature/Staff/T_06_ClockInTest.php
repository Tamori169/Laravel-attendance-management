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

    public function test_出勤ボタンが正しく機能する()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['attendance-buttons__clock_in']);
        $response->assertSee('出勤');

        $postResponse = $this->actingAs($this->user)->post('/attendance/clock_in');

        $postResponse->assertRedirect('/attendance');

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('出勤中');

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $this->user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
        ]);
    }

    public function test_出勤は一日一回のみできる()
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

        // 退勤済ステータスの場合、ヘッダーに「今月の出勤一覧」が表示されてしまうため、クラス名のみでチェック
        $response->assertDontSee('attendance-buttons__clock_in');
        $response->assertSee('お疲れ様でした。');
    }

    public function test_出勤時刻が勤怠一覧画面で確認できる()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['attendance-buttons__clock_in']);
        $response->assertSee('出勤');

        $postResponse = $this->actingAs($this->user)->post('/attendance/clock_in');

        $postResponse->assertRedirect('/attendance');

        $response = $this->actingAs($this->user)->get('/attendance/list?month=2026-06');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['06/24', '09:00']);
    }
}
