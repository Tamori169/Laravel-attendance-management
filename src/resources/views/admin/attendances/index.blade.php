@extends('layouts.admin.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendances/index.css') }}">
@endsection

@section('content')
<div class="attendance-index">
    <div class="attendance-index__heading">
        <h2 class="attendance-index__heading-text">
            {{ $today->format('Y年n月j日') }}の一覧
        </h2>
    </div>
    <div class="date-navigation">
        <div class="date-navigation__previous-day">
            <a class="date-navigation__link"
                href="{{ route('adminAttendance.index', ['date' => $today->copy()->subDay()->format('Y-m-d')]) }}">
                <span class="arrow">←</span>
                前日
            </a>
        </div>
        <div class="date-navigation__today">
            <svg class="date-navigation__icon" viewBox="0 0 24 24" aria-hidden="true">
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
            <span class="date-navigation__text">
                {{ $today->format('Y/m/d') }}
            </span>
        </div>
        <div class="date-navigation__next-day">
            <a class="date-navigation__link"
                href="{{ route('adminAttendance.index', ['date' => $today->copy()->addDay()->format('Y-m-d')]) }}">
                翌日
                <span class="arrow">→</span>
            </a>
        </div>
    </div>
    <table class="attendance-list">
        <tr class="attendance-list__row">
            <th class="attendance-list__header">名前</th>
            <th class="attendance-list__header">出勤</th>
            <th class="attendance-list__header">退勤</th>
            <th class="attendance-list__header">休憩</th>
            <th class="attendance-list__header">合計</th>
            <th class="attendance-list__header">詳細</th>
        </tr>
        @foreach($attendanceRecords as $attendanceRecord)
        <tr class="attendance-list__row">
            <td class="attendance-list__description">{{ $attendanceRecord->user->name }}</td>
            <td class="attendance-list__description">{{ $attendanceRecord->clock_in->format('H:i') }}</td>
            <td class="attendance-list__description">{{ $attendanceRecord->clock_out?->format('H:i') }}</td>
            <td class="attendance-list__description">{{ $attendanceRecord->formatted_break_time }}</td>
            <td class="attendance-list__description">{{ $attendanceRecord->formatted_work_time }}</td>
            <td class="attendance-list__description">
                @if ($attendanceRecord)
                <a class="attendance-list__link"
                    href="{{ route('adminAttendance.show', ['id' => $attendanceRecord->id]) }}">
                    詳細
                </a>
                @else
                <span class="attendance-list__alternate">詳細</span>
                @endif
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection