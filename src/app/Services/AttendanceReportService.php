<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;

class AttendanceReportService
{
    public function make($user)
    {
        return[
            'six_months_working_hours' => $this->calculateSixMonthsWorkingHours($user),
            'six_months_overtime_hours' => $this->calculateSixMonthsOvertimeHours($user),
            'six_months_average_working_hours' => $this->calculateSixMonthsAverageWorkingHours($user),
            'five_month_ago_working_hours' => $this->calculateFiveMonthAgoWorkingHours($user),
            'five_month_ago_overtime_hours' => $this->calculateFiveMonthAgoOvertimeHours($user),
            'four_month_ago_working_hours' => $this->calculateFourMonthAgoWorkingHours($user),
            'four_month_ago_overtime_hours' => $this->calculateFourMonthAgoOvertimeHours($user),
            'three_month_ago_working_hours' => $this->calculateThreeMonthAgoWorkingHours($user),
            'three_month_ago_overtime_hours' => $this->calculateThreeMonthAgoOvertimeHours($user),
            'two_month_ago_working_hours' => $this->calculateTwoMonthAgoWorkingHours($user),
            'two_month_ago_overtime_hours' => $this->calculateTwoMonthAgoOvertimeHours($user),
            'previous_month_working_hours' => $this->calculatePreviousMonthWorkingHours($user),
            'previous_month_overtime_hours' => $this->calculatePreviousMonthOvertimeHours($user),
            'current_month_working_hours' => $this->calculateCurrentMonthWorkingHours($user),
            'current_month_overtime_hours' => $this->calculateCurrentMonthOvertimeHours($user),
            'late_count' => $this->calculateLateCount($user),
            'early_leave_count' => $this->calculateEarlyLeaveCount($user),
            'long_working_hours_count' => $this->calculateLongWorkingHoursCount($user),
        ];
    }

    private function calculateTotalWorkingMinutes(int $userId, Carbon $startDate, Carbon $endDate)
    {
        $attendanceRecords = AttendanceRecord::with('breakRecords')
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $totalWorkingMinutes = $attendanceRecords->sum(
            function ($attendanceRecord) {
                if (!$attendanceRecord->clock_in ||!$attendanceRecord->clock_out) {
                    return 0;
                }

                $breakMinutes = $attendanceRecord->breakRecords->sum(
                    function ($breakRecord) {
                        if (!$breakRecord->break_in ||!$breakRecord->break_out) {
                            return 0;
                        }

                        return $breakRecord->break_in->diffInMinutes($breakRecord->break_out);
                    }
                );

                $attendanceMinutes =$attendanceRecord->clock_in->diffInMinutes($attendanceRecord->clock_out);

                $workingMinutes = $attendanceMinutes - $breakMinutes;

            }
        );

        return $totalWorkingMinutes;
    }
}