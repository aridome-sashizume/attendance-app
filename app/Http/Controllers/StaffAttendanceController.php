<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StaffAttendanceController extends Controller
{
    public function index(User $user, Request $request)
    {
        // 表示対象月取得
        $date = Carbon::parse(
            $request->input('date', now()->format('Y-m'))
        );

        // 指定スタッフの勤怠一覧取得（Eager Loading）
        $attendanceRecords = AttendanceRecord::with('attendanceBreaks')
            ->where('user_id', $user->id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->orderBy('date')
            ->get();

        // Blade表示用データ作成
        $formattedAttendanceRecords = $attendanceRecords->map(function ($attendance) {

            // 休憩時間合計（分）
            $totalBreakMinutes = 0;

            foreach ($attendance->attendanceBreaks as $break) {

                if ($break->break_out) {
                    $totalBreakMinutes += Carbon::parse($break->break_in)
                        ->diffInMinutes(
                            Carbon::parse($break->break_out)
                        );
                }
            }

            // 出勤時刻
            $clockIn = '';

            if ($attendance->clock_in) {
                $clockIn = Carbon::parse(
                    $attendance->clock_in
                )->format('H:i');
            }

            // 退勤時刻
            $clockOut = '';

            if ($attendance->clock_out) {
                $clockOut = Carbon::parse(
                    $attendance->clock_out
                )->format('H:i');
            }

            // 休憩時間
            $totalBreakTime = null;

            if ($totalBreakMinutes > 0) {
                $totalBreakTime = Carbon::today()
                    ->addMinutes($totalBreakMinutes);
            }

            // 勤務時間
            $totalWorkTime = null;

            if ($attendance->clock_in && $attendance->clock_out) {

                $workedMinutes = Carbon::parse(
                    $attendance->clock_in
                )->diffInMinutes(
                    Carbon::parse($attendance->clock_out)
                );

                $totalWorkMinutes =
                    $workedMinutes - $totalBreakMinutes;

                $totalWorkTime = Carbon::today()
                    ->addMinutes($totalWorkMinutes);
            }

            return [
                'id' => $attendance->id,

                'date' => Carbon::parse(
                    $attendance->date
                )->format('m/d'),

                'clock_in' => $clockIn,

                'clock_out' => $clockOut,

                'total_break_time' => $totalBreakTime,

                'total_time' => $totalWorkTime,
            ];
        });

        return view('admin.staff-attendance-list', [
            'user' => $user,
            'formattedAttendanceRecords' => $formattedAttendanceRecords,
            'date' => $date,
            'previousMonth' => $date->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $date->copy()->addMonth()->format('Y-m'),
        ]);
    }
}
