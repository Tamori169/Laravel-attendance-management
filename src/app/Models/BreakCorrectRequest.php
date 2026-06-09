<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakCorrectRequest extends Model
{
    protected $fillable = [
        'attendance_correct_request_id',
        'break_record_id',
        'requested_break_in',
        'requested_break_out',
    ];

    protected $casts = [
        'requested_break_in' => 'datetime',
        'requested_break_out' => 'datetime',
    ];

    public function breakRecord()
    {
        return $this->belongsTo(BreakRecord::class);
    }

    public function attendanceCorrectRequest()
    {
        return $this->belongsTo(AttendanceCorrectRequest::class);
    }
}
