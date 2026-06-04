@extends('layouts.staff.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff/attendances/index.css') }}">
@endsection

@section('content')
<div class="attendance-index">
    <div class="attendancce-index__heading">
        <h2 class="attendance-index__heading-text">
            勤怠一覧
        </h2>
    </div>
</div>
<div class="month-navigation">
    <div class="month-navigation__previous-month">
        <span class="arrow">←</span>
        <a class="month-navigation__link"
            href="{{ route('staffAttendance.index', ['month' => $currentMonth->copy()->subMonth()->format('Y-m')]) }}">
            前月
        </a>
    </div>
    <div class="month-navigation__current-month">
        <svg class="month-navigation__icon" viewBox="0 0 24 24" aria-hidden="true">
            <rect x="3" y="5" width="18" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="2" />
            <line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2" />
            <line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            <line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            <rect x="7" y="13" width="2" height="2" fill="currentColor" />
            <rect x="11" y="13" width="2" height="2" fill="currentColor" />
            <rect x="15" y="13" width="2" height="2" fill="currentColor" />
            <rect x="7" y="17" width="2" height="2" fill="currentColor" />
            <rect x="11" y="17" width="2" height="2" fill="currentColor" />
        </svg>
        <span class="month-navigation__text">
            {{ $currentMonth->format('Y年n月') }}
        </span>
    </div>
    <div class="month-navigation__next-month">
        <a class="month-navigation__link"
            href="{{ route('staffAttendance.index', ['month' => $currentMonth->copy()->addMonth()->format('Y-m')]) }}">
            翌月
        </a>
        <span class="arrow">→</span>
    </div>
    <table class="attendance-records">
        <tr class="attendance-records__row">
            <th class="attendance-records__header">日付</th>
            <th class="attendance-records__header">出勤</th>
            <th class="attendance-records__header">退勤</th>
            <th class="attendance-records__header">休憩</th>
            <th class="attendance-records__header">合計</th>
            <th class="attendance-records__header">詳細</th>
        </tr>
        @foreach($attendanceRecordList as $attendanceRecordItem)
        <tr class="attendance-records__row">
            @php
            $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
            @endphp
            <td class="attendance-records__description">{{ $attendanceRecordItem['date']->format('m/d') }}
                ({{ $weekdays[$attendanceRecordItem['date']->dayOfWeek] }})</td>
            <td class="attendance-records__description">{{ $attendanceRecordItem['attendance_record']?->clock_in }}</td>
            <td class="attendance-records__description">{{ $attendanceRecordItem['attendance_record']?->clock_out }}</td>
            <td class="attendance-records__description">{{ $attendanceRecordItem['attendance_record']?->formatted_break_time }}</td>
            <td class="attendance-records__description">{{ $attendanceRecordItem['attendance_record']?->formatted_work_time }}</td>
            <td class="attendance-records__description">
                @if ($attendanceRecordItem['attendance_record'])
                <a class="attendance-records__link"
                    href="{{ route('staffAttendance.show', ['id' => $attendanceRecordItem['attendance_record']->id]) }}">
                    詳細
                </a>
                @else
                詳細
                @endif
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection