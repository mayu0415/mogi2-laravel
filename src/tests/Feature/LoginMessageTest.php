<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginMessageTest extends TestCase
{
    use RefreshDatabase;
    public function test_user_login_fails_when_email_is_empty()
    {
        $response = $this->from('/login')->followingRedirects()->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);
        ;
        $response->assertSeeText('メールアドレスを入力してください');
    }
    public function test_user_login_fails_when_password_is_empty()
    {
        $response = $this->from('/login')->followingRedirects()->post('/login', [
            'email' => 'user@example.com',
            'password' => '',
        ]);

        $response->assertSeeText('パスワードを入力してください');
    }
    public function test_user_login_fails_with_invalid_credentials()
    {
        User::create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => 0,
            'email_verified_at' => now(),
        ]);
        $response = $this->from('/login')->followingRedirects()->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertSeeText('ログイン情報が登録されていません');
    }
    public function test_admin_login_fails_when_email_is_empty()
    {
        $response = $this->from('/admin/login')->followingRedirects()->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSeeText('メールアドレスを入力してください');
    }
    public function test_admin_login_fails_when_password_is_empty()
    {
        $response = $this->from('/admin/login')->followingRedirects()->post('/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSeeText('パスワードを入力してください');
    }
}