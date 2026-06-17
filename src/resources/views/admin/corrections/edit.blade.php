@extends('layouts.admin.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/corrections/edit.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <div class="attendancce-detail__heading">
        <h2 class="attendance-detail__heading-text">
            勤怠詳細
        </h2>
    </div>
    <div class="attendance-detail__content"
        action="{{ route('adminCorrection.update',
        ['attendance_correct_request_id' => $attendanceCorrectRequest->id]) }}"
        method="POST" novalidate>
        @csrf
        @method('PATCH')
        <table class="attendance-detail__table">
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header">名前</th>
                <td class="attendance-detail__description">
                    {{$attendanceCorrectRequest->attendanceRecord->user->name}}
                </td>
                <td class="attendance-detail__tilde"></td>
                <td class="attendance-detail__description"></td>
                <td class="attendance-detail__description"></td>
            </tr>
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header">日付</th>
                <td class="attendance-detail__description">
                    {{$attendanceCorrectRequest->attendanceRecord->date->format('Y年')}}
                </td>
                <td class="attendance-detail__tilde"></td>
                <td class="attendance-detail__description">
                    {{$attendanceCorrectRequest->attendanceRecord->date->format('n月j日')}}
                </td>
                <td class="attendance-detail__description"></td>
            </tr>
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header">出勤・退勤</th>
                <td class="attendance-detail__description">
                    <span class="requested-time__text">
                        {{ $attendanceCorrectRequest->requested_clock_in->format('H:i') }}
                    </span>
                </td>
                <td class="attendance-detail__tilde">
                    <span class="attendance-detail__tilde-text">〜</span>
                </td>
                <td class="attendance-detail__description">
                    <span class="requested-time__text">
                        {{ $attendanceCorrectRequest->requested_clock_out->format('H:i') }}
                    </span>
                </td>
                <td class="attendance-detail__description"></td>
            </tr>
            @foreach($attendanceCorrectRequest->breakCorrectRequests as $breakCorrectRequest)
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header">
                    休憩{{ $loop->iteration === 1 ? '' : $loop->iteration }}
                </th>
                <td class="attendance-detail__description">
                    <span class="requested-time__text">
                        {{ $breakCorrectRequest->requested_break_in->format('H:i') }}
                    </span>
                </td>
                <td class="attendance-detail__tilde">
                    <span class="attendance-detail__tilde-text">〜</span>
                </td>
                <td class="attendance-detail__description">
                    <span class="requested-time__text">
                        {{ $breakCorrectRequest->requested_break_out->format('H:i') }}
                    </span>
                </td>
                <td class="attendance-detail__description"></td>
            </tr>
            @endforeach
            @php
            $nextBreakIndex = $attendanceCorrectRequest->breakCorrectRequests->count();
            $nextBreakLabelNumber = $attendanceCorrectRequest->breakCorrectRequests->count() + 1;
            @endphp
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header">
                    休憩{{ $nextBreakLabelNumber === 1 ? '' : $nextBreakLabelNumber }}
                </th>
                <td class="attendance-detail__description"></td>
                <td class="attendance-detail__tilde"></td>
                <td class="attendance-detail__description"></td>
                <td class="attendance-detail__description"></td>
            </tr>
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header">備考</th>
                <td class="attendance-detail__description readonly-comment" colspan="3">
                    <span class="requested-comment__text">
                        {{ $attendanceCorrectRequest->comment }}
                    </span>
                </td>
                <td class="attendance-detail__description"></td>
            </tr>
        </table>
        <form class="correction-approval__button">
            @if ($attendanceCorrectRequest->request_status_id === 2)
            <button type="button" class="alternate__button" disabled>承認済み</button>
            @else
            <button class="correction-approval__button-submit" type="submit">承認</button>
            @endif
        </form>
    </div>
</div>
@endsection