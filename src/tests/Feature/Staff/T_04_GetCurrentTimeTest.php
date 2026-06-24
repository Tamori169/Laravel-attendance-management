<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class T_04_GetCurrentTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_現在の日時情報がUIと同じ形式で出力されている()
    {
        $knownDate = Carbon::create(2026, 6, 24, 9, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($knownDate);

        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);

        $today = $knownDate->format('Y年n月j日');
        $currentTime = $knownDate->format('H:i');

        $response->assertSee($today);
        $response->assertSee($currentTime);

        Carbon::setTestNow();
    }
}
