@extends('layouts.minimal')

@section('title', 'ログイン - LoveAtlas')

@section('content')
    <div class="auth-form card">
        <div class="auth-form__header">
            <a href="/" class="auth-form__logo">LoveAtlas</a>
            <h1 class="auth-form__title">ログイン</h1>
        </div>

        <login-form></login-form>

        <div class="auth-form__footer">
            <p>アカウントをお持ちでない方は <a href="/register">新規登録</a></p>
        </div>
    </div>
@endsection
