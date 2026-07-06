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
            @php
            $now = now('Asia/Tokyo');
            $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
            @endphp
            <time class="current-time__date" id="current-date" datetime="{{ $now->format('Y-m-d') }}">
                {{ $now->format('Y年n月j日') }}({{ $weekdays[$now->dayOfWeek] }})
            </time>
            <time class="current-time__time" id="current-time" datetime="{{ now('Asia/Tokyo')->format('H:i') }}">
                {{ now('Asia/Tokyo')->format('H:i') }}
            </time>
        </div>
        <!-- ボタン -->
        @if(auth()->user()->attendance_status === '勤務外')
        <form class="attendance-buttons" action="{{ route('staffAttendance.clockIn') }}" method="post">
            @csrf
            <button class="attendance-buttons__clock_in" type="submit">出勤</button>
        </form>
        @elseif(auth()->user()->attendance_status === '出勤中')
        <div class="attendance-buttons__wrapper">
            <form class="attendance-buttons" method="post">
                @csrf
                @method('PATCH')
                <button class="attendance-buttons__clock_out" formaction="{{ route('staffAttendance.clockOut') }}" type="submit">退勤</button>
            </form>
            <form class="attendance-buttons" method="post">
                @csrf
                <button class="attendance-buttons__break_in" formaction="{{ route('staffAttendance.breakIn') }}" type="submit">休憩入</button>
            </form>
        </div>
        @elseif(auth()->user()->attendance_status === '休憩中')
        <form class="attendance-buttons" method="post">
            @csrf
            @method('PATCH')
            <button class="attendance-buttons__break_out" formaction="{{ route('staffAttendance.breakOut') }}" type="submit">休憩戻</button>
        </form>
        @elseif(auth()->user()->attendance_status === '退勤済')
        <div class="message">
            <p class="message__text">お疲れ様でした。</p>
        </div>
        @endif
    </div>
</div>

<script>
    function updateCurrentDateTime() {
        const now = new Date();

        const dateParts = new Intl.DateTimeFormat('ja-JP', {
            timeZone: 'Asia/Tokyo',
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            weekday: 'short'
        }).formatToParts(now);

        const year = dateParts.find(part => part.type === 'year').value;
        const month = dateParts.find(part => part.type === 'month').value;
        const day = dateParts.find(part => part.type === 'day').value;
        const weekday = dateParts.find(part => part.type === 'weekday').value;

        const dateText = `${year}年${month}月${day}日（${weekday}）`;
        const dateValue = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;

        const timeText = new Intl.DateTimeFormat('ja-JP', {
            timeZone: 'Asia/Tokyo',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        }).format(now);

        document.getElementById('current-date').textContent = dateText;
        document.getElementById('current-date').setAttribute('datetime', dateValue);

        document.getElementById('current-time').textContent = timeText;
        document.getElementById('current-time').setAttribute('datetime', timeText);
    }

    updateCurrentDateTime();
    setInterval(updateCurrentDateTime, 1000);
</script>

@endsection