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
                <td class="attendance-detail__description">
                    <time class="attendance-detail__description-year"
                    datetime="{{ $attendanceRecord->date->format('Y-m-d') }}">
                        {{ $attendanceRecord->date->format('Y年') }}
                    </time>
                </td>
                <td class="attendance-detail__tilde"></td>
                <td class="attendance-detail__description">
                    <time class="attendance-detail__description-date"
                    datetime="{{ $attendanceRecord->date->format('Y-m-d') }}">
                        {{ $attendanceRecord->date->format('n月j日') }}
                    </time>
                </td>
                <td class="attendance-detail__description"></td>
            </tr>
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header" rowspan="2">出勤・退勤</th>
                <td class="attendance-detail__description">
                    @if($attendanceCorrectRequest)
                    <time class="requested-time__text"
                    datetime="{{ $attendanceCorrectRequest->requested_clock_in->format('H:i') }}">
                        {{ $attendanceCorrectRequest->requested_clock_in->format('H:i') }}
                    </time>
                    @else
                    <input class="attendance-detail__form-input" type="text" name="requested_clock_in"
                        value="{{ old('requested_clock_in', $attendanceRecord->clock_in->format('H:i')) }}">
                    @endif
                </td>
                <td class="attendance-detail__tilde">
                    <span class="attendance-detail__tilde-text">〜</span>
                </td>
                <td class="attendance-detail__description">
                    @if($attendanceCorrectRequest)
                    <time class="requested-time__text"
                    datetime="{{ $attendanceCorrectRequest->requested_clock_out->format('H:i') }}">
                        {{ $attendanceCorrectRequest->requested_clock_out->format('H:i') }}
                    </time>
                    @else
                    <input class="attendance-detail__form-input" type="text" name="requested_clock_out"
                        value="{{ old('requested_clock_out', $attendanceRecord->clock_out?->format('H:i')) }}">
                    @endif
                </td>
                <td class="attendance-detail__description"></td>
            </tr>
            <tr class="error__row">
                <td class="error__description" colspan="4">
                    <span class="error__description-text">
                        @error('requested_clock_in')
                        {{ $message }}
                        @enderror
                    </span>
                    <span class="error__description-text">
                        @error('requested_clock_out')
                        {{ $message }}
                        @enderror
                    </span>
                </td>
            </tr>
            @if($attendanceCorrectRequest)
            @foreach($attendanceCorrectRequest->breakCorrectRequests as $breakCorrectRequest)
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header">
                    休憩{{ $loop->iteration === 1 ? '' : $loop->iteration }}
                </th>
                <td class="attendance-detail__description">
                    <time class="requested-time__text"
                    datetime="{{ $breakCorrectRequest->requested_break_in->format('H:i') }}">
                        {{ $breakCorrectRequest->requested_break_in->format('H:i') }}
                    </time>
                </td>
                <td class="attendance-detail__tilde">
                    <span class="attendance-detail__tilde-text">〜</span>
                </td>
                <td class="attendance-detail__description">
                    <time class="requested-time__text"
                    datetime="{{ $breakCorrectRequest->requested_break_out->format('H:i') }}">
                        {{ $breakCorrectRequest->requested_break_out->format('H:i') }}
                    </time>
                </td>
                <td class="attendance-detail__description"></td>
            </tr>
            @endforeach
            @else
            @foreach($breakRecords as $breakRecord)
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header" rowspan="2">
                    休憩{{ $loop->iteration === 1 ? '' : $loop->iteration }}
                </th>
                <td class="attendance-detail__description">
                    <input class="attendance-detail__form-input" type="text"
                        name="requested_breaks[{{ $loop->index }}][break_in]"
                        value="{{ old("requested_breaks.{$loop->index}.break_in", $breakRecord->break_in->format('H:i')) }}">
                </td>
                <td class="attendance-detail__tilde">
                    <span class="attendance-detail__tilde-text">〜</span>
                </td>
                <td class="attendance-detail__description">
                    <input class="attendance-detail__form-input" type="text"
                        name="requested_breaks[{{ $loop->index }}][break_out]"
                        value="{{ old("requested_breaks.{$loop->index}.break_out", $breakRecord->break_out?->format('H:i')) }}">
                </td>
                <td class="attendance-detail__description"></td>
            </tr>
            <tr class="error__row">
                <td class="error__description" colspan="4">
                    <span class="error__description-text">
                        @error("requested_breaks.{$loop->index}.break_in")
                        {{ $message }}
                        @enderror
                    </span>
                    <span class="error__description-text">
                        @error("requested_breaks.{$loop->index}.break_out")
                        {{ $message }}
                        @enderror
                    </span>
                </td>
            </tr>
            @endforeach
            @endif
            @php
            $nextBreakIndex = $breakRecords->count();
            $nextBreakLabelNumber = $breakRecords->count() + 1;
            @endphp
            @unless($attendanceCorrectRequest)
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header" rowspan="2">
                    休憩{{ $nextBreakLabelNumber === 1 ? '' : $nextBreakLabelNumber }}
                </th>
                <td class="attendance-detail__description">
                    <input class="attendance-detail__form-input" type="text"
                        name="requested_breaks[{{ $nextBreakIndex }}][break_in]"
                        value="{{ old("requested_breaks.{$nextBreakIndex}.break_in") }}">
                </td>
                <td class="attendance-detail__tilde">
                    <span class="attendance-detail__tilde-text">〜</span>
                </td>
                <td class="attendance-detail__description">
                    <input class="attendance-detail__form-input" type="text"
                        name="requested_breaks[{{ $nextBreakIndex }}][break_out]"
                        value="{{ old("requested_breaks.{$nextBreakIndex}.break_out") }}">
                </td>
                <td class="attendance-detail__description"></td>
            </tr>
            <tr class="error__row">
                <td class="error__description" colspan="4">
                    <span class="error__description-text">
                        @error("requested_breaks.{$nextBreakIndex}.break_in")
                        {{ $message }}
                        @enderror
                    </span>
                    <span class="error__description-text">
                        @error("requested_breaks.{$nextBreakIndex}.break_out")
                        {{ $message }}
                        @enderror
                    </span>
                </td>
            </tr>
            @endunless
            <tr class="attendance-detail__row">
                <th class="attendance-detail__header" rowspan="2">備考</th>
                @if($attendanceCorrectRequest)
                <td class="attendance-detail__description readonly-comment" colspan="3">
                    <span class="requested-comment__text">
                        {{ $attendanceCorrectRequest->comment }}
                    </span>
                </td>
                @else
                <td class="attendance-detail__description" colspan="3">
                    <textarea class="attendance-detail__form-textarea" name="comment">{{ old('comment') }}</textarea>
                </td>
                @endif
                <td class="attendance-detail__description"></td>
            </tr>
            <tr class="error__row">
                <td class="error__description" colspan="4">
                    <span class="error__description-text">
                        @error('comment')
                        {{ $message }}
                        @enderror
                    </span>
                </td>
            </tr>
        </table>
        @if($attendanceCorrectRequest)
        <div class="correction-request__message">
            <span class="correction-request__message-text">
                *承認待ちのため修正はできません。
            </span>
        </div>
        @else
        <div class="correction-request__button">
            <button class="correction-request__button-submit" type="submit">修正</button>
        </div>
        @endif
    </form>
</div>
@endsection