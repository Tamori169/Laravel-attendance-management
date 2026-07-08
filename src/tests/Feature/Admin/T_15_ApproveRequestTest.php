<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceRecord;
use App\Models\AttendanceCorrectRequest;
use App\Models\User;
use Database\Seeders\RequestStatusSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class T_15_ApproveRequestTest extends TestCase
{
    use RefreshDatabase;

    private Carbon $knownDate;
    private User $staff;
    private User $admin;
    private AttendanceRecord $attendanceRecord;

    protected function setUp(): void
    {
        parent::setUp();

        $this->knownDate = Carbon::create(2026, 6, 24, 19, 00, 0, 'Asia/Tokyo');
        Carbon::setTestNow($this->knownDate);

        $this->seed(RoleSeeder::class);
        $this->seed(RequestStatusSeeder::class);
        $this->staff = User::factory()->staff()->create();
        $this->admin = User::factory()->admin()->create();
        $this->attendanceRecord = AttendanceRecord::create([
            'user_id' => $this->staff->id,
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

    public function test_承認待ちの修正申請が全て表示されている()
    {
        AttendanceCorrectRequest::create([
            'attendance_record_id' => $this->attendanceRecord->id,
            'request_status_id' => '1',
            'requested_clock_in' => '2026-06-24 09:30:00',
            'requested_clock_out' => '2026-06-24 18:30:00',
            'comment' => '遅延のため',
        ]);

        $response = $this->actingAs($this->admin)->get('/stamp_correction_request/list?tab=pending');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['承認待ち', $this->staff->name, '2026/06/24', '遅延のため', '2026/06/24']);
    }

    public function test_承認済みの修正申請が全て表示されている()
    {
        AttendanceCorrectRequest::create([
            'attendance_record_id' => $this->attendanceRecord->id,
            'request_status_id' => '2',
            'requested_clock_in' => '2026-06-24 09:30:00',
            'requested_clock_out' => '2026-06-24 18:30:00',
            'comment' => '遅延のため',
        ]);

        $response = $this->actingAs($this->admin)->get('/stamp_correction_request/list?tab=approved');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['承認済み', $this->staff->name, '2026/06/24', '遅延のため', '2026/06/24']);
    }

    public function test_修正申請の詳細内容が正しく表示されている()
    {
        $attendanceCorrectRequest = AttendanceCorrectRequest::create([
            'attendance_record_id' => $this->attendanceRecord->id,
            'request_status_id' => '1',
            'requested_clock_in' => '2026-06-24 09:30:00',
            'requested_clock_out' => '2026-06-24 18:30:00',
            'comment' => '遅延のため',
        ]);

        $url = route(
            'adminCorrection.edit',
            ['attendance_correct_request_id' => $attendanceCorrectRequest->id]
        );

        $response = $this->actingAs($this->admin)->get($url);
        $response->assertStatus(200);

        $response->assertSeeInOrder(['名前', $this->staff->name]);
        $response->assertSeeInOrder(['日付', '2026年', '6月24日']);
        $response->assertSeeInOrder(['出勤・退勤', '09:30', '18:30']);
        $response->assertSeeInOrder(['備考', '遅延のため']);
    }

    public function test_修正申請の承認処理が正しく行われる()
    {
        $attendanceCorrectRequest = AttendanceCorrectRequest::create([
            'attendance_record_id' => $this->attendanceRecord->id,
            'request_status_id' => '1',
            'requested_clock_in' => '2026-06-24 09:30:00',
            'requested_clock_out' => '2026-06-24 18:30:00',
            'comment' => '遅延のため',
        ]);

        $url = route(
            'adminCorrection.edit',
            ['attendance_correct_request_id' => $attendanceCorrectRequest->id]
        );

        $patchResponse = $this->actingAs($this->admin)->patch($url);
        $patchResponse->assertStatus(302);

        $response = $this->actingAs($this->admin)->get($url);

        $response->assertSee('承認済み');

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $this->staff->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:30:00',
            'clock_out' => '2026-06-24 18:30:00',
        ]);

        $this->assertDatabaseHas('attendance_correct_requests', [
            'attendance_record_id' => $this->attendanceRecord->id,
            'request_status_id' => '2',
            'requested_clock_in' => '2026-06-24 09:30:00',
            'requested_clock_out' => '2026-06-24 18:30:00',
            'comment' => '遅延のため',
        ]);
    }
}
