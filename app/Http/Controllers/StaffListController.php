<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class StaffListController extends Controller
{
    /**
     * スタッフ一覧画面表示
     *
     * 管理者が全スタッフの情報を取得し、
     * スタッフ一覧画面に表示する。
     */
    public function index(): View
    {
        $users = User::all();

        return view('admin.staff-list', compact('users'));
    }
}
