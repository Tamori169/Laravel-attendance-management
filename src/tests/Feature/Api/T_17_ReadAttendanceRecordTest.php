<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceCorrectRequest;
use App\Models\AttendanceRecord;
use App\Models\BreakCorrectRequest;
use App\Models\BreakRecord;
use App\Models\User;
use Database\Seeders\AttendanceRecordSeeder;
use Database\Seeders\RequestStatusSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class T_17_ReadAttendanceRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠一覧がJSONで取得できる()
    {
        $this->seed(RoleSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(AttendanceRecordSeeder::class);

        $user = User::where('email', 'user1@example.com')->first();

        $response = $this->actingAs($user)->get('/api/v1/attendance-records');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user'=> [
                        'id',
                        'name',
                    ],
                    'date',
                    'clock_in',
                    'clock_out',
                    'total_time',
                    'total_break_time',
                    'breaks' => [
                        '*' => [
                            'id',
                            'break_in',
                            'break_out',
                        ],
                    ],
                ],
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);
    }

    public function test_勤怠詳細がJSONで取得できる()
    {
        $this->seed(RequestStatusSeeder::class);
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00',
        ]);

        $breakRecord1 = BreakRecord::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '2026-06-24 12:00:00',
            'break_out' => '2026-06-24 13:00:00',
        ]);

        $breakRecord2 = BreakRecord::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '2026-06-24 15:00:00',
            'break_out' => '2026-06-24 15:30:00',
        ]);

        $attendanceCorrectRequest = AttendanceCorrectRequest::create([
            'attendance_record_id' => $attendanceRecord->id,
            'request_status_id' => 1,
            'requested_clock_in' => '2026-06-24 08:30:00',
            'requested_clock_out' => '2026-06-24 18:30:00',
            'comment' => '操作ミスのため',
        ]);

        $breakCorrectRequest = BreakCorrectRequest::create([
            'attendance_correct_request_id' => $attendanceCorrectRequest->id,
            'requested_break_in' => '2026-06-24 12:30:00',
            'requested_break_out' => '2026-06-24 13:30:00',
        ]);

        $response = $this->actingAs($user)->get("/api/v1/attendance-records/{$attendanceRecord->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'user' => [
                    'id',
                    'name',
                ],
                'date',
                'clock_in',
                'clock_out',
                'total_time',
                'total_break_time',
                'breaks' => [
                    '*' => [
                        'id',
                        'break_in',
                        'break_out',
                    ],
                ],
                'applications' => [
                    '*' => [
                        'id',
                        'attendance_record_id',
                        'request_status' => [
                            'id',
                            'name',
                        ],
                        'requested_clock_in',
                        'requested_clock_out',
                        'break_corrections' => [
                            '*' => [
                                'id',
                                'requested_break_in',
                                'requested_break_out',
                            ],
                        ],
                        'comment',
                    ],
                ],
            ],
        ]);

        $response->assertJsonPath('data.id', $attendanceRecord->id);
        $response->assertJsonPath('data.date', '2026-06-24');
        $response->assertJsonPath('data.clock_in', '09:00:00');
        $response->assertJsonPath('data.clock_out', '18:00:00');

        $response->assertJsonPath('data.user.id', $user->id);
        $response->assertJsonPath('data.user.name', $user->name);

        $response->assertJsonPath('data.breaks.0.id', $breakRecord1->id);
        $response->assertJsonPath('data.breaks.0.break_in', '12:00:00');
        $response->assertJsonPath('data.breaks.1.id', $breakRecord2->id);
        $response->assertJsonPath('data.breaks.1.break_in', '15:00:00');

        $response->assertJsonPath('data.applications.0.id', $attendanceCorrectRequest->id);
        $response->assertJsonPath('data.applications.0.request_status.id', $attendanceCorrectRequest->request_status_id);
        $response->assertJsonPath('data.applications.0.requested_clock_in', '08:30:00');
        $response->assertJsonPath('data.applications.0.break_corrections.0.requested_break_in', '12:30:00');
    }

    public function test_存在しないIDでは404とエラーJSONが返る()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $attendanceRecord = AttendanceRecord::create([
            'id' => 1,
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00',
        ]);

        $response = $this->actingAs($user)->getJSON("/api/v1/attendance-records/99999");

        $response->assertStatus(404);
        $response->assertJson([
            'error' => '勤怠情報が見つかりませんでした。',
        ]);
    }
}
