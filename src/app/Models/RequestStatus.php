<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class RequestStatus extends Model
{
    protected $fillable = [
        'name',
    ];

    protected function label()
    {
        return Attribute::get(function () {
            return match ($this->name) {
                'pending' => '承認待ち',
                'approved' => '承認済み',
            };
        });
    }

    public function attendanceCorrectRequests()
    {
        return $this->hasMany(AttendanceCorrectRequest::class);
    }
}