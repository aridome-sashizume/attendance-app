<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    /**
     * 管理者ログイン画面表示
     *
     * 管理者ログイン画面を表示する。
     */
    public function showLoginForm(): View
    {
        return view('admin.admin-login');
    }

    /**
     * 管理者ログイン処理
     *
     * 管理者がログインフォームに入力した情報を検証し、ログイン処理を行う。
     *
     * @param  AdminLoginRequest  $request  リクエスト情報
     */
    public function login(AdminLoginRequest $request): RedirectResponse
    {
        $user = User::where('email', $request->email)
            ->where('admin_status', true)
            ->first();

        if (
            ! $user ||
            ! Hash::check($request->password, $user->password)
        ) {
            throw ValidationException::withMessages([
                'email' => 'ログイン情報が登録されていません',
            ]);
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect('/admin/attendance/list');
    }

    /**
     * 管理者ログアウト処理
     *
     * 管理者がログアウトする際に呼び出される。
     *
     * @param  Request  $request  リクエスト情報
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
