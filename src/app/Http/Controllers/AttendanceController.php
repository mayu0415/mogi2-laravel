<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttendanceDetailRequest;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceRequest;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        $onBreak = null;
        if ($attendance) {
            $onBreak = AttendanceBreak::where('attendance_id', $attendance->id)
                ->whereNull('break_end')
                ->latest('break_start')
                ->first();
        }

        return view('attendance', compact('attendance', 'onBreak'));
    }

    public function clockIn(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
        ->first();
        if (!$attendance) {
            Attendance::create([
                'user_id' => $user->id,
                'work_date' => $today,
                'clock_in' => $now,
                'clock_out' => null,
            ]);
        }

        return redirect()->route('attendance');
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();
        if (!$attendance || $attendance->clock_out) {
            return redirect()->route('attendance');
        }

        $openBreak = AttendanceBreak::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest('break_start')
            ->first();

        if ($openBreak) {
            $openBreak->update(['break_end' => $now]);
        }
        if (!$attendance->clock_in) {
            $attendance->clock_in = $now;
        }
        $attendance->clock_out = $now;
        $attendance->save();
        return redirect()->route('attendance');
        }


    public function breakIn(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        if (!$attendance || !$attendance->clock_in || $attendance->clock_out) {
            return redirect()->route('attendance');
        }

        $openBreak = AttendanceBreak::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest('break_start')
            ->first();
        if ($openBreak) {
        return redirect()->route('attendance');
        }
        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => $now,
            'break_end' => null,
        ]);
        return redirect()->route('attendance');
    }

    public function breakOut(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();
        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();
        if (!$attendance) {
            return redirect()->route('attendance');
        }
        $openBreak = AttendanceBreak::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest('break_start')
            ->first();
        if ($openBreak) {
        $openBreak->update(['break_end' => $now]);
        }
        return redirect()->route('attendance');
    }

    public function list(Request $request)
    {
    $user = Auth::user();

    $monthParam = $request->query('month');

    if ($monthParam) {
        $currentMonth = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
    } else {
        $currentMonth = Carbon::now()->startOfMonth();
    }

    $startOfMonth = $currentMonth->copy()->startOfMonth();
    $endOfMonth = $currentMonth->copy()->endOfMonth();

    $attendances = Attendance::with('breaks')
        ->where('user_id', $user->id)
        ->whereBetween('work_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
        ->get()
        ->keyBy(function ($attendance) {
            return Carbon::parse($attendance->work_date)->format('Y-m-d');
        });

    $days = [];
    $weekdays = ['日', '月', '火', '水', '木', '金', '土'];

    for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
        $dateKey = $date->format('Y-m-d');
        $attendance = $attendances->get($dateKey);

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

        $days[] = [
            'date' => $date->copy(),
            'attendance' => $attendance,
            'date_label' => $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')',
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'break_total' => $breakTotal,
            'work_total' => $workTotal,
        ];
    }

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
        $displayMonth = $currentMonth->format('Y/m');

        return view('attendance-list', compact(
            'days',
            'displayMonth',
            'prevMonth',
            'nextMonth'
        ));
    }

    public function detail($id)
    {
        $attendance = Attendance::with('breaks', 'user', 'requests')->findOrFail($id);

        $latestRequest = $attendance->requests()->latest()->first();
        $isPending = $latestRequest && $latestRequest->status === 'pending';

        $breaks = $attendance->breaks;

        return view('attendance-detail', compact('attendance', 'breaks', 'latestRequest', 'isPending'));
    }

    public function updateRequest(AttendanceDetailRequest $request, $id)
    {
        $attendance = Attendance::with(['breaks', 'requests'])->findOrFail($id);
        $latestRequest = $attendance->requests()->latest()->first();
        $isPending = $latestRequest && $latestRequest->status === 'pending';
        if ($isPending) {
            return back()->with('error', '承認待ちのため修正はできません。');
        }
        $workDate = $attendance->work_date;
        $breakInputs = $request->input('breaks', []);
        $requestedBreaks = [];
        foreach ($breakInputs as $break) {
            $breakStart = $break['break_start'] ?? null;
            $breakEnd = $break['break_end'] ?? null;
            if ($breakStart || $breakEnd) {
                $requestedBreaks[] = [
                    'break_start' => $breakStart ? $workDate . ' ' . $breakStart . ':00' : null,
                    'break_end' => $breakEnd ? $workDate . ' ' . $breakEnd . ':00' : null,
                ];
            }
        }
        AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'requested_clock_in' => $workDate . ' ' . $request->clock_in . ':00',
            'requested_clock_out' => $workDate . ' ' . $request->clock_out . ':00',
            'requested_breaks' => $requestedBreaks,
            'note' => $request->note,
            'status' => 'pending',
        ]);
        return back()->with('success', '修正申請を送信しました。');
    }


    public function requestList(Request $request)
    {
    $user = Auth::user();
    $status = $request->query('status', 'pending');

    $requests = AttendanceRequest::with(['attendance.user'])
        ->whereHas('attendance', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('status', $status)
        ->latest()
        ->get();

    return view('stamp-correction-request-list', compact('requests', 'status'));
}
}