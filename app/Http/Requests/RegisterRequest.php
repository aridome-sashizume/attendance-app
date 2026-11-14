<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'お名前を入力してください',
            'email.required' => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',
            'email.email' => 'メールアドレスはメール形式で入力してください',
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password.confirmed' => 'パスワードと一致しません',
        ];
    }
}
