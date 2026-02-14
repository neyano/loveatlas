@extends('layouts.minimal')

@section('title', 'パスワードリセット - LoveAtlas')

@section('content')
    <div class="auth-form card">
        <div class="auth-form__header">
            <a href="/" class="auth-form__logo">LoveAtlas</a>
            <h1 class="auth-form__title">パスワードリセット</h1>
            <p class="auth-form__description">
                登録しているメールアドレスを入力してください。パスワードリセット用のリンクをお送りします。
            </p>
        </div>

        <forgot-password-form></forgot-password-form>

        <div class="auth-form__footer">
            <p><a href="/login">ログインに戻る</a></p>
        </div>
    </div>
@endsection
