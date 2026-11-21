<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\User;

class AttendanceRecordPolicy
{
    /**
     * 管理者は全操作を許可する。
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
     * 勤怠情報の更新権限を判定する。
     *
     * 本人の勤怠情報のみ更新を許可する。
     */
    public function update(
        User $user,
        AttendanceRecord $attendanceRecord
    ): bool {
        return $user->id === $attendanceRecord->user_id;
    }

    /**
     * 勤怠情報の削除権限を判定する。
     *
     * 本人の勤怠情報のみ削除を許可する。
     */
    public function delete(
        User $user,
        AttendanceRecord $attendanceRecord
    ): bool {
        return $user->id === $attendanceRecord->user_id;
    }
}
