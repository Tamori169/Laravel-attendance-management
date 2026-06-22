<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\AttendanceReportService;
use Illuminate\Http\Request;

class AttendanceReportController extends Controller
{
    public function __construct(
        private AttendanceReportService $attendanceReportService
    ) {}

    public function report()
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
