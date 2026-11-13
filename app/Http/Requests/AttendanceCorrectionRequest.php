<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'new_clock_in' => ['required'],
            'new_clock_out' => ['required'],
            'comment' => ['required'],

            'new_break_in.*' => ['nullable'],
            'new_break_out.*' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'comment.required' => '備考を記入してください',
        ];
    }

    /**
     * 項目間バリデーション
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $clockIn = $this->input('new_clock_in');
            $clockOut = $this->input('new_clock_out');

            $clockInTime = Carbon::createFromFormat(
                'H:i',
                $clockIn
            );

            $clockOutTime = Carbon::createFromFormat(
                'H:i',
                $clockOut
            );

            /**
             * 出勤時間 > 退勤時間
             */
            if ($clockInTime->gte($clockOutTime)) {
                $validator->errors()->add(
                    'new_clock_in',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            $breakIns = $this->input('new_break_in', []);
            $breakOuts = $this->input('new_break_out', []);

            foreach ($breakIns as $index => $breakIn) {

                $breakOut = $breakOuts[$index] ?? null;

                /**
                 * 空行はスキップ
                 */
                if (! $breakIn && ! $breakOut) {
                    continue;
                }

                $breakInTime = Carbon::createFromFormat(
                    'H:i',
                    $breakIn
                );

                /**
                 * 休憩開始が出勤より前
                 */
                if ($breakInTime->lt($clockInTime)) {
                    $validator->errors()->add(
                        "new_break_in.$index",
                        '休憩時間が不適切な値です'
                    );
                }

                /**
                 * 休憩開始が退勤より後
                 */
                if ($breakInTime->gt($clockOutTime)) {
                    $validator->errors()->add(
                        "new_break_in.$index",
                        '休憩時間が不適切な値です'
                    );
                }

                /**
                 * 休憩終了が入力されている場合
                 */
                if ($breakOut) {

                    $breakOutTime = Carbon::createFromFormat(
                        'H:i',
                        $breakOut
                    );

                    /**
                     * 休憩終了が退勤より後
                     */
                    if ($breakOutTime->gt($clockOutTime)) {
                        $validator->errors()->add(
                            "new_break_out.$index",
                            '休憩時間もしくは退勤時間が不適切な値です'
                        );
                    }
                }
            }
        });
    }
}
