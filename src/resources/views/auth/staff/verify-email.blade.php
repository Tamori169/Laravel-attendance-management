@extends('layouts.staff.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/staff/verify-email.css') }}">
@endsection

@section('nav', '')

@section('content')

<div class="verify-email">
    <div class="verify-email__content">
        <!-- 案内文 -->
        <span class="verify-email__text">
            登録していただいたメールアドレスに認証メールを送付しました。</br>
            メール認証を完了してください。
        </span>
        <!-- 認証リンク -->
        <a class="verify-email__verify-link" href="http://127.0.0.1:8025/" target="_blank">認証はこちらから</a>
        <!-- 認証メール再送信フォーム -->
        <form class="verify-email__resend-form" method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="verify-email__resend-button" type="submit">
                認証メールを再送する
            </button>
        </form>
    </div>
</div>

@endsection