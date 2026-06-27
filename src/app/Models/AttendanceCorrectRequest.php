<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class AttendanceCorrectRequest extends Model
{
    /**
     * 一括代入可能な属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attendance_record_id',
        'request_status_id',
        'requested_clock_in',
        'requested_clock_out',
        'comment',
    ];

    /**
     * 属性の型変換定義。
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requested_clock_in' => 'datetime',
        'requested_clock_out' => 'datetime',
    ];

    /**
     * @return BelongsTo<AttendanceRecord, $this>
     */
    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    /**
     * @return HasMany<BreakCorrectRequest, $this>
     */
    public function breakCorrectRequests(): HasMany
    {
        return $this->hasMany(BreakCorrectRequest::class);
    }

    /**
     * @return BelongsTo<RequestStatus, $this>
     */
    public function requestStatus(): BelongsTo
    {
        return $this->belongsTo(RequestStatus::class);
    }
}
