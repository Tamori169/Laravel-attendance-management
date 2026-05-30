<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakRecord extends Model
{
    protected $fillable = [
        'attendance_record_id',
        'break_in',
        'break_out',
    ];

    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function breakCorrectRequests()
    {
        return $this->hasMany(BreakCorrectRequest::class);
    }
}
