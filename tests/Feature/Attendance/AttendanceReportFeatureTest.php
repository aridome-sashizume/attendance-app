<?php

namespace Tests\Feature;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceReportFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 認証ユーザーの統計情報が正しく計算される
     *
     * 総労働時間が正しく集計されることを確認する。
     */
    public function test_総労働時間を集計できる(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now(),
        ]);

        AttendanceBreak::factory()->create([
            'attendance_record_id' => $attendanceRecord->id,
        ]);

        $response = $this->get('/attendance/report');

        $response->assertOk();

        $response->assertViewHas(
            'summary',
            fn (array $summary): bool => $summary['total_work_minutes'] === 480
        );
    }

    /**
     * 認証ユーザーの統計情報が正しく計算される
     *
     * 総残業時間が正しく集計されることを確認する。
     */
    public function test_総残業時間を集計できる(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now(),
            'clock_in' => now()->setTime(8, 0),
            'clock_out' => now()->setTime(19, 0),
        ]);

        AttendanceBreak::factory()->create([
            'attendance_record_id' => $attendanceRecord->id,
        ]);

        $response = $this->get('/attendance/report');

        $response->assertOk();

        $response->assertViewHas(
            'summary',
            fn (array $summary): bool => $summary['total_overtime_minutes'] === 120
        );
    }

    /**
     * 認証ユーザーの統計情報が正しく計算される
     *
     * 平均労働時間が正しく集計されることを確認する。
     */
    public function test_平均労働時間を集計できる(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $record1 = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->subDay(),
        ]);

        AttendanceBreak::factory()->create([
            'attendance_record_id' => $record1->id,
        ]);

        $record2 = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now(),
            'clock_in' => now()->setTime(8, 0),
            'clock_out' => now()->setTime(19, 0),
        ]);

        AttendanceBreak::factory()->create([
            'attendance_record_id' => $record2->id,
        ]);

        $response = $this->get('/attendance/report');

        $response->assertOk();

        $response->assertViewHas(
            'summary',
            fn (array $summary): bool => $summary['avg_work_minutes'] === 540
        );
    }

    /**
     * 認証ユーザーの統計情報が正しく計算される
     *
     * 遅刻回数が正しく集計されることを確認する。
     */
    public function test_遅刻回数を集計できる(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now(),
            'clock_in' => now()->setTime(9, 30),
        ]);

        $response = $this->get('/attendance/report');

        $response->assertOk();

        $response->assertViewHas(
            'anomalies',
            fn (array $anomalies): bool => $anomalies['late_count'] === 1
        );
    }

    /**
     * 認証ユーザーの統計情報が正しく計算される
     *
     * 早退回数が正しく集計されることを確認する。
     */
    public function test_早退回数を集計できる(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now(),
            'clock_out' => now()->setTime(17, 0),
        ]);

        $response = $this->get('/attendance/report');

        $response->assertOk();

        $response->assertViewHas(
            'anomalies',
            fn (array $anomalies): bool => $anomalies['early_leave_count'] === 1
        );
    }

    /**
     * 認証ユーザーの統計情報が正しく計算される
     *
     * 長時間労働回数が正しく集計されることを確認する。
     */
    public function test_長時間労働回数を集計できる(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now(),
            'clock_in' => now()->setTime(8, 0),
            'clock_out' => now()->setTime(20, 0),
        ]);

        AttendanceBreak::factory()->create([
            'attendance_record_id' => $attendanceRecord->id,
        ]);

        $response = $this->get('/attendance/report');

        $response->assertOk();

        $response->assertViewHas(
            'anomalies',
            fn (array $anomalies): bool => $anomalies['long_work_count'] === 1
        );
    }

    /**
     * ゲストはレポートページにアクセスできない
     *
     * 未認証でアクセスした場合、
     * ログイン画面へリダイレクトされることを確認する。
     */
    public function test_未認証の場合はログイン画面へリダイレクトされる(): void
    {
        $response = $this->get('/attendance/report');

        $response->assertRedirect('/login');
    }

    /**
     * 勤怠記録がないユーザーで安全に処理される
     *
     * 勤怠データが存在しない場合でも
     * エラーなくレポート画面を表示できることを確認する。
     */
    public function test_勤怠記録がない場合でもエラーにならない(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/attendance/report');

        $response->assertOk();

        $response->assertViewHas(
            'summary',
            fn (array $summary): bool => $summary['total_work_minutes'] === 0
                && $summary['total_overtime_minutes'] === 0
                && $summary['avg_work_minutes'] === 0
        );

        $response->assertViewHas(
            'anomalies',
            fn (array $anomalies): bool => $anomalies['late_count'] === 0
                && $anomalies['early_leave_count'] === 0
                && $anomalies['long_work_count'] === 0
        );
    }
}
