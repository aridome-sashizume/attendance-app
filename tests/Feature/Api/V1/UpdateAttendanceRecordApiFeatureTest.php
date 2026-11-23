<?php

namespace Tests\Feature\Api\V1;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateAttendanceRecordApiFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤怠更新APIで勤怠情報を更新できる
     *
     * ログインユーザー自身の勤怠情報を更新できることを確認する。
     */
    public function test_勤怠更新_ap_iで勤怠情報を更新できる(): void
    {
        // 認証ユーザー作成
        $user = User::factory()->create();

        // API認証
        Sanctum::actingAs($user);

        // 更新対象の勤怠データ作成
        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-11-14',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $requestData = [
            'date' => '2026-11-15',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ];

        // 更新API実行
        $response = $this->putJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}",
            $requestData
        );

        // 更新成功を確認
        $response->assertOk();

        // DB更新を確認
        $this->assertDatabaseHas(
            'attendance_records',
            [
                'id' => $attendanceRecord->id,
                'date' => '2026-11-15',
                'clock_in' => '10:00:00',
                'clock_out' => '19:00:00',
            ]
        );
    }

    /**
     * 存在しない勤怠IDを指定した場合は404を返す
     *
     * 存在しない勤怠IDで更新APIを実行した場合、
     * 404エラーとなることを確認する。
     */
    public function test_存在しない勤怠_i_dを指定した場合は404を返す(): void
    {
        // 認証ユーザー作成
        $user = User::factory()->create();

        // API認証
        Sanctum::actingAs($user);

        // 存在しないIDで更新API実行
        $response = $this->putJson(
            '/api/v1/attendance-records/99999',
            [
                'date' => '2026-11-15',
            ]
        );

        // 404エラーを確認
        $response->assertNotFound();
    }

    /**
     * 出勤時刻の形式が不正な場合は422を返す
     *
     * 不正な時刻形式を送信した場合、
     * バリデーションエラーとなることを確認する。
     */
    public function test_出勤時刻の形式が不正な場合は422を返す(): void
    {
        // 認証ユーザー作成
        $user = User::factory()->create();

        // API認証
        Sanctum::actingAs($user);

        // 更新対象の勤怠データ作成
        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        // 不正な時刻形式で更新API実行
        $response = $this->putJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}",
            [
                'clock_in' => '09:00',
            ]
        );

        // バリデーションエラーを確認
        $response->assertStatus(422);

        // clock_inのエラーを確認
        $response->assertJsonValidationErrors([
            'clock_in',
        ]);
    }

    /**
     * 未認証の場合は401を返す
     *
     * 認証情報なしで更新APIへアクセスした場合、
     * 未認証エラーとなることを確認する。
     */
    public function test_未認証の場合は401を返す(): void
    {
        // 更新対象の勤怠データ作成
        $attendanceRecord = AttendanceRecord::factory()->create();

        // 未認証で更新API実行
        $response = $this->putJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}",
            [
                'date' => '2026-11-15',
            ]
        );

        // 401エラーを確認
        $response->assertUnauthorized();

        // エラーメッセージを確認
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    /**
     * 他ユーザーの勤怠更新時は403を返す
     *
     * 他ユーザーの勤怠情報を更新しようとした場合、
     * 認可エラーとなることを確認する。
     */
    public function test_他ユーザーの勤怠更新時は403を返す(): void
    {
        // ログインユーザー作成
        $loginUser = User::factory()->create();

        // 別ユーザー作成
        $otherUser = User::factory()->create();

        // API認証
        Sanctum::actingAs($loginUser);

        // 他ユーザーの勤怠データ作成
        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        // 他ユーザーの勤怠を更新
        $response = $this->putJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}",
            [
                'date' => '2026-11-15',
            ]
        );

        // 403エラーを確認
        $response->assertForbidden();
    }
}
