<?php

namespace App\Http\Controllers;
use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        // 本日の勤怠レコードを取得
        $attendance = AttendanceRecord::where('user_id', Auth::id())
            ->whereDate('date', today())
            ->first();

        //勤怠レコードの状態から画面表示用ステータスを判定
        if (! $attendance) {
            $attendanceStatus = '勤務外';
        } elseif ($attendance->clock_out) {
            $attendanceStatus = '退勤済';
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
    public function store()
    {
        // 同日に複数の出勤レコードが作成されないよう、
        // ログインユーザーの当日の勤怠レコード有無を確認する
        $attendance = AttendanceRecord::where('user_id', Auth::id())
            ->whereDate('date', today())
            ->first();

        // 既に出勤済みの場合は処理を行わず打刻画面へ戻す
        if ($attendance) {
            return redirect('/attendance');
        }

        // 出勤時刻を保存し、その日の勤怠レコードを作成する
        AttendanceRecord::create([
            'user_id' => Auth::id(),
            'date' => now()->toDateString(),
            'clock_in' => now(),
        ]);

        // 出勤打刻後に勤怠画面へリダイレクトする
        return redirect('/attendance');
    }
}
