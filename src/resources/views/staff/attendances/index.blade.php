@extends('layouts.staff.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff/attendances/index.css') }}">
@endsection

@section('content')
<div class="attendance-index">
    <div class="attendance-index__heading">
        <h2 class="attendance-index__heading-text">
            勤怠一覧
        </h2>
    </div>
    <div class="month-navigation">
        <div class="month-navigation__previous-month">
            <a class="month-navigation__link"
                href="{{ route('staffAttendance.index', ['month' => $currentMonth->copy()->subMonth()->format('Y-m')]) }}">
                <span class="arrow">←</span>
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
            <time class="month-navigation__text" datetime="{{ $currentMonth->format('Y-m') }}">
                {{ $currentMonth->format('Y/m') }}
            </time>
        </div>
        <div class="month-navigation__next-month">
            <a class="month-navigation__link"
                href="{{ route('staffAttendance.index', ['month' => $currentMonth->copy()->addMonth()->format('Y-m')]) }}">
                翌月
                <span class="arrow">→</span>
            </a>
        </div>
    </div>
    <table class="attendance-list">
        <thead>
            <tr class="attendance-list__row">
                <th class="attendance-list__header" scope="col">日付</th>
                <th class="attendance-list__header" scope="col">出勤</th>
                <th class="attendance-list__header" scope="col">退勤</th>
                <th class="attendance-list__header" scope="col">休憩</th>
                <th class="attendance-list__header" scope="col">合計</th>
                <th class="attendance-list__header" scope="col">詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendanceRecordList as $attendanceRecordItem)
            <tr class="attendance-list__row">
                @php
                $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                @endphp
                <td class="attendance-list__description">
                    <time class="attendance-list__description-date"
                        datetime="{{ $attendanceRecordItem['date']->format('Y-m-d') }}">
                        {{ $attendanceRecordItem['date']->format('m/d') }}
                        ({{ $weekdays[$attendanceRecordItem['date']->dayOfWeek] }})
                    </time>
                </td>
                <td class="attendance-list__description">
                    @if ($attendanceRecordItem['attendance_record']?->clock_in)
                        <time class="attendance-list__description-clock-in"
                            datetime="{{ $attendanceRecordItem['attendance_record']?->clock_in?->format('Y-m-d H:i') }}">
                            {{ $attendanceRecordItem['attendance_record']?->clock_in?->format('H:i') }}
                        </time>
                    @endif
                </td>
                <td class="attendance-list__description">
                    @if ($attendanceRecordItem['attendance_record']?->clock_out)
                        <time class="attendance-list__description-clock-out"
                            datetime="{{ $attendanceRecordItem['attendance_record']?->clock_out?->format('Y-m-d H:i') }}">
                            {{ $attendanceRecordItem['attendance_record']?->clock_out?->format('H:i') }}
                        </time>
                    @endif
                </td>
                <td class="attendance-list__description">
                    {{ $attendanceRecordItem['attendance_record']?->formatted_break_time }}
                </td>
                <td class="attendance-list__description">
                    {{ $attendanceRecordItem['attendance_record']?->formatted_work_time }}
                </td>
                <td class="attendance-list__description">
                    @if ($attendanceRecordItem['attendance_record'])
                    <a class="attendance-list__link"
                        href="{{ route('staffAttendance.show', ['id' => $attendanceRecordItem['attendance_record']->id]) }}">
                        詳細
                    </a>
                    @else
                    <span class="attendance-list__alternate">詳細</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection