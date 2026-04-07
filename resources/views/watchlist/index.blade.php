@extends("layouts.app")

@section("title", "My Watchlist - KFLIX")

@section("content")
<div class="watchlist-page">
    <h1><i class="fa-solid fa-heart"></i> My List</h1>
    @if(!empty($items))
    <div class="movie-grid">
        @foreach($items as $movie)
        @php
            $poster = $movie["poster_path"] ? "https://image.tmdb.org/t/p/w500" . $movie["poster_path"] : "https://via.placeholder.com/500x750?text=No+Poster";
            $year = !empty($movie["release_date"]) ? substr($movie["release_date"], 0, 4) : "N/A";
        @endphp
        <div class="movie-card" data-id="{{ $movie["movie_id"] ?? $movie["id"] }}">
            <div class="card-poster">
                <img src="{{ $poster }}" alt="{{ $movie["title"] }}" loading="lazy" onerror="this.src='https://via.placeholder.com/500x750?text=No+Image'">
                <div class="card-overlay">
                    <div class="overlay-buttons">
                        <button class="overlay-btn play" onclick="location.href='{{ route("player") }}?id={{ $movie["movie_id"] ?? $movie["id"] }}'"><i class="fa-solid fa-play"></i></button>
                        <button class="overlay-btn heart" onclick="removeFromList({{ $movie["movie_id"] ?? $movie["id"] }})"><i class="fa-solid fa-heart" style="color:var(--clr-primary)"></i></button>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <p class="movie-title">{{ $movie["title"] }}</p>
                <div class="movie-meta">
                    <span class="rating-badge">&#11088; {{ number_format($movie["vote_average"], 1) }}</span>
                    <span class="release-year">{{ $year }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="no-results"><i class="fa-solid fa-film"></i><h3>Your list is empty</h3><p>Add movies to keep track of what you want to watch.</p></div>
    @endif
</div>

<script>
function removeFromList(id) {
    fetch("/api/watchlist?movie_id=" + id, { method: "DELETE" }).then(function() { location.reload(); });
}
</script>
@endsection
