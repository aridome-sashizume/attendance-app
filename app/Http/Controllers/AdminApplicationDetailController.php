<?php

namespace App\Http\Controllers;
use App\Models\Application;     
use Carbon\Carbon;
use App\Models\AttendanceBreak;
class AdminApplicationDetailController extends Controller
{
    /**
     * 修正申請詳細表示
     */
    public function show(Application $application)
    {
        $application->load([
            'user',
            'attendanceRecord',
            'applicationBreaks',
        ]);

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


public function update(Application $application)
{
    $application->load([
        'attendanceRecord',
        'applicationBreaks',
    ]);

    // 勤怠情報更新
    $application->attendanceRecord->update([
        'date' => $application->new_date,
        'clock_in' => $application->new_clock_in,
        'clock_out' => $application->new_clock_out,
    ]);

    // 既存休憩削除
    AttendanceBreak::where(
        'attendance_record_id',
        $application->attendanceRecord->id
    )->delete();

    // 休憩情報反映
    foreach ($application->applicationBreaks as $break) {

        AttendanceBreak::create([
            'attendance_record_id'
                => $application->attendanceRecord->id,

            'break_in'
                => $break->new_break_in,

            'break_out'
                => $break->new_break_out,
        ]);
    }

    // 承認済みに更新
    $application->update([
        'approval_status' => '承認済み',
    ]);

    return redirect(
        '/stamp_correction_request/list'
    );
}

}
