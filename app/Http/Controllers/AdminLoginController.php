<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AdminLoginController extends Controller
{
    /**
     * 管理者ログイン画面表示
     */
    public function showLoginForm()
    {
        return view('admin.admin-login');
    }

    /**
     * 管理者ログイン処理
     */
    public function login(AdminLoginRequest $request)
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
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
