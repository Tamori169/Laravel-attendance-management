<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class T_16_EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_会員登録後、認証メールが送信される()
    {
        Notification::fake();
        $this->seed(RoleSeeder::class);

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する()
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->staff()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/email/verify');
        $response->assertStatus(200);

        $response->assertSee('認証はこちらから');
        $response->assertSee('http://127.0.0.1:8025/');
    }

    public function test_メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する()
    {
        Notification::fake();
        $this->seed(RoleSeeder::class);

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo(
            $user,
            VerifyEmail::class,
            function ($notification) use ($user) {
                $verificationUrl = $notification->toMail($user)->actionUrl;

                $response = $this->actingAs($user)->get($verificationUrl);

                $response->assertRedirect('/attendance?verified=1');

                $this->assertNotNull($user->fresh()->email_verified_at);

                return true;
            }
        );
    }
}
