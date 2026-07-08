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

    private User $user;
    private AttendanceRecord $attendanceRecord;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->user = User::factory()->staff()->create();
        $this->attendanceRecord = AttendanceRecord::create([
            'user_id' => $this->user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00'
        ]);
        BreakRecord::create([
            'attendance_record_id' => $this->attendanceRecord->id,
            'break_in' => '2026-06-24 12:00:00',
            'break_out' => '2026-06-24 13:00:00',
        ]);
    }

    public function test_勤怠詳細画面の「名前」がログインユーザーの氏名になっている()
    {
        $url = route('staffAttendance.show', ['id' => $this->attendanceRecord->id]);

        $response = $this->actingAs($this->user)->get($url);
        $response->assertStatus(200);

        $response->assertSeeInOrder(['名前', $this->user->name]);
    }

    public function test_勤怠詳細画面の「日付」が選択した日付になっている()
    {
        $url = route('staffAttendance.show', ['id' => $this->attendanceRecord->id]);

        $response = $this->actingAs($this->user)->get($url);
        $response->assertStatus(200);

        $response->assertSeeInOrder(['日付', '2026年', '6月24日']);
    }

    public function test_「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $url = route('staffAttendance.show', ['id' => $this->attendanceRecord->id]);

        $response = $this->actingAs($this->user)->get($url);
        $response->assertStatus(200);

        $response->assertSeeInOrder(['出勤・退勤', '09:00', '18:00']);
    }

    public function test_「休憩」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $url = route('staffAttendance.show', ['id' => $this->attendanceRecord->id]);

        $response = $this->actingAs($this->user)->get($url);
        $response->assertStatus(200);

        $response->assertSeeInOrder(['休憩', '12:00', '13:00']);
    }
}
