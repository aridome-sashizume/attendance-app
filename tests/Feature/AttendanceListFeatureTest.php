<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 自分の勤怠情報が表示される
     */
    public function test_自分の勤怠情報が表示される(): void
    {
        $user = User::factory()->create();

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertOk();

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 現在月が表示される
     */
    public function test_現在月が表示される(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertOk();

        $response->assertSee(
            now()->format('Y/m')
        );
    }

    /**
     * 前月の情報を表示できる
     */
    public function test_前月の情報を表示できる(): void
    {
        $user = User::factory()->create();

        $previousMonth = now()
            ->subMonth()
            ->format('Y-m');

        $response = $this->actingAs($user)
            ->get("/attendance/list?date={$previousMonth}");

        $response->assertOk();

        $response->assertSee(
            now()->subMonth()->format('Y/m')
        );
    }

    /**
     * 翌月の情報を表示できる
     */
    public function test_翌月の情報を表示できる(): void
    {
        $user = User::factory()->create();

        $nextMonth = now()
            ->addMonth()
            ->format('Y-m');

        $response = $this->actingAs($user)
            ->get("/attendance/list?date={$nextMonth}");

        $response->assertOk();

        $response->assertSee(
            now()->addMonth()->format('Y/m')
        );
    }

    /**
     * 詳細リンクが表示される
     */
    public function test_詳細リンクが表示される(): void
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertOk();

        $response->assertSee('詳細');

        $response->assertSee(
            '/attendance/'.$attendance->id,
            false
        );
    }
}
