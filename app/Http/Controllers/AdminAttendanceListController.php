<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminAttendanceListController extends Controller
{
    /**
     * 日次勤怠一覧表示
     */
    public function index(Request $request)
    {
        if ($request->filled('date')) {
            $date = Carbon::parse($request->date);
        } else {
            $date = Carbon::today();
        }

        $previousDay = $date
            ->copy()
            ->subDay()
            ->format('Y-m-d');

        $nextDay = $date
            ->copy()
            ->addDay()
            ->format('Y-m-d');

        $users = User::all();

        $attendanceRecords = AttendanceRecord::with(
            'user',
            'attendanceBreaks'
        )
            ->whereDate('date', $date)
            ->get();

        /**
         * 一覧表示用の休憩時間・勤務時間を算出
         */
        foreach ($attendanceRecords as $attendance) {

            $totalBreakMinutes = 0;

            foreach ($attendance->attendanceBreaks as $break) {

                if ($break->break_out) {

                    $totalBreakMinutes += Carbon::parse(
                        $break->break_in
                    )->diffInMinutes(
                        Carbon::parse($break->break_out)
                    );
                }
            }

            $attendance->total_break_time = null;

            if ($totalBreakMinutes > 0) {
                $attendance->total_break_time =
                    Carbon::today()
                        ->addMinutes($totalBreakMinutes);
            }

            $attendance->total_time = null;

            if (
                $attendance->clock_in &&
                $attendance->clock_out
            ) {

                $workedMinutes = Carbon::parse(
                    $attendance->clock_in
                )->diffInMinutes(
                    Carbon::parse(
                        $attendance->clock_out
                    )
                );

                $attendance->total_time =
                    Carbon::today()
                        ->addMinutes(
                            $workedMinutes - $totalBreakMinutes
                        );
            }
        }

        return view(
            'admin.admin-attendance-list',
            [
                'date' => $date,
                'previousDay' => $previousDay,
                'nextDay' => $nextDay,
                'users' => $users,
                'attendanceRecords' => $attendanceRecords,
            ]
        );
    }
}
