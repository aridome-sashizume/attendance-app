<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use Illuminate\View\View;

class AdminAttendanceDetailController extends Controller
{
    /**
     * 勤怠詳細画面表示
     *
     * 管理者が選択した勤怠の詳細情報を取得し、
     * 勤怠詳細画面に表示する。
     *
     * @param  AttendanceRecord  $attendanceRecord  勤怠情報
     * @return View
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
            'admin.admin-detail',
            [
                'attendanceRecord' => $data,
                'user' => $attendanceRecord->user,
            ]
        );
    }


    /**
     * 管理者による勤怠詳細更新処理
     *
     * 管理者が勤怠詳細画面で承認ステータスを更新する際に呼び出される。
     *
     * @param  Request  $request  リクエスト情報
     * @param  int  $attendanceRecordId  勤怠ID
     * @return RedirectResponse
     */
    public function update(Request $request, int $attendanceRecordId): RedirectResponse
    {
        // 勤怠レコードを取得
        $attendanceRecord = AttendanceRecord::findOrFail($attendanceRecordId);

        // 勤怠レコードの承認ステータスを更新
        $attendanceRecord->approval_status = $request->input('approval_status');
        $attendanceRecord->save();

        return redirect()->back()->with('success', '勤怠の承認ステータスが更新されました。');
    }
}
