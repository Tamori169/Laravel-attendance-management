@extends('layouts.admin.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/admin/login.css') }}">
@endsection

@section('nav', '')

@section('content')

<div class="login-form">
    <div class="login-form__heading">
        <h1 class="login-form__heading-text">
            管理者ログイン
        </h1>
    </div>
    <form class="form" action="{{ route('login') }}" method="POST" novalidate>
        @csrf
        <input type="hidden" name="login_type" value="admin">
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
        <!-- ログインボタン -->
        <div class="form__button">
            <button class="form__button-submit" type="submit">
                管理者ログインする
            </button>
        </div>
    </form>
</div>
@endsection