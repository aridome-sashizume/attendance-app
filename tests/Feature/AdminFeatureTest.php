<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'admin_status' => true,
        ]);
    }

    public function test_管理者勤怠一覧が表示される(): void
    {
        $admin = $this->createAdmin();

        $user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list');

        $response->assertOk();
        $response->assertSee('テストユーザー');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_前日を表示できる(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?date=' . now()->subDay()->format('Y-m-d'));

        $response->assertOk();

        $response->assertSee(
            now()->subDay()->format('Y/m/d')
        );
    }

    public function test_翌日を表示できる(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?date=' . now()->addDay()->format('Y-m-d'));

        $response->assertOk();

        $response->assertSee(
            now()->addDay()->format('Y/m/d')
        );
    }

    public function test_管理者勤怠詳細が表示される(): void
    {
        $admin = $this->createAdmin();

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/{$attendance->id}");

        $response->assertOk();

        $response->assertSee('山田太郎');
    }

    public function test_管理者が勤怠を直接修正できる(): void
    {
        $admin = $this->createAdmin();

        $attendance = AttendanceRecord::factory()->create();

        $response = $this->actingAs($admin)
            ->post("/admin/attendance/{$attendance->id}", [
                'new_clock_in' => '08:00',
                'new_clock_out' => '17:00',
                'comment' => '管理者修正',
            ]);

        $response->assertRedirect('/admin/attendance/list');

        $attendance->refresh();

        $this->assertEquals(
            '08:00:00',
            $attendance->clock_in->format('H:i:s')
        );

        $this->assertEquals(
            '17:00:00',
            $attendance->clock_out->format('H:i:s')
        );
    }

    public function test_スタッフ一覧が表示される(): void
    {
        $admin = $this->createAdmin();

        $staff = User::factory()->create([
            'name' => 'スタッフA',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/staff/list');

        $response->assertOk();
        $response->assertSee('スタッフA');
        $response->assertSee($staff->email);
    }

    public function test_スタッフ別勤怠一覧が表示される(): void
    {
        $admin = $this->createAdmin();

        $staff = User::factory()->create([
            'name' => 'スタッフB',
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $staff->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$staff->id}");

        $response->assertOk();

        $response->assertSee('スタッフB');
    }

    public function test_スタッフ別勤怠一覧で前月表示できる(): void
    {
        $admin = $this->createAdmin();

        $staff = User::factory()->create();

        $response = $this->actingAs($admin)
            ->get(
                "/admin/attendance/staff/{$staff->id}?date="
                . now()->subMonth()->format('Y-m')
            );

        $response->assertOk();

        $response->assertSee(
            now()->subMonth()->format('Y/m')
        );
    }

    public function test_スタッフ別勤怠一覧で翌月表示できる(): void
    {
        $admin = $this->createAdmin();

        $staff = User::factory()->create();

        $response = $this->actingAs($admin)
            ->get(
                "/admin/attendance/staff/{$staff->id}?date="
                . now()->addMonth()->format('Y-m')
            );

        $response->assertOk();

        $response->assertSee(
            now()->addMonth()->format('Y/m')
        );
    }

    public function test_管理者が修正申請を承認できる(): void
    {
        $admin = $this->createAdmin();

        $application = Application::factory()->create([
            'approval_status' => '承認待ち',
        ]);

        $response = $this->actingAs($admin)
            ->post(
                "/stamp_correction_request/approve/{$application->id}"
            );

        $response->assertRedirect(
            '/stamp_correction_request/list'
        );

        $application->refresh();

        $this->assertEquals(
            '承認済み',
            $application->approval_status
        );
    }
}