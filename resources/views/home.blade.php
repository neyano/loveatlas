@extends('layouts.app')

@section('title', 'LoveAtlas - 名セリフで旅する世界地図')

@section('content')
    <div class="map-page">
        <aside class="map-page__side" id="side-panel">
            {{-- Vue: SidePanel component will mount here --}}
        </aside>
        <div class="map-page__map" id="map-container">
            {{-- Vue: MapView component will mount here --}}
        </div>
    </div>
@endsection
