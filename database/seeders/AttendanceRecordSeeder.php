<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceRecordSeeder extends Seeder
{
    public function run(): void
    {
        $user1 = User::where('email', 'user1@example.com')->first();
        $user2 = User::where('email', 'user2@example.com')->first();
        $user3 = User::where('email', 'user3@example.com')->first();

        if ($user1) {
            $this->seedUser1($user1);
        }

        if ($user2) {
            $this->seedGeneralUser($user2);
        }

        if ($user3) {
            $this->seedGeneralUser($user3);
        }
    }

    /**
     * 要件で指定された user1 の意図的データ
     */
    private function seedUser1(User $user): void
    {
        // 過去5ヶ月 × 各月15日 = 75件
        for ($month = 5; $month >= 1; $month--) {

            $baseDate = now()
                ->subMonths($month)
                ->startOfMonth();

            for ($day = 1; $day <= 15; $day++) {

                AttendanceRecord::create([
                    'user_id' => $user->id,
                    'date' => $baseDate->copy()->addDays($day - 1),
                    'clock_in' => '09:00:00',
                    'clock_out' => '18:00:00',
                ]);
            }
        }

        $monthStart = now()->startOfMonth();

        // 通常勤務 10件
        for ($i = 0; $i < 10; $i++) {
            AttendanceRecord::create([
                'user_id' => $user->id,
                'date' => $monthStart->copy()->addDays($i),
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ]);
        }

        // 残業 3件
        for ($i = 10; $i < 13; $i++) {
            AttendanceRecord::create([
                'user_id' => $user->id,
                'date' => $monthStart->copy()->addDays($i),
                'clock_in' => '09:00:00',
                'clock_out' => '20:00:00',
            ]);
        }

        // 遅刻 2件
        for ($i = 13; $i < 15; $i++) {
            AttendanceRecord::create([
                'user_id' => $user->id,
                'date' => $monthStart->copy()->addDays($i),
                'clock_in' => '09:30:00',
                'clock_out' => '18:00:00',
            ]);
        }

        // 早退 1件
        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => $monthStart->copy()->addDays(15),
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
        ]);

        // 長時間労働 1件
        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => $monthStart->copy()->addDays(16),
            'clock_in' => '08:00:00',
            'clock_out' => '21:00:00',
        ]);
    }

    /**
     * user2・user3 用の一般データ
     */
    private function seedGeneralUser(User $user): void
    {
        $baseDate = now()
            ->subMonth()
            ->startOfMonth();

        for ($day = 1; $day <= 30; $day++) {

            AttendanceRecord::create([
                'user_id' => $user->id,
                'date' => $baseDate->copy()->addDays($day - 1),
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ]);
        }
    }
}
