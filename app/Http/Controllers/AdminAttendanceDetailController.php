<?php

namespace App\Http\Controllers;
use App\Models\AttendanceRecord;    
use Illuminate\Http\Request;

class AdminAttendanceDetailController extends Controller
{
    // 
    public function update(Request $request, $attendanceRecordId)
    {
        // 勤怠レコードを取得
        $attendanceRecord = AttendanceRecord::findOrFail($attendanceRecordId);

        // 勤怠レコードの承認ステータスを更新
        $attendanceRecord->approval_status = $request->input('approval_status');
        $attendanceRecord->save();

        return redirect()->back()->with('success', '勤怠の承認ステータスが更新されました。');
    }
}
