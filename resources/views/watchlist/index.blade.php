@extends('layouts.app')

@section('title', 'My Watchlist - KFLIX')

@section('content')
@php
    $watchlistIds = [];
    try { $w = new App\Models\Watchlist(); $wi = $w->getWatchlist(); $watchlistIds = array_column($wi, 'movie_id'); } catch (Exception $e) {}
@endphp

<div class="watchlist-container" style="max-width:1400px;margin:0 auto;padding:20px;">
    <h1 style="margin-bottom:30px;"><i class="fas fa-heart" style="color:var(--primary-color,#e50914)"></i> My Watchlist</h1>

    @if(!empty($items))
    <div class="movie-grid" id="watchlistGrid">
        @foreach($items as $movie)
        @php
            $poster = $movie['poster_path'] ? "https://image.tmdb.org/t/p/w500" . $movie['poster_path'] : "https://via.placeholder.com/500x750?text=No+Poster";
            $year = !empty($movie['release_date']) ? substr($movie['release_date'], 0, 4) : 'N/A';
        @endphp
        <div class="movie-card" data-id="{{ $movie['movie_id'] ?? $movie['id'] }}" data-title="{{ $movie['title'] }}" data-poster="{{ $movie['poster_path'] }}" data-rating="{{ $movie['vote_average'] }}" data-year="{{ $year }}">
            <div class="card-poster">
                <img src="{{ $poster }}" alt="{{ $movie['title'] }}" loading="lazy" onerror="this.src='https://via.placeholder.com/500x750?text=No+Image'">
                <div class="card-overlay">
                    <button class="play-icon" onclick="window.location.href='/watch?id={{ $movie['movie_id'] ?? $movie['id'] }}'"><i class="fas fa-play"></i></button>
                    <button class="like-icon" title="Remove" onclick="removeFromWatchlist({{ $movie['movie_id'] ?? $movie['id'] }})"><i class="fas fa-heart" style="color: var(--primary-color)"></i></button>
                    <button class="info-icon"><i class="fas fa-info-circle"></i></button>
                </div>
            </div>
            <div class="card-footer">
                <div class="card-rating"><span class="rating-badge">&#11088; {{ number_format($movie['vote_average'], 1) }}</span><span class="match-badge">In Watchlist</span></div>
                <p class="movie-title">{{ $movie['title'] }}</p>
                <div class="movie-meta"><span class="release-year">{{ $year }}</span></div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="no-results"><i class="fas fa-film"></i><h3>Your watchlist is empty</h3><p>Add movies to keep track of what you want to watch.</p></div>
    @endif
</div>

<script>
function removeFromWatchlist(id) {
    fetch('/api/watchlist?movie_id=' + id, { method: 'DELETE' }).then(r => r.json()).then(() => location.reload());
}
</script>
@endsection
