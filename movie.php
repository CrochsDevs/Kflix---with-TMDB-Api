<?php
    // movie.php
    require_once 'vendor/autoload.php';
    use GuzzleHttp\Client;

    // ==================== DATABASE CONNECTION FOR WATCHLIST ====================
    require_once 'config/db.php';
    require_once 'models/Watchlist.php';

    $watchlistIds = [];

    try {
    $watchlistModel = new Watchlist();
    $watchlistItems = $watchlistModel->getWatchlist();
    $watchlistIds   = array_column($watchlistItems, 'movie_id');
    error_log("Loaded " . count($watchlistIds) . " watchlist items");
    } catch (Exception $e) {
    error_log("Error loading watchlist: " . $e->getMessage());
    $watchlistIds = [];
    }

    // ==================== TMDB API ====================
    $client        = new Client();
    $movies        = [];
    $genres        = [];
    $error_message = "";

    $search_query   = isset($_GET['search']) ? trim($_GET['search']) : '';
    $selected_genre = isset($_GET['genre']) ? (int) $_GET['genre'] : 0;
    $filter_type    = isset($_GET['filter']) ? $_GET['filter'] : 'day';
    $sort_by        = isset($_GET['sort']) ? $_GET['sort'] : 'popularity.desc';
    $current_page   = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    if ($current_page < 1) {
    $current_page = 1;
    }

    $token = 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI2YjQxYTFjYzY0NzQyODc2ZWY2MmUxNzEwOGMxOGNjMyIsIm5iZiI6MTc3MTExNTQ1Ny42NTUsInN1YiI6IjY5OTExM2MxM2ZiNTkwYzNmNGZhMmMyOSIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.pxULVqOJMZJeRx1nVfQ0ynS_ZgYvbV86Uanoi1FqjsI';

    try {
    // Get Movie Genres
    $cache_file = 'cache/movie_genres.json';
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < 86400)) {
        $genres = json_decode(file_get_contents($cache_file), true);
    } else {
        $genre_response = $client->request('GET', "https://api.themoviedb.org/3/genre/movie/list?language=en-US", [
            'headers' => ['Authorization' => 'Bearer ' . $token, 'accept' => 'application/json'],
        ]);
        $genres = json_decode($genre_response->getBody(), true)['genres'] ?? [];
        if (! is_dir('cache')) {
            mkdir('cache', 0777, true);
        }

        file_put_contents($cache_file, json_encode($genres));
    }

    // Build URL for Movies
    if (! empty($search_query)) {
        $url = "https://api.themoviedb.org/3/search/movie?query=" . urlencode($search_query) . "&include_adult=false&language=en-US&page=$current_page";
        if ($selected_genre > 0) {
            $url .= "&with_genres=$selected_genre";
        }

    } elseif ($selected_genre > 0) {
        $url = "https://api.themoviedb.org/3/discover/movie?include_adult=false&language=en-US&page=$current_page&sort_by=$sort_by&with_genres=$selected_genre";
    } else {
        $url = "https://api.themoviedb.org/3/trending/movie/$filter_type?language=en-US&page=$current_page";
    }

    if (! empty($search_query) && $sort_by) {
        $url .= "&sort_by=$sort_by";
    }

    $response = $client->request('GET', $url, [
        'headers' => ['Authorization' => 'Bearer ' . $token, 'accept' => 'application/json'],
        'timeout' => 5,
    ]);

    $data          = json_decode($response->getBody(), true);
    $movies        = $data['results'] ?? [];
    $total_pages   = min($data['total_pages'] ?? 1, 500);
    $total_results = $data['total_results'] ?? 0;

    } catch (\Exception $e) {
    $error_message = "Error: " . $e->getMessage();
    }

    $page_title = "Movies - KFLIX";
    include 'layout/header.php';
?>

<!-- MAIN LAYOUT - Same as index.php but without hero section -->
<div class="main-layout">
    <!-- SIDEBAR - Same as index.php -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-sliders-h"></i> Filters</h3>
        </div>

        <!-- Search -->
        <div class="sidebar-search">
            <form action="" method="GET" id="searchForm">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon-input"></i>
                    <input type="text" name="search" class="search-input" placeholder="Search movies..."
                        value="<?php echo htmlspecialchars($search_query); ?>">
                    <?php if (! empty($search_query)): ?>
                        <a href="?" class="clear-search"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>
                <?php if ($selected_genre > 0): ?>
                    <input type="hidden" name="genre" value="<?php echo $selected_genre; ?>">
                <?php endif; ?>
                <?php if (! empty($filter_type) && empty($search_query)): ?>
                    <input type="hidden" name="filter" value="<?php echo $filter_type; ?>">
                <?php endif; ?>
            </form>
        </div>

        <!-- Genres -->
        <div class="filter-section">
            <h4><i class="fas fa-tags"></i> Genres</h4>
            <div class="genre-list">
                <a href="?<?php echo http_build_query(array_filter(['search' => $search_query, 'filter' => $filter_type])); ?>"
                    class="genre-item <?php echo $selected_genre == 0 ? 'active' : ''; ?>">All Genres</a>
                <?php foreach (array_slice($genres, 0, 15) as $genre): ?>
                    <a href="?genre=<?php echo $genre['id']; ?>&<?php echo http_build_query(array_filter(['search' => $search_query, 'filter' => $filter_type])); ?>"
                        class="genre-item <?php echo $selected_genre == $genre['id'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($genre['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Time Period -->
        <div class="filter-section">
            <h4><i class="fas fa-clock"></i> Time</h4>
            <div class="time-filters">
                <a href="?filter=day&<?php echo http_build_query(array_filter(['genre' => $selected_genre, 'search' => $search_query])); ?>"
                    class="time-btn <?php echo($filter_type == 'day' && empty($search_query)) ? 'active' : ''; ?>">Today</a>
                <a href="?filter=week&<?php echo http_build_query(array_filter(['genre' => $selected_genre, 'search' => $search_query])); ?>"
                    class="time-btn <?php echo($filter_type == 'week' && empty($search_query)) ? 'active' : ''; ?>">This Week</a>
            </div>
        </div>

        <!-- Sort By -->
        <div class="filter-section">
            <h4><i class="fas fa-sort-amount-down"></i> Sort By</h4>
            <select class="sort-select" id="sortSelect">
                <option value="popularity.desc" <?php echo $sort_by == 'popularity.desc' ? 'selected' : ''; ?>>Popularity</option>
                <option value="vote_average.desc" <?php echo $sort_by == 'vote_average.desc' ? 'selected' : ''; ?>>Rating</option>
                <option value="release_date.desc" <?php echo $sort_by == 'release_date.desc' ? 'selected' : ''; ?>>Release Date</option>
                <option value="title.asc" <?php echo $sort_by == 'title.asc' ? 'selected' : ''; ?>>Title A-Z</option>
            </select>
        </div>

        <!-- Clear Filters -->
        <?php if (! empty($search_query) || $selected_genre > 0 || $filter_type != 'day'): ?>
            <a href="?" class="clear-filters-btn"><i class="fas fa-undo-alt"></i> Clear All Filters</a>
        <?php endif; ?>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <!-- Results Header - Same as index.php -->
        <div class="results-header">
            <h1 class="section-title">
                <?php
                    if (! empty($search_query)) {
                        echo "Search: \"" . htmlspecialchars($search_query) . "\"";
                        if ($total_results > 0) {
                            echo " <span class='result-count'>($total_results results)</span>";
                        }

                    } elseif ($selected_genre > 0) {
                        $genre_name = 'Selected Genre';
                        foreach ($genres as $genre) {
                            if ($genre['id'] == $selected_genre) {$genre_name = $genre['name'];
                                break;}
                        }
                        echo htmlspecialchars($genre_name) . " Movies";
                    } else {
                        $titles = ['day' => 'Top Movies', 'week' => 'Top Movies This Week'];
                        echo $titles[$filter_type] ?? 'Trending Now';
                    }
                ?>
            </h1>
            <span class="results-count">Showing <?php echo count($movies); ?> movies</span>
        </div>

        <!-- Error Message -->
        <?php if ($error_message): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- No Results -->
        <?php if (empty($movies)): ?>
            <div class="no-results">
                <i class="fas fa-film"></i>
                <h3>No movies found</h3>
                <p>Try adjusting your filters to find what you're looking for.</p>
            </div>
        <?php endif; ?>

        <!-- Movies Grid - Same cards as index.php -->
        <div class="movie-grid" id="movieGrid">
            <?php foreach ($movies as $movie):
                    $poster      = $movie['poster_path'] ? "https://image.tmdb.org/t/p/w500{$movie['poster_path']}" : "https://via.placeholder.com/500x750?text=No+Poster";
                    $year        = isset($movie['release_date']) && ! empty($movie['release_date']) ? substr($movie['release_date'], 0, 4) : 'N/A';
                    $inWatchlist = in_array($movie['id'], $watchlistIds);
            ?>
                <div class="movie-card"
                     data-id="<?php echo $movie['id']; ?>"
                     data-title="<?php echo htmlspecialchars($movie['title']); ?>"
                     data-poster="<?php echo $movie['poster_path']; ?>"
                     data-rating="<?php echo $movie['vote_average']; ?>"
                     data-year="<?php echo $year; ?>"
                     data-watchlist="<?php echo $inWatchlist ? '1' : '0'; ?>">
                    <div class="card-poster">
                        <img src="<?php echo $poster; ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>"
                             loading="lazy"
                             onerror="this.src='https://via.placeholder.com/500x750?text=No+Image'">
                        <div class="card-overlay">
                            <button class="play-icon" title="Play" onclick="window.location.href='play.php?id=<?php echo $movie['id']; ?>&type=movie'">
                                <i class="fas fa-play"></i>
                            </button>
                            <button class="like-icon" title="Add to list">
                                <i class="<?php echo $inWatchlist ? 'fas' : 'far'; ?> fa-heart"
                                   style="<?php echo $inWatchlist ? 'color: var(--primary-color);' : ''; ?>"></i>
                            </button>
                            <button class="info-icon" title="More info"><i class="fas fa-info-circle"></i></button>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="card-rating">
                            <span class="rating-badge">⭐ <?php echo number_format($movie['vote_average'], 1); ?></span>
                        </div>
                        <p class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></p>
                        <div class="movie-meta">
                            <span class="release-year"><?php echo $year; ?></span>
                            <span class="maturity-rating"><?php echo $movie['adult'] ? 'R' : 'PG-13'; ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination - Same as index.php -->
        <?php if (! empty($movies) && $total_pages > 1): ?>
            <div class="pagination" id="pagination">
                <?php
                    $query_params = array_filter([
                        'search' => $search_query,
                        'genre'  => $selected_genre > 0 ? $selected_genre : null,
                        'filter' => empty($search_query) && $selected_genre == 0 ? $filter_type : null,
                        'sort'   => $sort_by != 'popularity.desc' ? $sort_by : null,
                    ]);
                    $query_prefix = ! empty($query_params) ? '&' . http_build_query($query_params) : '';
                ?>

                <?php if ($current_page > 1): ?>
                    <a href="?page=<?php echo $current_page - 1 . $query_prefix; ?>" class="page-link prev"><i class="fas fa-chevron-left"></i> Previous</a>
                <?php endif; ?>

                <div class="page-numbers">
                    <?php
                        $start = max(1, $current_page - 2);
                        $end   = min($total_pages, $current_page + 2);
                        if ($start > 1) {
                            echo '<a href="?page=1' . $query_prefix . '" class="page-num">1</a>';
                            if ($start > 2) {
                                echo '<span class="page-dots">...</span>';
                            }

                        }
                        for ($i = $start; $i <= $end; $i++) {
                            $active = $i == $current_page ? 'active' : '';
                            echo '<a href="?page=' . $i . $query_prefix . '" class="page-num ' . $active . '">' . $i . '</a>';
                        }
                        if ($end < $total_pages) {
                            if ($end < $total_pages - 1) {
                                echo '<span class="page-dots">...</span>';
                            }

                            echo '<a href="?page=' . $total_pages . $query_prefix . '" class="page-num">' . $total_pages . '</a>';
                        }
                    ?>
                </div>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?php echo $current_page + 1 . $query_prefix; ?>" class="page-link next">Next <i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
// Sort select handler
document.getElementById('sortSelect').addEventListener('change', function() {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('sort', this.value);
    window.location.href = '?' + urlParams.toString();
});

// Auto-submit search on enter
document.querySelector('.search-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        this.form.submit();
    }
});

// Watchlist toggle (copy from index.php)
function toggleWatchlist(movieId, element) {
    // You can copy the watchlist function from index.php here
    console.log('Toggle watchlist for:', movieId);
}
</script>

<?php
    include 'components/modal.php';
include 'layout/footer.php';
?>