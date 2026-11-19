<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceRecord;
use Illuminate\View\View;



class AttendanceReportController extends Controller
{
    /**
     * 勤怠レポート画面表示
     *
     * 勤怠レポート画面に必要なデータを表示する。
     * 
     * @return View
     */
    public function index(): View
    {
        $attendanceRecords = AttendanceRecord::query()
            ->where(
                'user_id',
                Auth::id()
            )
            ->where(
                'date',
                '>=',
                now()->subMonths(6)->startOfMonth()
            )
            ->with('attendanceBreaks')
            ->orderBy('date')
            ->get();

        return view('reports.index', [
            'attendanceRecords' => $attendanceRecords,
            'summary' => [
                'total_work_minutes' => 0,
                'total_overtime_minutes' => 0,
                'avg_work_minutes' => 0,
            ],
            'monthlyTrend' => [],
            'anomalies' => [
                'late_count' => 0,
                'early_leave_count' => 0,
                'long_work_count' => 0,
            ],
        ]);
    }
}
