@extends('layouts.app')

@section('title', '訪問記録を追加 - LoveAtlas')

@section('content')
    <div class="page page--visit-create">
        <div class="page__inner">
            <h1 class="page__title">訪問記録を追加</h1>
            <h2 class="page__subtitle">{{ $location->name }}</h2>

            <div id="visit-form-app" data-location-id="{{ $location->id }}">
                {{-- Vue が app.js でここに VisitForm をマウント --}}
            </div>
        </div>
    </div>
@endsection
