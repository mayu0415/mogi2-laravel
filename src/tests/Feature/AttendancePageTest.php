<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendancePageTest extends TestCase
{
    use RefreshDatabase;

    private function createVerifiedUser(): User
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        if (method_exists($user, 'markEmailAsVerified')) {
            $user->markEmailAsVerified();
        }
        return $user->fresh();
    }

    public function test_current_datetime_is_displayed_in_the_correct_format()
    {
        Carbon::setTestNow(Carbon::parse('2026-03-23 09:30:00'));

        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user, 'web')->get('/attendance');

        $response->assertStatus(200);

        $response->assertSeeText('2026年3月23日');
        $response->assertSeeText('09:30');

        Carbon::setTestNow();
    }

    public function test_status_is_displayed_as_off_duty()
    {
        Carbon::setTestNow(Carbon::parse('2026-03-23 09:30:00'));

        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user, 'web')->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeText('勤務外');

        Carbon::setTestNow();
    }

    public function test_status_is_displayed_as_working()
    {
        Carbon::setTestNow(Carbon::parse('2026-03-23 09:30:00'));

        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-23',
            'clock_in' => '2026-03-23 09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user, 'web')->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeText('出勤中');

        Carbon::setTestNow();
    }

    public function test_status_is_displayed_as_on_break()
    {
        Carbon::setTestNow(Carbon::parse('2026-03-23 12:30:00'));

        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-23',
            'clock_in' => '2026-03-23 09:00:00',
            'clock_out' => null,
        ]);

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => '2026-03-23 12:00:00',
            'break_end' => null,
        ]);

        $response = $this->actingAs($user, 'web')->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeText('休憩中');

        Carbon::setTestNow();
    }

    public function test_status_is_displayed_as_finished()
    {
        Carbon::setTestNow(Carbon::parse('2026-03-23 19:00:00'));

        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-23',
            'clock_in' => '2026-03-23 09:00:00',
            'clock_out' => '2026-03-23 18:00:00',
        ]);

        $response = $this->actingAs($user, 'web')->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeText('退勤済');

        Carbon::setTestNow();
    }
}
