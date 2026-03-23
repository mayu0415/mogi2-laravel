<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminStaffController extends Controller
{
    public function index()
    {
         $staffs = User::where('role', 0)
            ->orderBy('id', 'asc')
            ->get();
        return view('admin-staff-list', compact('staffs'));
    }

    public function show(Request $request, $id)
    {
        $staff = User::where('role', 0)->findOrFail($id);
        $monthParam = $request->query('month');
        if ($monthParam) {
            $currentMonth = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
        } else {
            $currentMonth = Carbon::now()->startOfMonth();
        }
        $startDate = $currentMonth->copy()->startOfMonth();
        $endDate = $currentMonth->copy()->endOfMonth();
        $attendances = Attendance::with('breaks')
            ->where('user_id', $staff->id)
            ->whereBetween('work_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->work_date)->format('Y-m-d');
            });
        $days = [];
        $date = $startDate->copy();
        while ($date->lte($endDate)) {
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
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'break_total' => $breakTotal,
                'work_total' => $workTotal,
            ];
            $date->addDay();
        }
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
        $displayMonth = $currentMonth->format('Y/m');
        return view('admin-staff-attendance-list', compact(
            'staff',
            'days',
            'displayMonth',
            'prevMonth',
            'nextMonth'
        ));
    }

    public function exportCsv(Request $request, $id): StreamedResponse
    {
        $staff = User::where('role', 0)->findOrFail($id);
        $monthParam = $request->query('month');
        if ($monthParam) {
            $currentMonth = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
        } else {
            $currentMonth = Carbon::now()->startOfMonth();
        }
        $startDate = $currentMonth->copy()->startOfMonth();
        $endDate = $currentMonth->copy()->endOfMonth();
        $attendances = Attendance::with('breaks')
            ->where('user_id', $staff->id)
            ->whereBetween('work_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->work_date)->format('Y-m-d');
            });
        $fileName = $staff->name . '_' . $currentMonth->format('Y_m') . '_attendance.csv';
        $response = new StreamedResponse(function () use ($attendances, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);
            $date = $startDate->copy();
            while ($date->lte($endDate)) {
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
                        $breakTotal = sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60);
                    }

                    if ($attendance->clock_in && $attendance->clock_out) {
                        $workMinutes = Carbon::parse($attendance->clock_in)
                            ->diffInMinutes(Carbon::parse($attendance->clock_out)) - $breakMinutes;
                        $workTotal = sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60);
                    }
                }
                fputcsv($handle, [
                    $date->format('m/d'),
                    $clockIn,
                    $clockOut,
                    $breakTotal,
                    $workTotal,
                ]);
                $date->addDay();
            }
            fclose($handle);
        });
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        return $response;
    }
}