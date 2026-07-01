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
     * 集計処理はsrc/app/Services/AttendanceReportService.phpの各メソッドにて行う。
     *
     * @return View マイ勤怠レポート画面のビュー
     */
    public function report(): View
    {
        $userId = auth()->id();

        $reports = $this->attendanceReportService->make($userId);

        return view('staff.attendances.report', compact('reports'));
    }
}
