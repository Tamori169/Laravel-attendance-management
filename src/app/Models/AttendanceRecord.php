<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakRecords()
    {
        return $this->hasMany(BreakRecord::class);
    }

    public function attendanceCorrectRequests()
    {
        return $this->hasMany(AttendanceCorrectRequest::class);
    }

    public function getBreakMinutesAttribute()
    {
        return $this->breakRecords->sum(function ($breakRecord) {
            if (!$breakRecord->break_in || !$breakRecord->break_out) {
                return 0;
            }

            return $breakRecord->break_in->diffInMinutes($breakRecord->break_out);
        });
    }

    public function getWorkMinutesAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        return $this->clock_in->diffInMinutes($this->clock_out) - $this->break_minutes;
    }

    public function getFormattedBreakTimeAttribute()
    {
        $hours = floor($this->break_minutes / 60);
        $minutes = $this->break_minutes % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }

    public function getFormattedWorkTimeAttribute()
    {
        $hours = floor($this->work_minutes / 60);
        $minutes = $this->work_minutes % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }
}
