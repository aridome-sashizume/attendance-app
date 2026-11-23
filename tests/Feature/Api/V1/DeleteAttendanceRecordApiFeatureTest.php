<?php

namespace Tests\Feature\Api\V1;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeleteAttendanceRecordApiFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤怠削除APIで勤怠情報を削除できる
     *
     * ログインユーザー自身の勤怠情報を削除できることを確認する。
     */
    public function test_勤怠削除_ap_iで勤怠情報を削除できる(): void
    {
        // 認証ユーザー作成
        $user = User::factory()->create();

        // API認証
        Sanctum::actingAs($user);

        // 削除対象の勤怠データ作成
        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        // 削除API実行
        $response = $this->deleteJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}"
        );

        // 204 No Content を確認
        $response->assertNoContent();

        // レコード削除を確認
        $this->assertDatabaseMissing(
            'attendance_records',
            [
                'id' => $attendanceRecord->id,
            ]
        );
    }

    /**
     * 存在しない勤怠IDを指定した場合は404を返す
     *
     * 存在しない勤怠IDで削除APIを実行した場合、
     * 404エラーとなることを確認する。
     */
    public function test_存在しない勤怠_i_dを指定した場合は404を返す(): void
    {
        // 認証ユーザー作成
        $user = User::factory()->create();

        // API認証
        Sanctum::actingAs($user);

        // 存在しないIDで削除API実行
        $response = $this->deleteJson(
            '/api/v1/attendance-records/99999'
        );

        // 404を確認
        $response->assertNotFound();

        $response->assertJson([
            'error' => '勤怠情報が見つかりませんでした。',
        ]);
    }

    /**
     * 未認証の場合は401を返す
     *
     * 認証情報なしで削除APIへアクセスした場合、
     * 未認証エラーとなることを確認する。
     */
    public function test_未認証の場合は401を返す(): void
    {
        // 削除対象の勤怠データ作成
        $attendanceRecord = AttendanceRecord::factory()->create();

        // 未認証で削除API実行
        $response = $this->deleteJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}"
        );

        // 401を確認
        $response->assertUnauthorized();

        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    /**
     * 他ユーザーの勤怠削除時は403を返す
     *
     * 他ユーザーの勤怠情報を削除しようとした場合、
     * 認可エラーとなることを確認する。
     */
    public function test_他ユーザーの勤怠削除時は403を返す(): void
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

        // 他ユーザーの勤怠削除を実行
        $response = $this->deleteJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}"
        );

        // 403を確認
        $response->assertForbidden();

        $response->assertJson([
            'error' => 'この操作を実行する権限がありません。',
        ]);
    }
}
