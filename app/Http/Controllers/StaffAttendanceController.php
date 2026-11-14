<?php

namespace App\Http\Controllers;

use App\Models\User;

class StaffAttendanceController extends Controller
{
    /**
     * スタッフ勤怠一覧画面表示
     */
    public function index(User $user)
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
