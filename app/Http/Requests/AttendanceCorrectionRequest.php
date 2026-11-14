<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AttendanceCorrectionRequest extends FormRequest
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
     *
     * ユーザーが入力した出退勤時刻・休憩時間の整合性を検証する。
     *
     * @param  Validator  $validator  バリデータ
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {

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
             * 出勤時間 >= 退勤時間
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
                     * 休憩終了が出勤より前
                     */
                    if ($breakOutTime->lt($clockInTime)) {
                        $validator->errors()->add(
                            "new_break_out.$index",
                            '休憩時間が不適切な値です'
                        );
                    }

                    /**
                     * 休憩終了が休憩開始以前
                     */
                    if ($breakOutTime->lte($breakInTime)) {
                        $validator->errors()->add(
                            "new_break_out.$index",
                            '休憩時間が不適切な値です'
                        );
                    }

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
