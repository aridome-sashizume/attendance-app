<?php

namespace Tests\Feature\Api\V1;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendanceRecordPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 本人は更新できる
     */
    public function test_owner_can_update_attendance_record(): void
    {
        $user = User::factory()->create();

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->patchJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}",
            [
                'clock_out' => '19:00:00',
            ]
        );

        $response->assertOk();
    }

    /**
     * 他人は更新できない
     */
    public function test_other_user_cannot_update_attendance_record(): void
    {
        $owner = User::factory()->create();

        $otherUser = User::factory()->create();

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($otherUser, ['*']);

        $response = $this->patchJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}",
            [
                'clock_out' => '19:00:00',
            ]
        );

        $response->assertForbidden();
    }

    /**
     * 管理者は更新できる
     */
    public function test_admin_can_update_attendance_record(): void
    {
        $owner = User::factory()->create();

        $admin = User::factory()->create([
            'admin_status' => true,
        ]);

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($admin, ['*']);

        $response = $this->patchJson(
            "/api/v1/attendance-records/{$attendanceRecord->id}",
            [
                'clock_out' => '19:00:00',
            ]
        );

        $response->assertOk();
    }
}