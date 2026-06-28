<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 一括代入可能な属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * シリアライズ時に非表示にする属性。
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * 属性の型を変換する。
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * @return HasMany<AttendanceRecord, $this>
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * 勤怠ステータスを取得。
     *
     * @return string 勤務外、出勤中、休憩中、退勤済のいずれか
     */
    public function getAttendanceStatusAttribute(): string
    {
        $attendanceRecord = AttendanceRecord::where('user_id', $this->id)
            ->whereDate('date', today('Asia/Tokyo'))
            ->first();

        $hasAttendanceRecord = AttendanceRecord::where('user_id', $this->id)
            ->whereDate('date', today('Asia/Tokyo'))
            ->exists();

        $hasFinishedWork = AttendanceRecord::where('user_id', $this->id)
            ->whereDate('date', today('Asia/Tokyo'))
            ->whereNotNull('clock_in')
            ->whereNotNull('clock_out')
            ->exists();

        $isOnBreak = $hasAttendanceRecord &&
            $attendanceRecord->breakRecords()
            ->whereNotNull('break_in')
            ->whereNull('break_out')
            ->exists();

        if (!$hasAttendanceRecord) {
            return '勤務外';
        }

        if ($hasFinishedWork) {
            return '退勤済';
        }

        if ($isOnBreak) {
            return '休憩中';
        }

        return '出勤中';
    }
}
