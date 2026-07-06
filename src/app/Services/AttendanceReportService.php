<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use Carbon\Carbon;

class AttendanceReportService
{
    private const STANDARD_CLOCK_IN = '09:00:00';
    private const STANDARD_CLOCK_OUT = '18:00:00';
    private const LONG_WORKING_DAY_MINUTES = 600;
    private const MONTHLY_TREND_MONTHS = 6;

    /**
     * 勤怠レポートのデータを作成。
     *
     * @param int $userId ログインユーザーのID
     * @return array array<string, int> 勤怠レポートのデータ
     */
    public function make(int $userId): array
    {
        return [
            'summary' => $this->makeSummary($userId),
            'monthly_trend' => $this->makeMonthlyTrend($userId),
            'anomalies' => $this->makeAnomalies($userId),
        ];
    }

    /**
     * 直近6ヶ月間の総労働時間および総残業時間、平均労働時間を分単位で集計。
     *
     * @param int $userId ログインユーザーのID
     * @return array 直近6ヶ月間の総労働時間および総残業時間、平均労働時間（分単位）
     */
    private function makeSummary(int $userId): array
    {
        $startDate = now()->subMonths(5)->startOfMonth();
        $endDate = now()->endOfMonth();

        $sixMonthsWorkingMinutes = $this->calculateTotalWorkingMinutes($userId,$startDate,$endDate);

        $sixMonthsOvertimeMinutes = $this->calculateTotalOvertimeMinutes($userId,$startDate,$endDate);

        $totalWorkingDays = AttendanceRecord::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('clock_in')
            ->whereNotNull('clock_out')
            ->count();

        $sixMonthsAverageWorkingMinutes = 0;

        if ($totalWorkingDays > 0) {
            $sixMonthsAverageWorkingMinutes = round($sixMonthsWorkingMinutes / $totalWorkingDays);
        }

        return [
            'six_months_working_minutes' => $sixMonthsWorkingMinutes,
            'six_months_overtime_minutes' => $sixMonthsOvertimeMinutes,
            'six_months_average_working_minutes' => $sixMonthsAverageWorkingMinutes,
        ];
    }

    /**
     * 直近6ヶ月について、各月の総労働時間および総残業時間を分単位で集計。
     *
     * @param int $userId ログインユーザーのID
     * @return array 直近6ヶ月の総労働時間および総残業時間（分単位）
     */
    private function makeMonthlyTrend(int $userId): array
    {
        $monthlyTrend = [];

        for ($monthsAgo = self::MONTHLY_TREND_MONTHS - 1; $monthsAgo >= 0; $monthsAgo--) {
            $month = now()->subMonths($monthsAgo);
            $startDate = $month->copy()->startOfMonth();
            $endDate = $month->copy()->endOfMonth();

            $monthlyTrend[] = [
                'month' => $month->format('Y-m'),
                'working_minutes' => $this->calculateTotalWorkingMinutes($userId, $startDate, $endDate),
                'overtime_minutes' => $this->calculateTotalOvertimeMinutes($userId, $startDate, $endDate),
            ];
        }

        return $monthlyTrend;
    }

    /**
     * 当月の遅刻回数および早退回数、長時間労働日数を集計。
     *
     * @param int $userId ログインユーザーのID
     * @return array 当月の遅刻回数、早退回数、長時間労働日数
     */
    private function makeAnomalies(int $userId): array
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        $lateCount = AttendanceRecord::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('clock_in')
            ->whereTime('clock_in', '>', self::STANDARD_CLOCK_IN)
            ->count();

        $earlyLeaveCount = AttendanceRecord::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('clock_out')
            ->whereTime('clock_out', '<', self::STANDARD_CLOCK_OUT)
            ->count();

        $longWorkingDayCount = AttendanceRecord::with('breakRecords')
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('clock_in')
            ->whereNotNull('clock_out')
            ->get()
            ->filter(function ($attendanceRecord): bool {
            $breakMinutes = $attendanceRecord->breakRecords
                ->filter(fn($breakRecord) => $breakRecord->break_in && $breakRecord->break_out)
                ->sum(fn($breakRecord) => $breakRecord->break_in->diffInMinutes($breakRecord->break_out));

            $attendanceMinutes = $attendanceRecord->clock_in->diffInMinutes($attendanceRecord->clock_out);

            return ($attendanceMinutes - $breakMinutes) > self::LONG_WORKING_DAY_MINUTES;
        })->count();

        return [
            'late_count' => $lateCount,
            'early_leave_count' => $earlyLeaveCount,
            'long_working_day_count' => $longWorkingDayCount,
        ];
    }

    /**
     * 総労働時間を分単位で集計。
     *
     * @param int $userId ログインユーザーのID
     * @return int 総労働時間（分単位）
     */
    private function calculateTotalWorkingMinutes(int $userId, Carbon $startDate, Carbon $endDate): int
    {
        $totalWorkingMinutes = AttendanceRecord::with('breakRecords')
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->filter(fn($attendanceRecord) => $attendanceRecord->clock_in && $attendanceRecord->clock_out)
            ->map(function ($attendanceRecord): int {
                $breakMinutes = $attendanceRecord->breakRecords
                    ->filter(fn($breakRecord) => $breakRecord->break_in && $breakRecord->break_out)
                    ->sum(fn($breakRecord) => $breakRecord->break_in->diffInMinutes($breakRecord->break_out));

                return $attendanceRecord->clock_in->diffInMinutes($attendanceRecord->clock_out) - $breakMinutes;
            })
            ->sum();

        return $totalWorkingMinutes;
    }

    /**
     * 総残業時間を分単位で集計。
     *
     * @param int $userId ログインユーザーのID
     * @return int 総残業時間（分単位）
     */
    private function calculateTotalOvertimeMinutes(int $userId, Carbon $startDate, Carbon $endDate): int
    {
        $totalOvertimeMinutes = AttendanceRecord::with('breakRecords')
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereTime('clock_out', '>', self::STANDARD_CLOCK_OUT)
            ->get()
            ->filter(fn($attendanceRecord) => $attendanceRecord->clock_in && $attendanceRecord->clock_out)
            ->map(function ($attendanceRecord): int {
                $overtimeStart = $attendanceRecord->clock_out->copy()->setTime(18, 0);

                if ($attendanceRecord->clock_in->gt($overtimeStart)) {
                    $overtimeStart = $attendanceRecord->clock_in;
                }

                $breakMinutes = $attendanceRecord->breakRecords
                    ->filter(fn($breakRecord) => $breakRecord->break_in && $breakRecord->break_out)
                    ->map(function ($breakRecord) use ($overtimeStart): int {
                        $breakStart = $breakRecord->break_in->gt($overtimeStart)
                            ? $breakRecord->break_in
                            : $overtimeStart;

                        return $breakRecord->break_out->lte($breakStart)
                            ? 0
                            : $breakStart->diffInMinutes($breakRecord->break_out);
                    })
                    ->sum();

                return $overtimeStart->diffInMinutes($attendanceRecord->clock_out) - $breakMinutes;
            })
            ->sum();

        return $totalOvertimeMinutes;
    }
}