<?php

namespace App\Http\Controllers;
use App\Models\Application;     
use Carbon\Carbon;
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

        $application->proposalBreaks = $application->applicationBreaks;

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
}
