<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class AttendanceRecordResource extends JsonResource
{
    /**
     * リソースを配列、もしくはシリアライズ可能な型式に変換する
     *
     * @param Request $request HTTPリクエストオブジェクト
     * @return array<string, mixed> 整形済みの配列データ、または配列に変換可能なオブジェクト
     */
    public function toArray(Request $request): array|JsonResource|JsonSerializable
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->whenLoaded('user', function () {
                return $this->user->name;
            }),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ];
            }),
            'date' => $this->date?->format('Y-m-d'),
            'clock_in' => $this->clock_in?->format('H:i:s'),
            'clock_out' => $this->clock_out?->format('H:i:s'),
            'total_time' => $this->formatMinutes($this->work_minutes),
            'total_break_time' => $this->formatMinutes($this->break_minutes),
            'comment' => $this->comment,
            'breaks' => $this->whenLoaded('breakRecords', function () {
                return $this->breakRecords->map(function ($breakRecord) {
                    return [
                        'id' => $breakRecord->id,
                        'break_in' => $breakRecord->break_in?->format('H:i:s'),
                        'break_out' => $breakRecord->break_out?->format('H:i:s'),
                    ];
                });
            }),
            'applications' => $this->whenLoaded('attendanceCorrectRequests', function () {
                return $this->attendanceCorrectRequests->map(function ($attendanceCorrectRequest) {
                    return [
                        'id' => $attendanceCorrectRequest->id,
                        'attendance_record_id' => $attendanceCorrectRequest->attendance_record_id,
                        'request_status_id' => $attendanceCorrectRequest->request_status_id,
                        'request_status' => $attendanceCorrectRequest->relationLoaded('requestStatus')
                            ? [
                                'id' => $attendanceCorrectRequest->requestStatus->id,
                                'name' => $attendanceCorrectRequest->requestStatus->name,
                            ]
                            : null,
                        'requested_clock_in' => $attendanceCorrectRequest->requested_clock_in?->format('H:i:s'),
                        'requested_clock_out' => $attendanceCorrectRequest->requested_clock_out?->format('H:i:s'),
                        'break_corrections' => $attendanceCorrectRequest->relationLoaded('breakCorrectRequests')
                            ? $attendanceCorrectRequest->breakCorrectRequests->map(function ($breakCorrectRequest) {
                                return [
                                    'id' => $breakCorrectRequest->id,
                                    'requested_break_in' => $breakCorrectRequest->requested_break_in?->format('H:i:s'),
                                    'requested_break_out' => $breakCorrectRequest->requested_break_out?->format('H:i:s'),
                                ];
                            })
                            : [],
                        'comment' => $attendanceCorrectRequest->comment,
                    ];
                });
            }),
        ];
    }

    /**
     * 分数を HH:MM 形式に変換する。
     */
    private function formatMinutes(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $remainingMinutes);
    }
}
