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

    public function breakRecords()
    {
        return $this->hasMany(BreakRecord::class);
    }

    public function attendanceCorrectRequests()
    {
        return $this->hasMany(AttendanceCorrectRequest::class);
    }
}
