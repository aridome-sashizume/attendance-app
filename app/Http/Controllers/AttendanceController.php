<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('user.attendance-register',
            [
                'user' => auth()->user(),
                'formattedDate' => now()->format('Y年n月j日(D)'),
                'formattedTime' => now()->format('H:i'),
            ]
        );
    }
}
