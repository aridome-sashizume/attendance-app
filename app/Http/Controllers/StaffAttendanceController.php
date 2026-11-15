<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffAttendanceController extends Controller
{
    /**
     * スタッフ勤怠一覧画面表示
     *
     * 管理者が選択したスタッフの勤怠情報を取得し、
     * スタッフ勤怠一覧画面に表示する。
     *
     * @param  User  $user  スタッフ情報
     */
    public function index(User $user): View
    {
        return view('admin.staff-attendance-list', [
            'user' => $user,
            'date' => now(),
            'previousMonth' => now()->copy()->subMonth()->format('Y-m'),
            'nextMonth' => now()->copy()->addMonth()->format('Y-m'),
            'formattedAttendanceRecords' => [],
        ]);
    }

    /**
     * スタッフ勤怠情報をCSVで出力
     * 
     * 管理者が選択したスタッフの勤怠情報を取得し、
     * CSVに出力する。
     * 
     * @param User $user
     * @return StreamedResponse
     */
    public function exportCsv(User $user): StreamedResponse
    {
        $fileName = 'attendance_records_' .
            $user->id .
            '_' .
            now()->format('Ymd_His') .
            '.csv';

        return response()->streamDownload(
            function () use ($user): void {
                $handle = fopen('php://output', 'w');

                fputcsv($handle, [
                    '日付',
                    '出勤時刻',
                    '退勤時刻',
                ]);

                $user->attendanceRecords
                    ->each(function ($record) use ($handle): void {
                        fputcsv($handle, [
                            optional($record->date)
                                ?->format('Y-m-d'),

                            optional($record->clock_in)
                                ?->format('H:i'),

                            optional($record->clock_out)
                                ?->format('H:i'),
                        ]);
                    });

                fclose($handle);
            },
            $fileName,
            [
                'Content-Type' => 'text/csv',
            ]
        );
    }
}
