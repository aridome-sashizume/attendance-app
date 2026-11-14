<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

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
}
