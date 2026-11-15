<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * リクエスト認可判定
     *
     * 勤怠修正申請機能の利用を許可する。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール定義
     *
     * 勤怠修正申請時の入力チェックルールを定義する。
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }
}
