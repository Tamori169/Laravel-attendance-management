@extends('layouts.staff.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff/attendances/report.css') }}">
@endsection

@section('content')
<div class="attendance-report">
    <div class="attendance-report__heading">
        <h2 class="attendance-report__heading-text">
            マイ勤怠レポート
        </h2>
    </div>
    <div class="attendance-report__description">
        <p class="attendance-report__description-text">
            過去6ヶ月間の勤怠データから集計しています。
        </p>
    </div>
    <!-- 基本サマリー -->
    <div class="attendance-report__summary">
        <h3 class="attendance-report__summary-title">
            基本サマリー
        </h3>
        <div class="attendance-report__summary-content">
            <div class="attendance-report__summary-wrapper">
                <span class="attendance-report__summary-label">総労働時間</span>
                <span class="attendance-report__summary-value">
                    {{ intdiv($reports['six_months_working_minutes'], 60) }}h
                    {{ $reports['six_months_working_minutes'] % 60 }}m
                </span>
            </div>
            <div class="attendance-report__summary-wrapper">
                <span class="attendance-report__summary-label">総残業時間</span>
                <span class="attendance-report__summary-value">
                    {{ intdiv($reports['six_months_overtime_minutes'], 60) }}h
                    {{ $reports['six_months_overtime_minutes'] % 60 }}m
                </span>
            </div>
            <div class="attendance-report__summary-wrapper">
                <span class="attendance-report__summary-label">平均労働時間/日</span>
                <span class="attendance-report__summary-value">
                    {{ intdiv($reports['six_months_average_working_minutes'], 60) }}h
                    {{ $reports['six_months_average_working_minutes'] % 60 }}m
                </span>
            </div>
        </div>
    </div>
    <!-- 月次推移 -->
    <div class="attendance-report__trend">
        <h3 class="attendance-report__trend-title">
            月次推移（過去6ヶ月）
        </h3>
        <table class="attendance-trend__table">
            <tr class="attendance-trend__table-row">
                <th class="attendance-trend__table-header">月</th>
                <th class="attendance-trend__table-header">労働時間</th>
                <th class="attendance-trend__table-header">残業時間</th>
            </tr>
            <tr class="attendance-trend__table-row">
                <td class="attendance-trend__table-description">
                    <time datetime="{{ $months['five_months_ago'] }}">
                        {{ $months['five_months_ago'] }}
                    </time>
                </td>
                <td class="attendance-trend__table-description">
                    {{ intdiv($reports['five_month_ago_working_minutes'], 60) }}h
                    {{ $reports['five_month_ago_working_minutes'] % 60 }}m
                </td>
                <td class="attendance-trend__table-description">
                    {{ intdiv($reports['five_month_ago_overtime_minutes'], 60) }}h
                    {{ $reports['five_month_ago_overtime_minutes'] % 60 }}m
                </td>
            </tr>
            <tr class="attendance-trend__table-row">
                <td class="attendance-trend__table-description">
                    <time datetime="{{ $months['five_months_ago'] }}">
                        {{ $months['four_months_ago'] }}
                    </time>
                </td>
                <td class="attendance-trend__table-description">
                    {{ intdiv($reports['four_month_ago_working_minutes'], 60) }}h
                    {{ $reports['four_month_ago_working_minutes'] % 60 }}m
                </td>
                <td class="attendance-trend__table-description">
                    {{ intdiv($reports['four_month_ago_overtime_minutes'], 60) }}h
                    {{ $reports['four_month_ago_overtime_minutes'] % 60 }}m
                </td>
            </tr>
            <tr class="attendance-trend__table-row">
                <td class="attendance-trend__table-description">
                    <time datetime="{{ $months['five_months_ago'] }}">
                        {{ $months['three_months_ago'] }}
                    </time>
                </td>
                <td class="attendance-trend__table-description">
                    {{ intdiv($reports['three_month_ago_working_minutes'], 60) }}h
                    {{ $reports['three_month_ago_working_minutes'] % 60 }}m
                </td>
                <td class="attendance-trend__table-description">
                    {{ intdiv($reports['three_month_ago_overtime_minutes'], 60) }}h
                    {{ $reports['three_month_ago_overtime_minutes'] % 60 }}m
                </td>
            </tr>
            <tr class="attendance-trend__table-row">
                <td class="attendance-trend__table-description">
                    <time datetime="{{ $months['five_months_ago'] }}">
                        {{ $months['two_months_ago'] }}
                    </time>
                </td>
                <td class="attendance-trend__table-description">
                    {{ intdiv($reports['two_month_ago_working_minutes'], 60) }}h
                    {{ $reports['two_month_ago_working_minutes'] % 60 }}m
                </td>
                <td class="attendance-trend__table-description">
                    {{ intdiv($reports['two_month_ago_overtime_minutes'], 60) }}h
                    {{ $reports['two_month_ago_overtime_minutes'] % 60 }}m
                </td>
            </tr>
            <tr class="attendance-trend__table-row">
                <td class="attendance-trend__table-description">
                    <time datetime="{{ $months['five_months_ago'] }}">
                        {{ $months['last_month'] }}
                    </time>
                </td>
                <td class="attendance-trend__table-description">
                    {{ intdiv($reports['last_month_working_minutes'], 60) }}h
                    {{ $reports['last_month_working_minutes'] % 60 }}m
                </td>
                <td class="attendance-trend__table-description">
                    {{ intdiv($reports['last_month_overtime_minutes'], 60) }}h
                    {{ $reports['last_month_overtime_minutes'] % 60 }}m
                </td>
            </tr>
            <tr class="attendance-trend__table-row">
                <td class="attendance-trend__table-description">
                    <time datetime="{{ $months['five_months_ago'] }}">
                        {{ $months['current_month'] }}
                    </time>
                </td>
                <td class="attendance-trend__table-description">
                    {{ intdiv($reports['current_month_working_minutes'], 60) }}h
                    {{ $reports['current_month_working_minutes'] % 60 }}m
                </td>
                <td class="attendance-trend__table-description">
                    {{ intdiv($reports['current_month_overtime_minutes'], 60) }}h
                    {{ $reports['current_month_overtime_minutes'] % 60 }}m
                </td>
            </tr>
        </table>
    </div>
    <!-- 異常検知 -->
    <div class="attendance-report__detection">
        <h3 class="attendance-report__detection-title">
            今月の異常検知
        </h3>
        <p class="attendance-report__addition-text">
            基準: 始業09:00/終業18:00/長時間労働は1日10時間超
        </p>
        <div class="attendance-report__detection-content">
            <div class="attendance-report__detection-wrapper">
                <span class="attendance-report__detection-label">遅刻回数</span>
                <span class="attendance-report__detection-value">{{ $reports['late_count'] }}回</span>
            </div>
            <div class="attendance-report__detection-wrapper">
                <span class="attendance-report__detection-label">早退回数</span>
                <span class="attendance-report__detection-value">{{ $reports['early_leave_count'] }}回</span>
            </div>
            <div class="attendance-report__detection-wrapper">
                <span class="attendance-report__detection-label">長時間労働日数</span>
                <span class="attendance-report__detection-value">{{ $reports['long_working_day_count'] }}日</span>
            </div>
        </div>
    </div>
    @endsection