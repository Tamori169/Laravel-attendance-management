<?php

namespace App\Models;

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

    /**
     * @return HasMany<AttendanceCorrectRequest, $this>
     */
    public function attendanceCorrectRequests(): HasMany
    {
        return $this->hasMany(AttendanceCorrectRequest::class);
    }

    /**
     * 修正申請中のステータスを表示用に変換。
     *
     * @return string 承認待ち、承認済みのいずれか
     */
    public function getLabelAttribute(): string
    {
        return match ($this->name) {
            'pending' => '承認待ち',
            'approved' => '承認済み',
        };
    }
}