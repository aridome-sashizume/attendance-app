<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\Application;
use App\Models\ApplicationBreak;
use App\Models\AttendanceRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceDetailController extends Controller
{
    /**
     * 勤怠詳細画面表示
     *
     * ユーザーが選択した勤怠の詳細情報を取得し、
     * 勤怠詳細画面に表示する。
     *
     * @param  AttendanceRecord  $attendanceRecord  勤怠情報
     */
    public function show(
        AttendanceRecord $attendanceRecord
    ): View {
        $attendanceRecord->load('attendanceBreaks');

        $data = [
            'id' => $attendanceRecord->id,
            'application' => null,
            'year' => $attendanceRecord->date->format('Y年'),
            'date' => $attendanceRecord->date->format('n月j日'),
            'clock_in' => optional(
                $attendanceRecord->clock_in
            )->format('H:i'),
            'clock_out' => optional(
                $attendanceRecord->clock_out
            )->format('H:i'),
            'comment' => '',

            // 休憩情報をBlade表示用の形式へ変換
            'breaks' => $attendanceRecord->attendanceBreaks
                ->map(function ($break): array {
                    return [
                        'break_in' => optional(
                            $break->break_in
                        )->format('H:i'),
                        'break_out' => optional(
                            $break->break_out
                        )->format('H:i'),
                    ];
                })
                ->toArray(),
        ];

        return view(
            'user.user-detail',
            [
                'data' => $data,
                'user' => $attendanceRecord->user,
            ]
        );
    }

    /**
     * 勤怠修正申請登録
     *
     * ユーザーが入力した出退勤時刻・休憩時間・備考を
     * 勤怠修正申請として登録する。
     *
     * @param  AttendanceCorrectionRequest  $request  リクエスト情報
     * @param  AttendanceRecord  $attendanceRecord  勤怠情報
     */
    public function store(AttendanceCorrectionRequest $request, AttendanceRecord $attendanceRecord): RedirectResponse
    {
        $application = Application::create([
            'user_id' => auth()->id(),
            'attendance_record_id' => $attendanceRecord->id,
            'new_date' => $attendanceRecord->date,
            'new_clock_in' => $request->new_clock_in,
            'new_clock_out' => $request->new_clock_out,
            'comment' => $request->comment,
            'approval_status' => '承認待ち',
        ]);

        // 入力された休憩時間を申請用テーブルへ登録
        collect($request->new_break_in)
            ->each(function (
                $breakIn,
                int $index
            ) use (
                $request,
                $application
            ): void {
                $breakOut =
                    $request->new_break_out[$index] ?? null;

                // 開始・終了とも未入力の場合は登録しない
                if (! $breakIn && ! $breakOut) {
                    return;
                }

                ApplicationBreak::create([
                    'application_id' => $application->id,
                    'new_break_in' => $breakIn,
                    'new_break_out' => $breakOut,
                ]);
            });

        return redirect('/attendance/list');
    }
}
