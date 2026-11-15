<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ApplicationListController extends Controller
{
    /**
     * 申請一覧画面表示
     * 管理者は全ユーザーの修正申請一覧、
     * 一般ユーザーは自身の修正申請一覧を表示する。
     */
    public function index(): View
    {
        // 管理者ユーザーの場合は全ユーザーの申請一覧を表示
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

        // ログイン中の一般ユーザーが作成した申請一覧を取得
        $applications = Application::where(
            'user_id',
            Auth::id()
        )
            ->latest()
            ->get();

        // Blade表示用に申請データを整形
        $formattedApplications = $applications->map(
            function (Application $application): array {

                return [
                    // 申請ID
                    'id' => $application->id,

                    // 承認状態（承認待ち・承認済み）
                    'approval_status' => $application->approval_status,

                    // 申請対象日を表示用フォーマットへ変換
                    'date' => Carbon::parse(
                        $application->new_date
                    )->format('Y/m/d'),

                    // 申請理由
                    'comment' => $application->comment,

                    // 申請日を表示用フォーマットへ変換
                    'application_date' => $application->created_at
                        ->format('Y/m/d'),
                ];
            }
        );

        return view(
            'user.user-application-list',
            [
                'formattedApplications' => $formattedApplications,
                'user' => Auth::user(),
            ]
        );
    }
}
