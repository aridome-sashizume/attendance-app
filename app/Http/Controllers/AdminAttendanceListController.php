<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAttendanceListController extends Controller
{
    /**
     * 日次勤怠一覧表示
     *
     * 管理者が日次勤怠一覧を表示する。
     *
     * @param  Request  $request  リクエスト情報
     */
    public function index(Request $request): View
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
            ->get()

            // 一覧表示用の休憩時間・勤務時間を算出
            ->map(function (
                AttendanceRecord $attendanceRecord
            ): AttendanceRecord {

                // 休憩終了時刻が登録されている休憩のみを対象として
                // 休憩時間（分）を集計する
                $totalBreakMinutes = $attendanceRecord->attendanceBreaks
                    ->filter(function ($break): bool {
                        return ! is_null($break->break_out);
                    })
                    ->sum(function ($break): int {
                        return Carbon::parse(
                            $break->break_in
                        )->diffInMinutes(
                            Carbon::parse(
                                $break->break_out
                            )
                        );
                    });

                // 一覧表示用の合計休憩時間を設定
                $attendanceRecord->total_break_time = null;

                if ($totalBreakMinutes > 0) {
                    $attendanceRecord->total_break_time =
                        Carbon::today()
                            ->addMinutes(
                                $totalBreakMinutes
                            );
                }

                // 一覧表示用の実働時間を設定
                $attendanceRecord->total_time = null;

                if (
                    $attendanceRecord->clock_in &&
                    $attendanceRecord->clock_out
                ) {
                    // 出勤から退勤までの勤務時間を算出
                    $workedMinutes = Carbon::parse(
                        $attendanceRecord->clock_in
                    )->diffInMinutes(
                        Carbon::parse(
                            $attendanceRecord->clock_out
                        )
                    );

                    // 勤務時間から休憩時間を差し引き実働時間を算出
                    $attendanceRecord->total_time =
                        Carbon::today()
                            ->addMinutes(
                                $workedMinutes - $totalBreakMinutes
                            );
                }

                return $attendanceRecord;
            });

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
