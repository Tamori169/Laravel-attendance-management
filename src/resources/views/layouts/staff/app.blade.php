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
        <a class="header_logo" href="">
            <img class="header_logo-image header_logo-image--desktop" src="{{ asset('images/logos/coachtech_header-logo_desktop.png') }}"
                alt="COACHTECH">
            <img class="header_logo-image header_logo-image--mobile" src="{{ asset('images/logos/coachtech_header-logo_mobile.png') }}"
                alt="COACHTECH">
        </a>
        <!-- 真ん中の空白 -->
        <div class="header_space">
        </div>
        <!-- メニュー -->
        <nav class="header_nav">
            @section('nav')
            <!-- ハンバーガーメニュー（モバイル用） -->
            <ul class="hamburger js-hamburger">
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
            </ul>
            <!-- ナビゲーションメニュー（デスクトップ用） -->
            <ul class="nav-menu js-nav-menu">
                <!-- 勤怠 -->
                <li class="header_nav-items">
                    <a class="attendance-create_link" href="">勤怠</a>
                </li>
                <!-- 勤怠一覧 -->
                <li class="header_nav-items">
                    <a class="attendance-index_link" href="">勤怠一覧</a>
                </li>
                <!-- 申請一覧 -->
                <li class="header_nav-items">
                    <a class="correction-index_link" href="">申請</a>
                </li>
                <!-- レポート -->
                <li class="header_nav-items">
                    <a class="attendance-report_link" href="">レポート</a>
                </li>
                <!-- ログアウト -->
                <li class="header_nav-items">
                    <form class="logout__button" method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout__button-submit" type="submit">ログアウト</button>
                    </form>
                </li>
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