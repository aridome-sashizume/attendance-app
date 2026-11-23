<?php

namespace Tests\Feature\Api\V1;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IndexAttendanceRecordApiFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤怠一覧APIで勤怠一覧を取得できる
     *
     * 認証済みユーザーが勤怠一覧APIへアクセスした場合、
     * 登録済みの勤怠情報を取得できることを確認する。
     */
    public function test_勤怠一覧_ap_iで勤怠一覧を取得できる(): void
    {
        // 認証ユーザー作成
        $user = User::factory()->create();

        // API認証
        Sanctum::actingAs($user);

        // 勤怠データ作成
        AttendanceRecord::factory()
            ->count(3)
            ->create([
                'user_id' => $user->id,
            ]);

        // 一覧取得API実行
        $response = $this->getJson(
            '/api/v1/attendance-records'
        );

        // レスポンス成功を確認
        $response->assertOk();

        // 勤怠データ件数を確認
        $response->assertJsonCount(
            3,
            'data'
        );
    }

    /**
     * 勤怠一覧APIのレスポンス構造が正しい
     *
     * 勤怠一覧APIが仕様どおりのJSON構造で
     * レスポンスを返すことを確認する。
     */
    public function test_勤怠一覧_ap_iのレスポンス構造が正しい(): void
    {
        // 認証ユーザー作成
        $user = User::factory()->create();

        // API認証
        Sanctum::actingAs($user);

        // 勤怠データ作成
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        // 一覧取得API実行
        $response = $this->getJson(
            '/api/v1/attendance-records'
        );

        // レスポンス成功を確認
        $response->assertOk();

        // JSONレスポンス構造を確認
        $response->assertJsonStructure([
            'data' => [
                '*' => [
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
                ],
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'links',
                'path',
                'per_page',
                'to',
                'total',
            ],
        ]);
    }
}
