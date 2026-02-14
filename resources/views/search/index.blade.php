@extends('layouts.app')

@section('title', '検索結果 - LoveAtlas')

@section('content')
    <div class="page page--search">
        <div class="page__inner">
            <h1 class="page__title">検索結果</h1>

            @if(!empty($keyword))
                <p class="page__query">「{{ e($keyword) }}」の検索結果</p>

                <section class="search-section">
                    <h2 class="search-section__title">セリフ ({{ count($results['quotes']) }}件)</h2>
                    @if(count($results['quotes']) > 0)
                        <div class="quote-grid">
                            @foreach($results['quotes'] as $quote)
                                <div class="quote-grid__item">
                                    <article class="quote-card">
                                        <a href="{{ url('/quotes/' . $quote->id) }}" class="quote-card__link">
                                            <p class="quote-card__text">{{ e($quote->quote_text) }}</p>
                                            @if($quote->character_name)
                                                <p class="quote-card__character">— {{ e($quote->character_name) }}</p>
                                            @endif
                                            <div class="quote-card__meta">
                                                @if($quote->work)
                                                    <span class="quote-card__work">{{ e($quote->work->title) }}</span>
                                                @endif
                                                @if($quote->location)
                                                    <span class="quote-card__location">{{ e($quote->location->name) }}</span>
                                                @endif
                                            </div>
                                        </a>
                                        <div class="quote-card__actions" data-quote-id="{{ $quote->id }}" data-likes="{{ $quote->likes_count ?? 0 }}">
                                            {{-- LikeButton / FavoriteButton は Vue アイランドで必要に応じてマウント --}}
                                        </div>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="search-section__empty">一致するセリフが見つかりませんでした</p>
                    @endif
                </section>

                <section class="search-section">
                    <h2 class="search-section__title">作品 ({{ count($results['works']) }}件)</h2>
                    @if(count($results['works']) > 0)
                        <ul class="search-list">
                            @foreach($results['works'] as $work)
                                <li class="search-list__item">
                                    <a href="#" class="search-list__link">
                                        {{ e($work->title) }}
                                    </a>
                                    <span class="search-list__count">{{ $work->quotes_count ?? 0 }}件のセリフ</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="search-section__empty">一致する作品が見つかりませんでした</p>
                    @endif
                </section>

                <section class="search-section">
                    <h2 class="search-section__title">場所 ({{ count($results['locations']) }}件)</h2>
                    @if(count($results['locations']) > 0)
                        <ul class="search-list">
                            @foreach($results['locations'] as $location)
                                <li class="search-list__item">
                                    <a href="#" class="search-list__link">{{ e($location->name) }}</a>
                                    @if($location->address)
                                        <span class="search-list__sub">{{ e($location->address) }}</span>
                                    @endif
                                    <span class="search-list__count">{{ $location->quotes_count ?? 0 }}件のセリフ</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="search-section__empty">一致する場所が見つかりませんでした</p>
                    @endif
                </section>
            @else
                <p class="page__empty">検索キーワードを入力してください</p>
            @endif
        </div>
    </div>
@endsection
