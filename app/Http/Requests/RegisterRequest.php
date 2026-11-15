<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * リクエスト認可判定
     *
     * 勤怠修正申請機能の利用を許可する。
     * 
     *  @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール定義
     *
     * 勤怠修正申請時の入力チェックルールを定義する。
     * 
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * バリデーションエラーメッセージ定義
     * 
     * バリデーションエラー時に表示するメッセージを定義する。
     * 
     * @return array{"email.email": string, "email.required": string, "email.unique": string, "name.required": string, "password.confirmed": string, "password.min": string, "password.required": string}
     */
    public function messages(): array
    {
        return [
            'name.required' => 'お名前を入力してください',
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メールアドレスはメール形式で入力してください',
            'email.unique' => 'このメールアドレスは既に登録されています',   
            'password.required' => 'パスワードを入力してください',
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password.confirmed' => 'パスワードと一致しません',

        ];
    }
}
