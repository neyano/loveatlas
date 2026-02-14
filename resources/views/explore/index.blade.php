@extends('layouts.app')

@section('title', 'Explore - LoveAtlas')

@section('content')
    <div class="page page--explore">
        <div class="page__inner">
            <h1 class="page__title">Explore</h1>

            <section class="explore-section">
                <h2 class="explore-section__title">人気のセリフ</h2>
                <div class="quote-grid">
                    @foreach($popular as $quote)
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
                                <div class="quote-card__actions" data-quote-id="{{ $quote->id }}" data-likes="{{ $quote->likes_count ?? 0 }}"></div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="explore-section">
                <h2 class="explore-section__title">新着のセリフ</h2>
                <div class="quote-grid">
                    @foreach($recent as $quote)
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
                                <div class="quote-card__actions" data-quote-id="{{ $quote->id }}" data-likes="{{ $quote->likes_count ?? 0 }}"></div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
@endsection
