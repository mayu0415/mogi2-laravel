<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginValidationTest extends TestCase
{
    use RefreshDatabase;
    public function test_user_login_fails_when_email_is_empty()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);
        $response->assertSessionHasErrors(['email']);
    
    }
    public function test_user_login_fails_when_password_is_empty()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'user@example.com',
            'password' => '',
        ]);
        $response->assertSessionHasErrors(['password']);
    
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
        $response = $this->from('/login')->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);
        $response->assertSessionHasErrors(['password']);
    
    }
    public function test_admin_login_fails_when_email_is_empty()
    {
        $response = $this->from('/admin/login')->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);
        $response->assertSessionHasErrors(['email']);
    
    }
    public function test_admin_login_fails_when_password_is_empty()
    {
        $response = $this->from('/admin/login')->post('/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);
        $response->assertSessionHasErrors(['password']);
    
    }
}