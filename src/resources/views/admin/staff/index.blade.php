@extends('layouts.admin.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/index.css') }}">
@endsection

@section('content')
<div class="staff-index">
    <div class="staff-index__heading">
        <h2 class="staff-index__heading-text">
            スタッフ一覧
        </h2>
    </div>
    <table class="staff-list">
        <tr class="staff-list__row">
            <th class="staff-list__header"></th>
            <th class="staff-list__header">名前</th>
            <th class="staff-list__header">メールアドレス</th>
            <th class="staff-list__header">月次勤怠</th>
            <th class="staff-list__header"></th>
        </tr>
        @foreach($users as $user)
        <tr class="staff-list__row">
            <td class="staff-list__description"></td>
            <td class="staff-list__description">{{$user->name}}</td>
            <td class="staff-list__description">{{$user->email}}</td>
            <td class="staff-list__description">
                <a class="staff-list__link"
                    href="{{ route('adminStaff.show', ['id' => $user->id]) }}">
                    詳細
                </a>
            </td>
            <td class="staff-list__description"></td>
        </tr>
        @endforeach
    </table>
</div>

@endsection