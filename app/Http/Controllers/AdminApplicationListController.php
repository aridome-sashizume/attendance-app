<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminApplicationListController extends Controller
{
    /**
     * 修正申請一覧表示
     *
     * 管理者が修正申請一覧を表示する。
     * クエリパラメータに応じて承認待ちまたは承認済みの修正申請を取得し、
     * 一覧画面に表示する。
     *
     * @param  Request  $request  リクエスト情報
     * @return View 修正申請一覧画面
     */
    public function index(Request $request): View
    {
        $status = $request->input('status', 'pending');

        if ($status === 'approved') {
            $applications = Application::with('user')
                ->where('approval_status', '承認済み')
                ->latest()
                ->get();
        } else {
            $applications = Application::with('user')
                ->where('approval_status', '承認待ち')
                ->latest()
                ->get();
        }

        /**
         * Bladeで利用する一覧表示用データへ変換
         */
        $formattedApplications = $applications->map(
            function (Application $application): array {
                return [
                    'id' => $application->id,
                    'name' => $application->user->name,
                    'approval_status' => $application->approval_status,
                    'date' => Carbon::parse(
                        $application->new_date
                    )->format('Y/m/d'),
                    'comment' => $application->comment,
                    'application_date' => $application->created_at
                        ->format('Y/m/d'),
                ];
            });

        return view(
            'admin.admin-application-list',
            compact(
                'formattedApplications',
                'status'
            )
        );
    }
}
