<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    private function createVerifiedUser(array $attributes = [])
    {
        return User::factory()->create(array_merge([
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

    public function test_error_message_is_displayed_when_clock_in_is_after_clock_out()
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)
            ->from('/attendance/detail/' . $attendance->id)
            ->followingRedirects()
            ->post('/attendance/detail/' . $attendance->id, [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'note' => '修正申請テスト',
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
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)
            ->from('/attendance/detail/' . $attendance->id)
            ->followingRedirects()
            ->post('/attendance/detail/' . $attendance->id, [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => '修正申請テスト',
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
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)
            ->from('/attendance/detail/' . $attendance->id)
            ->followingRedirects()
            ->post('/attendance/detail/' . $attendance->id, [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => '修正申請テスト',
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
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)
            ->from('/attendance/detail/' . $attendance->id)
            ->followingRedirects()
            ->post('/attendance/detail/' . $attendance->id, [
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

    public function test_correction_request_is_created()
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        $this->actingAs($user)
            ->post('/attendance/detail/' . $attendance->id, [
                'clock_in' => '08:30',
                'clock_out' => '17:30',
                'note' => '電車遅延のため',
                'breaks' => [
                    [
                        'break_start' => '12:00',
                        'break_end' => '13:00',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id' => $attendance->id,
            'note' => '電車遅延のため',
            'status' => 'pending',
        ]);
    }

    public function test_all_own_pending_requests_are_displayed()
    {
        $user = $this->createVerifiedUser();
        $otherUser = $this->createVerifiedUser(['email' => 'other@example.com']);

        $attendance1 = $this->createAttendance($user);
        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-24',
            'clock_in' => '2026-03-24 09:00:00',
            'clock_out' => '2026-03-24 18:00:00',
        ]);

        $otherAttendance = $this->createAttendance($otherUser);

        DB::table('attendance_requests')->insert([
            [
                'attendance_id' => $attendance1->id,
                'requested_clock_in' => '2026-03-23 08:30:00',
                'requested_clock_out' => '2026-03-23 17:30:00',
                'requested_breaks' => json_encode([]),
                'note' => '自分の申請1',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => $attendance2->id,
                'requested_clock_in' => '2026-03-24 08:45:00',
                'requested_clock_out' => '2026-03-24 17:45:00',
                'requested_breaks' => json_encode([]),
                'note' => '自分の申請2',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => $otherAttendance->id,
                'requested_clock_in' => '2026-03-23 08:00:00',
                'requested_clock_out' => '2026-03-23 17:00:00',
                'requested_breaks' => json_encode([]),
                'note' => '他人の申請',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list?status=pending');

        $response->assertStatus(200);
        $response->assertSeeText('自分の申請1');
        $response->assertSeeText('自分の申請2');
        $response->assertDontSeeText('他人の申請');
    }

    public function test_all_approved_requests_of_the_user_are_displayed()
    {
        $user = $this->createVerifiedUser();
        $otherUser = $this->createVerifiedUser(['email' => 'other2@example.com']);

        $attendance1 = $this->createAttendance($user);
        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-24',
            'clock_in' => '2026-03-24 09:00:00',
            'clock_out' => '2026-03-24 18:00:00',
        ]);

        $otherAttendance = $this->createAttendance($otherUser);

        DB::table('attendance_requests')->insert([
            [
                'attendance_id' => $attendance1->id,
                'requested_clock_in' => '2026-03-23 08:30:00',
                'requested_clock_out' => '2026-03-23 17:30:00',
                'requested_breaks' => json_encode([]),
                'note' => '承認済み申請1',
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => $attendance2->id,
                'requested_clock_in' => '2026-03-24 08:45:00',
                'requested_clock_out' => '2026-03-24 17:45:00',
                'requested_breaks' => json_encode([]),
                'note' => '承認済み申請2',
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => $otherAttendance->id,
                'requested_clock_in' => '2026-03-23 08:00:00',
                'requested_clock_out' => '2026-03-23 17:00:00',
                'requested_breaks' => json_encode([]),
                'note' => '他人の承認済み申請',
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        $response->assertSeeText('承認済み申請1');
        $response->assertSeeText('承認済み申請2');
        $response->assertDontSeeText('他人の承認済み申請');
    }

    public function test_detail_link_redirects_to_attendance_detail_page()
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createAttendance($user);

        DB::table('attendance_requests')->insert([
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '2026-03-23 08:30:00',
            'requested_clock_out' => '2026-03-23 17:30:00',
            'requested_breaks' => json_encode([]),
            'note' => '詳細確認用申請',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list?status=pending');

        $response->assertStatus(200);
        $response->assertSee(route('attendance.detail', ['id' => $attendance->id]), false);

        $detailResponse = $this->actingAs($user)
            ->get('/attendance/detail/' . $attendance->id);

        $detailResponse->assertStatus(200);
    }
}
