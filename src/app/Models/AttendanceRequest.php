<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    protected $fillable = [
        'attendance_id',
        'requested_clock_in',
        'requested_clock_out',
        'requested_breaks',
        'note',
        'status',
    ];
    protected $casts = [
        'requested_clock_in' => 'datetime',
        'requested_clock_out' => 'datetime',
        'requested_breaks' => 'array',
    ];
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
