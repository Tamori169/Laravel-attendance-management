<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceRecord extends Model
{
    /**
     * 一括代入可能な属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
    ];

    /**
     * 属性の型変換定義。
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<BreakRecord, $this>
     */
    public function breakRecords(): HasMany
    {
        return $this->hasMany(BreakRecord::class);
    }

    /**
     * @return HasMany<AttendanceCorrectRequest, $this>
     */
    public function attendanceCorrectRequests(): HasMany
    {
        return $this->hasMany(AttendanceCorrectRequest::class);
    }

    /**
     * 休憩時間の合計を分単位で計算して取得。
     *
     * @return int 休憩時間の合計分数
     */
    public function getBreakMinutesAttribute(): int
    {
        return $this->breakRecords->sum(function ($breakRecord) {
            if (!$breakRecord->break_in || !$breakRecord->break_out) {
                return 0;
            }

            return $breakRecord->break_in->diffInMinutes($breakRecord->break_out);
        });
    }

    /**
     * 勤務時間の合計を分単位で計算して取得。
     *
     * @return int 勤務時間の合計分数
     */
    public function getWorkMinutesAttribute(): int
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        return $this->clock_in->diffInMinutes($this->clock_out) - $this->break_minutes;
    }

    /**
     * 休憩時間をHH:MM形式に変換して表示。
     *
     * getBreakMinutesAttributeで取得された休憩時間を変換する。
     *
     * @return string HH:MM形式に変換された休憩時間
     */
    public function getFormattedBreakTimeAttribute(): string
    {
        $hours = floor($this->break_minutes / 60);
        $minutes = $this->break_minutes % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }

    /**
     * 労働時間をHH:MM形式に変換して表示。
     *
     * getFormattedWorkTimeAttributeで取得された休憩時間を変換する。
     *
     * @return string HH:MM形式に変換された労働時間
     */
    public function getFormattedWorkTimeAttribute(): string
    {
        $hours = floor($this->work_minutes / 60);
        $minutes = $this->work_minutes % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }
}
