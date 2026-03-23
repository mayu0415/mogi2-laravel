<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceActionTest extends TestCase
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

    public function test_clock_in_button_works_correctly()
    {
        Carbon::setTestNow(Carbon::parse('2026-03-23 09:00:00'));

        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user, 'web')->post('/attendance/clock-in');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => '2026-03-23',
        ]);

        $attendance = Attendance::where('user_id', $user->id)->where('work_date', '2026-03-23')->first();
        $this->assertNotNull($attendance->clock_in);
        $this->assertNull($attendance->clock_out);

        $page = $this->actingAs($user, 'web')->get('/attendance');
        $page->assertSeeText('出勤中');

        Carbon::setTestNow();
    }

    public function test_clock_in_can_be_done_only_once_per_day()
    {
        Carbon::setTestNow(Carbon::parse('2026-03-23 09:00:00'));

        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-23',
            'clock_in' => '2026-03-23 09:00:00',
            'clock_out' => null,
        ]);

        $page = $this->actingAs($user, 'web')->get('/attendance');

        $page->assertSeeText('出勤中');
        $page->assertSeeText('休憩入');

        Carbon::setTestNow();
    }

    public function test_clock_in_time_is_displayed_on_attendance_list()
    {
        Carbon::setTestNow(Carbon::parse('2026-03-23 09:00:00'));

        $user = $this->createVerifiedUser();

        $this->actingAs($user, 'web')->post('/attendance/clock-in');

        $page = $this->actingAs($user, 'web')->get('/attendance/list');

        $page->assertStatus(200);
        $page->assertSeeText('09:00');

        Carbon::setTestNow();
    }

    public function test_break_in_button_works_correctly()
    {
        Carbon::setTestNow(Carbon::parse('2026-03-23 12:00:00'));

        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-23',
            'clock_in' => '2026-03-23 09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user, 'web')->post('/attendance/break-in');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
        ]);

        $page = $this->actingAs($user, 'web')->get('/attendance');
        $page->assertSeeText('休憩中');

        Carbon::setTestNow();
    }

    public function test_break_in_can_be_done_multiple_times_per_day()
    {
        Carbon::setTestNow(Carbon::parse('2026-03-23 12:00:00'));

        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-23',
            'clock_in' => '2026-03-23 09:00:00',
            'clock_out' => null,
        ]);

        $this->actingAs($user, 'web')->post('/attendance/break-in');

        Carbon::setTestNow(Carbon::parse('2026-03-23 13:00:00'));
        $this->actingAs($user, 'web')->post('/attendance/break-out');

        Carbon::setTestNow(Carbon::parse('2026-03-23 15:00:00'));
        $page = $this->actingAs($user, 'web')->get('/attendance');
        $page->assertSeeText('休憩入');

        Carbon::setTestNow();
    }

    public function test_break_out_button_works_correctly()
    {
        Carbon::setTestNow(Carbon::parse('2026-03-23 13:00:00'));

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

        $response = $this->actingAs($user, 'web')->post('/attendance/break-out');

        $response->assertRedirect('/attendance');

        $break = AttendanceBreak::where('attendance_id', $attendance->id)->latest('id')->first();
        $this->assertNotNull($break->break_end);

        $page = $this->actingAs($user, 'web')->get('/attendance');
        $page->assertSeeText('出勤中');

        Carbon::setTestNow();
    }

    public function test_break_out_can_be_done_multiple_times_per_day()
    {
        Carbon::setTestNow(Carbon::parse('2026-03-23 09:00:00'));

        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-23',
            'clock_in' => '2026-03-23 09:00:00',
            'clock_out' => null,
        ]);

        $this->actingAs($user, 'web')->post('/attendance/break-in');
        Carbon::setTestNow(Carbon::parse('2026-03-23 10:00:00'));
        $this->actingAs($user, 'web')->post('/attendance/break-out');

        Carbon::setTestNow(Carbon::parse('2026-03-23 15:00:00'));
        $this->actingAs($user, 'web')->post('/attendance/break-in');

        $page = $this->actingAs($user, 'web')->get('/attendance');
        $page->assertSeeText('休憩戻');

        Carbon::setTestNow();
    }

    public function test_break_time_is_displayed_on_attendance_list()
    {
        Carbon::setTestNow(Carbon::parse('2026-03-23 09:00:00'));

        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-23',
            'clock_in' => '2026-03-23 09:00:00',
            'clock_out' => null,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-03-23 12:00:00'));
        $this->actingAs($user, 'web')->post('/attendance/break-in');

        Carbon::setTestNow(Carbon::parse('2026-03-23 13:00:00'));
        $this->actingAs($user, 'web')->post('/attendance/break-out');

        $page = $this->actingAs($user, 'web')->get('/attendance/list');

        $page->assertStatus(200);
        $page->assertSeeText('1:00');

        Carbon::setTestNow();
    }

    public function test_clock_out_button_works_correctly()
    {
        Carbon::setTestNow(Carbon::parse('2026-03-23 18:00:00'));

        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-23',
            'clock_in' => '2026-03-23 09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user, 'web')->post('/attendance/clock-out');

        $response->assertRedirect('/attendance');

        $attendance = Attendance::where('user_id', $user->id)->where('work_date', '2026-03-23')->first();
        $this->assertNotNull($attendance->clock_out);

        $page = $this->actingAs($user, 'web')->get('/attendance');
        $page->assertSeeText('退勤済');

        Carbon::setTestNow();
    }

    public function test_clock_out_time_is_displayed_on_attendance_list()
    {
        Carbon::setTestNow(Carbon::parse('2026-03-23 18:00:00'));

        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-23',
            'clock_in' => '2026-03-23 09:00:00',
            'clock_out' => null,
        ]);

        $this->actingAs($user, 'web')->post('/attendance/clock-out');

        $page = $this->actingAs($user, 'web')->get('/attendance/list');

        $page->assertStatus(200);
        $page->assertSeeText('18:00');

        Carbon::setTestNow();
    }
}