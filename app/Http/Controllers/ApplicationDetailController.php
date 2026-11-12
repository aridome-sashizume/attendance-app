<?php

namespace App\Http\Controllers;

use App\Models\Application;

class ApplicationDetailController extends Controller
{
    /**
     * 申請詳細画面表示
     */
    public function show(Application $application)
    {
        $application->load('applicationBreaks');

        return view(
            'user.application-detail',
            [
                'application' => $application,
                'user' => auth()->user(),
            ]
        );
    }
}