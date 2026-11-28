<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationDetailFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 承認待ち申請の詳細画面を表示できることを確認する。
     */
    public function test_承認待ち申請の詳細を表示できる(): void
    {
        $user = User::factory()->create();

        $application = Application::factory()->create([
            'user_id' => $user->id,
            'approval_status' => '承認待ち',
            'new_clock_in' => '09:00:00',
            'new_clock_out' => '18:00:00',
            'comment' => '承認待ちテスト',
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$application->id}");

        $response->assertOk();

        $response->assertSee('承認待ちテスト');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 承認済み申請の詳細画面を表示できることを確認する。
     */
    public function test_承認済み申請の詳細を表示できる(): void
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        AttendanceBreak::factory()->create([
            'attendance_record_id' => $attendance->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $application = Application::factory()->create([
            'user_id' => $user->id,
            'attendance_record_id' => $attendance->id,
            'approval_status' => '承認済み',
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$application->id}");

        $response->assertOk();

        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
