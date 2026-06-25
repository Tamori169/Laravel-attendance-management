<?php

namespace Tests\Feature\Staff;

use App\Models\AttendanceCorrectRequest;
use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use App\Models\User;
use Database\Seeders\RequestStatusSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class T_11_RequestCorrectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00'
        ]);

        $url = route('staffAttendance.show', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($user)->get($url);
        $response->assertStatus(200);

        $response = $this->post($url, [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '08:30',
            'comment' => '遅延のため',
        ]);

        $response->assertSessionHasErrors(['requested_clock_out' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    public function test_休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00'
        ]);

        $url = route('staffAttendance.show', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($user)->get($url);
        $response->assertStatus(200);

        $response = $this->post($url, [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '18:00',
            'requested_breaks' => [
                0 => [
                    'break_in' => '18:30',
                    'break_out' => '13:00',
                ],
            ],
            'comment' => '遅延のため',
        ]);

        $response->assertSessionHasErrors(['requested_breaks.0.break_in' => '休憩時間が不適切な値です']);
    }

    public function test_休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00'
        ]);

        $url = route('staffAttendance.show', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($user)->get($url);
        $response->assertStatus(200);

        $response = $this->post($url, [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '18:00',
            'requested_breaks' => [
                0 => [
                    'break_in' => '12:00',
                    'break_out' => '18:30',
                ],
            ],
            'comment' => '遅延のため',
        ]);

        $response->assertSessionHasErrors(['requested_breaks.0.break_out' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    public function test_備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00'
        ]);

        $url = route('staffAttendance.show', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($user)->get($url);
        $response->assertStatus(200);

        $response = $this->post($url, [
            'comment' => '',
        ]);

        $response->assertSessionHasErrors(['comment' => '備考を記入してください']);
    }

    public function test_修正申請処理が実行される()
    {
        $knownDate = Carbon::create(2026, 6, 25, 9, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($knownDate);

        $this->seed(RequestStatusSeeder::class);
        $this->seed(RoleSeeder::class);
        $staff = User::factory()->staff()->create([
            'name' => 'Test User',
            ]);
        $admin = User::factory()->admin()->create();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $staff->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00'
        ]);

        $url = route('staffAttendance.show', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($staff)->get($url);
        $response->assertStatus(200);

        $postResponse = $this->post($url, [
            'requested_clock_in' => '09:30',
            'requested_clock_out' => '18:00',
            'requested_breaks' => [
                0 => [
                    'break_in' => '12:00',
                    'break_out' => '13:00',
                ],
            ],
            'comment' => '遅延のため',
        ]);

        $postResponse->assertRedirect($url);

        $response = $this->actingAs($staff)->get($url);
        $response->assertStatus(200);

        $response->assertSee('承認待ちのため修正はできません');

        $this->assertDatabaseHas('attendance_correct_requests', [
            'attendance_record_id' => $attendanceRecord->id,
            'request_status_id' => '1',
            'requested_clock_in' => '2026-06-24 09:30:00',
            'requested_clock_out' => '2026-06-24 18:00:00',
        ]);

        $attendanceCorrectRequest =
        AttendanceCorrectRequest::where('attendance_record_id', $attendanceRecord->id)->first();

        $this->assertDatabaseHas('break_correct_requests', [
            'attendance_correct_request_id' => $attendanceCorrectRequest->id,
            'requested_break_in' => '2026-06-24 12:00:00',
            'requested_break_out' => '2026-06-24 13:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?tab=pending');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['承認待ち', 'Test User', '2026/06/24', '遅延のため', '2026/06/25']);

        $url2 = route(
            'adminCorrection.edit',
            ['attendance_correct_request_id' => $attendanceCorrectRequest->id]
        );

        $response = $this->actingAs($admin)->get($url2);
        $response->assertStatus(200);

        $response->assertSee('勤怠詳細');
        $response->assertSeeInOrder(['名前', 'Test User']);
        $response->assertSeeInOrder(['日付', '2026年', '6月24日']);
        $response->assertSeeInOrder(['correction-approval__button-submit']);
        $response->assertSee('承認');

        Carbon::setTestNow();
    }
}
