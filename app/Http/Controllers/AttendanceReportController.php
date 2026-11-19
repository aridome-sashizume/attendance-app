<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceReportController extends Controller
{
    /**
     * 勤怠レポート画面表示
     *
     * 勤怠レポート画面に必要なデータを表示する。
     */
    public function index(): View
    {
        // ログインユーザーの直近6ヶ月分の勤怠情報を取得
        $attendanceRecords = AttendanceRecord::query()
            ->where(
                'user_id',
                Auth::id()
            )
            ->where(
                'date',
                '>=',
                now()->subMonths(6)->startOfMonth()
            )
            ->with('attendanceBreaks')
            ->orderBy('date')
            ->get();

        // 日毎の労働時間（分）を算出
        $workMinutesByDay = $attendanceRecords->map(
            function (AttendanceRecord $attendanceRecord): int {
                if (
                    $attendanceRecord->clock_in === null ||
                    $attendanceRecord->clock_out === null
                ) {
                    return 0;
                }

                $workMinutes =
                    $attendanceRecord->clock_in
                        ->diffInMinutes(
                            $attendanceRecord->clock_out
                        );

                $breakMinutes =
                    $attendanceRecord->attendanceBreaks
                        ->sum(function ($break): int {

                            if (
                                $break->break_in === null ||
                                $break->break_out === null
                            ) {
                                return 0;
                            }

                            return $break->break_in
                                ->diffInMinutes(
                                    $break->break_out
                                );
                        });

                return $workMinutes - $breakMinutes;
            }
        );

        // 総労働時間
        $totalWorkMinutes = $workMinutesByDay->sum();

        // 総残業時間（8時間（480分）超過分）
        $totalOvertimeMinutes = $workMinutesByDay
            ->sum(
                fn (int $minutes): int => max(0, $minutes - 480)
            );

        // 平均労働時間
        $avgWorkMinutes = $workMinutesByDay->isNotEmpty()
            ? (int) round(
                $totalWorkMinutes /
                $workMinutesByDay->count()
            )
            : 0;

        // 月別グループ化
        $groupedAttendanceRecords = $attendanceRecords
            ->groupBy(
                fn (AttendanceRecord $attendanceRecord): string => $attendanceRecord->date->format('Y-m')
            );

        // 直近6ヶ月（当月から5か月前まで）の月一覧
        $months = collect(range(5, 0))
            ->map(
                fn (int $offset): string => now()
                    ->subMonths($offset)
                    ->format('Y-m')
            );

        // 6ヶ月分の月一覧をもとに、月ごとの集計データを作成
        $monthlyTrend = $months->map(
            function (
                string $month
            ) use (
                $groupedAttendanceRecords
            ): array {

                $records = $groupedAttendanceRecords->get(
                    $month,
                    collect()
                );

                $monthlyWorkMinutes = $records->sum(
                    function (
                        AttendanceRecord $attendanceRecord
                    ): int {

                        if (
                            $attendanceRecord->clock_in === null ||
                            $attendanceRecord->clock_out === null
                        ) {
                            return 0;
                        }

                        $workMinutes =
                            $attendanceRecord->clock_in
                                ->diffInMinutes(
                                    $attendanceRecord->clock_out
                                );

                        $breakMinutes =
                            $attendanceRecord->attendanceBreaks
                                ->sum(function ($break): int {

                                    if (
                                        $break->break_in === null ||
                                        $break->break_out === null
                                    ) {
                                        return 0;
                                    }

                                    return $break->break_in
                                        ->diffInMinutes(
                                            $break->break_out
                                        );
                                });

                        return $workMinutes - $breakMinutes;
                    }
                );

                $monthlyOvertimeMinutes = $records->sum(
                    function (
                        AttendanceRecord $attendanceRecord
                    ): int {

                        if (
                            $attendanceRecord->clock_in === null ||
                            $attendanceRecord->clock_out === null
                        ) {
                            return 0;
                        }

                        $workMinutes =
                            $attendanceRecord->clock_in
                                ->diffInMinutes(
                                    $attendanceRecord->clock_out
                                );

                        $breakMinutes =
                            $attendanceRecord->attendanceBreaks
                                ->sum(function ($break): int {

                                    if (
                                        $break->break_in === null ||
                                        $break->break_out === null
                                    ) {
                                        return 0;
                                    }

                                    return $break->break_in
                                        ->diffInMinutes(
                                            $break->break_out
                                        );
                                });

                        $actualWorkMinutes =
                            $workMinutes - $breakMinutes;

                        return max(
                            0,
                            $actualWorkMinutes - 480
                        );
                    }
                );

                return [
                    'month' => $month,
                    'work_minutes' => $monthlyWorkMinutes,
                    'overtime_minutes' => $monthlyOvertimeMinutes,
                ];
            }
        );

        return view('reports.index', [
            'summary' => [
                'total_work_minutes' => $totalWorkMinutes,
                'total_overtime_minutes' => $totalOvertimeMinutes,
                'avg_work_minutes' => $avgWorkMinutes,
            ],
            'monthlyTrend' => $monthlyTrend,
            'anomalies' => [
                'late_count' => 0,
                'early_leave_count' => 0,
                'long_work_count' => 0,
            ],
        ]);
    }
}
