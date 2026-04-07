@extends("layouts.app")
@section("title","New &amp; Popular - KFLIX")
@section("content")
@php $wl=[]; try{ $w=new App\Models\Watchlist(); $wl=array_column($w->getWatchlist(),"movie_id"); }catch(Exception $e){} @endphp
<div class="main-layout">
    <aside class="sidebar">
        <h3><i class="fa-solid fa-fire-flame-curved"></i> Browse</h3>
        <div class="search-wrapper"><i class="fa-solid fa-search"></i><input type="text" class="search-input" placeholder="Search TV shows..." value="{{ $search??"" }}" onkeydown="if(event.key==='Enter')this.form.submit()"></div>
        <div class="filter-section"><h4><i class="fa-solid fa-tags"></i> Genres</h4><div class="genres"><a href="?" class="genre-item {{ ($genre??0)==0?"active":"" }}">All</a>@foreach(array_slice($genres??[],0,15) as $g)<a href="?genre={{ $g["id"] }}&amp;search={{ $search??"" }}" class="genre-item {{ ($genre??0)==$g["id"]?"active":"" }}">{{ $g["name"] }}</a>@endforeach</div></div>
        <div class="filter-section"><h4><i class="fa-solid fa-sort"></i> Sort</h4><select class="sort-select" onchange="location.search=this.value"><option value="?{{ http_build_query(["sort"=>"popularity.desc"]) }}" {{ ($sortBy??"popularity.desc")=="popularity.desc"?"selected":"" }}>Popularity</option><option value="?{{ http_build_query(["sort"=>"vote_average.desc"]) }}" {{ ($sortBy??"")=="vote_average.desc"?"selected":"" }}>Rating</option><option value="?{{ http_build_query(["sort"=>"first_air_date.desc"]) }}" {{ ($sortBy??"")=="first_air_date.desc"?"selected":"" }}>Air Date</option><option value="?{{ http_build_query(["sort"=>"name.asc"]) }}" {{ ($sortBy??"")=="name.asc"?"selected":"" }}>A-Z</option></select></div>
    </aside>
    <div class="content-area">
        <div class="results-header"><h1 class="section-title">New &amp; Popular</h1><span class="results-info">{{ count($tvshows??[]) }} shows</span></div>
        @if(empty($tvshows??[]))<div class="no-results"><i class="fa-solid fa-clapperboard"></i><h3>No shows found</h3></div>@endif
        <div class="movie-grid">@foreach($tvshows??[] as $show)@php $poster=$show["poster_path"]?"https://image.tmdb.org/t/p/w500".$show["poster_path"]:"https://via.placeholder.com/500x750?text=No+Poster"; $year=!empty($show["first_air_date"])?substr($show["first_air_date"],0,4):"N/A"; $inList=in_array($show["id"],$wl); @endphp
        <div class="movie-card" data-id="{{ $show["id"] }}" data-title="{{ $show["name"]??"" }}" data-poster="{{ $show["poster_path"] }}" data-rating="{{ $show["vote_average"] }}" data-year="{{ $year }}" data-watchlist="{{ $inList?"1":"0" }}">
            <div class="card-poster"><img src="{{ $poster }}" alt="{{ $show["name"]??"" }}" loading="lazy" onerror="this.src='https://via.placeholder.com/500x750?text=No+Image'"><div class="card-overlay"><div class="overlay-buttons"><button class="overlay-btn play" onclick="location.href='{{ route("player") }}?id={{ $show["id"] }}&amp;type=tv&amp;season=1&amp;episode=1'"><i class="fa-solid fa-play"></i></button><button class="overlay-btn heart" data-mid="{{ $show["id"] }}"><i class="{{ $inList?"fa-solid":"fa-regular" }} fa-heart"{{ $inList?' style="color:var(--clr-primary)"' :"" }}></i></button><button class="overlay-btn info" data-mid="{{ $show["id"] }}"><i class="fa-solid fa-info-circle"></i></button></div></div></div>
            <div class="card-footer"><p class="movie-title">{{ $show["name"]??"" }}</p><div class="movie-meta"><span class="rating-badge">&#11088; {{ number_format($show["vote_average"],1) }}</span><span class="release-year">{{ $year }}</span></div></div>
        </div>@endforeach</div>
    </div>
</div>
@endsection
