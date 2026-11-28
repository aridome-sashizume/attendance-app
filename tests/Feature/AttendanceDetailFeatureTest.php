<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠詳細にログインユーザー名が表示される(): void
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/{$attendance->id}");

        $response->assertOk();
        $response->assertSee('テストユーザー');
    }

    public function test_勤怠詳細に対象日付が表示される(): void
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-11-28',
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/{$attendance->id}");

        $response->assertOk();
        $response->assertSee('2026年');
        $response->assertSee('11月28日');
    }

    public function test_出退勤時刻が表示される(): void
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/{$attendance->id}");

        $response->assertOk();
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_休憩時刻が表示される(): void
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        AttendanceBreak::factory()->create([
            'attendance_record_id' => $attendance->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/{$attendance->id}");

        $response->assertOk();
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    public function test_出勤時間が退勤時間より後の場合エラーになる(): void
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->from("/attendance/{$attendance->id}")
            ->post("/attendance/{$attendance->id}", [
                'new_clock_in' => '19:00',
                'new_clock_out' => '18:00',
                'comment' => '修正申請',
            ]);

        $response->assertSessionHasErrors([
            'new_clock_in' =>
                '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_休憩開始時間が退勤時間より後の場合エラーになる(): void
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->from("/attendance/{$attendance->id}")
            ->post("/attendance/{$attendance->id}", [
                'new_clock_in' => '09:00',
                'new_clock_out' => '18:00',
                'new_break_in' => ['19:00'],
                'new_break_out' => ['19:30'],
                'comment' => '修正申請',
            ]);

        $response->assertSessionHasErrors([
            'new_break_in.0' =>
                '休憩時間が不適切な値です',
        ]);
    }

    public function test_休憩終了時間が退勤時間より後の場合エラーになる(): void
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->from("/attendance/{$attendance->id}")
            ->post("/attendance/{$attendance->id}", [
                'new_clock_in' => '09:00',
                'new_clock_out' => '18:00',
                'new_break_in' => ['12:00'],
                'new_break_out' => ['19:00'],
                'comment' => '修正申請',
            ]);

        $response->assertSessionHasErrors([
            'new_break_out.0' =>
                '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_備考未入力の場合エラーになる(): void
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->from("/attendance/{$attendance->id}")
            ->post("/attendance/{$attendance->id}", [
                'new_clock_in' => '09:00',
                'new_clock_out' => '18:00',
                'comment' => '',
            ]);

        $response->assertSessionHasErrors([
            'comment' => '備考を記入してください',
        ]);
    }

    public function test_修正申請を登録できる(): void
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->post("/attendance/{$attendance->id}", [
                'new_clock_in' => '09:00',
                'new_clock_out' => '18:00',
                'new_break_in' => ['12:00'],
                'new_break_out' => ['13:00'],
                'comment' => '修正申請',
            ]);

        $response->assertRedirect('/attendance/list');

        $this->assertDatabaseHas('applications', [
            'attendance_record_id' => $attendance->id,
            'approval_status' => '承認待ち',
        ]);
    }

    public function test_承認待ちの場合は編集不可メッセージが表示される(): void
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        Application::create([
            'user_id' => $user->id,
            'attendance_record_id' => $attendance->id,
            'new_date' => $attendance->date,
            'new_clock_in' => '09:00',
            'new_clock_out' => '18:00',
            'comment' => '修正申請',
            'approval_status' => '承認待ち',
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/{$attendance->id}");

        $response->assertOk();

        $response->assertSee(
            '承認待ちのため修正できません'
        );
    }
}