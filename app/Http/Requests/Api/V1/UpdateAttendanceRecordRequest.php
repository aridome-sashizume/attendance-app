<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceRecordRequest extends FormRequest
{
    /**
     * リクエストの認可判定
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 勤怠更新時のバリデーションルール
     *
     * 更新APIは部分更新に対応するため、
     * 送信された項目のみ検証を行う。
     *
     * 未送信の項目は既存値を保持する。
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // ユーザーID
            'user_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:users,id',
            ],

            // 勤怠日
            'date' => [
                'sometimes',
                'required',
                'date',

                Rule::unique('attendance_records')
                    ->ignore(
                        $this->route('attendanceRecord')->id
                    )
                    ->where(
                        fn ($query) => $query->where(
                            'user_id',
                            $this->input(
                                'user_id',
                                $this->route('attendanceRecord')->user_id
                            )
                        )
                    ),
            ],

            // 出勤時刻
            'clock_in' => [
                'sometimes',
                'required',
                'date_format:H:i:s',
            ],

            // 退勤時刻
            'clock_out' => [
                'sometimes',
                'nullable',
                'date_format:H:i:s',
                'after:clock_in',
            ],

            // 備考
            'comment' => [
                'sometimes',
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
            'date.unique' => 'この日付の勤怠は既に登録されています。',

            'clock_in.required' => '出勤時刻は必須です。',
            'clock_in.date_format' => '出勤時刻は HH:MM:SS 形式で指定してください。',

            'clock_out.date_format' => '退勤時刻は HH:MM:SS 形式で指定してください。',
            'clock_out.after' => '退勤時刻は出勤時刻より後の時刻を指定してください。',

            'comment.max' => '備考は 255 文字以内で入力してください。',
        ];
    }
}
