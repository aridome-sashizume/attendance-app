<?php

namespace App\Http\Controllers;
use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Http\Request;

class AdminAttendanceListController extends Controller
{
    //
    public function index(Request $request)
    {
        /**
         * 表示日付取得
         */
        if ($request->filled('date')) {
            $date = Carbon::parse($request->date);
        } else {
            $date = Carbon::today();
        }

        /**
         * 前日
         */
        $previousDay = $date
            ->copy()
            ->subDay()
            ->format('Y-m-d');

        /**
         * 翌日
         */
        $nextDay = $date
            ->copy()
            ->addDay()
            ->format('Y-m-d');

        /**
         * 全ユーザー取得
         */
        $users = User::all();

        /**
         * 指定日の勤怠取得
         */
        $attendanceRecords = AttendanceRecord::with('user', 'attendanceBreaks')
            ->whereDate('date', $date)
            ->get();

/**
 * 休憩時間・勤務時間計算
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

    /**
     * 休憩時間
     */
            $attendance->total_break_time = null;

            if ($totalBreakMinutes > 0) {

                $attendance->total_break_time = Carbon::today()
                    ->addMinutes($totalBreakMinutes);
            }

            /**
             * 勤務時間
             */
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

                $attendance->total_time = Carbon::today()
                    ->addMinutes(
                        $workedMinutes - $totalBreakMinutes
                    );
            }
        }

        
        /**
         * 日次勤怠一覧画面表示
         */
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
