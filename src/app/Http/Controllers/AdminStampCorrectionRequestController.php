<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminStampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $requests = AttendanceRequest::with(['attendance.user'])
            ->where('status', $status)
            ->whereHas('attendance.user', function ($query) {
                $query->where('role', 0);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin-stamp-correction-request-list', compact('requests', 'status'));
    }
    public function approve($id)
    {
        $attendanceRequest = AttendanceRequest::with(['attendance.user', 'attendance.breaks'])
            ->findOrFail($id);

        $attendance = $attendanceRequest->attendance;
        $requestedBreaks = $attendanceRequest->requested_breaks ?? [];
        $isApproved = $attendanceRequest->status === 'approved';
        
        return view('admin-stamp-correction-request-approve', compact(
            'attendanceRequest',
            'attendance',
            'requestedBreaks',
            'isApproved'
        ));
    }

    public function approveUpdate($id)
    {
        $attendanceRequest = AttendanceRequest::with(['attendance.breaks'])
            ->findOrFail($id);

        if ($attendanceRequest->status === 'approved') {
            return back();
        }

        $attendance = $attendanceRequest->attendance;

        $attendance->update([
            'clock_in' => $attendanceRequest->requested_clock_in,
            'clock_out' => $attendanceRequest->requested_clock_out,
        ]);

        $attendance->breaks()->delete();

        $requestedBreaks = $attendanceRequest->requested_breaks ?? [];

        foreach ($requestedBreaks as $break) {
            $attendance->breaks()->create([
                'break_start' => $break['break_start'] ?? null,
                'break_end' => $break['break_end'] ?? null,
            ]);
        }


        $attendanceRequest->status = 'approved';
        $attendanceRequest->save();

        return redirect()
            ->route('admin.stamp_correction_request.approve', ['id' => $attendanceRequest->id])
            ->with('success', '承認しました。');
    }
}