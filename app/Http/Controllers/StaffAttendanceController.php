<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffAttendanceController extends Controller
{
    /**
     * スタッフ勤怠一覧画面表示
     *
     * 管理者が選択したスタッフの勤怠一覧を取得し、
     * スタッフ勤怠一覧画面に表示する。
     *
     * @param  User  $user  選択スタッフのユーザー情報
     * @param  Request  $request  リクエスト情報
     */
    public function index(User $user, Request $request): View
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
            $totalBreakMinutes = $attendance->attendanceBreaks
                ->sum(function ($break): int {
                    if (! $break->break_in || ! $break->break_out) {
                        return 0;
                    }

                    return Carbon::parse($break->break_in)
                        ->diffInMinutes(
                            Carbon::parse($break->break_out)
                        );
                });

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

    /**
     * スタッフ勤怠情報CSV出力
     *
     * 指定スタッフの対象月の勤怠情報を
     * CSVファイルとして出力する。
     *
     * @param  Request  $request  リクエスト情報
     */
    public function exportCsv(
        Request $request
    ): StreamedResponse {

        // 指定スタッフのユーザー情報取得
        $user = User::findOrFail($request->user_id);

        // 対象月取得
        $date = Carbon::parse(
            $request->input(
                'year_month',
                now()->format('Y-m')
            )
        );

        // 指定スタッフの対象月の勤怠情報取得
        $attendanceRecords = AttendanceRecord::with('attendanceBreaks')
            ->where('user_id', $user->id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->orderBy('date')
            ->get();

        $csvData = [];

        // CSVヘッダー行作成
        $csvData[] = [
            '日付',
            '出勤',
            '退勤',
            '休憩',
            '合計',
        ];

        // CSVデータ行作成
        $attendanceRecords->each(
            function ($attendance) use (&$csvData): void {

                // 休憩時間合計（分）
                $totalBreakMinutes = $attendance->attendanceBreaks
                    ->sum(function ($break): int {
                        if (
                            ! $break->break_in ||
                            ! $break->break_out
                        ) {
                            return 0;
                        }

                        return Carbon::parse(
                            $break->break_in
                        )->diffInMinutes(
                            Carbon::parse(
                                $break->break_out
                            )
                        );
                    });

                // 休憩時間を H:i 形式へ変換
                $breakTime = sprintf(
                    '%d:%02d',
                    intdiv($totalBreakMinutes, 60),
                    $totalBreakMinutes % 60
                );

                $totalTime = '';

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

                    $totalWorkMinutes =
                        $workedMinutes - $totalBreakMinutes;

                    // 勤務時間を H:i 形式へ変換
                    $totalTime = sprintf(
                        '%d:%02d',
                        intdiv($totalWorkMinutes, 60),
                        $totalWorkMinutes % 60
                    );
                }

                $csvData[] = [
                    Carbon::parse(
                        $attendance->date
                    )->format('m/d'),

                    optional(
                        $attendance->clock_in
                    )?->format('H:i'),

                    optional(
                        $attendance->clock_out
                    )?->format('H:i'),

                    $breakTime,

                    $totalTime,
                ];
            }
        );

        // CSVファイル名作成
        $fileName =
            'attendance_'.
            $user->name.
            '_'.
            $date->format('Y-m').
            '.csv';

        // CSVデータをストリームとして返却
        return response()->stream(
            function () use ($csvData): void {
                $file = fopen('php://output', 'w');

                foreach ($csvData as $row) {
                    fputcsv(
                        $file,
                        array_map(
                            fn ($value) => mb_convert_encoding(
                                (string) $value,
                                'SJIS-win',
                                'UTF-8'
                            ),
                            $row
                        )
                    );
                }

                fclose($file);
            },
            200,
            [
                'Content-Type' => 'text/csv; charset=Shift_JIS',
                'Content-Disposition' => "attachment; filename={$fileName}",
            ]
        );
    }
}
