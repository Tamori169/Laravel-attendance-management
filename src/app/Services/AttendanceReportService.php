<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use Carbon\Carbon;

class AttendanceReportService
{
    /**
     * 勤怠レポートのデータを作成。
     *
     * @param int $userId ログインユーザーのID
     * @return array array<string, int> 勤怠レポートのデータ
     */
    public function make(int $userId): array
    {
        $five_months_ago = now()->subMonths(5)->startOfMonth()->format('Y-m');
        $four_months_ago = now()->subMonths(4)->startOfMonth()->format('Y-m');
        $three_months_ago = now()->subMonths(3)->startOfMonth()->format('Y-m');
        $two_months_ago = now()->subMonths(2)->startOfMonth()->format('Y-m');
        $last_month = now()->subMonths(1)->startOfMonth()->format('Y-m');
        $current_month = now()->startOfMonth()->format('Y-m');

        return[
            'summary' => [
                'six_months_working_minutes' => $this->calculateSixMonthsWorkingMinutes($userId),
                'six_months_overtime_minutes' => $this->calculateSixMonthsOvertimeMinutes($userId),
                'six_months_average_working_minutes' => $this->calculateSixMonthsAverageWorkingMinutes($userId),
            ],

            'monthly_trend' => [
                [
                    'month' => $five_months_ago,
                    'working_minutes' => $this->calculateFiveMonthsAgoWorkingMinutes($userId),
                    'overtime_minutes' => $this->calculateFiveMonthsAgoOvertimeMinutes($userId),
                ],
                [
                    'month' => $four_months_ago,
                    'working_minutes' => $this->calculateFourMonthsAgoWorkingMinutes($userId),
                    'overtime_minutes' => $this->calculateFourMonthsAgoOvertimeMinutes($userId),
                ],
                [
                    'month' => $three_months_ago,
                    'working_minutes' => $this->calculateThreeMonthsAgoWorkingMinutes($userId),
                    'overtime_minutes' => $this->calculateThreeMonthsAgoOvertimeMinutes($userId),
                ],
                [
                    'month' => $two_months_ago,
                    'working_minutes' => $this->calculateTwoMonthsAgoWorkingMinutes($userId),
                    'overtime_minutes' => $this->calculateTwoMonthsAgoOvertimeMinutes($userId),
                ],
                [
                    'month' => $last_month,
                    'working_minutes' => $this->calculateLastMonthWorkingMinutes($userId),
                    'overtime_minutes' => $this->calculateLastMonthOvertimeMinutes($userId),
                ],
                [
                    'month' => $current_month,
                    'working_minutes' => $this->calculateCurrentMonthWorkingMinutes($userId),
                    'overtime_minutes' => $this->calculateCurrentMonthOvertimeMinutes($userId),
                ],
            ],

            'anomalies' => [
                'late_count' => $this->calculateLateCount($userId),
                'early_leave_count' => $this->calculateEarlyLeaveCount($userId),
                'long_working_day_count' => $this->calculateLongWorkingDayCount($userId),
            ],
        ];
    }

    /**
     * 直近6ヶ月間の総労働時間を分単位で集計。
     *
     * このメソッドでは対象期間を指定。
     * 集計処理はcalculateTotalWorkingMinutesにて行う。
     *
     * @param int $userId ログインユーザーのID
     * @return int 直近6ヶ月間の総労働時間（分単位）
     */
    private function calculateSixMonthsWorkingMinutes(int $userId): int
    {
        $startDate = now()->subMonths(5)->startOfMonth();
        $endDate = now()->endOfMonth();

        return $this->calculateTotalWorkingMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    /**
     * 直近6ヶ月間の総残業時間を分単位で集計。
     *
     * このメソッドでは対象期間を指定。
     * 集計処理はcalculateTotalOvertimeMinutesにて行う。
     *
     * @param int $userId ログインユーザーのID
     * @return int 直近6ヶ月間の総残業時間（分単位）
     */
    private function calculateSixMonthsOvertimeMinutes(int $userId): int
    {
        $startDate = now()->subMonths(5)->startOfMonth();
        $endDate = now()->endOfMonth();

        return $this->calculateTotalOvertimeMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    /**
     * 直近6ヶ月間の1日あたりの平均労働時間を分単位で集計。
     *
     * @param int $userId ログインユーザーのID
     * @return int 直近6ヶ月間の1日あたりの平均労働時間（分単位）
     */
    private function calculateSixMonthsAverageWorkingMinutes(int $userId): int
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

    /**
     * 5ヶ月前の総労働時間を分単位で集計。
     *
     * このメソッドでは対象期間を指定。
     * 集計処理はcalculateTotalWorkingMinutesにて行う。
     *
     * @param int $userId ログインユーザーのID
     * @return int 5ヶ月前の総労働時間（分単位）
     */
    private function calculateFiveMonthsAgoWorkingMinutes(int $userId): int
    {
        $startDate = now()->subMonths(5)->startOfMonth();
        $endDate = now()->subMonths(5)->endOfMonth();

        return $this->calculateTotalWorkingMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    /**
     * 5ヶ月前の総残業時間を分単位で集計。
     *
     * このメソッドでは対象期間を指定。
     * 集計処理はcalculateTotalOvertimeMinutesにて行う。
     *
     * @param int $userId ログインユーザーのID
     * @return int 5ヶ月前の総残業時間（分単位）
     */
    private function calculateFiveMonthsAgoOvertimeMinutes(int $userId): int
    {
        $startDate = now()->subMonths(5)->startOfMonth();
        $endDate = now()->subMonths(5)->endOfMonth();

        return $this->calculateTotalOvertimeMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    /**
     * 4ヶ月前の総労働時間を分単位で集計。
     *
     * このメソッドでは対象期間を指定。
     * 集計処理はcalculateTotalWorkingMinutesにて行う。
     *
     * @param int $userId ログインユーザーのID
     * @return int 4ヶ月前の総労働時間（分単位）
     */
    private function calculateFourMonthsAgoWorkingMinutes(int $userId): int
    {
        $startDate = now()->subMonths(4)->startOfMonth();
        $endDate = now()->subMonths(4)->endOfMonth();

        return $this->calculateTotalWorkingMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    /**
     * 4ヶ月前の総残業時間を分単位で集計。
     *
     * このメソッドでは対象期間を指定。
     * 集計処理はcalculateTotalOvertimeMinutesにて行う。
     *
     * @param int $userId ログインユーザーのID
     * @return int 4ヶ月前の総残業時間（分単位）
     */
    private function calculateFourMonthsAgoOvertimeMinutes(int $userId): int
    {
        $startDate = now()->subMonths(4)->startOfMonth();
        $endDate = now()->subMonths(4)->endOfMonth();

        return $this->calculateTotalOvertimeMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    /**
     * 3ヶ月前の総労働時間を分単位で集計。
     *
     * このメソッドでは対象期間を指定。
     * 集計処理はcalculateTotalWorkingMinutesにて行う。
     *
     * @param int $userId ログインユーザーのID
     * @return int 3ヶ月前の総労働時間（分単位）
     */
    private function calculateThreeMonthsAgoWorkingMinutes(int $userId): int
    {
        $startDate = now()->subMonths(3)->startOfMonth();
        $endDate = now()->subMonths(3)->endOfMonth();

        return $this->calculateTotalWorkingMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    /**
     * 3ヶ月前の総残業時間を分単位で集計。
     *
     * このメソッドでは対象期間を指定。
     * 集計処理はcalculateTotalOvertimeMinutesにて行う。
     *
     * @param int $userId ログインユーザーのID
     * @return int 3ヶ月前の総残業時間（分単位）
     */
    private function calculateThreeMonthsAgoOvertimeMinutes(int $userId): int
    {
        $startDate = now()->subMonths(3)->startOfMonth();
        $endDate = now()->subMonths(3)->endOfMonth();

        return $this->calculateTotalOvertimeMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    /**
     * 2ヶ月前の総労働時間を分単位で集計。
     *
     * このメソッドでは対象期間を指定。
     * 集計処理はcalculateTotalWorkingMinutesにて行う。
     *
     * @param int $userId ログインユーザーのID
     * @return int 2ヶ月前の総労働時間（分単位）
     */
    private function calculateTwoMonthsAgoWorkingMinutes(int $userId): int
    {
        $startDate = now()->subMonths(2)->startOfMonth();
        $endDate = now()->subMonths(2)->endOfMonth();

        return $this->calculateTotalWorkingMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    /**
     * 2ヶ月前の総残業時間を分単位で集計。
     *
     * このメソッドでは対象期間を指定。
     * 集計処理はcalculateTotalOvertimeMinutesにて行う。
     *
     * @param int $userId ログインユーザーのID
     * @return int 2ヶ月前の総残業時間（分単位）
     */
    private function calculateTwoMonthsAgoOvertimeMinutes(int $userId): int
    {
        $startDate = now()->subMonths(2)->startOfMonth();
        $endDate = now()->subMonths(2)->endOfMonth();

        return $this->calculateTotalOvertimeMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    /**
     * 先月の総労働時間を分単位で集計。
     *
     * このメソッドでは対象期間を指定。
     * 集計処理はcalculateTotalWorkingMinutesにて行う。
     *
     * @param int $userId ログインユーザーのID
     * @return int 先月の総労働時間（分単位）
     */
    private function calculateLastMonthWorkingMinutes(int $userId): int
    {
        $startDate = now()->subMonth()->startOfMonth();
        $endDate = now()->subMonth()->endOfMonth();

        return $this->calculateTotalWorkingMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    /**
     * 先月の総残業時間を分単位で集計。
     *
     * このメソッドでは対象期間を指定。
     * 集計処理はcalculateTotalOvertimeMinutesにて行う。
     *
     * @param int $userId ログインユーザーのID
     * @return int 先月の総残業時間（分単位）
     */
    private function calculateLastMonthOvertimeMinutes(int $userId): int
    {
        $startDate = now()->subMonth()->startOfMonth();
        $endDate = now()->subMonth()->endOfMonth();

        return $this->calculateTotalOvertimeMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    /**
     * 当月の総労働時間を分単位で集計。
     *
     * このメソッドでは対象期間を指定。
     * 集計処理はcalculateTotalWorkingMinutesにて行う。
     *
     * @param int $userId ログインユーザーのID
     * @return int 当月の総労働時間（分単位）
     */
    private function calculateCurrentMonthWorkingMinutes(int $userId): int
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        return $this->calculateTotalWorkingMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    /**
     * 当月の総残業時間を分単位で集計。
     *
     * このメソッドでは対象期間を指定。
     * 集計処理はcalculateTotalOvertimeMinutesにて行う。
     *
     * @param int $userId ログインユーザーのID
     * @return int 当月の総残業時間（分単位）
     */
    private function calculateCurrentMonthOvertimeMinutes(int $userId): int
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        return $this->calculateTotalOvertimeMinutes(
            $userId,
            $startDate,
            $endDate,
        );
    }

    /**
     * 当月の遅刻回数を集計。
     *
     * @param int $userId ログインユーザーのID
     * @return int 当月の遅刻回数
     */
    private function calculateLateCount(int $userId): int
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        return AttendanceRecord::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('clock_in')
            ->whereTime('clock_in', '>', '09:00:00')
            ->count();
    }

    /**
     * 当月の早退回数を集計。
     *
     * @param int $userId ログインユーザーのID
     * @return int 当月の早退回数
     */
    private function calculateEarlyLeaveCount(int $userId): int
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        return AttendanceRecord::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('clock_out')
            ->whereTime('clock_out', '<', '18:00:00')
            ->count();
    }

    /**
     * 当月の長時間労働日数を集計。
     *
     * @param int $userId ログインユーザーのID
     * @return int 当月の長時間労働日数
     */
    private function calculateLongWorkingDayCount(int $userId): int
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        $attendanceRecords = AttendanceRecord::with('breakRecords')
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('clock_in')
            ->whereNotNull('clock_out')
            ->get();

        return $attendanceRecords->filter(function ($attendanceRecord): bool {
            $breakMinutes = $attendanceRecord->breakRecords
                ->filter(fn($breakRecord) => $breakRecord->break_in && $breakRecord->break_out)
                ->sum(fn($breakRecord) => $breakRecord->break_in->diffInMinutes($breakRecord->break_out));

            $attendanceMinutes = $attendanceRecord->clock_in->diffInMinutes($attendanceRecord->clock_out);

            $workingMinutes = $attendanceMinutes - $breakMinutes;

            return $workingMinutes > 600;
        })->count();
    }

    /**
     * 総労働時間を分単位で集計。
     *
     * 対象期間は別メソッドにて指定。
     * このメソッドで集計処理を行い、指定元のメソッドに返す。
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
     * 対象期間は別メソッドにて指定。
     * このメソッドで集計処理を行い、指定元のメソッドに返す。
     *
     * @param int $userId ログインユーザーのID
     * @return int 総残業時間（分単位）
     */
    private function calculateTotalOvertimeMinutes(int $userId, Carbon $startDate, Carbon $endDate): int
    {
        $totalOvertimeMinutes = AttendanceRecord::with('breakRecords')
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereTime('clock_out', '>', '18:00:00')
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