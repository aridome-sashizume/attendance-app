<?php

namespace Database\Seeders;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use Illuminate\Database\Seeder;

class AttendanceBreakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /* 固定休憩 12:00-13:00（1 時間） を付与 */
        AttendanceRecord::all()->each(function (
            AttendanceRecord $attendanceRecord
        ) {
            AttendanceBreak::create([
                'attendance_record_id' => $attendanceRecord->id,
                'break_in' => '12:00:00',
                'break_out' => '13:00:00',
            ]);
        });
    }
}
