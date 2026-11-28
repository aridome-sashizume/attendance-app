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

    /**
     * 管理者ユーザーを作成する。
     *
     * Feature Test で利用する管理者権限付きユーザーを生成する。
     */
    private function createAdmin(): User
    {
        return User::factory()->create([
            'admin_status' => true,
        ]);
    }

    /**
     * 管理者が日次勤怠一覧を閲覧できることを確認する。
     */
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

    /**
     * 管理者が前日の勤怠一覧を表示できることを確認する。
     */
    public function test_前日を表示できる(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?date='.now()->subDay()->format('Y-m-d'));

        $response->assertOk();

        $response->assertSee(
            now()->subDay()->format('Y/m/d')
        );
    }

    /**
     * 管理者が翌日の勤怠一覧を表示できることを確認する。
     */
    public function test_翌日を表示できる(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?date='.now()->addDay()->format('Y-m-d'));

        $response->assertOk();

        $response->assertSee(
            now()->addDay()->format('Y/m/d')
        );
    }

    /**
     * 管理者が勤怠詳細画面を閲覧できることを確認する。
     */
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

    /**
     * 管理者が勤怠情報を直接修正できることを確認する。
     */
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

    /**
     * 管理者がスタッフ一覧を閲覧できることを確認する。
     */
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

    /**
     * 管理者がスタッフ別勤怠一覧を閲覧できることを確認する。
     */
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

    /**
     * 管理者がスタッフ別勤怠一覧で前月の情報を表示できることを確認する。
     */
    public function test_スタッフ別勤怠一覧で前月表示できる(): void
    {
        $admin = $this->createAdmin();

        $staff = User::factory()->create();

        $response = $this->actingAs($admin)
            ->get(
                "/admin/attendance/staff/{$staff->id}?date="
                .now()->subMonth()->format('Y-m')
            );

        $response->assertOk();

        $response->assertSee(
            now()->subMonth()->format('Y/m')
        );
    }

    /**
     * 管理者がスタッフ別勤怠一覧で翌月の情報を表示できることを確認する。
     */
    public function test_スタッフ別勤怠一覧で翌月表示できる(): void
    {
        $admin = $this->createAdmin();

        $staff = User::factory()->create();

        $response = $this->actingAs($admin)
            ->get(
                "/admin/attendance/staff/{$staff->id}?date="
                .now()->addMonth()->format('Y-m')
            );

        $response->assertOk();

        $response->assertSee(
            now()->addMonth()->format('Y/m')
        );
    }

    /**
     * 管理者が修正申請を承認できることを確認する。
     */
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

    /**
     * 承認待ちの修正申請が一覧画面に表示されることを確認する。
     */
    public function test_承認待ち申請一覧が表示される(): void
    {
        $admin = $this->createAdmin();

        $application = Application::factory()->create([
            'approval_status' => '承認待ち',
        ]);

        $response = $this->actingAs($admin)
            ->get('/stamp_correction_request/list');

        $response->assertOk();
        $response->assertSee('承認待ち');
    }

    /**
     * 承認済みの修正申請が一覧画面に表示されることを確認する。
     */
    public function test_承認済み申請一覧が表示される(): void
    {
        $admin = $this->createAdmin();

        Application::factory()->create([
            'approval_status' => '承認済み',
        ]);

        $response = $this->actingAs($admin)
            ->get('/stamp_correction_request/list?status=approved');

        $response->assertOk();
        $response->assertSee('承認済み');
    }

    /**
     * 修正申請の詳細内容が正しく表示されることを確認する。
     */
    public function test_修正申請詳細が表示される(): void
    {
        $admin = $this->createAdmin();

        $application = Application::factory()->create([
            'new_clock_in' => '08:00:00',
            'new_clock_out' => '17:00:00',
            'comment' => '修正申請テスト',
        ]);

        $response = $this->actingAs($admin)
            ->get("/stamp_correction_request/approve/{$application->id}");

        $response->assertOk();

        $response->assertSee('08:00');
        $response->assertSee('17:00');
        $response->assertSee('修正申請テスト');
    }

    /**
     * スタッフ別勤怠一覧から勤怠詳細画面へ遷移できることを確認する。
     */
    public function test_スタッフ勤怠一覧から詳細画面へ遷移できる(): void
    {
        $admin = $this->createAdmin();

        $staff = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $staff->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$staff->id}");

        $response->assertOk();

        $response->assertSee(
            "/attendance/{$attendance->id}",
            false
        );
    }

    /**
     * 管理者が出勤時間を退勤時間より後に設定した場合、
     * バリデーションエラーが発生することを確認する。
     */
    public function test_管理者_出勤時間が退勤時間より後の場合エラー(): void
    {
        $admin = $this->createAdmin();

        $attendance = AttendanceRecord::factory()->create();

        $response = $this->actingAs($admin)
            ->from("/admin/attendance/{$attendance->id}")
            ->post("/admin/attendance/{$attendance->id}", [
                'new_clock_in' => '19:00',
                'new_clock_out' => '18:00',
                'comment' => '管理者修正',
            ]);

        $response->assertSessionHasErrors([
            'new_clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /**
     * 管理者が休憩開始時間を退勤時間より後に設定した場合、
     * バリデーションエラーが発生することを確認する。
     */
    public function test_管理者_休憩開始時間が退勤時間より後の場合エラー(): void
    {
        $admin = $this->createAdmin();

        $attendance = AttendanceRecord::factory()->create();

        $response = $this->actingAs($admin)
            ->from("/admin/attendance/{$attendance->id}")
            ->post("/admin/attendance/{$attendance->id}", [
                'new_clock_in' => '09:00',
                'new_clock_out' => '18:00',
                'new_break_in' => ['19:00'],
                'new_break_out' => ['19:30'],
                'comment' => '管理者修正',
            ]);

        $response->assertSessionHasErrors([
            'new_break_in.0' => '休憩時間が不適切な値です',
        ]);
    }

    /**
     * 管理者が休憩終了時間を退勤時間より後に設定した場合、
     * バリデーションエラーが発生することを確認する。
     */
    public function test_管理者_休憩終了時間が退勤時間より後の場合エラー(): void
    {
        $admin = $this->createAdmin();

        $attendance = AttendanceRecord::factory()->create();

        $response = $this->actingAs($admin)
            ->from("/admin/attendance/{$attendance->id}")
            ->post("/admin/attendance/{$attendance->id}", [
                'new_clock_in' => '09:00',
                'new_clock_out' => '18:00',
                'new_break_in' => ['12:00'],
                'new_break_out' => ['19:00'],
                'comment' => '管理者修正',
            ]);

        $response->assertSessionHasErrors([
            'new_break_out.0' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /**
     * 管理者が備考未入力で勤怠修正を行った場合、
     * バリデーションエラーが発生することを確認する。
     */
    public function test_管理者_備考未入力の場合エラー(): void
    {
        $admin = $this->createAdmin();

        $attendance = AttendanceRecord::factory()->create();

        $response = $this->actingAs($admin)
            ->from("/admin/attendance/{$attendance->id}")
            ->post("/admin/attendance/{$attendance->id}", [
                'new_clock_in' => '09:00',
                'new_clock_out' => '18:00',
                'comment' => '',
            ]);

        $response->assertSessionHasErrors([
            'comment' => '備考を記入してください',
        ]);
    }
}
