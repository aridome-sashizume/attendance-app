<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminApplicationDetailController extends Controller
{
    /**
     * 修正申請詳細表示
     *
     * 修正申請時に保存された出退勤時刻および休憩時間を取得し、
     * 修正申請詳細画面へ表示する。
     *
     * @param  int  $attendance_correct_request_id  修正申請ID
     */
    public function show(int $attendance_correct_request_id): View
    {
        $application = Application::with([
            'user',
            'attendanceRecord',
            'applicationBreaks',
        ])->findOrFail(
            $attendance_correct_request_id
        );

        /**
         * application_breaks の修正後休憩時間をBladeで利用している break_in / break_out 形式へ変換
         */
        $application->proposalBreaks =
            $application->applicationBreaks->map(function ($break) {

                $break->break_in = $break->new_break_in;
                $break->break_out = $break->new_break_out;

                return $break;
            });

        $user = $application->user;

        // 申請の新しい出勤時刻と退勤時刻をフォーマット
        $application->new_clock_in =
            Carbon::parse(
                $application->new_clock_in
            )->format('H:i');

        $application->new_clock_out =
        Carbon::parse(
            $application->new_clock_out
        )->format('H:i');

        return view(
            'admin.admin-application-detail',
            compact(
                'application',
                'user',
            )
        );
    }

    /**
     * 修正申請承認処理
     *
     * 承認された修正申請の内容を勤怠情報へ反映し、申請状態を承認済みに更新する。
     *
     * @param  int  $attendance_correct_request_id  修正申請ID
     */
    public function update(int $attendance_correct_request_id): RedirectResponse
    {
        $application = Application::with([
            'attendanceRecord',
            'applicationBreaks',
        ])->findOrFail(
            $attendance_correct_request_id
        );

        // 勤怠情報更新
        $application->attendanceRecord->update([
            'date' => $application->new_date,
            'clock_in' => $application->new_clock_in,
            'clock_out' => $application->new_clock_out,
        ]);

        /**
         * 修正申請の内容で休憩情報を再登録するため、既存の休憩レコードを一度削除する
         */
        AttendanceBreak::where(
            'attendance_record_id',
            $application->attendanceRecord->id
        )->delete();

        // 休憩情報を反映
        $application->applicationBreaks
            ->each(function ($break) use ($application): void {
                AttendanceBreak::create([
                    'attendance_record_id' => $application->attendanceRecord->id,
                    'break_in' => $break->new_break_in,
                    'break_out' => $break->new_break_out,
                ]);
            });

        // 承認済みに更新
        $application->update([
            'approval_status' => '承認済み',
        ]);

        return redirect(
            '/stamp_correction_request/list'
        );
    }
}
