<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use Carbon\Carbon;

class AttendanceReportService
{
    private const STANDARD_CLOCK_IN = '09:00:00';
    private const STANDARD_CLOCK_OUT = '18:00:00';
    private const STANDARD_WORKING_MINUTES = 480;
    private const LONG_WORKING_DAY_MINUTES = 600;
    private const MONTHLY_TREND_MONTHS = 6;

    /**
     * 勤怠レポートデータを作成。
     *
     * @param int $userId ログインユーザーのID
     * @return array<string, mixed> 勤怠レポートのデータ
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
     * サマリー用データを集計。
     *
     * @param int $userId ログインユーザーのID
     * @return array<string, int> 直近6ヶ月間の総労働時間および総残業時間、平均労働時間（分単位）
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
     * 月次トレンド用データを集計。
     *
     * @param int $userId ログインユーザーのID
     * @return array<int, array{month: string, working_minutes: int, overtime_minutes: int}> 直近6ヶ月の単月ごとの総労働時間、総残業時間間（分単位）
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
     * 異常検知用データを集計。
     *
     * @param int $userId ログインユーザーのID
     * @return array<string, int> 当月の遅刻回数、早退回数、長時間労働日数
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
     * @param Carbon $startDate 集計開始日
     * @param Carbon $endDate 集計終了日
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
     * @param Carbon $startDate 集計開始日
     * @param Carbon $endDate 集計終了日
     * @return int 総残業時間（分単位）
     */
    private function calculateTotalOvertimeMinutes(int $userId, Carbon $startDate, Carbon $endDate): int
    {
        return AttendanceRecord::with('breakRecords')
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->filter(fn($attendanceRecord) => $attendanceRecord->clock_in && $attendanceRecord->clock_out)
            ->map(function ($attendanceRecord): int {
                $breakMinutes = $attendanceRecord->breakRecords
                    ->filter(fn($breakRecord) => $breakRecord->break_in && $breakRecord->break_out)
                    ->map(fn($breakRecord): int => $breakRecord->break_in->diffInMinutes($breakRecord->break_out))
                    ->sum();

                $workingMinutes = $attendanceRecord->clock_in->diffInMinutes($attendanceRecord->clock_out) - $breakMinutes;

                return max(0, $workingMinutes - self::STANDARD_WORKING_MINUTES);
            })
            ->sum();
    }
}