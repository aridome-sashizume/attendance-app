<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ApplicationListController extends Controller
{
    /**
     * 申請一覧画面表示
     */
    public function index()
    {
        /**
         * ログインユーザーの申請情報取得
         */
        $applications = Application::where(
            'user_id',
            Auth::id()
        )
            ->latest()
            ->get();

        /**
         * 一覧表示用データ整形
         */
        $formattedApplications = $applications->map(
            function ($application) {

                return [
                    /**
                     * 申請ID
                     */
                    'id' => $application->id,

                    /**
                     * 承認ステータス
                     */
                    'approval_status'
                        => $application->approval_status,

                    /**
                     * 対象日
                     */
                    'date'
                        => Carbon::parse(
                            $application->new_date
                        )->format('Y/m/d'),

                    /**
                     * 修正理由
                     */
                    'comment'
                        => $application->comment,

                    /**
                     * 申請日
                     */
                    'application_date'
                        => $application->created_at
                            ->format('Y/m/d'),
                ];
            }
        );

        /**
         * 申請一覧画面表示
         */
        return view(
            'user.user-application-list',
            [
                'formattedApplications'
                    => $formattedApplications,

                'user'
                    => Auth::user(),
            ]
        );
    }
}