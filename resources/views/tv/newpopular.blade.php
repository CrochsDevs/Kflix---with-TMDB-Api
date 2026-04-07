@extends('layouts.app')

@section('title', 'New & Popular - Watch Free')

@section('content')
@php
    $watchlistIds = [];
    try { $w = new App\Models\Watchlist(); $wi = $w->getWatchlist(); $watchlistIds = array_column($wi, 'movie_id'); } catch (Exception $e) {}
@endphp

<div class="main-layout">
    <main class="main-content" style="width:100%;">
        <div class="results-header">
            <h1 class="section-title">New & Popular</h1>
            <span class="results-count">Showing {{ count($tvshows) }} TV shows</span>
        </div>

        @if(empty($tvshows))<div class="no-results"><i class="fas fa-tv"></i><h3>Loading...</h3></div>@endif

        <div class="movie-grid" id="tvGrid">
            @foreach($tvshows as $show)
            @php
                $poster = $show['poster_path'] ? "https://image.tmdb.org/t/p/w500" . $show['poster_path'] : "https://via.placeholder.com/500x750?text=No+Poster";
                $year = !empty($show['first_air_date']) ? substr($show['first_air_date'], 0, 4) : 'N/A';
                $inList = in_array($show['id'], $watchlistIds);
            @endphp
            <div class="movie-card" data-id="{{ $show['id'] }}" data-title="{{ $show['name'] }}" data-poster="{{ $show['poster_path'] }}" data-rating="{{ $show['vote_average'] }}" data-year="{{ $year }}" data-watchlist="{{ $inList ? '1' : '0' }}">
                <div class="card-poster">
                    <img src="{{ $poster }}" alt="{{ $show['name'] }}" loading="lazy" onerror="this.src='https://via.placeholder.com/500x750?text=No+Image'">
                    <div class="card-overlay">
                        <button class="play-icon" onclick="window.location.href='/watch?id={{ $show['id'] }}&type=tv&season=1&episode=1'"><i class="fas fa-play"></i></button>
                        <button class="like-icon"><i class="{{ $inList ? 'fas' : 'far' }} fa-heart" style="{{ $inList ? 'color: var(--primary-color);' : '' }}"></i></button>
                        <button class="info-icon"><i class="fas fa-info-circle"></i></button>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="card-rating"><span class="rating-badge">&#11088; {{ number_format($show['vote_average'], 1) }}</span></div>
                    <p class="movie-title">{{ $show['name'] }}</p>
                    <div class="movie-meta"><span class="release-year">{{ $year }}</span></div>
                </div>
            </div>
            @endforeach
        </div>
    </main>
</div>
@endsection
