<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequestStatus extends Model
{
    /**
     * 一括代入可能な属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    public function getLabelAttribute()
    {
        return match ($this->name) {
            'pending' => '承認待ち',
            'approved' => '承認済み',
        };
    }

    /**
     * @return HasMany<AttendanceCorrectRequest, $this>
     */
    public function attendanceCorrectRequests(): HasMany
    {
        return $this->hasMany(AttendanceCorrectRequest::class);
    }
}