<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 勤怠情報Resource
 *
 * 一覧APIと詳細APIで共通利用する。
 */
class AttendanceRecordResource extends JsonResource
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

            'user_id' => $this->user_id,

            'user' => new UserResource(
                $this->whenLoaded('user')
            ),

            'date' => $this->date?->format('Y-m-d'),

            'clock_in' => $this->clock_in?->format('H:i:s'),

            'clock_out' => $this->clock_out?->format('H:i:s'),

            'total_time' => $this->total_time,

            'total_break_time' => $this->total_break_time,

            'comment' => $this->comment,

            'breaks' => AttendanceBreakResource::collection(
                $this->whenLoaded('attendanceBreaks')
            ),

            'applications' => ApplicationResource::collection(
                $this->whenLoaded('applications')
            ),
        ];
    }
}
