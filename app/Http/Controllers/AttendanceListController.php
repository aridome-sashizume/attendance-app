<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceListController extends Controller
{
    /**
     * 勤怠一覧画面表示
     *
     * ログインユーザーの対象月の勤怠情報を取得し、
     * 勤怠一覧画面に表示する。
     *
     * @param  Request  $request  リクエスト情報
     */
    public function index(Request $request): View
    {
        // 表示対象月を取得
        $date = Carbon::parse(
            $request->input('date', now()->format('Y-m'))
        );

        // ログインユーザーの対象月の勤怠情報を取得
        $attendanceRecords = AttendanceRecord::with(
            'attendanceBreaks'
        )
            ->where('user_id', Auth::id())
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->orderBy('date')
            ->get();

        // Blade表示用データへ変換
        $formattedAttendanceRecords = $attendanceRecords->map(
            function (
                AttendanceRecord $attendanceRecord
            ): array {

                // 休憩終了済みの休憩時間を集計
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

                // 出勤時刻を表示用形式へ変換
                $clockIn = '';

                if ($attendanceRecord->clock_in) {
                    $clockIn = Carbon::parse(
                        $attendanceRecord->clock_in
                    )->format('H:i');
                }

                // 退勤時刻を表示用形式へ変換
                $clockOut = '';

                if ($attendanceRecord->clock_out) {
                    $clockOut = Carbon::parse(
                        $attendanceRecord->clock_out
                    )->format('H:i');
                }

                // 合計休憩時間を算出
                $totalBreakTime = null;

                if ($totalBreakMinutes > 0) {
                    $totalBreakTime = Carbon::today()
                        ->addMinutes($totalBreakMinutes);
                }

                // 実働時間を算出
                $totalWorkTime = null;

                if (
                    $attendanceRecord->clock_in &&
                    $attendanceRecord->clock_out
                ) {
                    $workedMinutes = Carbon::parse(
                        $attendanceRecord->clock_in
                    )->diffInMinutes(
                        Carbon::parse(
                            $attendanceRecord->clock_out
                        )
                    );

                    $totalWorkMinutes =
                        $workedMinutes - $totalBreakMinutes;

                    $totalWorkTime = Carbon::today()
                        ->addMinutes($totalWorkMinutes);
                }

                return [
                    'id' => $attendanceRecord->id,

                    // 日付を表示用形式へ変換
                    'date' => Carbon::parse(
                        $attendanceRecord->date
                    )->format('m/d'),

                    'clock_in' => $clockIn,

                    'clock_out' => $clockOut,

                    'total_break_time' => $totalBreakTime,

                    'total_time' => $totalWorkTime,
                ];
            }
        );

        return view(
            'user.user-attendance-list',
            [
                'formattedAttendanceRecords' => $formattedAttendanceRecords,

                'date' => $date,

                'previousMonth' => $date
                    ->copy()
                    ->subMonth()
                    ->format('Y-m'),

                'nextMonth' => $date
                    ->copy()
                    ->addMonth()
                    ->format('Y-m'),
            ]
        );
    }
}
