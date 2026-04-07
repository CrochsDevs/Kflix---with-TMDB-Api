@extends("layouts.app")

@section("title", "KFLIX - Watch Free Movies")

@section("content")
@php
    $wl = [];
    try { $w = new App\Models\Watchlist(); $wl = array_column($w->getWatchlist(), "movie_id"); } catch (Exception $e) {}
    $s = $search ?? "";
    $g = $genre ?? 0;
    $f = $filter ?? "day";
@endphp

<div class="main-layout">
    <aside class="sidebar">
        <h3><i class="fa-solid fa-sliders-h"></i> Filters</h3>
        <div class="search-wrapper">
            <i class="fa-solid fa-search"></i>
            <input type="text" class="search-input" placeholder="Search movies..." value="{{ $s }}" onkeydown="if(event.key==='Enter')this.form.submit()">
            @if(!empty($s))<a href="{{ url()->current() }}" class="clear-search"><i class="fa-solid fa-times"></i></a>@endif
        </div>
        <div class="filter-section">
            <h4><i class="fa-solid fa-tags"></i> Genres</h4>
            <div class="genres">
                <a href="?{{ http_build_query(["search"=>$s,"filter"=>$f]) }}" class="genre-item {{ $g==0 ? "active" : "" }}">All</a>
                @foreach(array_slice($genres ?? [], 0, 15) as $gr)
                <a href="?{{ http_build_query(["genre"=>$gr["id"],"search"=>$s,"filter"=>$f]) }}" class="genre-item {{ $g==$gr["id"] ? "active" : "" }}">{{ $gr["name"] }}</a>
                @endforeach
            </div>
        </div>
        <div class="filter-section">
            <h4><i class="fa-solid fa-clock"></i> Time</h4>
            <div class="time-filters">
                <a href="?{{ http_build_query(["filter"=>"day","genre"=>$g,"search"=>$s]) }}" class="time-btn {{ $f=="day" && empty($s) ? "active" : "" }}">Today</a>
                <a href="?{{ http_build_query(["filter"=>"week","genre"=>$g,"search"=>$s]) }}" class="time-btn {{ $f=="week" && empty($s) ? "active" : "" }}">Week</a>
            </div>
        </div>
        <div class="filter-section">
            <h4><i class="fa-solid fa-sort"></i> Sort</h4>
            <select class="sort-select" onchange="location.search=this.value">
                <option value="?{{ http_build_query(["search"=>$s,"genre"=>$g>0?$g:null,"filter"=>empty($s)&&$g==0?$f:null,"sort"=>"popularity.desc"]) }}" {{ ($sortBy ?? "popularity.desc")=="popularity.desc" ? "selected" : "" }}>Popularity</option>
                <option value="?{{ http_build_query(["search"=>$s,"genre"=>$g>0?$g:null,"filter"=>empty($s)&&$g==0?$f:null,"sort"=>"vote_average.desc"]) }}" {{ ($sortBy ?? "")=="vote_average.desc" ? "selected" : "" }}>Rating</option>
                <option value="?{{ http_build_query(["search"=>$s,"genre"=>$g>0?$g:null,"filter"=>empty($s)&&$g==0?$f:null,"sort"=>"release_date.desc"]) }}" {{ ($sortBy ?? "")=="release_date.desc" ? "selected" : "" }}>Release Date</option>
                <option value="?{{ http_build_query(["search"=>$s,"genre"=>$g>0?$g:null,"filter"=>empty($s)&&$g==0?$f:null,"sort"=>"title.asc"]) }}" {{ ($sortBy ?? "")=="title.asc" ? "selected" : "" }}>A-Z</option>
            </select>
        </div>
        @if(!empty($s) || $g>0 || $f!="day")
        <a href="{{ url()->current() }}" class="clear-filters-btn"><i class="fa-solid fa-undo-alt"></i> Clear All Filters</a>
        @endif
    </aside>

    <div class="content-area">
        @if(!empty($movies) && ($page ?? 1)==1 && empty($s) && $g==0)
        <div class="hero-section" style="background-image:url('https://image.tmdb.org/t/p/original{{ $movies[0]["backdrop_path"] }}')">
            <div class="hero-content">
                <h1 class="hero-title">{{ $movies[0]["title"] }}</h1>
                <p class="hero-description">{{ Str::limit($movies[0]["overview"], 200) }}</p>
                <div class="hero-buttons">
                    <a href="{{ route("player") }}?id={{ $movies[0]["id"] }}" class="btn-play"><i class="fa-solid fa-play"></i> Play</a>
                </div>
            </div>
        </div>
        @endif

        <div class="results-header">
            <h1 class="section-title">
                @if(!empty($s))Search: "{{ $s }}"
                @elseif($g>0){{ $genreName ?? "Movies" }}
                @else{{ $f=="day" ? "Trending Today" : "Trending This Week" }}@endif
            </h1>
            <span class="results-info">{{ count($movies ?? []) }} results</span>
        </div>

        @if(empty($movies ?? []))
        <div class="no-results"><i class="fa-solid fa-film"></i><h3>No movies found</h3><p>Try adjusting your filters.</p></div>
        @endif

        <div class="movie-grid">
            @foreach($movies ?? [] as $mv)
            @php
                $poster = $mv["poster_path"] ? "https://image.tmdb.org/t/p/w500" . $mv["poster_path"] : "https://via.placeholder.com/500x750?text=No+Poster";
                $year = !empty($mv["release_date"]) ? substr($mv["release_date"], 0, 4) : "N/A";
                $inList = in_array($mv["id"], $wl);
            @endphp
            <div class="movie-card" data-id="{{ $mv["id"] }}" data-title="{{ $mv["title"] }}" data-poster="{{ $mv["poster_path"] }}" data-rating="{{ $mv["vote_average"] }}" data-year="{{ $year }}" data-watchlist="{{ $inList ? "1" : "0" }}">
                <div class="card-poster">
                    <img src="{{ $poster }}" alt="{{ $mv["title"] }}" loading="lazy" onerror="this.src='https://via.placeholder.com/500x750?text=No+Image'">
                    <div class="card-overlay">
                        <div class="overlay-buttons">
                            <button class="overlay-btn play" onclick="location.href='{{ route("player") }}?id={{ $mv["id"] }}'"><i class="fa-solid fa-play"></i></button>
                            <button class="overlay-btn heart" data-mid="{{ $mv["id"] }}"><i class="{{ $inList ? "fa-solid" : "fa-regular" }} fa-heart"{{ $inList ? ' style="color:var(--clr-primary)"' : "" }}></i></button>
                            <button class="overlay-btn info" data-mid="{{ $mv["id"] }}"><i class="fa-solid fa-info-circle"></i></button>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <p class="movie-title">{{ $mv["title"] }}</p>
                    <div class="movie-meta">
                        <span class="rating-badge">&#11088; {{ number_format($mv["vote_average"], 1) }}</span>
                        <span class="release-year">{{ $year }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if(!empty($movies) && ($totalPages ?? 1)>1)
        <div class="pagination">
            @php
                $p = $page ?? 1;
                $qp = array_filter(["search"=>!empty($s)?$s:null, "genre"=>$g>0?$g:null, "filter"=>empty($s)&&$g==0?$f:null, "sort"=>($sortBy??"popularity.desc")!="popularity.desc"?($sortBy??""):null]);
                $px = !empty($qp) ? "&".http_build_query($qp) : "";
            @endphp
            @if($p>1)<a href="?page={{ $p-1 }}{{ $px }}" class="page-link"><i class="fa-solid fa-chevron-left"></i></a>@endif
            @for($i=max(1,$p-2);$i<=min($totalPages??500,$p+2);$i++)
            <a href="?page={{ $i }}{{ $px }}" class="page-num {{ $i==$p ? "active" : "" }}">{{ $i }}</a>
            @endfor
            @if($p<($totalPages??1))<a href="?page={{ $p+1 }}{{ $px }}" class="page-link"><i class="fa-solid fa-chevron-right"></i></a>@endif
        </div>
        @endif
    </div>
</div>

@push("scripts")
<script>
document.addEventListener("click", function(e) {
    var heartBtn = e.target.closest(".overlay-btn.heart");
    if (heartBtn) {
        e.preventDefault();
        var card = heartBtn.closest(".movie-card");
        var id = card.dataset.id;
        var title = card.dataset.title;
        var poster = card.dataset.poster || "";
        var rating = parseFloat(card.dataset.rating) || 0;
        var year = card.dataset.year || "";
        var icon = heartBtn.querySelector("i");
        var isAdded = icon.classList.contains("fa-solid");
        fetch("/api/watchlist", {
            method: isAdded ? "DELETE" : "POST",
            headers: { "Content-Type": "application/json" },
            body: isAdded ? null : JSON.stringify({id:parseInt(id),title:title,poster_path:poster,vote_average:rating,release_date:year})
        }).then(function(r){return r.json()}).then(function(d){
            if (d.success) {
                if (isAdded) { icon.classList.replace("fa-solid","fa-regular"); icon.style.color=""; card.dataset.watchlist="0"; }
                else { icon.classList.replace("fa-regular","fa-solid"); icon.style.color="var(--clr-primary)"; card.dataset.watchlist="1"; }
            }
        });
        return;
    }
});

function openMovieModal(id) {
    var modal = document.getElementById("movieModal");
    var loading = document.getElementById("modalLoading");
    var body = document.getElementById("modalBody");
    modal.classList.add("show");
    loading.style.display = "flex";
    body.innerHTML = "";
    fetch('https://api.themoviedb.org/3/movie/'+id+'?append_to_response=credits,videos', {
        headers: { 'Authorization': 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI2YjQxYTFjYzY0NzQyODc2ZWY2MmUxNzEwOGMxOGNjMyIsIm5iZiI6MTc3MTExNTQ1Ny42NTUsInN1YiI6IjY5OTExM2MxM2ZiNTkwYzNmNGZhMmMyOSIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.pxULVqOJMZJeRx1nVfQ0ynS_ZgYvbV86Uanoi1FqjsI', 'Accept': 'application/json' }
    }).then(function(r){return r.json()}).then(function(movie){
        loading.style.display = "none";
        var poster = movie.poster_path ? 'https://image.tmdb.org/t/p/w300'+movie.poster_path : '';
        var year = (movie.release_date||'').substring(0,4)||'N/A';
        var genres = (movie.genres||[]).map(function(g){return g.name}).join(', ');
        var dir = ''; if(movie.credits&&movie.credits.crew){var d=movie.credits.crew.find(function(p){return p.job==='Director'}); if(d)dir='<p><b>Director:</b> '+d.name+'</p>';}
        var cast = ''; if(movie.credits&&movie.credits.cast){cast='<p><b>Cast:</b> '+movie.credits.cast.slice(0,5).map(function(a){return a.name}).join(', ')+'</p>';}
        body.innerHTML = '<div style="display:flex;gap:16px;flex-wrap:wrap;">'+
            (poster?'<img src="'+poster+'" style="width:180px;border-radius:10px;flex-shrink:0;">':'')+
            '<div><h2 style="margin-bottom:6px;">'+movie.title+'</h2>'+
            '<p style="color:#bbb;margin-bottom:8px;">&#11088; '+(movie.vote_average||0).toFixed(1)+' &middot; '+year+' &middot; '+(movie.runtime||'?')+' min</p>'+
            (genres?'<p style="color:#999;margin-bottom:8px;">'+genres+'</p>':'')+
            '<p style="color:#bbb;line-height:1.6;margin-bottom:12px;">'+(movie.overview||'')+'</p>'+
            dir+cast+'</div></div>';
        var playBtn = document.getElementById("modalPlayBtn");
        var listBtn = document.getElementById("modalAddToList");
        playBtn.onclick = function(){ window.location.href='/watch?id='+movie.id; };
        listBtn.onclick = function(){ listBtn.innerHTML='<i class="fa-solid fa-heart"></i> Added'; setTimeout(function(){ window.location.reload(); }, 800); };
    }).catch(function(err){
        loading.style.display = "none";
        body.innerHTML = '<p style="text-align:center;color:#999;">Could not load movie details.</p>';
    });
}

document.querySelectorAll(".overlay-btn.info").forEach(function(btn){
    btn.addEventListener("click", function(){
        var card = btn.closest(".movie-card");
        openMovieModal(parseInt(card.dataset.id));
    });
});

var modal = document.getElementById("movieModal");
if (modal) {
    modal.addEventListener("click", function(e) {
        if (e.target === modal) modal.classList.remove("show");
    });
}
</script>
@endpush
@endsection
