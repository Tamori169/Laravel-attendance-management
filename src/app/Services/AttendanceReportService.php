<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use Carbon\Carbon;

class AttendanceReportService
{
    public function make($userId)
    {
        return[
            'six_months_working_minutes' => $this->calculateSixMonthsWorkingMinutes($userId),
            'six_months_overtime_minutes' => $this->calculateSixMonthsOvertimeMinutes($userId),
            'six_months_average_working_minutes' => $this->calculateSixMonthsAverageWorkingMinutes($userId),
            'five_month_ago_working_minutes' => $this->calculateFiveMonthsAgoWorkingMinutes($userId),
            'five_month_ago_overtime_minutes' => $this->calculateFiveMonthsAgoOvertimeMinutes($userId),
            'four_month_ago_working_minutes' => $this->calculateFourMonthsAgoWorkingMinutes($userId),
            'four_month_ago_overtime_minutes' => $this->calculateFourMonthsAgoOvertimeMinutes($userId),
            'three_month_ago_working_minutes' => $this->calculateThreeMonthsAgoWorkingMinutes($userId),
            'three_month_ago_overtime_minutes' => $this->calculateThreeMonthsAgoOvertimeMinutes($userId),
            'two_month_ago_working_minutes' => $this->calculateTwoMonthsAgoWorkingMinutes($userId),
            'two_month_ago_overtime_minutes' => $this->calculateTwoMonthsAgoOvertimeMinutes($userId),
            'last_month_working_minutes' => $this->calculateLastMonthWorkingMinutes($userId),
            'last_month_overtime_minutes' => $this->calculateLastMonthOvertimeMinutes($userId),
            'current_month_working_minutes' => $this->calculateCurrentMonthWorkingMinutes($userId),
            'current_month_overtime_minutes' => $this->calculateCurrentMonthOvertimeMinutes($userId),
            'late_count' => $this->calculateLateCount($userId),
            'early_leave_count' => $this->calculateEarlyLeaveCount($userId),
            'long_working_day_count' => $this->calculateLongWorkingDayCount($userId),
        ];
    }

    private function calculateSixMonthsWorkingMinutes($userId)
    {
        $startDate = now()->subMonths(5)->startOfMonth();
        $endDate = now()->endOfMonth();

        return $this->calculateTotalWorkingMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    private function calculateSixMonthsOvertimeMinutes($userId)
    {
        $startDate = now()->subMonths(5)->startOfMonth();
        $endDate = now()->endOfMonth();

        return $this->calculateTotalOvertimeMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    private function calculateSixMonthsAverageWorkingMinutes($userId)
    {
        $startDate = now()->subMonths(5)->startOfMonth();
        $endDate = now()->endOfMonth();

        $totalWorkingMinutes = $this->calculateTotalWorkingMinutes(
            $userId,
            $startDate,
            $endDate,
        );

        $totalWorkingDays = AttendanceRecord::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('clock_in')
            ->whereNotNull('clock_out')
            ->count();

        $averageWorkingMinutes = $totalWorkingMinutes/ $totalWorkingDays;

        return round($averageWorkingMinutes);
    }

    private function calculateFiveMonthsAgoWorkingMinutes($userId)
    {
        $startDate = now()->subMonths(5)->startOfMonth();
        $endDate = now()->subMonths(5)->endOfMonth();

        return $this->calculateTotalWorkingMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    private function calculateFiveMonthsAgoOvertimeMinutes($userId)
    {
        $startDate = now()->subMonths(5)->startOfMonth();
        $endDate = now()->subMonths(5)->endOfMonth();

        return $this->calculateTotalOvertimeMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    private function calculateFourMonthsAgoWorkingMinutes($userId)
    {
        $startDate = now()->subMonths(4)->startOfMonth();
        $endDate = now()->subMonths(4)->endOfMonth();

        return $this->calculateTotalWorkingMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    private function calculateFourMonthsAgoOvertimeMinutes($userId)
    {
        $startDate = now()->subMonths(4)->startOfMonth();
        $endDate = now()->subMonths(4)->endOfMonth();

        return $this->calculateTotalOvertimeMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    private function calculateThreeMonthsAgoWorkingMinutes($userId)
    {
        $startDate = now()->subMonths(3)->startOfMonth();
        $endDate = now()->subMonths(3)->endOfMonth();

        return $this->calculateTotalWorkingMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    private function calculateThreeMonthsAgoOvertimeMinutes($userId)
    {
        $startDate = now()->subMonths(3)->startOfMonth();
        $endDate = now()->subMonths(3)->endOfMonth();

        return $this->calculateTotalOvertimeMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    private function calculateTwoMonthsAgoWorkingMinutes($userId)
    {
        $startDate = now()->subMonths(2)->startOfMonth();
        $endDate = now()->subMonths(2)->endOfMonth();

        return $this->calculateTotalWorkingMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    private function calculateTwoMonthsAgoOvertimeMinutes($userId)
    {
        $startDate = now()->subMonths(2)->startOfMonth();
        $endDate = now()->subMonths(2)->endOfMonth();

        return $this->calculateTotalOvertimeMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    private function calculateLastMonthWorkingMinutes($userId)
    {
        $startDate = now()->subMonth()->startOfMonth();
        $endDate = now()->subMonth()->endOfMonth();

        return $this->calculateTotalWorkingMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    private function calculateLastMonthOvertimeMinutes($userId)
    {
        $startDate = now()->subMonth()->startOfMonth();
        $endDate = now()->subMonth()->endOfMonth();

        return $this->calculateTotalOvertimeMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    private function calculateCurrentMonthWorkingMinutes($userId)
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        return $this->calculateTotalWorkingMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    private function calculateCurrentMonthOvertimeMinutes($userId)
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        return $this->calculateTotalOvertimeMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    private function calculateLateCount($userId)
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        return AttendanceRecord::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('clock_in')
            ->whereTime('clock_in', '>', '09:00:00')
            ->count();
    }

    private function calculateEarlyLeaveCount($userId)
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        return AttendanceRecord::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('clock_out')
            ->whereTime('clock_out', '<', '18:00:00')
            ->count();
    }

    private function calculateLongWorkingDayCount($userId)
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        $attendanceRecords = AttendanceRecord::with('breakRecords')
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('clock_in')
            ->whereNotNull('clock_out')
            ->get();

        return $attendanceRecords->filter(function ($attendanceRecord) {
            $breakMinutes = $attendanceRecord->breakRecords->sum(
                function ($breakRecord) {
                    if (
                        !$breakRecord->break_in ||
                        !$breakRecord->break_out
                    ) {
                        return 0;
                    }

                    return $breakRecord->break_in->diffInMinutes(
                        $breakRecord->break_out
                    );
                }
            );

            $attendanceMinutes = $attendanceRecord->clock_in->diffInMinutes(
                $attendanceRecord->clock_out
            );

            $workingMinutes = $attendanceMinutes - $breakMinutes;

            return $workingMinutes > 600;
        })->count();
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

                return $attendanceMinutes - $breakMinutes;
            }
        );

        return $totalWorkingMinutes;
    }

    private function calculateTotalOvertimeMinutes(int $userId, Carbon $startDate, Carbon $endDate)
    {
        $attendanceRecords = AttendanceRecord::with('breakRecords')
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereTime('clock_out', '>', '18:00:00')
            ->get();

        $totalOvertimeMinutes = $attendanceRecords->sum(
            function ($attendanceRecord) {
                $overtimeStart = $attendanceRecord->clock_out
                    ->copy()
                    ->setTime(18, 0);

                if ($attendanceRecord->clock_in->gt($overtimeStart)) {
                    $overtimeStart = $attendanceRecord->clock_in;
                }

                $breakMinutes = $attendanceRecord->breakRecords->sum(
                    function ($breakRecord) use ($overtimeStart, $attendanceRecord) {
                        if (
                            !$breakRecord->break_in ||
                            !$breakRecord->break_out
                        ) {
                            return 0;
                        }

                        $breakStart = $breakRecord->break_in->gt($overtimeStart)
                            ? $breakRecord->break_in
                            : $overtimeStart;

                        $breakEnd = $breakRecord->break_out;

                        if ($breakEnd->lte($breakStart)) {
                            return 0;
                        }

                        return $breakStart->diffInMinutes($breakEnd);
                    }
                );

                $overtimeEnd = $attendanceRecord->clock_out;

                $overtimeMinutes = $overtimeStart->diffInMinutes($overtimeEnd);

                return $overtimeMinutes - $breakMinutes;
            }
        );

        return $totalOvertimeMinutes;
    }
}