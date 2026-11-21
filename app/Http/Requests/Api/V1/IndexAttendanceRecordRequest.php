<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexAttendanceRecordRequest extends FormRequest
{
    /**
     * リクエストの認可判定
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 勤怠一覧取得時のバリデーションルール
     *
     * 検証項目
     * - user_id
     * - date
     * - month
     * - page
     * - per_page
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'date_format:Y-m'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.date_format' => '日付はYYYY-MM-DD形式で指定してください。',
            'month.date_format' => '年月はYYYY-MM形式で指定してください。',
            'page.integer' => 'ページ番号は整数で指定してください。',
            'page.min' => 'ページ番号は1以上で指定してください。',
            'per_page.integer' => '取得件数は整数で指定してください。',
            'per_page.min' => '取得件数は1以上で指定してください。',
            'per_page.max' => '取得件数は100件以下で指定してください。',
        ];
    }
}
