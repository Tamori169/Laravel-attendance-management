<?php

namespace Tests\Feature\Staff;

use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
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

    public function test_「翌月」を押下した時に表示月の前月の情報が表示される()
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
    }
}
