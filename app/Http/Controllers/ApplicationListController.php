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
        // 管理者ユーザー
        if (Auth::user()->admin_status) {

            $applications = Application::with(
                    'user',
                    'attendanceRecord',
                )
                ->latest()
                ->get();


            return view(
                'admin.admin-application-list',
                [
                    'applications' => $applications,
                ]
            );
        }

        // 一般ユーザー
        $applications = Application::where(
            'user_id',
            Auth::id()
        )
            ->latest()
            ->get();

        $formattedApplications = $applications->map(
            function ($application) {

                return [
                    'id' => $application->id,
                    'approval_status' =>
                        $application->approval_status,
                    'date' => Carbon::parse(
                        $application->new_date
                    )->format('Y/m/d'),
                    'comment' => $application->comment,
                    'application_date' =>
                        $application->created_at
                            ->format('Y/m/d'),
                ];
            }
        );

        return view(
            'user.user-application-list',
            [
                'formattedApplications'
                    => $formattedApplications,
                'user' => Auth::user(),
            ]
        );
    }
}