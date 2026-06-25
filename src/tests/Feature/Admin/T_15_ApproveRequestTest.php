<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceRecord;
use App\Models\AttendanceCorrectRequest;
use App\Models\BreakRecord;
use App\Models\BreakCorrectRequest;
use App\Models\User;
use Database\Seeders\RequestStatusSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class T_15_ApproveRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_承認待ちの修正申請が全て表示されている()
    {
        $knownDate = Carbon::create(2026, 6, 25, 9, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($knownDate);

        $this->seed(RoleSeeder::class);
        $this->seed(RequestStatusSeeder::class);

        $staff1 = User::factory()->staff()->create([
            'name' => 'Test User1',
        ]);

        $attendanceRecord1 = AttendanceRecord::create([
            'user_id' => $staff1->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00',
        ]);

        AttendanceCorrectRequest::create([
            'attendance_record_id' => $attendanceRecord1->id,
            'request_status_id' => '1',
            'requested_clock_in' => '2026-06-24 09:30:00',
            'requested_clock_out' => '2026-06-24 18:30:00',
            'comment' => '遅延のため',
        ]);

        $staff2 = User::factory()->staff()->create([
            'name' => 'Test User2',
        ]);

        $attendanceRecord2 = AttendanceRecord::create([
            'user_id' => $staff2->id,
            'date' => '2026-06-23',
            'clock_in' => '2026-06-23 09:00:00',
            'clock_out' => '2026-06-23 18:00:00',
        ]);

        AttendanceCorrectRequest::create([
            'attendance_record_id' => $attendanceRecord2->id,
            'request_status_id' => '1',
            'requested_clock_in' => '2026-06-23 09:30:00',
            'requested_clock_out' => '2026-06-23 18:30:00',
            'comment' => '遅延のため',
        ]);

        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?tab=pending');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['承認待ち', 'Test User1', '2026/06/24', '遅延のため', '2026/06/25']);
        $response->assertSeeInOrder(['承認待ち', 'Test User2', '2026/06/23', '遅延のため', '2026/06/25']);

        Carbon::setTestNow();
    }

    public function test_承認済みの修正申請が全て表示されている()
    {
        $knownDate = Carbon::create(2026, 6, 25, 9, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($knownDate);

        $this->seed(RoleSeeder::class);
        $this->seed(RequestStatusSeeder::class);

        $staff1 = User::factory()->staff()->create([
            'name' => 'Test User1',
        ]);

        $attendanceRecord1 = AttendanceRecord::create([
            'user_id' => $staff1->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00',
        ]);

        AttendanceCorrectRequest::create([
            'attendance_record_id' => $attendanceRecord1->id,
            'request_status_id' => '2',
            'requested_clock_in' => '2026-06-24 09:30:00',
            'requested_clock_out' => '2026-06-24 18:30:00',
            'comment' => '遅延のため',
        ]);

        $staff2 = User::factory()->staff()->create([
            'name' => 'Test User2',
        ]);

        $attendanceRecord2 = AttendanceRecord::create([
            'user_id' => $staff2->id,
            'date' => '2026-06-23',
            'clock_in' => '2026-06-23 09:00:00',
            'clock_out' => '2026-06-23 18:00:00',
        ]);

        AttendanceCorrectRequest::create([
            'attendance_record_id' => $attendanceRecord2->id,
            'request_status_id' => '2',
            'requested_clock_in' => '2026-06-23 09:30:00',
            'requested_clock_out' => '2026-06-23 18:30:00',
            'comment' => '遅延のため',
        ]);

        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?tab=approved');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['承認済み', 'Test User1', '2026/06/24', '遅延のため', '2026/06/25']);
        $response->assertSeeInOrder(['承認済み', 'Test User2', '2026/06/23', '遅延のため', '2026/06/25']);

        Carbon::setTestNow();
    }

    public function test_修正申請の詳細内容が正しく表示されている()
    {
        $this->seed(RoleSeeder::class);
        $this->seed(RequestStatusSeeder::class);

        $staff = User::factory()->staff()->create([
            'name' => 'Test User',
        ]);

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $staff->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00',
        ]);

        $attendanceCorrectRequest = AttendanceCorrectRequest::create([
            'attendance_record_id' => $attendanceRecord->id,
            'request_status_id' => '1',
            'requested_clock_in' => '2026-06-24 09:30:00',
            'requested_clock_out' => '2026-06-24 18:30:00',
            'comment' => '遅延のため',
        ]);

        $admin = User::factory()->admin()->create();

        $url = route(
            'adminCorrection.edit',
            ['attendance_correct_request_id' => $attendanceCorrectRequest->id]
        );

        $response = $this->actingAs($admin)->get($url);
        $response->assertStatus(200);

        $response->assertSeeInOrder(['名前', 'Test User']);
        $response->assertSeeInOrder(['日付', '2026年', '6月24日']);
        $response->assertSeeInOrder(['出勤・退勤', '09:30', '18:30']);
        $response->assertSeeInOrder(['備考', '遅延のため']);

        Carbon::setTestNow();
    }

    public function test_修正申請の承認処理が正しく行われる()
    {
        $knownDate = Carbon::create(2026, 6, 25, 9, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($knownDate);

        $this->seed(RoleSeeder::class);
        $this->seed(RequestStatusSeeder::class);

        $staff = User::factory()->staff()->create([
            'name' => 'Test User',
        ]);

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $staff->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00',
        ]);

        $attendanceCorrectRequest = AttendanceCorrectRequest::create([
            'attendance_record_id' => $attendanceRecord->id,
            'request_status_id' => '1',
            'requested_clock_in' => '2026-06-24 09:30:00',
            'requested_clock_out' => '2026-06-24 18:30:00',
            'comment' => '遅延のため',
        ]);

        $admin = User::factory()->admin()->create();

        $url = route(
            'adminCorrection.edit',
            ['attendance_correct_request_id' => $attendanceCorrectRequest->id]
        );

        $response = $this->actingAs($admin)->patch($url);
        $response->assertStatus(302);

        $response = $this->actingAs($admin)->get($url);

        $response->assertSee('承認済み');

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $staff->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:30:00',
            'clock_out' => '2026-06-24 18:30:00',
        ]);

        $this->assertDatabaseHas('attendance_correct_requests', [
            'attendance_record_id' => $attendanceRecord->id,
            'request_status_id' => '2',
            'requested_clock_in' => '2026-06-24 09:30:00',
            'requested_clock_out' => '2026-06-24 18:30:00',
            'comment' => '遅延のため',
        ]);

        Carbon::setTestNow();
    }
}
