<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
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

    public function test_all_users_attendance_for_today_is_displayed()
    {
        Carbon::setTestNow('2026-03-23 09:00:00');

        $admin = $this->createAdminUser();
        $user1 = $this->createGeneralUser(['name' => '山田太郎']);
        $user2 = $this->createGeneralUser(['name' => '田中花子']);

        Attendance::create([
            'user_id' => $user1->id,
            'work_date' => '2026-03-23',
            'clock_in' => '2026-03-23 09:00:00',
            'clock_out' => '2026-03-23 18:00:00',
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'work_date' => '2026-03-23',
            'clock_in' => '2026-03-23 10:00:00',
            'clock_out' => '2026-03-23 19:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSeeText('山田太郎');
        $response->assertSeeText('田中花子');
        $response->assertSeeText('09:00');
        $response->assertSeeText('10:00');
    }

    public function test_current_date_is_displayed_when_opening_admin_attendance_list()
    {
        Carbon::setTestNow('2026-03-23 09:00:00');

        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSeeText('2026/03/23');
    }

    public function test_previous_day_attendance_is_displayed_when_previous_day_button_is_pressed()
    {
        Carbon::setTestNow('2026-03-23 09:00:00');

        $admin = $this->createAdminUser();
        $user = $this->createGeneralUser(['name' => '前日ユーザー']);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-22',
            'clock_in' => '2026-03-22 09:00:00',
            'clock_out' => '2026-03-22 18:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=2026-03-22');

        $response->assertStatus(200);
        $response->assertSeeText('2026/03/22');
        $response->assertSeeText('前日ユーザー');
    }

    public function test_next_day_attendance_is_displayed_when_next_day_button_is_pressed()
    {
        Carbon::setTestNow('2026-03-23 09:00:00');

        $admin = $this->createAdminUser();
        $user = $this->createGeneralUser(['name' => '翌日ユーザー']);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-24',
            'clock_in' => '2026-03-24 09:00:00',
            'clock_out' => '2026-03-24 18:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=2026-03-24');

        $response->assertStatus(200);
        $response->assertSeeText('2026/03/24');
        $response->assertSeeText('翌日ユーザー');
    }
}
