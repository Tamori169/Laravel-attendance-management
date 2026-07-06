<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakCorrectRequest extends Model
{
    /**
     * 一括代入可能な属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attendance_correct_request_id',
        'requested_break_in',
        'requested_break_out',
    ];

    /**
     * 属性の型変換定義。
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requested_break_in' => 'datetime',
        'requested_break_out' => 'datetime',
    ];

    /**
     * @return BelongsTo<AttendanceCorrectRequest, $this>
     */
    public function attendanceCorrectRequest(): BelongsTo
    {
        return $this->belongsTo(AttendanceCorrectRequest::class);
    }
}
