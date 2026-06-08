@extends('layouts.staff.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff/attendances/show.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <div class="attendancce-detail__heading">
        <h2 class="attendance-detail__heading-text">
            勤怠詳細
        </h2>
    </div>
    <form class="form"
        action="{{ route('staffCorrection.store', ['id' => $attendanceRecord->id]) }}"
        method="POST" novalidate>
        @csrf
        <table class="attendance-detail__table">
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header">名前</th>
                <td class="attendance-detail__description">{{$user->name}}</td>
                <td class="attendance-detail__tilde"></td>
                <td class="attendance-detail__description"></td>
                <td class="attendance-detail__description"></td>
            </tr>
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header">日付</th>
                <td class="attendance-detail__description">{{$attendanceRecord->date->format('Y年')}}</td>
                <td class="attendance-detail__tilde"></td>
                <td class="attendance-detail__description">{{$attendanceRecord->date->format('n月j日')}}</td>
                <td class="attendance-detail__description"></td>
            </tr>
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header">出勤・退勤</th>
                <td class="attendance-detail__description">
                    <input class="attendance-detail__form-input" type="text"
                        name="requested_clock_in"
                        value="{{ $attendanceRecord->clock_in->format('H:i') }}">
                </td>
                <td class="attendance-detail__tilde">
                    <span class="attendance-detail__tilde-text">〜</span>
                </td>
                <td class="attendance-detail__description">
                    <input class="attendance-detail__form-input" type="text" name="requested_clock_out"
                        value="{{ $attendanceRecord->clock_out->format('H:i') }}">
                </td>
                <td class="attendance-detail__description"></td>
            </tr>
            @foreach($breakRecords as $breakRecord)
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header">
                    休憩{{ $loop->iteration === 1 ? '' : $loop->iteration }}
                </th>
                <td class="attendance-detail__description">
                    <input class="attendance-detail__form-input" type="text"
                        name="requested_breaks[{{ $loop->index }}][break_in]"
                        value="{{ $breakRecord->break_in->format('H:i') }}">
                </td>
                <td class="attendance-detail__tilde">
                    <span class="attendance-detail__tilde-text">〜</span>
                </td>
                <td class="attendance-detail__description">
                    <input class="attendance-detail__form-input" type="text"
                        name="requested_breaks[{{ $loop->index }}][break_out]"
                        value="{{ $breakRecord->break_out->format('H:i') }}">
                </td>
                <td class="attendance-detail__description"></td>
            </tr>
            @endforeach
            @php
            $nextBreakIndex = $breakRecords->count();
            $nextBreakLabelNumber = $breakRecords->count() + 1;
            @endphp
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header">
                    休憩{{ $nextBreakLabelNumber === 1 ? '' : $nextBreakLabelNumber }}
                </th>
                <td class="attendance-detail__description">
                    <input class="attendance-detail__form-input" type="text"
                        name="requested_breaks[{{ $nextBreakIndex }}][break_in]">
                </td>
                <td class="attendance-detail__tilde">
                    <span class="attendance-detail__tilde-text">〜</span>
                </td>
                <td class="attendance-detail__description">
                    <input class="attendance-detail__form-input" type="text"
                        name="requested_breaks[{{ $nextBreakIndex }}][break_out]">
                </td>
                <td class="attendance-detail__description"></td>
            </tr>
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header">備考</th>
                <td class="attendance-detail__description" colspan="3">
                    <textarea class="attendance-detail__form-textarea" name="comment">{{ old('comment') }}</textarea>
                </td>
                <td class="attendance-detail__description"></td>
            </tr>
        </table>
        <div class="correction-request__button">
            <button class="correction-request__button-submit" type="submit">修正</button>
        </div>
    </form>
</div>
@endsection