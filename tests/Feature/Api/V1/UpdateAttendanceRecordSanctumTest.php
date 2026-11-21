<?php

namespace Tests\Feature\Api\V1;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateAttendanceRecordSanctumTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 未認証ユーザーは更新できない
     */
    public function test_guest_cannot_update_attendance_record(): void
    {
        $attendanceRecord = AttendanceRecord::factory()->create();

        $response = $this->patchJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}",
            [
                'clock_out' => '19:00:00',
            ]
        );

        $response->assertUnauthorized();
    }

    /**
     * 認証済みユーザーは更新できる
     */
    public function test_authenticated_user_can_update_attendance_record(): void
    {
        $user = User::factory()->create();

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->patchJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}",
            [
                'clock_out' => '19:00:00',
            ]
        );

        $response->assertOk();
    }
}