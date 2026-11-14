<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'new_break_in',
        'new_break_out',
    ];

    /**
     * 勤怠修正申請情報
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
