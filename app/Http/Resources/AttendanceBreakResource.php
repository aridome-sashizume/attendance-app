<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 休憩情報Resource
 */
class AttendanceBreakResource extends JsonResource
{
    /**
     * Resourceを配列形式へ変換
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'attendance_record_id' => $this->attendance_record_id,

            'break_in' => $this->break_in,

            'break_out' => $this->break_out,
        ];
    }
}
