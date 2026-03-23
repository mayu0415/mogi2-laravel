<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_attendance_list_shows_own_records()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now(),
            'clock_out' => now()->addHours(8),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSeeText(now()->format('Y/m'));
    }

    public function test_current_month_is_displayed()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSeeText(now()->format('Y/m'));
    }

    public function test_previous_month_button_shows_previous_month()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $lastMonth = now()->subMonth();

        $response = $this->actingAs($user)
            ->get('/attendance/list?month=' . $lastMonth->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSeeText('前月');
    }

    public function test_next_month_button_shows_next_month()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $nextMonth = now()->addMonth();

        $response = $this->actingAs($user)
            ->get('/attendance/list?month=' . $nextMonth->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSeeText('翌月');
    }

    public function test_detail_button_redirects_to_attendance_detail_page()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
    }
}