<?php

namespace Tests\Feature\Api\V1;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowAttendanceRecordApiFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤怠詳細APIで勤怠詳細を取得できる
     *
     * 存在する勤怠IDを指定した場合、
     * 勤怠詳細情報を取得できることを確認する。
     */
    public function test_勤怠詳細_ap_iで勤怠詳細を取得できる(): void
    {
        // ユーザー作成
        $user = User::factory()->create();

        // 勤怠データ作成
        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        // 詳細取得API実行
        $response = $this->getJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}"
        );

        // レスポンス成功を確認
        $response->assertOk();
        // JSONレスポンス構造を確認
        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'user' => [
                    'id',
                    'name',
                ],
                'date',
                'clock_in',
                'clock_out',
                'total_time',
                'total_break_time',
                'comment',
                'breaks',
                'applications',
            ],
        ]);
    }

    /**
     * 存在しない勤怠IDを指定した場合は404を返す
     *
     * 存在しない勤怠IDを指定した場合、
     * 404エラーとなることを確認する。
     */
    public function test_存在しない勤怠_i_dを指定した場合は404を返す(): void
    {
        // 存在しないIDで詳細取得API実行
        $response = $this->getJson(
            '/api/v1/attendance-records/99999'
        );

        // 404エラーを確認
        $response->assertNotFound();
    }
}
