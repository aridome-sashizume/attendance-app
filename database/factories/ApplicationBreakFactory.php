<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\ApplicationBreak;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationBreak>
 */
class ApplicationBreakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),

            /*
             * 要件で固定休憩（12:00～13:00）が定義されているため、
             * 修正後の休憩時間は固定値を初期値として設定する。
             */
            'new_break_in' => '12:00:00',

            'new_break_out' => '13:00:00',
        ];
    }
}
