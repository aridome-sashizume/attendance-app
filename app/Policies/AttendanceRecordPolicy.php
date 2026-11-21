<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\User;

class AttendanceRecordPolicy
{
    /**
     * 管理者は全操作を許可
     */
    public function before(
        User $user,
        string $ability
    ): ?bool {
        if ($user->admin_status) {
            return true;
        }

        return null;
    }

    /**
     * 勤怠更新
     */
    public function update(
        User $user,
        AttendanceRecord $attendanceRecord
    ): bool {
        return $user->id === $attendanceRecord->user_id;
    }

    /**
     * 勤怠削除
     */
    public function delete(
        User $user,
        AttendanceRecord $attendanceRecord
    ): bool {
        return $user->id === $attendanceRecord->user_id;
    }
}