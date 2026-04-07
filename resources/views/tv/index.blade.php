@php $wl=[]; try{ $w=new App\Models\Watchlist(); $wl=array_column($w->getWatchlist(),"movie_id"); }catch(Exception $e){} $s=$search??""; $g=$genre??0; $f=$filter??"day"; @endphp
@extends("layouts.app")
@section("title","TV Shows - KFLIX")
@section("content")
<div class="main-layout">
    <aside class="sidebar">
        <h3><i class="fa-solid fa-sliders-h"></i> Filters</h3>
        <div class="search-wrapper">
            <i class="fa-solid fa-search"></i>
            <input type="text" class="search-input" placeholder="Search TV shows..." value="{{ $s }}" onkeydown="if(event.key==='Enter')this.form.submit()">
            @if(!empty($s))<a href="{{ url()->current() }}" class="clear-search"><i class="fa-solid fa-times"></i></a>@endif
        </div>
        <div class="filter-section">
            <h4><i class="fa-solid fa-tags"></i> Genres</h4>
            <div class="genres">
                <a href="?{{ http_build_query(["search"=>$s,"filter"=>$f]) }}" class="genre-item {{ $g==0?"active":"" }}">All</a>
                @foreach(array_slice($genres??[],0,15) as $gr)
                <a href="?{{ http_build_query(["genre"=>$gr["id"],"search"=>$s,"filter"=>$f]) }}" class="genre-item {{ $g==$gr["id"]?"active":"" }}">{{ $gr["name"] }}</a>
                @endforeach
            </div>
        </div>
        <div class="filter-section">
            <h4><i class="fa-solid fa-clock"></i> Time</h4>
            <div class="time-filters">
                <a href="?{{ http_build_query(["filter"=>"day","genre"=>$g,"search"=>$s]) }}" class="time-btn {{ $f=="day"&&empty($s)?"active":"" }}">Today</a>
                <a href="?{{ http_build_query(["filter"=>"week","genre"=>$g,"search"=>$s]) }}" class="time-btn {{ $f=="week"&&empty($s)?"active":"" }}">Week</a>
            </div>
        </div>
        <div class="filter-section">
            <h4><i class="fa-solid fa-sort"></i> Sort</h4>
            <select class="sort-select" onchange="location.search=this.value">
                <option value="?{{ http_build_query(["search"=>$s,"genre"=>$g>0?$g:null,"filter"=>empty($s)&&$g==0?$f:null,"sort"=>"popularity.desc"]) }}" {{ ($sortBy??"popularity.desc")=="popularity.desc"?"selected":"" }}>Popularity</option>
                <option value="?{{ http_build_query(["search"=>$s,"genre"=>$g>0?$g:null,"filter"=>empty($s)&&$g==0?$f:null,"sort"=>"vote_average.desc"]) }}" {{ ($sortBy??"")=="vote_average.desc"?"selected":"" }}>Rating</option>
                <option value="?{{ http_build_query(["search"=>$s,"genre"=>$g>0?$g:null,"filter"=>empty($s)&&$g==0?$f:null,"sort"=>"first_air_date.desc"]) }}" {{ ($sortBy??"")=="first_air_date.desc"?"selected":"" }}>Air Date</option>
                <option value="?{{ http_build_query(["search"=>$s,"genre"=>$g>0?$g:null,"filter"=>empty($s)&&$g==0?$f:null,"sort"=>"name.asc"]) }}" {{ ($sortBy??"")=="name.asc"?"selected":"" }}>Name A-Z</option>
            </select>
        </div>
        @if(!empty($s)||$g>0||$f!="day")
        <a href="{{ url()->current() }}" class="clear-filters-btn"><i class="fa-solid fa-undo-alt"></i> Clear All Filters</a>
        @endif
    </aside>
    <div class="content-area">
        @if(!empty($tvshows)&&($page??1)==1&&empty($s)&&$g==0)
        <div class="hero-section" style="background-image:url('https://image.tmdb.org/t/p/original{{ $tvshows[0]["backdrop_path"] }}')">
            <div class="hero-content">
                <h1 class="hero-title">{{ $tvshows[0]["name"] }}</h1>
                <p class="hero-description">{{ Str::limit($tvshows[0]["overview"],200) }}</p>
                <div class="hero-buttons"><a href="{{ route("player") }}?id={{ $tvshows[0]["id"] }}&type=tv&season=1&episode=1" class="btn-play"><i class="fa-solid fa-play"></i> Play</a></div>
            </div>
        </div>
        @endif
        <div class="results-header"><h1 class="section-title">@if(!empty($s))Search: "{{ $s }}"@elseif($g>0){{ $genreName??"Shows" }}@else{{ $f=="day"?"Trending Today":"Trending This Week" }}@endif</h1><span class="results-info">{{ count($tvshows??[]) }} shows</span></div>
        @if(empty($tvshows??[]))<div class="no-results"><i class="fa-solid fa-tv"></i><h3>No shows found</h3></div>@endif
        <div class="movie-grid">
            @foreach($tvshows??[] as $show)
            @php $poster=$show["poster_path"]?"https://image.tmdb.org/t/p/w500".$show["poster_path"]:"https://via.placeholder.com/500x750?text=No+Poster"; $year=!empty($show["first_air_date"])?substr($show["first_air_date"],0,4):"N/A"; $inList=in_array($show["id"],$wl); @endphp
            <div class="movie-card" data-id="{{ $show["id"] }}" data-title="{{ $show["name"]??"" }}" data-poster="{{ $show["poster_path"] }}" data-rating="{{ $show["vote_average"] }}" data-year="{{ $year }}" data-watchlist="{{ $inList?"1":"0" }}">
                <div class="card-poster"><img src="{{ $poster }}" alt="{{ $show["name"]??"" }}" loading="lazy" onerror="this.src='https://via.placeholder.com/500x750?text=No+Image'"><div class="card-overlay"><div class="overlay-buttons"><button class="overlay-btn play" onclick="location.href='{{ route("player") }}?id={{ $show["id"] }}&type=tv&season=1&episode=1'"><i class="fa-solid fa-play"></i></button><button class="overlay-btn heart" data-mid="{{ $show["id"] }}"><i class="{{ $inList?"fa-solid":"fa-regular" }} fa-heart"{{ $inList?' style="color:var(--clr-primary)"' :"" }}></i></button><button class="overlay-btn info" data-mid="{{ $show["id"] }}"><i class="fa-solid fa-info-circle"></i></button></div></div></div>
                <div class="card-footer"><p class="movie-title">{{ $show["name"]??"" }}</p><div class="movie-meta"><span class="rating-badge">&#11088; {{ number_format($show["vote_average"],1) }}</span><span class="release-year">{{ $year }}</span></div></div>
            </div>
            @endforeach
        </div>
        @if(!empty($tvshows??[])&&($totalPages??1)>1)<div class="pagination">@php $p=$page??1; $px=""; @endphp@if($p>1)<a href="?page={{ $p-1 }}" class="page-link"><i class="fa-solid fa-chevron-left"></i></a>@endif@for($i=max(1,$p-2);$i<=min($totalPages??500,$p+2);$i++)<a href="?page={{ $i }}" class="page-num {{ $i==$p?"active":"" }}">{{ $i }}</a>@endfor@if($p<($totalPages??1))<a href="?page={{ $p+1 }}" class="page-link"><i class="fa-solid fa-chevron-right"></i></a>@endif</div>@endif
    </div>
</div>
@endsection
