@extends('layouts.app')

@section('title', 'KFLIX - Watch Free Movies')

@section('content')
@php
    $watchlistIds = [];
    try {
        $w = new App\Models\Watchlist();
        $watchlistItems = $w->getWatchlist();
        $watchlistIds = array_column($watchlistItems, 'movie_id');
    } catch (Exception $e) {}
@endphp

@isset($error)
<div class="error-msg">{{ $error }}</div>
@endisset

@if(!empty($movies) && $page == 1 && empty($search) && $genre == 0)
<section class="hero-section" style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.8) 100%), url('https://image.tmdb.org/t/p/original{{ $movies[0]['backdrop_path'] }}')">
    <div class="hero-content">
        <h2 class="hero-title">{{ $movies[0]['title'] }}</h2>
        <p class="hero-description">{{ Str::limit($movies[0]['overview'], 200) }}</p>
        <div class="hero-buttons">
            <button class="btn-play" onclick="window.location.href='/watch?id={{ $movies[0]['id'] }}'"><i class="fas fa-play"></i> Play</button>
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
                    <input type="text" name="search" class="search-input" placeholder="Search movies..." value="{{ $search }}">
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
                <option value="release_date.desc" {{ $sortBy == 'release_date.desc' ? 'selected' : '' }}>Release Date</option>
                <option value="title.asc" {{ $sortBy == 'title.asc' ? 'selected' : '' }}>Title A-Z</option>
            </select>
        </div>
        @if(!empty($search) || $genre > 0 || $filter != 'day')
        <a href="?" class="clear-filters-btn"><i class="fas fa-undo-alt"></i> Clear All Filters</a>
        @endif
    </aside>

    <main class="main-content">
        <div class="results-header">
            <h1 class="section-title">
                @if(!empty($search))
                    Search: "{{ $search }}" @if($totalResults > 0)<span class="result-count">({{ $totalResults }} results)</span>@endif
                @elseif($genre > 0)
                    @php
                        $gname = 'Selected Genre';
                        foreach($genres as $g) { if($g['id'] == $genre) { $gname = $g['name']; break; } }
                    @endphp
                    {{ $gname }} Movies
                @else
                    {{ $filter == 'day' ? 'Trending Today' : ($filter == 'week' ? 'Trending This Week' : 'Trending Now') }}
                @endif
            </h1>
            <span class="results-count">Showing {{ count($movies) }} movies</span>
        </div>

        @if(empty($movies))
        <div class="no-results"><i class="fas fa-film"></i><h3>No movies found</h3><p>Try adjusting your filters.</p></div>
        @endif

        <div class="movie-grid" id="movieGrid">
            @foreach($movies as $movie)
            @php
                $poster = $movie['poster_path'] ? "https://image.tmdb.org/t/p/w500" . $movie['poster_path'] : "https://via.placeholder.com/500x750?text=No+Poster";
                $year = !empty($movie['release_date']) ? substr($movie['release_date'], 0, 4) : 'N/A';
                $inList = in_array($movie['id'], $watchlistIds);
            @endphp
            <div class="movie-card" data-id="{{ $movie['id'] }}" data-title="{{ $movie['title'] }}" data-poster="{{ $movie['poster_path'] }}" data-rating="{{ $movie['vote_average'] }}" data-year="{{ $year }}" data-watchlist="{{ $inList ? '1' : '0' }}">
                <div class="card-poster">
                    <img src="{{ $poster }}" alt="{{ $movie['title'] }}" loading="lazy" onerror="this.src='https://via.placeholder.com/500x750?text=No+Image'">
                    <div class="card-overlay">
                        <button class="play-icon" title="Play" onclick="window.location.href='/watch?id={{ $movie['id'] }}'"><i class="fas fa-play"></i></button>
                        <button class="like-icon" title="Add to list"><i class="{{ $inList ? 'fas' : 'far' }} fa-heart" style="{{ $inList ? 'color: var(--primary-color);' : '' }}"></i></button>
                        <button class="info-icon" title="More info"><i class="fas fa-info-circle"></i></button>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="card-rating"><span class="rating-badge">&#11088; {{ number_format($movie['vote_average'], 1) }}</span></div>
                    <p class="movie-title">{{ $movie['title'] }}</p>
                    <div class="movie-meta"><span class="release-year">{{ $year }}</span><span class="maturity-rating">{{ $movie['adult'] ? 'R' : 'PG-13' }}</span></div>
                </div>
            </div>
            @endforeach
        </div>

        @if(!empty($movies) && $totalPages > 1)
        <div class="pagination" id="pagination">
            @php
                $qp = array_filter(['search' => $search, 'genre' => $genre > 0 ? $genre : null, 'filter' => empty($search) && $genre == 0 ? $filter : null, 'sort' => $sortBy != 'popularity.desc' ? $sortBy : null]);
                $prefix = !empty($qp) ? '&' . http_build_query($qp) : '';
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
            @endphp
            @if($page > 1)<a href="?page={{ $page - 1 }}{{ $prefix }}" class="page-link prev"><i class="fas fa-chevron-left"></i> Previous</a>@endif
            <div class="page-numbers">
                @if($start > 1)<a href="?page=1{{ $prefix }}" class="page-num">1</a>@if($start > 2)<span class="page-dots">...</span>@endif @endif
                @for($i = $start; $i <= $end; $i++)
                <a href="?page={{ $i }}{{ $prefix }}" class="page-num {{ $i == $page ? 'active' : '' }}">{{ $i }}</a>
                @endfor
                @if($end < $totalPages)@if($end < $totalPages - 1)<span class="page-dots">...</span>@endif <a href="?page={{ $totalPages }}{{ $prefix }}" class="page-num">{{ $totalPages }}</a>@endif
            </div>
            @if($page < $totalPages)<a href="?page={{ $page + 1 }}{{ $prefix }}" class="page-link next">Next <i class="fas fa-chevron-right"></i></a>@endif
        </div>
        @endif
    </main>
</div>
@endsection
