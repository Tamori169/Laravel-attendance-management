@extends('layouts.staff.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff/corrections/index.css') }}">
@endsection

@section('content')

<div class="correction-index">
    <div class="correction-index__heading">
        <h2 class="correction-index__heading-text">
            申請一覧
        </h2>
    </div>
    <!-- タブ -->
    <div class="tabs">
        <div class="tab__item">
            <a class="tab__link {{ request('tab') != 'approved' ? 'is-active' : '' }}"
                href="{{ route('staffCorrection.index', ['tab' => 'pending']) }}">
                承認待ち
            </a>
        </div>
        <div class="tab__item">
            <a class="tab__link {{ request('tab') == 'approved' ? 'is-active' : '' }}"
                href="{{ route('staffCorrection.index', ['tab' => 'approved']) }}">
                承認済み
            </a>
        </div>
    </div>
    <table class="correction-index__table">
        <tr class="correction-index__row">
            <th class="correction-index__header">状態</th>
            <th class="correction-index__header">名前</th>
            <th class="correction-index__header">対象日時</th>
            <th class="correction-index__header">申請理由</th>
            <th class="correction-index__header">申請日時</th>
            <th class="correction-index__header">詳細</th>
        </tr>
        @foreach($attendanceCorrectRequests as $attendanceCorrectRequest)
        <tr class="correction-index__row">
            <td class="correction-index__description">
                {{ $attendanceCorrectRequest->requestStatus->label }}
            </td>
            <td class="correction-index__description">
                {{ $user->name }}
            </td>
            <td class="correction-index__description">
                {{ $attendanceCorrectRequest->attendanceRecord->date }}
            </td>
            <td class="correction-index__description">
                {{ $attendanceCorrectRequest->comment }}
            </td>
            <td class="correction-index__description">
                {{ $attendanceCorrectRequest->created_at->format('Y/m/d') }}
            </td>
            <td class="correction-index__description">
                <a class="attendance-list__link"
                    href="{{ route('staffAttendance.show', ['id' => $attendanceCorrectRequest->attendanceRecord->id]) }}">
                    詳細
                </a>
            </td>
        </tr>
        @endforeach
    </table>
</div>

@endsection