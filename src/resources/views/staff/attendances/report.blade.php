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
                <span class="attendance-report__summary-value">744h 0m</span>
            </div>
            <div class="attendance-report__summary-wrapper">
                <span class="attendance-report__summary-label">総残業時間</span>
                <span class="attendance-report__summary-value">10h 0m</span>
            </div>
            <div class="attendance-report__summary-wrapper">
                <span class="attendance-report__summary-label">平均残業時間/日</span>
                <span class="attendance-report__summary-value">8h 5m</span>
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
                    <time datetime="2025-12">2025-12</time>
                </td>
                <td class="attendance-trend__table-description">120h 0m</td>
                <td class="attendance-trend__table-description">0h 0m</td>
            </tr>
            <tr class="attendance-trend__table-row">
                <td class="attendance-trend__table-description">
                    <time datetime="2026-01">2026-01</time>
                </td>
                <td class="attendance-trend__table-description">120h 0m</td>
                <td class="attendance-trend__table-description">0h 0m</td>
            </tr>
            <tr class="attendance-trend__table-row">
                <td class="attendance-trend__table-description">
                    <time datetime="2026-02">2026-02</time>
                </td>
                <td class="attendance-trend__table-description">120h 0m</td>
                <td class="attendance-trend__table-description">0h 0m</td>
            </tr>
            <tr class="attendance-trend__table-row">
                <td class="attendance-trend__table-description">
                    <time datetime="2026-03">2026-03</time>
                </td>
                <td class="attendance-trend__table-description">120h 0m</td>
                <td class="attendance-trend__table-description">0h 0m</td>
            </tr>
            <tr class="attendance-trend__table-row">
                <td class="attendance-trend__table-description">
                    <time datetime="2026-04">2026-04</time>
                </td>
                <td class="attendance-trend__table-description">120h 0m</td>
                <td class="attendance-trend__table-description">0h 0m</td>
            </tr>
            <tr class="attendance-trend__table-row">
                <td class="attendance-trend__table-description">
                    <time datetime="2026-05">2026-05</time>
                </td>
                <td class="attendance-trend__table-description">144h 0m</td>
                <td class="attendance-trend__table-description">10h 0m</td>
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
                <span class="attendance-report__detection-value">２回</span>
            </div>
            <div class="attendance-report__detection-wrapper">
                <span class="attendance-report__detection-label">早退回数</span>
                <span class="attendance-report__detection-value">１回</span>
            </div>
            <div class="attendance-report__detection-wrapper">
                <span class="attendance-report__detection-label">長時間労働日数</span>
                <span class="attendance-report__detection-value">１日</span>
            </div>
        </div>
    </div>
    @endsection