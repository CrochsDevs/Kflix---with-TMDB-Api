@extends("layouts.app")

@section("title", ($movie["title"] ?? "") . " - Watch Free")

@section("content")
<div class="detail-page">
    <div class="detail-hero" style="background-image:url('https://image.tmdb.org/t/p/original{{ $movie["backdrop_path"] ?? $movie["poster_path"] }}')">
        <div class="detail-hero-inner">
            <div class="detail-poster"><img src="https://image.tmdb.org/t/p/w500{{ $movie["poster_path"] }}" alt="{{ $movie["title"] }}" onerror="this.src='https://via.placeholder.com/250x375?text=No+Image'"></div>
            <div class="detail-info">
                <h1>{{ $movie["title"] }}</h1>
                <div class="detail-badges">
                    <span class="badge r">&#11088; {{ number_format($movie["vote_average"] ?? 0, 1) }}</span>
                    <span class="badge">{{ $movie["release_date"] ? substr($movie["release_date"], 0, 4) : "N/A" }}</span>
                    @if(!empty($movie["runtime"]))<span class="badge">{{ $movie["runtime"] }} min</span>@endif
                    @if(!empty($movie["genres"]))
                    @foreach($movie["genres"] as $g)<span class="badge">{{ $g["name"] }}</span>@endforeach
                    @endif
                </div>
                <p class="detail-overview">{{ $movie["overview"] }}</p>
                <div class="detail-actions">
                    <a href="{{ route("player") }}?id={{ $movie["id"] }}" class="btn btn-play"><i class="fa-solid fa-play"></i> Play</a>
                    <button class="btn btn-outline" id="addToListBtn"><i class="fa-regular fa-heart"></i> My List</button>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($movie["credits"]["cast"]))
    <div class="detail-cast">
        <h2>Cast</h2>
        <div class="cast-grid">
            @foreach(array_slice($movie["credits"]["cast"], 0, 10) as $actor)
            <div class="cast-card">
                <img src="https://image.tmdb.org/t/p/w185{{ $actor["profile_path"] }}" alt="{{ $actor["name"] }}" onerror="this.src='https://via.placeholder.com/185x278?text=No+Image'">
                <p>{{ $actor["name"] }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<script>
document.getElementById("addToListBtn").addEventListener("click", function() {
    fetch("/api/watchlist", { method: "POST", headers: {"Content-Type":"application/json"}, body: JSON.stringify({id:{{ $movie["id"] }}, title:"{{ $movie["title"] }}", poster_path:"{{ $movie["poster_path"] }}", vote_average:{{ $movie["vote_average"] ?? 0 }}, release_date:"{{ $movie["release_date"] }}"}) })
    .then(function(r){return r.json()}).then(function(d){
        var btn = document.getElementById("addToListBtn");
        if(d.success) btn.innerHTML = d.count > 0 ? '<i class="fa-solid fa-heart"></i> In My List' : '<i class="fa-regular fa-heart"></i> My List';
    });
});
</script>
@endsection
