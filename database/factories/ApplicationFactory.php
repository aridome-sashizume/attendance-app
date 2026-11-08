<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    /**
     * 修正申請のダミーデータとして、
     * 標準勤務時間（09:00～18:00）を初期値に設定する。
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),

            'attendance_record_id' => AttendanceRecord::factory(),

            'new_date' => fake()->date(),

            'new_clock_in' => '09:00:00',

            'new_clock_out' => '18:00:00',

            'comment' => fake()->optional()->sentence(),

            'approval_status' => '承認待ち',
        ];
    }
}