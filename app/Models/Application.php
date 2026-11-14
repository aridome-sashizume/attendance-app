<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_record_id',
        'new_date',
        'new_clock_in',
        'new_clock_out',
        'comment',
        'approval_status',
    ];

    protected $casts = [
        'new_date' => 'date',
    ];

    /**
     * 申請ユーザー
     *
     * 修正申請を行ったユーザー情報を取得する。
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 対象勤怠情報
     *
     * 修正申請の対象となる勤怠情報を取得する。
     */
    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    /**
     * 修正申請の休憩情報
     */
    public function applicationBreaks(): HasMany
    {
        return $this->hasMany(ApplicationBreak::class);
    }
}
