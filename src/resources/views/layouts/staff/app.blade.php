<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech 勤怠管理アプリ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/layouts/staff/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <!-- ロゴ -->
        <h1 class="header__logo">
            <img class="header__logo-image" src="{{ asset('images/logos/coachtech_header-logo.png') }}"
                alt="COACHTECH">
        </h1>
        <!-- メニュー -->
        <nav class="header__nav">
            @section('nav')
            <!-- ハンバーガーメニュー（モバイル用） -->
            <ul class="hamburger js-hamburger">
                <li></li>
                <li></li>
                <li></li>
            </ul>
            <!-- ナビゲーションメニュー（デスクトップ用） -->
            <ul class="nav-menu js-nav-menu">
                @if(auth()->check() && auth()->user()->attendance_status === '退勤済')
                <!-- 勤怠一覧 -->
                <li class="header__nav-items">
                    <a class="attendance-index__link" href="{{ route('staffAttendance.index') }}">今月の出勤一覧</a>
                </li>
                <!-- 申請一覧 -->
                <li class="header__nav-items">
                    <a class="correction-index__link" href="{{ route('correction.index') }}">申請</a>
                </li>
                <!-- レポート -->
                <li class="header__nav-items">
                    <a class="attendance-report__link" href="{{ route('staffAttendanceReport.report') }}">レポート</a>
                </li>
                <!-- ログアウト -->
                <li class="header__nav-items">
                    <form class="logout__button" method="post" action="{{ route('logout') }}">
                        @csrf
                        <input type="hidden" name="login_type" value="staff">
                        <button class="logout__button-submit" type="submit">ログアウト</button>
                    </form>
                </li>
                @else
                <!-- 勤怠 -->
                <li class="header__nav-items">
                    <a class="attendance-create__link" href="{{ route('staffAttendance.create') }}">勤怠</a>
                </li>
                <!-- 勤怠一覧 -->
                <li class="header__nav-items">
                    <a class="attendance-index__link" href="{{ route('staffAttendance.index') }}">勤怠一覧</a>
                </li>
                <!-- 申請一覧 -->
                <li class="header__nav-items">
                    <a class="correction-index__link" href="{{ route('correction.index') }}">申請</a>
                </li>
                <!-- レポート -->
                <li class="header__nav-items">
                    <a class="attendance-report__link" href="{{ route('staffAttendanceReport.report') }}">レポート</a>
                </li>
                <!-- ログアウト -->
                <li class="header__nav-items">
                    <form class="logout__button" method="post" action="{{ route('logout') }}">
                        @csrf
                        <input type="hidden" name="login_type" value="staff">
                        <button class="logout__button-submit" type="submit">ログアウト</button>
                    </form>
                </li>
                @endif
            </ul>
            @show
        </nav>
    </header>
    <main>
        @yield('content')
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hamburger = document.querySelector('.js-hamburger');
            const navMenu = document.querySelector('.js-nav-menu');

            if (hamburger && navMenu) {
                hamburger.addEventListener('click', function() {
                    navMenu.classList.toggle('active');
                });
            }
        });
    </script>
    @stack('scripts')
</body>

</html>