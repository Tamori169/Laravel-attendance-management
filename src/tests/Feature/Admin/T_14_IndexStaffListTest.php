<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class T_14_IndexStaffListTest extends TestCase
{
    use RefreshDatabase;

    private Carbon $knownDate;
    private User $staff1;
    private User $staff2;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->knownDate = Carbon::create(2026, 6, 24, 9, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($this->knownDate);

        $this->seed(RoleSeeder::class);
        $this->staff1 = User::factory()->staff()->create();
        $this->staff2 = User::factory()->staff()->create();
        $this->admin = User::factory()->admin()->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる()
    {
        $response = $this->actingAs($this->admin)->get('/admin/staff/list');
        $response->assertStatus(200);

        $response->assertSeeInOrder([$this->staff1->name, $this->staff1->email]);
        $response->assertSeeInOrder([$this->staff2->name, $this->staff2->email]);
    }

    public function test_ユーザーの勤怠情報が正しく表示される()
    {
        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $this->staff1->id,
            'date' => '2026-06-23',
            'clock_in' => '2026-06-23 09:00',
            'clock_out' => '2026-06-23 18:00',
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '2026-06-23 12:00',
            'break_out' => '2026-06-23 13:00',
        ]);

        $url = route('adminStaff.show', ['id' => $this->staff1->id, 'month' => $this->knownDate->format('Y-m')]);

        $response = $this->actingAs($this->admin)->get($url);
        $response->assertStatus(200);

        $response->assertSee($this->staff1->name . 'さんの勤怠');
        $response->assertSee('2026/06');
        $response->assertSeeInOrder(['06/23', '09:00', '18:00', '1:00', '8:00']);
    }

    public function test_「前月」を押下した時に表示月の前月の情報が表示される()
    {
        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $this->staff1->id,
            'date' => '2026-05-23',
            'clock_in' => '2026-05-23 09:00',
            'clock_out' => '2026-05-23 18:00',
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '2026-05-23 12:00',
            'break_out' => '2026-05-23 13:00',
        ]);

        $admin = User::factory()->admin()->create();

        $url = route('adminStaff.show', [
            'id' => $this->staff1->id,
            'month' => $this->knownDate->format('Y-m'),
            ]);

        $response = $this->actingAs($admin)->get($url);
        $response->assertStatus(200);

        $url2 = route('adminStaff.show', [
            'id' => $this->staff1->id,
            'month' => $this->knownDate->copy()->subMonth()->format('Y-m'),
        ]);

        $response = $this->actingAs($admin)->get($url2);
        $response->assertStatus(200);

        $response->assertSee($this->staff1->name . 'さんの勤怠');
        $response->assertSee('2026/05');
        $response->assertSeeInOrder(['05/23', '09:00', '18:00', '1:00', '8:00']);
    }

    public function test_「翌月」を押下した時に表示月の翌月の情報が表示される()
    {
        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $this->staff1->id,
            'date' => '2026-07-23',
            'clock_in' => '2026-07-23 09:00',
            'clock_out' => '2026-07-23 18:00',
        ]);

        BreakRecord::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_in' => '2026-07-23 12:00',
            'break_out' => '2026-07-23 13:00',
        ]);

        $url = route('adminStaff.show', [
            'id' => $this->staff1->id,
            'month' => $this->knownDate->format('Y-m')
        ]);

        $response = $this->actingAs($this->admin)->get($url);
        $response->assertStatus(200);

        $url2 = route('adminStaff.show', [
            'id' => $this->staff1->id,
            'month' => $this->knownDate->copy()->addMonth()->format('Y-m'),
        ]);

        $response = $this->actingAs($this->admin)->get($url2);
        $response->assertStatus(200);

        $response->assertSee($this->staff1->name . 'さんの勤怠');
        $response->assertSee('2026/07');
        $response->assertSeeInOrder(['07/23', '09:00', '18:00', '1:00', '8:00']);
    }

    public function test_「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $this->staff1->id,
            'date' => '2026-06-24',
            'clock_in' => '2026-06-24 09:00',
            'clock_out' => '2026-06-24 18:00',
        ]);

        $url = route('adminStaff.show', [
            'id' => $this->staff1->id,
            'month' => $this->knownDate->format('Y-m')
        ]);

        $response = $this->actingAs($this->admin)->get($url);
        $response->assertStatus(200);

        $url2 = route('adminAttendance.show', ['id' => $attendanceRecord->id]);

        $response = $this->actingAs($this->admin)->get($url2);
        $response->assertStatus(200);

        $response->assertSee('勤怠詳細');
        $response->assertSeeInOrder(['名前', $this->staff1->name]);
        $response->assertSeeInOrder(['日付', '2026年', '6月24日']);
    }
}
