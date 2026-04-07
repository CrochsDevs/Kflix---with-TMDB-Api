@extends('layouts.app')

@section('title', 'TV Shows - Watch Free')

@section('content')
@php
    $watchlistIds = [];
    try {
        $w = new App\Models\Watchlist();
        $wi = $w->getWatchlist();
        $watchlistIds = array_column($wi, 'movie_id');
    } catch (Exception $e) {}
@endphp

@isset($error)<div class="error-msg">{{ $error }}</div>@endisset

@if(!empty($tvshows) && $page == 1 && empty($search) && $genre == 0)
<section class="hero-section" style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.8) 100%), url('https://image.tmdb.org/t/p/original{{ $tvshows[0]['backdrop_path'] }}')">
    <div class="hero-content">
        <h2 class="hero-title">{{ $tvshows[0]['name'] }}</h2>
        <p class="hero-description">{{ Str::limit($tvshows[0]['overview'], 200) }}</p>
        <div class="hero-buttons">
            <button class="btn-play" onclick="window.location.href='/watch?id={{ $tvshows[0]['id'] }}&type=tv&season=1&episode=1'"><i class="fas fa-play"></i> Play</button>
        </div>
    </div>
</section>
@endif

<div class="main-layout">
    <aside class="sidebar">
        <div class="sidebar-header"><h3><i class="fas fa-sliders-h"></i> Filters</h3></div>
        <div class="sidebar-search">
            <form action="" method="GET" id="searchForm">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon-input"></i>
                    <input type="text" name="search" class="search-input" placeholder="Search TV shows..." value="{{ $search }}">
                    @if(!empty($search))<a href="?" class="clear-search"><i class="fas fa-times"></i></a>@endif
                </div>
                @if($genre > 0)<input type="hidden" name="genre" value="{{ $genre }}">@endif
                @if(!empty($filter) && empty($search))<input type="hidden" name="filter" value="{{ $filter }}">@endif
            </form>
        </div>
        <div class="filter-section">
            <h4><i class="fas fa-tags"></i> Genres</h4>
            <div class="genre-list">
                <a href="?{{ http_build_query(array_filter(['search' => $search, 'filter' => $filter])) }}" class="genre-item {{ $genre == 0 ? 'active' : '' }}">All Genres</a>
                @foreach(array_slice($genres, 0, 15) as $g)
                <a href="?genre={{ $g['id'] }}&{{ http_build_query(array_filter(['search' => $search, 'filter' => $filter])) }}" class="genre-item {{ $genre == $g['id'] ? 'active' : '' }}">{{ $g['name'] }}</a>
                @endforeach
            </div>
        </div>
        <div class="filter-section">
            <h4><i class="fas fa-clock"></i> Time</h4>
            <div class="time-filters">
                <a href="?filter=day&{{ http_build_query(array_filter(['genre' => $genre, 'search' => $search])) }}" class="time-btn {{ $filter == 'day' && empty($search) ? 'active' : '' }}">Today</a>
                <a href="?filter=week&{{ http_build_query(array_filter(['genre' => $genre, 'search' => $search])) }}" class="time-btn {{ $filter == 'week' && empty($search) ? 'active' : '' }}">This Week</a>
            </div>
        </div>
        <div class="filter-section">
            <h4><i class="fas fa-sort-amount-down"></i> Sort By</h4>
            <select class="sort-select" id="sortSelect">
                <option value="popularity.desc" {{ $sortBy == 'popularity.desc' ? 'selected' : '' }}>Popularity</option>
                <option value="vote_average.desc" {{ $sortBy == 'vote_average.desc' ? 'selected' : '' }}>Rating</option>
                <option value="first_air_date.desc" {{ $sortBy == 'first_air_date.desc' ? 'selected' : '' }}>First Air Date</option>
                <option value="name.asc" {{ $sortBy == 'name.asc' ? 'selected' : '' }}>Name A-Z</option>
            </select>
        </div>
        @if(!empty($search) || $genre > 0 || $filter != 'day')
        <a href="?" class="clear-filters-btn"><i class="fas fa-undo-alt"></i> Clear All Filters</a>
        @endif
    </aside>

    <main class="main-content">
        <div class="results-header">
            <h1 class="section-title">
                @if(!empty($search))Search: "{{ $search }}" @if($totalResults > 0)<span class="result-count">({{ $totalResults }} results)</span>@endif
                @elseif($genre > 0)@php $gn='Selected'; foreach($genres as $g){if($g['id']==$genre){$gn=$g['name'];break;}} @endphp{{ $gn }} TV Shows
                @else{{ $filter == 'day' ? 'Trending Today' : 'Trending This Week' }}@endif
            </h1>
            <span class="results-count">Showing {{ count($tvshows) }} TV shows</span>
        </div>

        @if(empty($tvshows))<div class="no-results"><i class="fas fa-tv"></i><h3>No TV shows found</h3><p>Try adjusting your filters.</p></div>@endif

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
                    <div class="movie-meta"><span class="release-year">{{ $year }}</span><span class="maturity-rating">{{ $show['adult'] ? 'R' : 'TV-14' }}</span></div>
                </div>
            </div>
            @endforeach
        </div>

        @if(!empty($tvshows) && $totalPages > 1)
        <div class="pagination">
            @php $qp = array_filter(['search' => $search, 'genre' => $genre > 0 ? $genre : null, 'filter' => empty($search) && $genre == 0 ? $filter : null]); $px = !empty($qp) ? '&' . http_build_query($qp) : ''; @endphp
            @if($page > 1)<a href="?page={{ $page - 1 }}{{ $px }}" class="page-link prev"><i class="fas fa-chevron-left"></i> Previous</a>@endif
            <div class="page-numbers">
                @for($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++)<a href="?page={{ $i }}{{ $px }}" class="page-num {{ $i == $page ? 'active' : '' }}">{{ $i }}</a>@endfor
            </div>
            @if($page < $totalPages)<a href="?page={{ $page + 1 }}{{ $px }}" class="page-link next">Next <i class="fas fa-chevron-right"></i></a>@endif
        </div>
        @endif
    </main>
</div>
@endsection
