<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class T_13_ShowAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $this->seed(RoleSeeder::class);

        $staff = User::factory()->staff()->create([
            'name' => 'Test User1',
        ]);

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $staff->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00'
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '2026-06-24 12:00:00',
            'break_out' => '2026-06-24 13:00:00',
        ]);

        $admin = User::factory()->admin()->create();

        $url = route('adminAttendance.show', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($admin)->get($url);
        $response->assertStatus(200);

        $response->assertSeeInOrder(['名前', 'Test User']);
        $response->assertSeeInOrder(['日付', '2026年', '6月24日']);
        $response->assertSeeInOrder(['出勤・退勤', '09:00', '18:00']);
        $response->assertSeeInOrder(['休憩', '12:00', '13:00']);
    }

    public function test_出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $this->seed(RoleSeeder::class);

        $staff = User::factory()->staff()->create([
            'name' => 'Test User1',
        ]);

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $staff->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00'
        ]);

        $admin = User::factory()->admin()->create();

        $url = route('adminAttendance.show', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($admin)->get($url);
        $response->assertStatus(200);

        $url2 = route('adminAttendance.update', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($admin)->patch($url2, [
            'clock_in' => '18:30',
            'clock_out' => '18:00',
            'comment' => '遅延のため',
        ]);

        $response->assertSessionHasErrors(['clock_out' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    public function test_休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $this->seed(RoleSeeder::class);

        $staff = User::factory()->staff()->create([
            'name' => 'Test User1',
        ]);

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $staff->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00'
        ]);

        $admin = User::factory()->admin()->create();

        $url = route('adminAttendance.show', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($admin)->get($url);
        $response->assertStatus(200);

        $url2 = route('adminAttendance.update', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($admin)->patch($url2, [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                0 => [
                    'break_in' => '18:30',
                    'break_out' => '13:00',
                ],
            ],
            'comment' => '遅延のため',
        ]);

        $response->assertSessionHasErrors(['breaks.0.break_in' => '休憩時間が不適切な値です']);
    }

    public function test_休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $this->seed(RoleSeeder::class);

        $staff = User::factory()->staff()->create([
            'name' => 'Test User1',
        ]);

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $staff->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00'
        ]);

        $admin = User::factory()->admin()->create();

        $url = route('adminAttendance.show', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($admin)->get($url);
        $response->assertStatus(200);

        $url2 = route('adminAttendance.update', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($admin)->patch($url2, [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                0 => [
                    'break_in' => '12:00',
                    'break_out' => '18:30',
                ],
            ],
            'comment' => '遅延のため',
        ]);

        $response->assertSessionHasErrors(['breaks.0.break_out' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    public function test_備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $this->seed(RoleSeeder::class);

        $staff = User::factory()->staff()->create([
            'name' => 'Test User1',
        ]);

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $staff->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00'
        ]);

        $admin = User::factory()->admin()->create();

        $url = route('adminAttendance.show', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($admin)->get($url);
        $response->assertStatus(200);

        $url2 = route('adminAttendance.update', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($admin)->patch($url2, [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'comment' => '',
        ]);

        $response->assertSessionHasErrors(['comment' => '備考を記入してください']);
    }
}
