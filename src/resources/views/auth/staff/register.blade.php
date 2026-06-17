@extends('layouts.staff.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/staff/register.css') }}">
@endsection

@section('nav', '')

@section('content')

<div class="register-form">
    <div class="register-form__heading">
        <h1 class="register-form__heading-text">
            会員登録
        </h1>
    </div>
    <form class="form" action="{{ route('register') }}" method="POST" novalidate>
        @csrf
        <!-- ユーザー名 -->
        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">
                    ユーザー名
                </span>
            </div>
            <div class="form__group-content">
                <div class="form__item">
                    <input class="form__item-input" type="text" name="name"
                        value="{{ old('name') }}">
                </div>
                <div class="form__error">
                    <span class="form__error-text">
                        @error('name')
                        {{ $message }}
                        @enderror
                    </span>
                </div>
            </div>
        </div>
        <!-- メールアドレス -->
        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">
                    メールアドレス
                </span>
            </div>
            <div class="form__group-content">
                <div class="form__item">
                    <input class="form__item-input" type="email" name="email"
                        value="{{ old('email') }}">
                </div>
                <div class="form__error">
                    <span class="form__error-text">
                        @error('email')
                        {{ $message }}
                        @enderror
                    </span>
                </div>
            </div>
        </div>
        <!-- パスワード -->
        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">
                    パスワード
                </span>
            </div>
            <div class="form__group-content">
                <div class="form__item">
                    <input class="form__item-input" type="password" name="password"
                        value="{{ old('password') }}">
                </div>
                <div class="form__error">
                    <span class="form__error-text">
                        @error('password')
                        {{ $message }}
                        @enderror
                    </span>
                </div>
            </div>
        </div>
        <!-- 確認用パスワード -->
        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">
                    パスワード確認
                </span>
            </div>
            <div class="form__group-content">
                <div class="form__item">
                    <input class="form__item-input" type="password" name="password_confirmation" value="{{ old('password_confirmation') }}">
                </div>
                <div class="form__error">
                    <span class="form__error-text">
                        @error('password_confirmation')
                        {{ $message }}
                        @enderror
                    </span>
                </div>
            </div>
        </div>
        <!-- 登録ボタン -->
        <div class="form__button">
            <button class="form__button-submit" type="submit">
                登録する
            </button>
        </div>
    </form>
    <!-- ログインリンク -->
    <div class="register-form__login">
        <a class="register-form__login-link" href="{{ route('login') }}">
            ログインはこちら
        </a>
    </div>
</div>
@endsection