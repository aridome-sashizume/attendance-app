<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * 管理者画面へのアクセス権限を確認する。
     *
     * 一般ユーザーによる管理者機能へのアクセスを防ぐため、
     * 管理者権限を持たない場合は 403 Forbidden を返す。
     *
     * @param  Request  $request  リクエスト情報
     * @param  Closure  $next  次の処理
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        if (! auth()->user()->admin_status) {
            abort(403);
        }

        return $next($request);
    }
}
