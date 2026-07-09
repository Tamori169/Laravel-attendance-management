<?php

namespace Tests\Feature\Staff;

use App\Models\AttendanceCorrectRequest;
use App\Models\AttendanceRecord;
use App\Models\User;
use Database\Seeders\RequestStatusSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class T_11_RequestCorrectionTest extends TestCase
{
    use RefreshDatabase;

    private Carbon $knownDate;
    private User $user;
    private AttendanceRecord $attendanceRecord;

    protected function setUp(): void
    {
        parent::setUp();

        $this->knownDate = Carbon::create(2026, 6, 25, 9, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($this->knownDate);

        $this->seed(RoleSeeder::class);
        $this->seed(RequestStatusSeeder::class);
        $this->user = User::factory()->staff()->create();
        $this->attendanceRecord = AttendanceRecord::create([
            'user_id' => $this->user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00'
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $url = route('staffAttendance.show', ['id' => $this->attendanceRecord->id]);

        $response = $this->actingAs($this->user)->get($url);
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
        $url = route('staffAttendance.show', ['id' => $this->attendanceRecord->id]);

        $response = $this->actingAs($this->user)->post($url, [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '18:00',
            'requested_breaks' => [
                0 => ['break_in' => '18:30','break_out' => '13:00'],
            ],
            'comment' => '遅延のため',
        ]);

        $response->assertSessionHasErrors(['requested_breaks.0.break_in' => '休憩時間が不適切な値です']);
    }

    public function test_休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $url = route('staffAttendance.show', ['id' => $this->attendanceRecord->id]);

        $response = $this->actingAs($this->user)->post($url, [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '18:00',
            'requested_breaks' => [
                0 => ['break_in' => '12:00','break_out' => '18:30'],
            ],
            'comment' => '遅延のため',
        ]);

        $response->assertSessionHasErrors(['requested_breaks.0.break_out' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    public function test_備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $url = route('staffAttendance.show', ['id' => $this->attendanceRecord->id]);

        $response = $this->actingAs($this->user)->post($url, [
            'comment' => '',
        ]);

        $response->assertSessionHasErrors(['comment' => '備考を記入してください']);
    }

    public function test_修正申請処理が実行される()
    {
        $url = route('staffAttendance.show', ['id' => $this->attendanceRecord->id]);

        $postResponse = $this->actingAs($this->user)->post($url, [
            'requested_clock_in' => '09:30',
            'requested_clock_out' => '18:00',
            'comment' => '遅延のため',
        ]);

        $postResponse->assertRedirect($url);

        $this->assertDatabaseHas('attendance_correct_requests', [
            'attendance_record_id' => $this->attendanceRecord->id,
            'request_status_id' => '1',
            'requested_clock_in' => '2026-06-24 09:30:00',
            'requested_clock_out' => '2026-06-24 18:00:00',
        ]);

        $attendanceCorrectRequest =
        AttendanceCorrectRequest::where('attendance_record_id', $this->attendanceRecord->id)->first();

        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?tab=pending');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['承認待ち', $this->user->name, '2026/06/24', '遅延のため', '2026/06/25']);

        $url2 = route(
            'adminCorrection.edit',['attendance_correct_request_id' => $attendanceCorrectRequest->id]);

        $response = $this->actingAs($admin)->get($url2);
        $response->assertStatus(200);

        $response->assertSee('勤怠詳細');
        $response->assertSeeInOrder(['名前', $this->user->name]);
        $response->assertSeeInOrder(['日付', '2026年', '6月24日']);
    }

    public function test_「承認待ち」にログインユーザーが行った申請が全て表示されていること()
    {
        AttendanceCorrectRequest::create([
            'attendance_record_id' => $this->attendanceRecord->id,
            'request_status_id' => 1,
            'requested_clock_in' => '2026-06-24 09:30:00',
            'requested_clock_out' => '2026-06-24 18:00:00',
            'comment' => '遅延のため',
        ]);

        $response = $this->actingAs($this->user)->get('/stamp_correction_request/list?tab=pending');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['承認待ち', $this->user->name, '2026/06/24', '遅延のため', '2026/06/25']);
    }

    public function test_「承認済み」に管理者が承認した修正申請が全て表示されている()
    {
        AttendanceCorrectRequest::create([
            'attendance_record_id' => $this->attendanceRecord->id,
            'request_status_id' => 2,
            'requested_clock_in' => '2026-06-24 09:30:00',
            'requested_clock_out' => '2026-06-24 18:00:00',
            'comment' => '遅延のため',
        ]);

        $response = $this->actingAs($this->user)->get('/stamp_correction_request/list?tab=approved');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['承認済み', $this->user->name, '2026/06/24', '遅延のため', '2026/06/25']);
    }

    public function test_各申請の「詳細」を押下すると勤怠詳細画面に遷移する()
    {
        AttendanceCorrectRequest::create([
            'attendance_record_id' => $this->attendanceRecord->id,
            'request_status_id' => 1,
            'requested_clock_in' => '2026-06-24 09:30:00',
            'requested_clock_out' => '2026-06-24 18:00:00',
            'comment' => '遅延のため',
        ]);

        $url = route('staffAttendance.show', ['id' => $this->attendanceRecord->id]);

        $response = $this->actingAs($this->user)->get($url);
        $response->assertStatus(200);

        $response->assertSee('勤怠詳細');
        $response->assertSeeInOrder(['名前', $this->user->name]);
        $response->assertSeeInOrder(['日付', '2026年', '6月24日']);
        $response->assertSeeInOrder(['出勤・退勤', '09:30', '18:00']);
    }
}
