<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Carbon\Carbon;
use Illuminate\View\View;

class ApplicationDetailController extends Controller
{
    /**
     * 申請詳細画面表示
     *
     * 承認待ちの場合は申請内容を表示する。
     * 承認済みの場合は最新の勤怠情報を表示し、
     * 再申請フォームとして利用できるようにする。
     *
     * @param  Application  $application  修正申請情報
     */
    public function show(
        Application $application
    ): View {

        // 承認済みの場合は最新の勤怠情報を表示し、再申請を可能にする
        if ($application->approval_status === '承認済み') {

            $application->load(
                'attendanceRecord.attendanceBreaks',
                'user'
            );

            $attendanceRecord = $application->attendanceRecord;

            $data = [
                'id' => $attendanceRecord->id,

                // 修正フォームを表示するため application は null を渡す
                'application' => null,

                'year' => $attendanceRecord->date->format('Y年'),

                'date' => $attendanceRecord->date->format('n月j日'),

                'clock_in' => optional(
                    $attendanceRecord->clock_in
                )?->format('H:i'),

                'clock_out' => optional(
                    $attendanceRecord->clock_out
                )?->format('H:i'),

                // 承認後の備考欄は新規申請用のため空欄にする
                'comment' => '',

                'breaks' => $attendanceRecord
                    ->attendanceBreaks
                    ->map(function ($break): array {
                        return [
                            'break_in' => optional(
                                $break->break_in
                            )?->format('H:i'),

                            'break_out' => optional(
                                $break->break_out
                            )?->format('H:i'),
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

        // 承認待ちの場合は申請内容を表示する
        $application->load(
            'applicationBreaks',
            'user'
        );

        $data = [
            'id' => $application->id,

            'application' => $application,

            'year' => Carbon::parse(
                $application->new_date
            )->format('Y年'),

            'date' => Carbon::parse(
                $application->new_date
            )->format('n月j日'),

            'clock_in' => $application->new_clock_in
                ? Carbon::parse(
                    $application->new_clock_in
                )->format('H:i')
                : null,

            'clock_out' => $application->new_clock_out
                ? Carbon::parse(
                    $application->new_clock_out
                )->format('H:i')
                : null,

            // 申請時点の備考を表示する
            'comment' => $application->comment,

            'breaks' => $application
                ->applicationBreaks
                ->map(function ($break): array {
                    return [
                        'break_in' => $break->new_break_in
                            ? Carbon::parse(
                                $break->new_break_in
                            )->format('H:i')
                            : null,

                        'break_out' => $break->new_break_out
                            ? Carbon::parse(
                                $break->new_break_out
                            )->format('H:i')
                            : null,
                    ];
                })
                ->toArray(),
        ];

        return view(
            'user.user-detail',
            [
                'data' => $data,
                'user' => $application->user,
            ]
        );
    }
}
