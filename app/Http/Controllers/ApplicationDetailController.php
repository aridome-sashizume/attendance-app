<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\View\View;

use Carbon\Carbon;

class ApplicationDetailController extends Controller
{
    public function show(
        Application $application
    ): View {

        $application->load(
            'applicationBreaks',
            'user'
        );

        $data = [

            'id' => $application->id,

            'application' => $application,

            'year' => Carbon::parse(
                $application->new_date
            )->format('Y年'),

            'date' => Carbon::parse(
                $application->new_date
            )->format('n月j日'),

            'clock_in' => $application->new_clock_in
                ? Carbon::parse(
                    $application->new_clock_in
                )->format('H:i')
                : null,

            'clock_out' => $application->new_clock_out
                ? Carbon::parse(
                    $application->new_clock_out
                )->format('H:i')
                : null,

            'comment' => $application->comment,

            'breaks' => $application
                ->applicationBreaks
                ->map(function ($break): array {
                    return [
                        'break_in' => $break->new_break_in
                            ? Carbon::parse(
                                $break->new_break_in
                            )->format('H:i')
                            : null,

                        'break_out' => $break->new_break_out
                            ? Carbon::parse(
                                $break->new_break_out
                            )->format('H:i')
                            : null,
                    ];
                })
                ->toArray(),
        ];

        return view(
            'user.user-detail',
            [
                'data' => $data,
                'user' => $application->user,
            ]
        );
    }
}