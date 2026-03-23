<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BreakSeeder extends Seeder
{
    public function run()
    {
        $attendances = DB::table('attendances')->get();

        foreach ($attendances as $attendance) {
            $workDate = $attendance->work_date;

            // 休憩1
            DB::table('attendance_breaks')->insert([
                'attendance_id' => $attendance->id,
                'break_start' => $workDate . ' 12:00:00',
                'break_end' => $workDate . ' 13:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 休憩2
            DB::table('attendance_breaks')->insert([
                'attendance_id' => $attendance->id,
                'break_start' => $workDate . ' 15:00:00',
                'break_end' => $workDate . ' 15:30:00',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}