@extends('layouts.admin.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/corrections/index.css') }}">
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
                href="{{ route('correction.index', ['tab' => 'pending']) }}">
                承認待ち
            </a>
        </div>
        <div class="tab__item">
            <a class="tab__link {{ request('tab') == 'approved' ? 'is-active' : '' }}"
                href="{{ route('correction.index', ['tab' => 'approved']) }}">
                承認済み
            </a>
        </div>
    </div>
    <table class="correction-list">
        <tr class="correction-list__row">
            <th class="correction-list__header">状態</th>
            <th class="correction-list__header">名前</th>
            <th class="correction-list__header">対象日時</th>
            <th class="correction-list__header">申請理由</th>
            <th class="correction-list__header">申請日時</th>
            <th class="correction-list__header">詳細</th>
        </tr>
        @foreach($attendanceCorrectRequests as $attendanceCorrectRequest)
        <tr class="correction-list__row">
            <td class="correction-list__description">
                {{ $attendanceCorrectRequest->requestStatus->label }}
            </td>
            <td class="correction-list__description">
                {{ $attendanceCorrectRequest->attendanceRecord->user->name }}
            </td>
            <td class="correction-list__description">
                {{ $attendanceCorrectRequest->attendanceRecord->date->format('Y/m/d') }}
            </td>
            <td class="correction-list__description">
                {{ $attendanceCorrectRequest->comment }}
            </td>
            <td class="correction-list__description">
                {{ $attendanceCorrectRequest->created_at->format('Y/m/d') }}
            </td>
            <td class="correction-list__description">
                <a class="correction-list__link"
                    href="{{ route('adminCorrection.edit',
                    ['attendance_correct_request_id' => $attendanceCorrectRequest->id]) }}">
                    詳細
                </a>
            </td>
        </tr>
        @endforeach
    </table>
</div>

@endsection