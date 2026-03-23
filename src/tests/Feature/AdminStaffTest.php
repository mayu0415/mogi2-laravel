<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(array $attributes = [])
    {
        return User::factory()->create(array_merge([
            'role' => 1,
            'email_verified_at' => now(),
        ], $attributes));
    }

    private function createGeneralUser(array $attributes = [])
    {
        return User::factory()->create(array_merge([
            'role' => 0,
            'email_verified_at' => now(),
        ], $attributes));
    }

    public function test_admin_can_see_all_general_users_names_and_emails()
    {
        $admin = $this->createAdminUser();

        $user1 = $this->createGeneralUser([
            'name' => '山田太郎',
            'email' => 'taro@example.com',
        ]);

        $user2 = $this->createGeneralUser([
            'name' => '田中花子',
            'email' => 'hanako@example.com',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertSeeText('山田太郎');
        $response->assertSeeText('taro@example.com');
        $response->assertSeeText('田中花子');
        $response->assertSeeText('hanako@example.com');
    }

    public function test_admin_can_see_selected_users_attendance_information()
    {
        $admin = $this->createAdminUser();

        $user = $this->createGeneralUser([
            'name' => '山田太郎',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-23',
            'clock_in' => '2026-03-23 09:00:00',
            'clock_out' => '2026-03-23 18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/' . $user->id . '?month=2026-03');

        $response->assertStatus(200);
        $response->assertSeeText('山田太郎');
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');
    }

    public function test_previous_month_attendance_is_displayed_when_previous_month_button_is_pressed()
    {
        $admin = $this->createAdminUser();

        $user = $this->createGeneralUser([
            'name' => '前月ユーザー',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-02-20',
            'clock_in' => '2026-02-20 09:00:00',
            'clock_out' => '2026-02-20 18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/' . $user->id . '?month=2026-02');

        $response->assertStatus(200);
        $response->assertSeeText('2026/02');
        $response->assertSeeText('前月ユーザー');
    }

    public function test_next_month_attendance_is_displayed_when_next_month_button_is_pressed()
    {
        $admin = $this->createAdminUser();

        $user = $this->createGeneralUser([
            'name' => '翌月ユーザー',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-20',
            'clock_in' => '2026-04-20 09:00:00',
            'clock_out' => '2026-04-20 18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/' . $user->id . '?month=2026-04');

        $response->assertStatus(200);
        $response->assertSeeText('2026/04');
        $response->assertSeeText('翌月ユーザー');
    }

    public function test_detail_link_redirects_to_attendance_detail_page()
    {
        $admin = $this->createAdminUser();

        $user = $this->createGeneralUser([
            'name' => '詳細確認ユーザー',
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-23',
            'clock_in' => '2026-03-23 09:00:00',
            'clock_out' => '2026-03-23 18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/' . $user->id . '?month=2026-03');

        $response->assertStatus(200);
        $response->assertSee(route('admin.attendance.detail', ['id' => $attendance->id]), false);

        $detailResponse = $this->actingAs($admin)
            ->get('/admin/attendance/' . $attendance->id);

        $detailResponse->assertStatus(200);
    }
}