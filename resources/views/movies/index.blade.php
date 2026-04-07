@extends("layouts.app")

@section("title", "KFLIX - Watch Free Movies")

@section("content")
@php
    $wl = [];
    try { $w = new App\Models\Watchlist(); $wl = array_column($w->getWatchlist(), "movie_id"); } catch (Exception $e) {}
@endphp

<div class="main-layout">
    <aside class="sidebar">
        <h3><i class="fa-solid fa-sliders-h"></i> Filters</h3>
        <div class="search-wrapper">
            <i class="fa-solid fa-search"></i>
            <input type="text" name="search" class="search-input" placeholder="Search movies..." value="{{ $search ?? "" }}">
            @if(!empty($search))<a href="?" class="clear-search"><i class="fa-solid fa-times"></i></a>@endif
        </div>
        <div class="filter-section">
            <h4><i class="fa-solid fa-tags"></i> Genres</h4>
            <div class="genres">
                <a href="?{{ http_build_query(array_filter(["search" => $search ?? "", "filter" => $filter ?? "day"])) }}" class="genre-item {{ ($genre ?? 0) == 0 ? "active" : "" }}">All Genres</a>
                @foreach(array_slice($genres ?? [], 0, 15) as $g)
                <a href="?genre={{ $g["id"] }}&{{ http_build_query(array_filter(["search" => $search ?? "", "filter" => $filter ?? "day"])) }}" class="genre-item {{ ($genre ?? 0) == $g["id"] ? "active" : "" }}">{{ $g["name"] }}</a>
                @endforeach
            </div>
        </div>
        <div class="filter-section">
            <h4><i class="fa-solid fa-clock"></i> Time</h4>
            <div class="time-filters">
                <a href="?filter=day&{{ http_build_query(array_filter(["genre" => $genre ?? 0, "search" => $search ?? ""])) }}" class="time-btn {{ ($filter ?? "day") == "day" && empty($search) ? "active" : "" }}">Today</a>
                <a href="?filter=week&{{ http_build_query(array_filter(["genre" => $genre ?? 0, "search" => $search ?? ""])) }}" class="time-btn {{ ($filter ?? "day") == "week" && empty($search) ? "active" : "" }}">This Week</a>
            </div>
        </div>
        <div class="filter-section">
            <h4><i class="fa-solid fa-sort-amount-down"></i> Sort</h4>
            <select class="sort-select" id="sortSelect">
                <option value="popularity.desc" {{ ($sortBy ?? "popularity.desc") == "popularity.desc" ? "selected" : "" }}>Popularity</option>
                <option value="vote_average.desc" {{ ($sortBy ?? "") == "vote_average.desc" ? "selected" : "" }}>Rating</option>
                <option value="release_date.desc" {{ ($sortBy ?? "") == "release_date.desc" ? "selected" : "" }}>Release Date</option>
                <option value="title.asc" {{ ($sortBy ?? "") == "title.asc" ? "selected" : "" }}>Title A-Z</option>
            </select>
        </div>
        @if(!empty($search) || ($genre ?? 0) > 0 || ($filter ?? "day") != "day")
        <a href="?" class="clear-filters-btn"><i class="fa-solid fa-undo-alt"></i> Clear All Filters</a>
        @endif
    </aside>

    <div class="content-area">
        @if(!empty($movies) && ($page ?? 1) == 1 && empty($search) && ($genre ?? 0) == 0)
        <div class="hero-section" style="background-image:url('https://image.tmdb.org/t/p/original{{ $movies[0]['backdrop_path'] }}')">
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
                @if(!empty($search))
                    Search: "{{ $search }}"
                @elseif(($genre ?? 0) > 0)
                    @php $gn="Selected"; foreach($genres??[] as $g){if($g["id"]==$genre){$gn=$g["name"];break;}} @endphp{{ $gn }} Movies
                @else
                    {{ ($filter ?? "day") == "day" ? "Trending Today" : "Trending This Week" }}
                @endif
            </h1>
            <span class="results-info">Showing {{ count($movies ?? []) }} movies</span>
        </div>

        @if(empty($movies))
        <div class="no-results"><i class="fa-solid fa-film"></i><h3>No movies found</h3><p>Try adjusting your filters.</p></div>
        @endif

        <div class="movie-grid">
            @foreach($movies ?? [] as $movie)
            @php
                $poster = $movie["poster_path"] ? "https://image.tmdb.org/t/p/w500" . $movie["poster_path"] : "https://via.placeholder.com/500x750?text=No+Poster";
                $year = !empty($movie["release_date"]) ? substr($movie["release_date"], 0, 4) : "N/A";
                $inList = in_array($movie["id"], $wl);
            @endphp
            <div class="movie-card" data-id="{{ $movie["id"] }}" data-title="{{ $movie["title"] }}" data-poster="{{ $movie["poster_path"] }}" data-rating="{{ $movie["vote_average"] }}" data-year="{{ $year }}" data-watchlist="{{ $inList ? "1" : "0" }}">
                <div class="card-poster">
                    <img src="{{ $poster }}" alt="{{ $movie["title"] }}" loading="lazy" onerror="this.src='https://via.placeholder.com/500x750?text=No+Image'">
                    <div class="card-overlay">
                        <div class="overlay-buttons">
                            <button class="overlay-btn play" onclick="location.href='{{ route("player") }}?id={{ $movie["id"] }}'"><i class="fa-solid fa-play"></i></button>
                            <button class="overlay-btn heart"><i class="{{ $inList ? "fa-solid" : "fa-regular" }} fa-heart"{{ $inList ? ' style="color:var(--clr-primary)"' : "" }}></i></button>
                            <button class="overlay-btn info"><i class="fa-solid fa-info-circle"></i></button>
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

        @if(!empty($movies) && ($totalPages ?? 1) > 1)
        <div class="pagination">
            @php
                $p = $page ?? 1;
                $qp = array_filter(["search" => $search ?? null, "genre" => ($genre ?? 0) > 0 ? ($genre ?? 0) : null, "filter" => empty($search ?? "") && ($genre ?? 0) == 0 ? ($filter ?? null) : null, "sort" => ($sortBy ?? "popularity.desc") != "popularity.desc" ? ($sortBy ?? null) : null]);
                $px = !empty($qp) ? "&" . http_build_query($qp) : "";
            @endphp
            @if($p > 1)<a href="?page={{ $p - 1 }}{{ $px }}" class="page-link"><i class="fa-solid fa-chevron-left"></i></a>@endif
            @for($i = max(1,$p-2); $i <= min($totalPages ?? 500, $p+2); $i++)
            <a href="?page={{ $i }}{{ $px }}" class="page-num {{ $i == $p ? "active" : "" }}">{{ $i }}</a>
            @endfor
            @if($p < ($totalPages ?? 1))<a href="?page={{ $p + 1 }}{{ $px }}" class="page-link"><i class="fa-solid fa-chevron-right"></i></a>@endif
        </div>
        @endif
    </div>
</div>
@endsection
