<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use Database\Seeders\AttendanceRecordSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class T_20_IndexReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_ゲストはレポートページにアクセスできない()
    {
        $response = $this->get('/attendance/report');

        $response->assertRedirect('/login');
    }

    public function test_認証ユーザーの統計情報が正しく計算される()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create([
            'name' => 'ユーザー1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->seed(AttendanceRecordSeeder::class);

        $response = $this->actingAs($user)->get('/attendance/report');
        $response->assertStatus(200);

        $response->assertViewHas('reports', function ($reports) {
            return isset($reports['summary']) &&
            isset($reports['monthly_trend']) &&
            isset($reports['anomalies']);
        });
    }

    public function test_勤怠記録がないユーザーで安全に処理される()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $response = $this->actingAs($user)->get('/attendance/report');
        $response->assertStatus(200);

        $response->assertViewHas('reports', function ($reports) {
            return isset($reports['summary']) &&
            isset($reports['monthly_trend']) &&
            isset($reports['anomalies']);
        });

        $response->assertSeeInOrder(['総労働時間', '0h', '0m']);
        $response->assertSeeInOrder(['総残業時間', '0h', '0m']);
        $response->assertSeeInOrder(['平均労働時間/日', '0h', '0m']);
        $response->assertSeeInOrder(['遅刻回数', '0 回']);
        $response->assertSeeInOrder(['早退回数', '0 回']);
        $response->assertSeeInOrder(['長時間労働日数', '0 日']);
    }
}
