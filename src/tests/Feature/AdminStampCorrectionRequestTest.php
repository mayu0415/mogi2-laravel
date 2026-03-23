<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminStampCorrectionRequestTest extends TestCase
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

    private function createAttendance(User $user, string $workDate = '2026-03-23')
    {
        return Attendance::create([
            'user_id' => $user->id,
            'work_date' => $workDate,
            'clock_in' => $workDate . ' 09:00:00',
            'clock_out' => $workDate . ' 18:00:00',
        ]);
    }

    public function test_all_pending_correction_requests_are_displayed()
    {
        $admin = $this->createAdminUser();

        $user1 = $this->createGeneralUser(['name' => '山田太郎']);
        $user2 = $this->createGeneralUser(['name' => '田中花子']);

        $attendance1 = $this->createAttendance($user1, '2026-03-23');
        $attendance2 = $this->createAttendance($user2, '2026-03-24');

        DB::table('attendance_requests')->insert([
            [
                'attendance_id' => $attendance1->id,
                'requested_clock_in' => '2026-03-23 08:30:00',
                'requested_clock_out' => '2026-03-23 17:30:00',
                'requested_breaks' => json_encode([]),
                'note' => '未承認申請1',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => $attendance2->id,
                'requested_clock_in' => '2026-03-24 08:45:00',
                'requested_clock_out' => '2026-03-24 17:45:00',
                'requested_breaks' => json_encode([]),
                'note' => '未承認申請2',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/list?status=pending');

        $response->assertStatus(200);
        $response->assertSeeText('未承認申請1');
        $response->assertSeeText('未承認申請2');
    }

    public function test_all_approved_correction_requests_are_displayed()
    {
        $admin = $this->createAdminUser();

        $user1 = $this->createGeneralUser(['name' => '山田太郎']);
        $user2 = $this->createGeneralUser(['name' => '田中花子']);

        $attendance1 = $this->createAttendance($user1, '2026-03-23');
        $attendance2 = $this->createAttendance($user2, '2026-03-24');

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
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        $response->assertSeeText('承認済み申請1');
        $response->assertSeeText('承認済み申請2');
    }

    public function test_correction_request_detail_is_displayed_correctly()
    {
        $admin = $this->createAdminUser();

        $user = $this->createGeneralUser([
            'name' => '山田太郎',
        ]);

        $attendance = $this->createAttendance($user, '2026-03-23');

        DB::table('attendance_requests')->insert([
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '2026-03-23 08:30:00',
            'requested_clock_out' => '2026-03-23 17:30:00',
            'requested_breaks' => json_encode([
                [
                    'break_start' => '12:00',
                    'break_end' => '13:00',
                ],
            ]),
            'note' => '電車遅延のため',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = DB::table('attendance_requests')->where('attendance_id', $attendance->id)->first();

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/approve/' . $request->id);

        $response->assertStatus(200);
        $response->assertSeeText('山田太郎');
        $response->assertSeeText('2026年');
        $response->assertSeeText('3月23日');
        $response->assertSeeText('08:30');
        $response->assertSeeText('17:30');
        $response->assertSeeText('12:00');
        $response->assertSeeText('13:00');
        $response->assertSeeText('電車遅延のため');
    }

    public function test_correction_request_is_approved_correctly()
    {
        $admin = $this->createAdminUser();

        $user = $this->createGeneralUser([
            'name' => '山田太郎',
        ]);

        $attendance = $this->createAttendance($user, '2026-03-23');

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => '2026-03-23 12:00:00',
            'break_end' => '2026-03-23 13:00:00',
        ]);

        DB::table('attendance_requests')->insert([
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '2026-03-23 08:30:00',
            'requested_clock_out' => '2026-03-23 17:30:00',
            'requested_breaks' => json_encode([
                [
                    'break_start' => '12:15',
                    'break_end' => '13:15',
                ],
            ]),
            'note' => '電車遅延のため',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = DB::table('attendance_requests')->where('attendance_id', $attendance->id)->first();

        $response = $this->actingAs($admin)
            ->post('/admin/stamp_correction_request/approve/' . $request->id);

        $response->assertRedirect();

        $this->assertDatabaseHas('attendance_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '2026-03-23 08:30:00',
            'clock_out' => '2026-03-23 17:30:00',
        ]);
    }
}
