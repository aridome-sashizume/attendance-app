<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminApplicationListController extends Controller
{
    /**
     * 修正申請一覧表示
     */
    public function index(Request $request)
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
        $formattedApplications = $applications->map(function ($application) {

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
