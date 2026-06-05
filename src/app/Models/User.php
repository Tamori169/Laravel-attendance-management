<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
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
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function getAttendanceStatusAttribute()
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
