<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\AttendanceReportService;
use Illuminate\Contracts\View\View;

class AttendanceReportController extends Controller
{
    public function __construct(
        private AttendanceReportService $attendanceReportService
    ) {}

    /**
     * マイ勤怠レポート画面を表示。
     *
     * ログインユーザーの当月を含む過去6ヶ月分の勤怠情報を集計しサマリー。
     * 集計結果をマイ勤怠レポート画面に表示。
     *
     * @return View マイ勤怠レポート画面のビュー
     */
    public function report(): View
    {
        $userId = auth()->id();

        $reports = $this->attendanceReportService->make($userId);

        $months = [
            'five_months_ago' => now()->subMonths(5)->startOfMonth()->format('Y-m'),
            'four_months_ago' => now()->subMonths(4)->startOfMonth()->format('Y-m'),
            'three_months_ago' => now()->subMonths(3)->startOfMonth()->format('Y-m'),
            'two_months_ago' => now()->subMonths(2)->startOfMonth()->format('Y-m'),
            'last_month' => now()->subMonths(1)->startOfMonth()->format('Y-m'),
            'current_month' => now()->startOfMonth()->format('Y-m'),
        ];

        return view('staff.attendances.report', compact('reports', 'months'));
    }
}
