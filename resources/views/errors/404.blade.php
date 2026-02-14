@extends('layouts.minimal')

@section('title', '404 - ページが見つかりません')

@section('content')
<div class="error-page">
    <h1 class="error-page__code">404</h1>
    <p class="error-page__message">お探しのページは見つかりませんでした。</p>
    <a href="/" class="btn btn--primary">ホームに戻る</a>
</div>

@push('styles')
<style>
    .error-page { text-align: center; padding: var(--space-12) var(--space-4); }
    .error-page__code { font-size: 6rem; font-weight: 700; color: var(--color-primary); line-height: 1; margin-bottom: var(--space-4); }
    .error-page__message { font-size: var(--font-size-lg); color: var(--color-text-secondary); margin-bottom: var(--space-8); }
</style>
@endpush
@endsection
