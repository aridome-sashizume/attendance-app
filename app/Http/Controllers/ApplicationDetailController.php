<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\View\View;

class ApplicationDetailController extends Controller
{
    /**
     * 申請詳細画面表示
     *
     * ユーザーが選択した修正申請の詳細情報を取得し、
     * 申請詳細画面に表示する。
     *
     * @param  Application  $application  申請情報
     */
    public function show(Application $application): View
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
