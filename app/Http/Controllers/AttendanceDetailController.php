<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Http\Requests\AttendanceCorrectionRequest;

use App\Models\Application;
use App\Models\ApplicationBreak;

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


    public function store(AttendanceCorrectionRequest $request, AttendanceRecord $attendanceRecord)
    {
        {
            $application = Application::create([
                'user_id' => auth()->id(),
                'attendance_record_id' => $attendanceRecord->id,
                'new_date' => $attendanceRecord->date,
                'new_clock_in' => $request->new_clock_in,
                'new_clock_out' => $request->new_clock_out,
                'comment' => $request->comment,
                'approval_status' => '承認待ち',
            ]);

            foreach ($request->new_break_in as $index => $breakIn) {

                $breakOut = $request->new_break_out[$index] ?? null;

                // 両方空欄なら保存しない
                if (!$breakIn && !$breakOut) {
                    continue;
                }

                ApplicationBreak::create([
                    'application_id' => $application->id,
                    'new_break_in' => $breakIn,
                    'new_break_out' => $breakOut,
                ]);
            }

            return redirect('/attendance/list');
        }
    }
}
