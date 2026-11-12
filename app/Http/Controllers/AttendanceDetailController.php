<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Illuminate\Http\Request;

class AttendanceDetailController extends Controller
{
    public function show(AttendanceRecord $attendanceRecord)
    {
        $attendanceRecord->load('attendanceBreaks');

        $data = [
            'id' => $attendanceRecord->id,
            'application' => null,
            'year' => $attendanceRecord->date->format('Y年'),
            'date' => $attendanceRecord->date->format('n月j日'),
            'clock_in' => optional($attendanceRecord->clock_in)->format('H:i'),
            'clock_out' => optional($attendanceRecord->clock_out)->format('H:i'),
            'comment' => '',
            'breaks' => $attendanceRecord->attendanceBreaks->map(function ($break) {
                return [
                    'break_in' => optional($break->break_in)->format('H:i'),
                    'break_out' => optional($break->break_out)->format('H:i'),
                ];
            })->toArray(),
        ];

        return view('user.user-detail', [
            'data' => $data,
            'user' => auth()->user(),
        ]);
    }

}
