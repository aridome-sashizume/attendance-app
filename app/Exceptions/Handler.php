<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // 勤怠情報が存在しない場合
        $this->renderable(function (
            NotFoundHttpException $e,
            $request
        ) {
            return response()->json([
                'error' => '勤怠情報が見つかりませんでした。',
            ], 404);
        });

        // 操作権限がない場合
        $this->renderable(function (
            AccessDeniedHttpException $e,
            $request
        ) {
            return response()->json([
                'error' => 'この操作を実行する権限がありません。',
            ], 403);
        });
    }
}
