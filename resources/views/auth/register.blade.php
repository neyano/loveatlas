@extends('layouts.minimal')

@section('title', '新規登録 - LoveAtlas')

@section('content')
    <div class="auth-form card">
        <div class="auth-form__header">
            <a href="/" class="auth-form__logo">LoveAtlas</a>
            <h1 class="auth-form__title">アカウント作成</h1>
        </div>

        <register-form></register-form>

        <div class="auth-form__footer">
            <p>既にアカウントをお持ちの方は <a href="/login">ログイン</a></p>
        </div>
    </div>
@endsection
