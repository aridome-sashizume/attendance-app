<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceReportController extends Controller
{
    /**
     * 勤怠レポート画面表示
     *
     * 勤怠レポート画面に必要なデータを表示する。
     */
    public function index()
    {
        return view('reports.index', [
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
