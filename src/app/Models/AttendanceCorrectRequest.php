<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectRequest extends Model
{
    protected $fillable = [
        'attendance_record_id',
        'request_status_id',
        'requested_clock_in',
        'requested_clock_out',
        'comment',
        'approved_at',
    ];

    protected $casts = [
        'requested_clock_in' => 'datetime',
        'requested_clock_out' => 'datetime',
    ];

    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function breakCorrectRequests()
    {
        return $this->hasMany(BreakCorrectRequest::class);
    }

    public function requestStatus()
    {
        return $this->belongsTo(RequestStatus::class);
    }
}
