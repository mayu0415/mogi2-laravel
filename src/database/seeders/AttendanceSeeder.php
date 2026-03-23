<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $users = DB::table('users')
            ->whereIn('email', ['taro@example.com', 'hanako@example.com'])
            ->get();

        foreach ($users as $user) {
            for ($i = 1; $i <= 3; $i++) {
                $workDate = Carbon::now()->subDays($i)->format('Y-m-d');
                DB::table('attendances')->insert([
                    'user_id' => $user->id,
                    'work_date' => $workDate,
                    'clock_in' => $workDate . ' 09:00:00',
                    'clock_out' => $workDate . ' 18:00:00',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}