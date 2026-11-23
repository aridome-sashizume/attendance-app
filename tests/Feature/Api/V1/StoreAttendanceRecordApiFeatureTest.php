<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StoreAttendanceRecordApiFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤怠登録APIで勤怠情報を登録できる
     *
     * 認証済みユーザーが有効なパラメータでリクエストした場合、
     * 勤怠情報を登録できることを確認する。
     */
    public function test_勤怠登録_ap_iで勤怠情報を登録できる(): void
    {
        // 認証ユーザー作成
        $user = User::factory()->create();

        // API認証
        Sanctum::actingAs($user);

        // 登録データ作成
        $requestData = [
            'user_id' => $user->id,
            'date' => '2026-11-14',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => 'テストコメント',
        ];

        // 登録API実行
        $response = $this->postJson(
            '/api/v1/attendance-records',
            $requestData
        );

        // 登録成功を確認
        $response->assertCreated();

        // DB登録を確認
        $this->assertDatabaseHas(
            'attendance_records',
            [
                'user_id' => $user->id,
                'date' => '2026-11-14',
            ]
        );
    }

    /**
     * 必須項目が未入力の場合はバリデーションエラーを返す
     *
     * 必須項目を送信せずに登録APIを実行した場合、
     * バリデーションエラーとなることを確認する。
     */
    public function test_必須項目が未入力の場合はバリデーションエラーを返す(): void
    {
        // 認証ユーザー作成
        $user = User::factory()->create();

        // API認証
        Sanctum::actingAs($user);

        // 必須項目なしで登録API実行
        $response = $this->postJson(
            '/api/v1/attendance-records',
            []
        );

        // バリデーションエラーを確認
        $response->assertStatus(422);

        // 必須項目のエラーを確認
        $response->assertJsonValidationErrors([
            'user_id',
            'date',
        ]);

        // 日本語メッセージを確認
        $response->assertJsonPath(
            'errors.date.0',
            '勤怠日は必須です。'
        );
    }

    /**
     * 日付形式が不正な場合はバリデーションエラーを返す
     *
     * 不正な日付形式で登録APIを実行した場合、
     * バリデーションエラーとなることを確認する。
     */
    public function test_日付形式が不正な場合はバリデーションエラーを返す(): void
    {
        // 認証ユーザー作成
        $user = User::factory()->create();

        // API認証
        Sanctum::actingAs($user);

        // 不正な日付形式で登録API実行
        $response = $this->postJson(
            '/api/v1/attendance-records',
            [
                'user_id' => $user->id,
                'date' => 'invalid-date',
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ]
        );

        // バリデーションエラーを確認
        $response->assertStatus(422);

        // dateのバリデーションエラーを確認
        $response->assertJsonValidationErrors([
            'date',
        ]);

        // 日本語メッセージを確認
        $response->assertJsonPath(
            'errors.date.0',
            '勤怠日は YYYY-MM-DD 形式で指定してください。'
        );
    }

    /**
     * 未認証の場合は401を返す
     *
     * 認証情報なしで登録APIへアクセスした場合、
     * 未認証エラーとなることを確認する。
     */
    public function test_未認証の場合は401を返す(): void
    {
        // 未認証で登録API実行
        $response = $this->postJson(
            '/api/v1/attendance-records',
            [
                'user_id' => 1,
                'date' => '2026-11-14',
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ]
        );

        // 401エラーを確認
        $response->assertUnauthorized();

        // レスポンス内容を確認
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }
}