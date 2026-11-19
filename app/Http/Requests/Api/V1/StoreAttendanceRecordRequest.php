<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRecordRequest extends FormRequest
{
    /**
     * リクエストの認可判定
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 勤怠登録時のバリデーションルール
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'date' => [
                'required',
                'date',
                Rule::unique('attendance_records')
                    ->where(
                        fn ($query) => $query->where(
                            'user_id',
                            $this->user_id
                        )
                    ),
            ],
            'clock_in' => [
                'required',
                'date_format:H:i:s',
            ],
            'clock_out' => [
                'nullable',
                'date_format:H:i:s',
                'after:clock_in',
            ],
            'comment' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * バリデーションエラーメッセージ
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.required' => '勤怠日は必須です。',
            'date.date' => '勤怠日は YYYY-MM-DD 形式で指定してください。',

            'clock_in.required' => '出勤時刻は必須です。',
            'clock_in.date_format' => '出勤時刻は HH:MM:SS 形式で指定してください。',

            'clock_out.date_format' => '退勤時刻は HH:MM:SS 形式で指定してください。',
            'clock_out.after' => '退勤時刻は出勤時刻より後の時刻を指定してください。',

            'comment.max' => '備考は 255 文字以内で入力してください。',

            'date.unique' => 'この日付の勤怠は既に登録されています。',
        ];
    }
}
