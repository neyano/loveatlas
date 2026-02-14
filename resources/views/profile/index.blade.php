@extends('layouts.app')

@section('title', 'プロフィール - LoveAtlas')

@section('content')
<div class="profile">
    {{-- プロフィールヘッダー --}}
    <div class="profile__header">
        <div class="profile__avatar">
            @if($user->avatar_path)
                <img src="{{ asset('storage/' . $user->avatar_path) }}" alt="{{ $user->display_name }}">
            @else
                <div class="profile__avatar-placeholder">
                    {{ mb_substr($user->display_name, 0, 1) }}
                </div>
            @endif
        </div>

        <div class="profile__info">
            <h1 class="profile__name">{{ $user->display_name }}</h1>
            <p class="profile__username">{{ '@' . $user->username }}</p>
            @if($user->bio)
                <p class="profile__bio">{{ $user->bio }}</p>
            @endif

            <div class="profile__stats">
                <span class="profile__stat">
                    <strong>{{ $stats['posts_count'] }}</strong> 投稿
                </span>
                <span class="profile__stat">
                    <strong>{{ $stats['favorites_count'] }}</strong> お気に入り
                </span>
                <span class="profile__stat">
                    <strong>{{ $stats['visits_count'] }}</strong> 訪問
                </span>
            </div>
        </div>

        <div class="profile__actions">
            <a href="{{ route('profile.settings') }}" class="btn btn--secondary">設定</a>
        </div>
    </div>

    {{-- タブナビゲーション --}}
    <nav class="profile__tabs">
        <a href="{{ route('profile.index', ['tab' => 'posts']) }}"
           class="profile__tab {{ $tab === 'posts' ? 'profile__tab--active' : '' }}">
            投稿
        </a>
        <a href="{{ route('profile.index', ['tab' => 'favorites']) }}"
           class="profile__tab {{ $tab === 'favorites' ? 'profile__tab--active' : '' }}">
            お気に入り
        </a>
        <a href="{{ route('profile.index', ['tab' => 'visits']) }}"
           class="profile__tab {{ $tab === 'visits' ? 'profile__tab--active' : '' }}">
            訪問記録
        </a>
    </nav>

    {{-- タブコンテンツ --}}
    <div class="profile__content">
        @if($items->isEmpty())
            <div class="profile__empty">
                @switch($tab)
                    @case('favorites')
                        <p>お気に入りのセリフはまだありません</p>
                        <a href="/explore" class="btn btn--primary">セリフを探す</a>
                        @break
                    @case('visits')
                        <p>訪問記録はまだありません</p>
                        <a href="/" class="btn btn--primary">地図を見る</a>
                        @break
                    @default
                        <p>投稿はまだありません</p>
                        {{-- <a href="/quotes/new" class="btn btn--primary">セリフを投稿する</a> --}}
                @endswitch
            </div>
        @else
            @if($tab === 'visits')
                {{-- 訪問記録一覧 --}}
                <div class="profile__grid">
                    @foreach($items as $visit)
                        <div class="card profile__visit-card">
                            <h3 class="profile__visit-location">📍 {{ $visit->location->name }}</h3>
                            <p class="profile__visit-date">{{ $visit->visited_at->format('Y年n月j日') }}</p>
                            @if($visit->rating)
                                <p class="profile__visit-rating">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="{{ $i <= $visit->rating ? 'star--filled' : 'star--empty' }}">★</span>
                                    @endfor
                                </p>
                            @endif
                            @if($visit->note)
                                <p class="profile__visit-note">{{ Str::limit($visit->note, 100) }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                {{-- セリフ一覧 (投稿 / お気に入り) --}}
                <div class="profile__grid">
                    @foreach($items as $quote)
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
                                @if($tab === 'posts')
                                    <span class="profile__quote-status profile__quote-status--{{ $quote->status }}">
                                        {{ match($quote->status) {
                                            'approved' => '公開中',
                                            'pending' => '承認待ち',
                                            'rejected' => '却下',
                                            default => $quote->status,
                                        } }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- ページネーション --}}
            <div class="profile__pagination">
                {{ $items->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
