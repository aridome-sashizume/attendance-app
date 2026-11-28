<?php

namespace Tests\Feature;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤務外ステータスが表示される
     */
    public function test_勤務外ステータスが表示される(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertOk();
        $response->assertSee('勤務外');
        $response->assertSee('出勤');
    }

    /**
     * 出勤中ステータスが表示される
     */
    public function test_出勤中ステータスが表示される(): void
    {
        $user = User::factory()->create();

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertOk();
        $response->assertSee('出勤中');
        $response->assertSee('退勤');
        $response->assertSee('休憩入');
    }

    /**
     * 休憩中ステータスが表示される
     */
    public function test_休憩中ステータスが表示される(): void
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        AttendanceBreak::factory()->create([
            'attendance_record_id' => $attendance->id,
            'break_in' => now(),
            'break_out' => null,
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertOk();
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');
    }

    /**
     * 退勤済ステータスが表示される
     */
    public function test_退勤済ステータスが表示される(): void
    {
        $user = User::factory()->create();

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertOk();
        $response->assertSee('退勤済');
        $response->assertSee('お疲れ様でした。');
    }

    /**
     * 出勤できる
     */
    public function test_出勤できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/attendance', [
                'action' => 'clock_in',
            ]);

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * 休憩開始できる
     */
    public function test_休憩開始できる(): void
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)
            ->post('/attendance', [
                'action' => 'break_in',
            ]);

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('breaks', [
            'attendance_record_id' => $attendance->id,
        ]);
    }

    /**
     * 休憩終了できる
     */
    public function test_休憩終了できる(): void
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_out' => null,
        ]);

        $break = AttendanceBreak::factory()->create([
            'attendance_record_id' => $attendance->id,
            'break_out' => null,
        ]);

        $response = $this->actingAs($user)
            ->post('/attendance', [
                'action' => 'break_out',
            ]);

        $response->assertRedirect('/attendance');

        $break->refresh();

        $this->assertNotNull($break->break_out);
    }

    /**
     * 退勤できる
     */
    public function test_退勤できる(): void
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)
            ->post('/attendance', [
                'action' => 'clock_out',
            ]);

        $response->assertRedirect('/attendance');

        $attendance->refresh();

        $this->assertNotNull($attendance->clock_out);
    }

    /**
     * 現在日時が表示される
     */
    public function test_現在日時が表示される(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertOk();

        $response->assertSee(
            now()->format('Y年n月j日'),
            false
        );

        $response->assertSee(
            now()->format('H:i'),
            false
        );
    }
}
