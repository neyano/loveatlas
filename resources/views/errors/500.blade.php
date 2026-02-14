@extends('layouts.minimal')

@section('title', '500 - サーバーエラー')

@section('content')
<div class="error-page">
    <h1 class="error-page__code">500</h1>
    <p class="error-page__message">サーバーで問題が発生しました。しばらくしてから再度お試しください。</p>
    <a href="/" class="btn btn--primary">ホームに戻る</a>
</div>

@push('styles')
<style>
    .error-page { text-align: center; padding: var(--space-12) var(--space-4); }
    .error-page__code { font-size: 6rem; font-weight: 700; color: var(--color-text-secondary); line-height: 1; margin-bottom: var(--space-4); }
    .error-page__message { font-size: var(--font-size-lg); color: var(--color-text-secondary); margin-bottom: var(--space-8); }
</style>
@endpush
@endsection
