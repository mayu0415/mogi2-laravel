<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
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

    private function createAttendance(User $user)
    {
        return Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-23',
            'clock_in' => '2026-03-23 09:00:00',
            'clock_out' => '2026-03-23 18:00:00',
        ]);
    }

    public function test_selected_attendance_data_is_displayed_on_admin_detail_page()
    {
        $admin = $this->createAdminUser();
        $user = $this->createGeneralUser(['name' => '山田太郎']);

        $attendance = $this->createAttendance($user);

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => '2026-03-23 12:00:00',
            'break_end' => '2026-03-23 13:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSeeText('山田太郎');
        $response->assertSeeText('2026年');
        $response->assertSeeText('3月23日');
        $response->assertSee('value="09:00"', false);
        $response->assertSee('value="18:00"', false);
        $response->assertSee('value="12:00"', false);
        $response->assertSee('value="13:00"', false);
    }

    public function test_error_message_is_displayed_when_clock_in_is_after_clock_out()
    {
        $admin = $this->createAdminUser();
        $user = $this->createGeneralUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($admin)
            ->from('/admin/attendance/' . $attendance->id)
            ->followingRedirects()
            ->post('/admin/attendance/' . $attendance->id, [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'note' => '管理者修正テスト',
                'breaks' => [
                    [
                        'break_start' => '12:00',
                        'break_end' => '13:00',
                    ],
                ],
            ]);

        $response->assertSeeText('出勤時間もしくは退勤時間が不適切な値です');
    }

    public function test_error_message_is_displayed_when_break_start_is_after_clock_out()
    {
        $admin = $this->createAdminUser();
        $user = $this->createGeneralUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($admin)
            ->from('/admin/attendance/' . $attendance->id)
            ->followingRedirects()
            ->post('/admin/attendance/' . $attendance->id, [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => '管理者修正テスト',
                'breaks' => [
                    [
                        'break_start' => '19:00',
                        'break_end' => '19:30',
                    ],
                ],
            ]);

        $response->assertSeeText('休憩時間が不適切な値です');
    }

    public function test_error_message_is_displayed_when_break_end_is_after_clock_out()
    {
        $admin = $this->createAdminUser();
        $user = $this->createGeneralUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($admin)
            ->from('/admin/attendance/' . $attendance->id)
            ->followingRedirects()
            ->post('/admin/attendance/' . $attendance->id, [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => '管理者修正テスト',
                'breaks' => [
                    [
                        'break_start' => '12:00',
                        'break_end' => '19:00',
                    ],
                ],
            ]);

        $response->assertSeeText('休憩時間もしくは退勤時間が不適切な値です');
    }

    public function test_error_message_is_displayed_when_note_is_empty()
    {
        $admin = $this->createAdminUser();
        $user = $this->createGeneralUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($admin)
            ->from('/admin/attendance/' . $attendance->id)
            ->followingRedirects()
            ->post('/admin/attendance/' . $attendance->id, [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => '',
                'breaks' => [
                    [
                        'break_start' => '12:00',
                        'break_end' => '13:00',
                    ],
                ],
            ]);

        $response->assertSeeText('備考を記入してください');
    }
}
