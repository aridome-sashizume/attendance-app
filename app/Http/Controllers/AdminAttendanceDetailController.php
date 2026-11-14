<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminAttendanceDetailController extends Controller
{
    /**
     * 管理者による勤怠詳細更新処理
     *
     * 管理者が勤怠詳細画面で承認ステータスを更新する際に呼び出される。
     *
     * @param  Request  $request  リクエスト情報
     * @param  int  $attendanceRecordId  勤怠ID
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
