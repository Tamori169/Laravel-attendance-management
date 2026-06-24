<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class T_02_LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_メールアドレスが未入力の場合、バリデーションメッセージが表示される()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $response = $this->get('/login');

        $response->assertStatus(200);

        $response = $this->post('/login', [
            'email' => '',
            'password' => $user->password,
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    public function test_パスワードが未入力の場合、バリデーションメッセージが表示される()
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->staff()->create();

        $response = $this->get('/login');

        $response->assertStatus(200);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    public function test_登録内容と一致しない場合、バリデーションメッセージが表示される_メールアドレス()
    {
        $this->seed(RoleSeeder::class);
        $email = 'test@example.com';
        $password = 'password';
        $user = User::factory()->staff()->create([
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $response = $this->get('/login');

        $response->assertStatus(200);

        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => $password,
        ]);

        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }

    public function test_登録内容と一致しない場合、バリデーションメッセージが表示される_パスワード()
    {
        $this->seed(RoleSeeder::class);
        $email = 'test@example.com';
        $password = 'password';
        $user = User::factory()->staff()->create([
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $response = $this->get('/login');

        $response->assertStatus(200);

        $response = $this->post('/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}
