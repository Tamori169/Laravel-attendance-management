<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakRecord extends Model
{
    /**
     * 一括代入可能な属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attendance_record_id',
        'break_in',
        'break_out',
    ];

    /**
     * 属性の型変換定義。
     *
     * @var array<string, string>
     */
    protected $casts = [
        'break_in' => 'datetime',
        'break_out' => 'datetime',
    ];

    /**
     * @return BelongsTo<AttendanceRecord, $this>
     */
    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }
}
