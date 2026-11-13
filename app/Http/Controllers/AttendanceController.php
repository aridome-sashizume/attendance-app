<?php

namespace App\Http\Controllers;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        // 本日の勤怠レコードを取得
        $attendance = AttendanceRecord::where('user_id', Auth::id())
            ->whereDate('date', today())
            ->first();

        // 本日の勤怠レコードに紐づく最新の休憩レコードを取得
        $latestBreak = $attendance?->attendanceBreaks()
            ->latest()
            ->first();

        // 勤怠レコードの状態から画面表示用ステータスを判定
        if (! $attendance) {
            $attendanceStatus = '勤務外';
        } elseif ($attendance->clock_out) {
            $attendanceStatus = '退勤済';
        } elseif (
            $latestBreak &&
            $latestBreak->break_in &&
            ! $latestBreak->break_out
        ) {
            $attendanceStatus = '休憩中';
        } else {
            $attendanceStatus = '出勤中';
        }

        /**
         * attendance-register.blade.php では
         * $user->attendance_status を参照しているため、
         * 画面表示用のステータスを動的に付与する。
         */
        $user = auth()->user();
        $user->attendance_status = $attendanceStatus;

        return view('user.attendance-register', [
            'user' => auth()->user(),
            'attendanceStatus' => $attendanceStatus,
            'formattedDate' => now()->format('Y年n月j日(D)'),
            'formattedTime' => now()->format('H:i'),
        ]);
    }

    /**
     * 出勤打刻処理
     */
    public function store(Request $request)
    {
        // 同日に複数の出勤レコードが作成されないよう、
        // ログインユーザーの当日の勤怠レコード有無を確認する
        $attendance = AttendanceRecord::where('user_id', Auth::id())
            ->whereDate('date', today())
            ->first();

        // 出勤
        if ($request->action === 'clock_in') {

            if ($attendance) {
                return redirect('/attendance');
            }

            AttendanceRecord::create([
                'user_id' => Auth::id(),
                'date' => now()->toDateString(),
                'clock_in' => now(),
            ]);

            return redirect('/attendance');
        }

        // 休憩入
        if ($request->action === 'break_in') {

            AttendanceBreak::create([
                'attendance_record_id' => $attendance->id,
                'break_in' => now(),
            ]);

            return redirect('/attendance');
        }

        // 休憩戻
        if ($request->action === 'break_out') {

            $break = AttendanceBreak::where(
                'attendance_record_id',
                $attendance->id
            )
                ->whereNull('break_out')
                ->latest()
                ->first();

            $break->update([
                'break_out' => now(),
            ]);

            return redirect('/attendance');
        }

        // 退勤
        if ($request->action === 'clock_out') {

            $attendance->update([
                'clock_out' => now(),
            ]);

            return redirect('/attendance');
        }

        return redirect('/attendance');
    }
}
