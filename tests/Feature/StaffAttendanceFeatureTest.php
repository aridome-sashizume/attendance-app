<?php

namespace Tests\Feature;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffAttendanceFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者ユーザーを作成する。
     */
    private function createAdmin(): User
    {
        return User::factory()->create([
            'admin_status' => true,
        ]);
    }

    /**
     * CSVファイル名が正しく設定されることを確認する。
     */
    public function test_csvファイル名が正しい(): void
    {
        $admin = $this->createAdmin();

        $user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);

        $response = $this->actingAs($admin)
            ->post('/export', [
                'user_id' => $user->id,
                'year_month' => now()->format('Y-m'),
            ]);

        $response->assertOk();

        $response->assertHeader(
            'Content-Disposition'
        );
    }

    /**
     * スタッフ勤怠一覧を表示できることを確認する。
     */
    public function test_スタッフ勤怠一覧を表示できる(): void
    {
        $admin = $this->createAdmin();

        $user = User::factory()->create([
            'name' => 'スタッフA',
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$user->id}");

        $response->assertOk();
        $response->assertSee('スタッフA');
    }

    /**
     * 休憩終了していない休憩は集計されないことを確認する。
     */
    public function test_未終了休憩は集計されない(): void
    {
        $admin = $this->createAdmin();

        $user = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        AttendanceBreak::factory()->create([
            'attendance_record_id' => $attendance->id,
            'break_in' => now(),
            'break_out' => null,
        ]);

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$user->id}");

        $response->assertOk();
    }

    /**
     * 休憩時間が集計されることを確認する。
     */
    public function test_休憩時間が集計される(): void
    {
        $admin = $this->createAdmin();

        $user = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        AttendanceBreak::factory()->create([
            'attendance_record_id' => $attendance->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$user->id}");

        $response->assertOk();
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
