<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * サービス登録処理
     *
     * アプリケーションで利用するサービスを登録する。
     */
    public function register(): void
    {
        //
    }

    /**
     * Fortify 認証設定
     *
     * ユーザー登録・ログイン・パスワード更新などの
     * Fortify認証機能の設定を行う。
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // 認証ビューの設定
        Fortify::loginView(function () {
            return view('user.user-login');
        });

        Fortify::registerView(function () {
            return view('user.register');
        });

        /*
        * FN008の使用技術としてFormRequestが指定されているため、
        * LoginRequestのバリデーションルールとメッセージを利用する。
        */
        Fortify::authenticateUsing(function (Request $request) {

            $loginRequest = new LoginRequest;

            Validator::make(
                $request->all(),
                $loginRequest->rules(),
                $loginRequest->messages()
            )->validate();

            $user = User::where('email', $request->email)->first();

            if (
                $user &&
                Hash::check($request->password, $user->password)
            ) {
                return $user;
            }

            throw ValidationException::withMessages([
                'email' => 'ログイン情報が登録されていません',
            ]);
        });

    }
}
