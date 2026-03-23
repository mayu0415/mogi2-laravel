<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceDetailRequest;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $dateParam = $request->query('date');
        if ($dateParam) {
            $currentDate = Carbon::createFromFormat('Y-m-d', $dateParam);
        } else {
            $currentDate = Carbon::today();
        }
        $users = User::where('role', 0)->orderBy('id')->get();
        $attendances = Attendance::with(['user', 'breaks'])
            ->where('work_date', $currentDate->toDateString())
            ->get()
            ->keyBy('user_id');
        $staffAttendances = [];
        foreach ($users as $user) {
            $attendance = $attendances->get($user->id);
            $clockIn = '';
            $clockOut = '';
            $breakTotal = '';
            $workTotal = '';
            if ($attendance) {
                if ($attendance->clock_in) {
                    $clockIn = Carbon::parse($attendance->clock_in)->format('H:i');
                }
                if ($attendance->clock_out) {
                    $clockOut = Carbon::parse($attendance->clock_out)->format('H:i');
                }
                $breakMinutes = 0;
                foreach ($attendance->breaks as $break) {
                    if ($break->break_start && $break->break_end) {
                        $breakMinutes += Carbon::parse($break->break_start)
                            ->diffInMinutes(Carbon::parse($break->break_end));
                    }
                }
                if ($breakMinutes > 0) {
                    $hours = floor($breakMinutes / 60);
                    $minutes = $breakMinutes % 60;
                    $breakTotal = sprintf('%d:%02d', $hours, $minutes);
                }
                if ($attendance->clock_in && $attendance->clock_out) {
                    $workMinutes = Carbon::parse($attendance->clock_in)
                        ->diffInMinutes(Carbon::parse($attendance->clock_out)) - $breakMinutes;
                    $hours = floor($workMinutes / 60);
                    $minutes = $workMinutes % 60;
                    $workTotal = sprintf('%d:%02d', $hours, $minutes);
                }
            }
            $staffAttendances[] = [
                'user' => $user,
                'attendance' => $attendance,
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'break_total' => $breakTotal,
                'work_total' => $workTotal,
            ];
        }
        $prevDate = $currentDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $currentDate->copy()->addDay()->format('Y-m-d');
        $displayDate = $currentDate->format('Y年n月j日');
        $navDate = $currentDate->format('Y/m/d');
        return view('admin-attendance-list', compact(
            'staffAttendances',
            'displayDate',
            'navDate',
            'prevDate',
            'nextDate'
        ));
    }
    public function detail($id)
    {
        $attendance = Attendance::with(['breaks', 'user', 'requests'])->findOrFail($id);
        $latestRequest = $attendance->requests()->latest()->first();
        $isPending = $latestRequest && $latestRequest->status === 'pending';
        $breaks = $attendance->breaks;
        return view('admin-attendance-detail', compact(
            'attendance',
            'breaks',
            'latestRequest',
            'isPending'
        ));
    }
    public function update(AttendanceDetailRequest $request, $id)
    {
        $attendance = Attendance::with(['breaks', 'requests'])->findOrFail($id);
        $latestRequest = $attendance->requests()->latest()->first();
        $isPending = $latestRequest && $latestRequest->status === 'pending';
        if ($isPending) {
            return back()->with('error', '承認待ちのため修正はできません。');
        }
        $workDate = $attendance->work_date;
        $attendance->update([
            'clock_in' => $workDate . ' ' . $request->clock_in . ':00',
            'clock_out' => $workDate . ' ' . $request->clock_out . ':00',
        ]);
        $attendance->breaks()->delete();
        $breaks = $request->input('breaks', []);
        foreach ($breaks as $break) {
            $breakStart = $break['break_start'] ?? null;
            $breakEnd = $break['break_end'] ?? null;
            if ($breakStart || $breakEnd) {
                $attendance->breaks()->create([
                    'break_start' => $breakStart ? $workDate . ' ' . $breakStart . ':00' : null,
                    'break_end' => $breakEnd ? $workDate . ' ' . $breakEnd . ':00' : null,
                ]);
            }
        }
        return redirect()
            ->route('admin.attendance.detail', ['id' => $attendance->id])
            ->with('success', '勤怠情報を修正しました。');
    }
}