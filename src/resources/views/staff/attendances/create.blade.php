@extends('layouts.staff.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff/attendances/create.css') }}">
@endsection

@section('content')

<div class="attendance-create">
    <div class="attendance-create__content">
        <!-- ステータス -->
        <div class="attendance-status">
            <span class="attendance-status__text">
                {{ auth()->user()->attendance_status }}
            </span>
        </div>
        <!-- 現在時刻 -->
        <div class="current-time">
            <span class="current-time__date" id="current-date">
                @php
                $now = now('Asia/Tokyo');
                $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                @endphp

                {{ $now->format('Y年n月j日') }}（{{ $weekdays[$now->dayOfWeek] }}）
            </span>
            <span class="current-time__time" id="current-time">
                {{ now('Asia/Tokyo')->format('H:i') }}
            </span>
        </div>
        <!-- ボタン -->
        @if(auth()->user()->attendance_status === '勤務外')
        <form class="attendance-buttons" action="{{ route('staff.attendances.clockIn') }}" method="post">
            @csrf
            <button class="attendance-buttons__clock_in" type="submit">出勤</button>
        </form>
        @elseif(auth()->user()->attendance_status === '出勤中')
        <form class="attendance-buttons" method="post">
            @csrf
            @method('PATCH')
            <button class="attendance-buttons__clock_out" formaction="{{ route('staff.attendances.clockOut') }}" type="submit">退勤</button>
        </form>
        <form class="attendance-buttons" method="post">
            @csrf
            <button class="attendance-buttons__break_in" formaction="{{ route('staff.attendances.breakIn') }}" type="submit">休憩入</button>
        </form>
        @elseif(auth()->user()->attendance_status === '休憩中')
        <form class="attendance-buttons" method="post">
            @csrf
            @method('PATCH')
            <button class="attendance-buttons__break_out" formaction="{{ route('staff.attendances.breakOut') }}" type="submit">休憩戻</button>
        </form>
        @elseif(auth()->user()->attendance_status === '退勤済')
        <div class="massage">
            <p class="massage__text">お疲れ様でした。</p>
        </div>
        @endif
    </div>
</div>

<script>
    function updateCurrentDateTime() {
        const now = new Date();

        const dateText = new Intl.DateTimeFormat('ja-JP', {
            timeZone: 'Asia/Tokyo',
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            weekday: 'short'
        }).format(now);

        const timeText = new Intl.DateTimeFormat('ja-JP', {
            timeZone: 'Asia/Tokyo',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        }).format(now);

        const formattedDateText = dateText.replace('/', '年').replace('/', '月').replace('(', '日（').replace(')', '）');

        document.getElementById('current-date').textContent = formattedDateText;
        document.getElementById('current-time').textContent = timeText;
    }

    updateCurrentDateTime();
    setInterval(updateCurrentDateTime, 1000);
</script>

@endsection