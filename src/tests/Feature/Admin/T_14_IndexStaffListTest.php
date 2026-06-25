<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class T_14_IndexStaffListTest extends TestCase
{
    use RefreshDatabase;

    public function test_管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる()
    {
        $this->seed(RoleSeeder::class);

        User::factory()->staff()->create([
            'name' => 'Test User1',
            'email' => 'test1@example.com',
        ]);

        User::factory()->staff()->create([
            'name' => 'Test User2',
            'email' => 'test2@example.com',
        ]);

        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/staff/list');
        $response->assertStatus(200);

        $response->assertSeeInOrder(['Test User1', 'test1@example.com']);
        $response->assertSeeInOrder(['Test User2', 'test2@example.com']);
    }

    public function test_ユーザーの勤怠情報が正しく表示される()
    {
        $this->seed(RoleSeeder::class);

        $staff = User::factory()->staff()->create([
            'name' => 'Test User1',
        ]);

        $attendanceRecord1 = AttendanceRecord::create([
            'user_id' => $staff->id,
            'date' => '2026-06-23',
            'clock_in' => '2026-06-23 09:00',
            'clock_out' => '2026-06-23 18:00',
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord1->id,
            'break_in' => '2026-06-23 12:00',
            'break_out' => '2026-06-23 13:00',
        ]);

        $attendanceRecord2 = AttendanceRecord::create([
            'user_id' => $staff->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00',
            'clock_out' => '2026-06-24 19:00',
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord2->id,
            'break_in' => '2026-06-24 12:00',
            'break_out' => '2026-06-24 13:00',
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord2->id,
            'break_in' => '2026-06-24 18:00',
            'break_out' => '2026-06-24 18:30',
        ]);

        $admin = User::factory()->admin()->create();

        $currentMonth = Carbon::parse('2026-06-01');
        $url = route('adminStaff.show', ['id' => $staff->id, 'month' => $currentMonth->format('Y-m')]);

        $response = $this->actingAs($admin)->get($url);
        $response->assertStatus(200);

        $response->assertSee('Test User1さんの勤怠');
        $response->assertSeeInOrder(['06/23', '09:00', '18:00', '1:00', '8:00']);
        $response->assertSeeInOrder(['06/24', '09:00', '19:00', '1:30', '8:30']);
    }

    public function test_「前月」を押下した時に表示月の前月の情報が表示される()
    {
        $this->seed(RoleSeeder::class);

        $staff = User::factory()->staff()->create([
            'name' => 'Test User1',
        ]);

        $attendanceRecord1 = AttendanceRecord::create([
            'user_id' => $staff->id,
            'date' => '2026-05-23',
            'clock_in' => '2026-05-23 09:00',
            'clock_out' => '2026-05-23 18:00',
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord1->id,
            'break_in' => '2026-05-23 12:00',
            'break_out' => '2026-05-23 13:00',
        ]);

        $attendanceRecord2 = AttendanceRecord::create([
            'user_id' => $staff->id,
            'date' => '2026-05-24',
            'clock_in' => '2026-05-24 09:00',
            'clock_out' => '2026-05-24 19:00',
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord2->id,
            'break_in' => '2026-05-24 12:00',
            'break_out' => '2026-05-24 13:00',
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord2->id,
            'break_in' => '2026-05-24 18:00',
            'break_out' => '2026-05-24 18:30',
        ]);

        $admin = User::factory()->admin()->create();

        $currentMonth = Carbon::parse('2026-06-01');
        $url = route('adminStaff.show', ['id' => $staff->id, 'month' => $currentMonth->format('Y-m')]);

        $response = $this->actingAs($admin)->get($url);
        $response->assertStatus(200);

        $url2 = route('adminStaff.show', [
            'id' => $staff->id,
            'month' => $currentMonth->copy()->subMonth()->format('Y-m'),
        ]);

        $response = $this->actingAs($admin)->get($url2);
        $response->assertStatus(200);

        $response->assertSee('Test User1さんの勤怠');
        $response->assertSeeInOrder(['05/23', '09:00', '18:00', '1:00', '8:00']);
        $response->assertSeeInOrder(['05/24', '09:00', '19:00', '1:30', '8:30']);
    }

    public function test_「翌月」を押下した時に表示月の前月の情報が表示される()
    {
        $this->seed(RoleSeeder::class);

        $staff = User::factory()->staff()->create([
            'name' => 'Test User1',
        ]);

        $attendanceRecord1 = AttendanceRecord::create([
            'user_id' => $staff->id,
            'date' => '2026-07-23',
            'clock_in' => '2026-07-23 09:00',
            'clock_out' => '2026-07-23 18:00',
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord1->id,
            'break_in' => '2026-07-23 12:00',
            'break_out' => '2026-07-23 13:00',
        ]);

        $attendanceRecord2 = AttendanceRecord::create([
            'user_id' => $staff->id,
            'date' => '2026-07-24',
            'clock_in' => '2026-07-24 09:00',
            'clock_out' => '2026-07-24 19:00',
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord2->id,
            'break_in' => '2026-07-24 12:00',
            'break_out' => '2026-07-24 13:00',
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord2->id,
            'break_in' => '2026-07-24 18:00',
            'break_out' => '2026-07-24 18:30',
        ]);

        $admin = User::factory()->admin()->create();

        $currentMonth = Carbon::parse('2026-06-01');
        $url = route('adminStaff.show', ['id' => $staff->id, 'month' => $currentMonth->format('Y-m')]);

        $response = $this->actingAs($admin)->get($url);
        $response->assertStatus(200);

        $url2 = route('adminStaff.show', [
            'id' => $staff->id,
            'month' => $currentMonth->copy()->addMonth()->format('Y-m'),
        ]);

        $response = $this->actingAs($admin)->get($url2);
        $response->assertStatus(200);

        $response->assertSee('Test User1さんの勤怠');
        $response->assertSeeInOrder(['07/23', '09:00', '18:00', '1:00', '8:00']);
        $response->assertSeeInOrder(['07/24', '09:00', '19:00', '1:30', '8:30']);
    }

    public function test_「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        $this->seed(RoleSeeder::class);

        $staff = User::factory()->staff()->create([
            'name' => 'Test User1',
        ]);

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $staff->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00',
            'clock_out' => '2026-06-24 18:00',
        ]);

        $admin = User::factory()->admin()->create();

        $currentMonth = Carbon::parse('2026-06-01');
        $url = route('adminStaff.show', ['id' => $staff->id, 'month' => $currentMonth->format('Y-m')]);

        $response = $this->actingAs($admin)->get($url);
        $response->assertStatus(200);

        $url2 = route('adminAttendance.show', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($admin)->get($url2);
        $response->assertStatus(200);

        $response->assertSee('勤怠詳細');
        $response->assertSeeInOrder(['名前', 'Test User1']);
        $response->assertSeeInOrder(['日付', '2026年', '6月24日']);
        $response->assertSeeInOrder(['出勤・退勤', '09:00', '18:00']);
    }
}
