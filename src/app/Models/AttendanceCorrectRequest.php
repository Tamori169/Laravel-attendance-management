<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectRequest extends Model
{
    protected $fillable = [
        'attendance_record_id',
        'status_id',
        'requested_clock_in',
        'requested_clock_out',
        'comment',
        'approved_at',
    ];
}
