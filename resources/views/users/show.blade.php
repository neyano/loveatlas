@extends('layouts.app')

@section('title', $profileUser->display_name . ' - LoveAtlas')

@section('content')
<div class="public-profile">
    {{-- プロフィールヘッダー --}}
    <div class="profile__header">
        <div class="profile__avatar">
            @if($profileUser->avatar_path)
                <img src="{{ asset('storage/' . $profileUser->avatar_path) }}" alt="{{ $profileUser->display_name }}">
            @else
                <div class="profile__avatar-placeholder">
                    {{ mb_substr($profileUser->display_name, 0, 1) }}
                </div>
            @endif
        </div>

        <div class="profile__info">
            <h1 class="profile__name">{{ $profileUser->display_name }}</h1>
            <p class="profile__username">{{ '@' . $profileUser->username }}</p>
            @if($profileUser->bio)
                <p class="profile__bio">{{ $profileUser->bio }}</p>
            @endif

            <div class="profile__stats">
                <span class="profile__stat">
                    <strong>{{ $stats['posts_count'] }}</strong> 投稿
                </span>
            </div>
        </div>
    </div>

    {{-- セリフ一覧 --}}
    <div class="public-profile__section">
        <h2 class="public-profile__section-title">投稿したセリフ</h2>

        @if($quotes->isEmpty())
            <div class="profile__empty">
                <p>まだ投稿はありません</p>
            </div>
        @else
            <div class="profile__grid">
                @foreach($quotes as $quote)
                    <div class="card profile__quote-card">
                        <blockquote class="profile__quote-text">
                            「{{ Str::limit($quote->quote_text, 80) }}」
                        </blockquote>
                        @if($quote->character_name)
                            <p class="profile__quote-character">── {{ $quote->character_name }}</p>
                        @endif
                        <div class="profile__quote-meta">
                            @if($quote->work)
                                <span class="profile__quote-work">🎬 {{ $quote->work->title }}</span>
                            @endif
                            @if($quote->location)
                                <span class="profile__quote-location">📍 {{ $quote->location->name }}</span>
                            @endif
                        </div>
                        <div class="profile__quote-footer">
                            <span>♡ {{ $quote->likes_count }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="profile__pagination">
                {{ $quotes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
