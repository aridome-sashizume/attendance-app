<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttendanceRecord>
 */
class AttendanceRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    /*
    * 要件で通常勤務（9:00～18:00）が定義されているため、
    * 出勤時刻・退勤時刻は標準勤務時間を初期値として設定する。
    */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),

            'date' => fake()->date(),

            'clock_in' => now()->setTime(9, 0),

            'clock_out' => now()->setTime(18, 0),
        ];
    }
}