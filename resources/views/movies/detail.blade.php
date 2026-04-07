@extends('layouts.app')

@section('title', $movie['title'] ?? 'Movie - KFLIX')

@section('content')
<style>
:root { --primary: #e50914; --bg-dark: #0a0a0a; --card-bg: #141414; --text-main: #fff; --text-dim: #b3b3b3; --border-color: #333; }
.detail-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
.detail-hero { margin-bottom: 40px; display: flex; gap: 30px; }
.detail-poster { flex-shrink: 0; width: 300px; border-radius: 12px; overflow: hidden; }
.detail-poster img { width: 100%; }
.detail-info h1 { font-size: 2.5rem; margin-bottom: 10px; }
.detail-meta { display: flex; gap: 15px; margin-bottom: 15px; color: var(--text-dim); }
.detail-actions { display: flex; gap: 15px; margin: 20px 0; }
.btn-play-d { background: var(--primary); color: #fff; border: none; padding: 12px 30px; border-radius: 30px; cursor: pointer; font-size: 1.1rem; font-weight: 600; }
.btn-list-d { background: transparent; color: #fff; border: 2px solid #fff; padding: 12px 30px; border-radius: 30px; cursor: pointer; font-size: 1.1rem; }
.detail-overview { line-height: 1.8; }
.cast-section { margin-top: 40px; }
.cast-section h2 { margin-bottom: 15px; font-size: 1.3rem; }
.cast-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 15px; }
.cast-card img { width: 100%; border-radius: 8px; }
.cast-card p { font-size: 0.8rem; text-align: center; margin-top: 5px; color: var(--text-dim); }
.recs-section { margin-top: 40px; }
.recs-section h2 { margin-bottom: 15px; }
.rec-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 15px; }
.rec-item { cursor: pointer; transition: transform 0.2s; }
.rec-item:hover { transform: scale(1.05); }
.rec-item img { width: 100%; border-radius: 8px; }
.rec-item p { font-size: 0.85rem; margin-top: 5px; }
</style>

<div class="detail-container">
    @php
        $poster = $movie['poster_path'] ? "https://image.tmdb.org/t/p/w500" . $movie['poster_path'] : "https://via.placeholder.com/500x750?text=No+Poster";
        $backdrop = $movie['backdrop_path'] ? "https://image.tmdb.org/t/p/original" . $movie['backdrop_path'] : $poster;
    @endphp
    <div class="hero-section" style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.6) 0%, rgba(10,10,10,1) 100%), url('{{ $backdrop }}'); background-size: cover; background-position: center; padding: 60px 30px; border-radius: 12px; margin-bottom: 40px; display: flex; gap: 30px; align-items: flex-end;">
        <div style="flex-shrink:0;width:250px;border-radius:12px;overflow:hidden;box-shadow:0 8px 20px rgba(0,0,0,0.6);"><img src="{{ $poster }}" alt="{{ $movie['title'] }}" onerror="this.src='https://via.placeholder.com/500x750?text=No+Image'"></div>
        <div>
            <h1 style="font-size:2.5rem;margin-bottom:10px;">{{ $movie['title'] }}</h1>
            <div style="display:flex;gap:15px;margin-bottom:10px;">
                <span>&#11088; {{ number_format($movie['vote_average'] ?? 0, 1) }}</span>
                <span>{{ $movie['release_date'] ? substr($movie['release_date'],0,4) : 'N/A' }}</span>
                <span>{{ $movie['runtime'] ?? 'N/A' }} min</span>
            </div>
            @if(!empty($movie['genres']))<p>{{ implode(', ', array_column($movie['genres'], 'name')) }}</p>@endif
            <div style="margin:20px 0;"><p>{{ $movie['overview'] }}</p></div>
            <div style="display:flex;gap:15px;">
                <button class="btn-play-d" onclick="window.location.href='/watch?id={{ $movie['id'] }}'"><i class="fas fa-play"></i> Play</button>
                <button class="btn-list-d" id="watchlistBtn"><i class="far fa-heart"></i> My List</button>
            </div>
        </div>
    </div>

    @if(!empty($movie['credits']['cast']))
    <div style="margin-bottom:40px;"><h2 style="margin-bottom:15px;font-size:1.3rem;"><i class="fas fa-users"></i> Cast</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:15px;">
            @foreach(array_slice($movie['credits']['cast'], 0, 10) as $actor)
            <div><img src="https://image.tmdb.org/t/p/w185{{ $actor['profile_path'] }}" alt="{{ $actor['name'] }}" onerror="this.src='https://via.placeholder.com/120x180?text=No+Image'" style="width:100%;border-radius:8px;"><p style="font-size:0.8rem;text-align:center;margin-top:5px;color:#b3b3b3;">{{ $actor['name'] }}</p></div>
            @endforeach
        </div>
    </div>
    @endif

    @if(!empty($movie['recommendations']['results']))
    <div style="margin-bottom:40px;"><h2 style="margin-bottom:15px;font-size:1.3rem;"><i class="fas fa-thumbs-up"></i> You May Also Like</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:15px;">
            @foreach(array_slice($movie['recommendations']['results'], 0, 12) as $rec)
            <div style="cursor:pointer;" onclick="window.location.href='/movie/{{ $rec['id'] }}'">
                <img src="https://image.tmdb.org/t/p/w300{{ $rec['poster_path'] }}" alt="{{ $rec['title'] }}" onerror="this.src='https://via.placeholder.com/300x450?text=No+Image'" style="width:100%;border-radius:8px;">
                <p style="font-size:0.85rem;margin-top:5px;">{{ $rec['title'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<style>
    .btn-play-d,.btn-list-d { background:var(--primary);color:#fff;border:none;padding:12px 30px;border-radius:30px;cursor:pointer;font-size:1.1rem;font-weight:600; }
    .btn-list-d { background:transparent;border:2px solid #fff; }
    .btn-play-d:hover { background:#b2070f; }
    .btn-list-d:hover { background:rgba(255,255,255,0.1); }
</style>

<script>
function toggleWatchlist(contentId) {
    fetch('/api/watchlist', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: contentId, title: "{{ $movie['title'] }}", poster_path: "{{ $movie['poster_path'] }}", vote_average: {{ $movie['vote_average'] ?? 0 }}, release_date: "{{ $movie['release_date'] }}" }) })
    .then(r => r.json()).then(d => { if(d.success) { var b = document.getElementById('watchlistBtn'); b.innerHTML = d.in_watchlist || d.count ? '<i class="fas fa-heart"></i> In My List' : '<i class="far fa-heart"></i> My List'; }});
}
document.getElementById('watchlistBtn').addEventListener('click', function() { toggleWatchlist({{ $movie['id'] }}); });
</script>
@endsection
