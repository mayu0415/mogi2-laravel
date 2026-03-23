<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStampCorrectionRequestController;
use App\Http\Controllers\AdminStaffController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('/login');
});

Route::get('/admin/login', function () {
    return view('admin-login');
})->name('admin.login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');

    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.breakIn');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.breakOut');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    Route::post('/attendance/detail/{id}', [AttendanceController::class, 'updateRequest'])->name('attendance.detail.update');

    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'requestList'])->name('stamp_correction_request.list');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');

    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'detail'])->name('admin.attendance.detail');

    Route::post('/admin/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');

    Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])
    ->name('admin.staff.list');

    Route::get('/admin/attendance/staff/{id}', [AdminStaffController::class, 'show'])
    ->name('admin.attendance.staff');

    Route::get('/admin/attendance/staff/{id}/csv', [AdminStaffController::class, 'exportCsv'])
    ->name('admin.attendance.staff.csv');

    Route::get('/admin/stamp_correction_request/list', [AdminStampCorrectionRequestController::class, 'index'])->name('admin.stamp_correction_request.list');

    Route::get('/admin/stamp_correction_request/approve/{id}', [AdminStampCorrectionRequestController::class, 'approve'])->name('admin.stamp_correction_request.approve');

    Route::post('/admin/stamp_correction_request/approve/{id}', [AdminStampCorrectionRequestController::class, 'approveUpdate'])
    ->name('admin.stamp_correction_request.approve.update');
});