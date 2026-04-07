@extends("layouts.app")

@section("title", "New & Popular - Watch Free")

@section("content")
@php $wl = []; try { $w = new App\Models\Watchlist(); $wl = array_column($w->getWatchlist(), "movie_id"); } catch (Exception $e) {} @endphp
<div class="main-layout">
    <div class="content-area">
        <div class="results-header">
            <h1 class="section-title">New & Popular</h1>
        </div>
        @if(empty($tvshows ?? []))
        <div class="no-results"><i class="fa-solid fa-tv"></i><h3>Loading...</h3></div>
        @endif
        <div class="movie-grid">
            @foreach($tvshows ?? [] as $show)
            @php
                $poster = $show["poster_path"] ? "https://image.tmdb.org/t/p/w500" . $show["poster_path"] : "https://via.placeholder.com/500x750?text=No+Poster";
                $year = !empty($show["first_air_date"]) ? substr($show["first_air_date"], 0, 4) : "N/A";
                $inList = in_array($show["id"], $wl);
            @endphp
            <div class="movie-card" data-id="{{ $show["id"] }}" data-title="{{ $show["name"] }}" data-poster="{{ $show["poster_path"] }}" data-rating="{{ $show["vote_average"] }}" data-year="{{ $year }}" data-watchlist="{{ $inList ? "1" : "0" }}">
                <div class="card-poster">
                    <img src="{{ $poster }}" alt="{{ $show["name"] }}" loading="lazy" onerror="this.src='https://via.placeholder.com/500x750?text=No+Image'">
                    <div class="card-overlay">
                        <div class="overlay-buttons">
                            <button class="overlay-btn play" onclick="location.href='{{ route("player") }}?id={{ $show["id"] }}&type=tv&season=1&episode=1'"><i class="fa-solid fa-play"></i></button>
                            <button class="overlay-btn heart"><i class="{{ $inList ? "fa-solid" : "fa-regular" }} fa-heart"></i></button>
                            <button class="overlay-btn info"><i class="fa-solid fa-info-circle"></i></button>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <p class="movie-title">{{ $show["name"] }}</p>
                    <div class="movie-meta">
                        <span class="rating-badge">&#11088; {{ number_format($show["vote_average"], 1) }}</span>
                        <span class="release-year">{{ $year }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
