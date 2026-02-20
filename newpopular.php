<?php
// newpopular.php
require_once('vendor/autoload.php');
use GuzzleHttp\Client;

// ==================== DATABASE CONNECTION FOR WATCHLIST ====================
require_once 'config/db.php';
require_once 'models/Watchlist.php';

$watchlistIds = [];

try {
    $watchlistModel = new Watchlist();
    $watchlistItems = $watchlistModel->getWatchlist();
    $watchlistIds = array_column($watchlistItems, 'movie_id');
    error_log("Loaded " . count($watchlistIds) . " watchlist items");
} catch (Exception $e) {
    error_log("Error loading watchlist: " . $e->getMessage());
    $watchlistIds = [];
}

// ==================== TMDB API ====================
$client = new Client();
$items = []; // Combined movies and TV shows
$genres = [];
$error_message = "";

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$selected_genre = isset($_GET['genre']) ? (int) $_GET['genre'] : 0;
$filter_type = isset($_GET['filter']) ? $_GET['filter'] : 'day';
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

$token = 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI2YjQxYTFjYzY0NzQyODc2ZWY2MmUxNzEwOGMxOGNjMyIsIm5iZiI6MTc3MTExNTQ1Ny42NTUsInN1YiI6IjY5OTExM2MxM2ZiNTkwYzNmNGZhMmMyOSIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.pxULVqOJMZJeRx1nVfQ0ynS_ZgYvbV86Uanoi1FqjsI';

try {
    // Get Genres (use movie genres for consistency)
    $cache_file = 'cache/genres.json';
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < 86400)) {
        $genres = json_decode(file_get_contents($cache_file), true);
    } else {
        $genre_response = $client->request('GET', "https://api.themoviedb.org/3/genre/movie/list?language=en-US", [
            'headers' => ['Authorization' => 'Bearer ' . $token, 'accept' => 'application/json'],
        ]);
        $genres = json_decode($genre_response->getBody(), true)['genres'] ?? [];
        if (!is_dir('cache')) mkdir('cache', 0777, true);
        file_put_contents($cache_file, json_encode($genres));
    }

    // Get trending movies and TV shows
    if (!empty($search_query)) {
        // Search both movies and TV shows
        $movie_url = "https://api.themoviedb.org/3/search/movie?query=" . urlencode($search_query) . "&include_adult=false&language=en-US&page=$current_page";
        if ($selected_genre > 0) $movie_url .= "&with_genres=$selected_genre";
        
        $tv_url = "https://api.themoviedb.org/3/search/tv?query=" . urlencode($search_query) . "&include_adult=false&language=en-US&page=$current_page";
        
        $movie_response = $client->request('GET', $movie_url, [
            'headers' => ['Authorization' => 'Bearer ' . $token, 'accept' => 'application/json'],
            'timeout' => 5,
        ]);
        $movie_data = json_decode($movie_response->getBody(), true);
        $movies = $movie_data['results'] ?? [];
        
        $tv_response = $client->request('GET', $tv_url, [
            'headers' => ['Authorization' => 'Bearer ' . $token, 'accept' => 'application/json'],
            'timeout' => 5,
        ]);
        $tv_data = json_decode($tv_response->getBody(), true);
        $tvshows = $tv_data['results'] ?? [];
        
        // Merge and sort by popularity
        $items = array_merge($movies, $tvshows);
        usort($items, function($a, $b) {
            return ($b['popularity'] ?? 0) <=> ($a['popularity'] ?? 0);
        });
        
        $total_results = count($items);
        $total_pages = max($movie_data['total_pages'] ?? 1, $tv_data['total_pages'] ?? 1);
        
    } else {
        // Get trending
        $movie_url = "https://api.themoviedb.org/3/trending/movie/$filter_type?language=en-US&page=$current_page";
        $tv_url = "https://api.themoviedb.org/3/trending/tv/$filter_type?language=en-US&page=$current_page";
        
        $movie_response = $client->request('GET', $movie_url, [
            'headers' => ['Authorization' => 'Bearer ' . $token, 'accept' => 'application/json'],
            'timeout' => 5,
        ]);
        $movie_data = json_decode($movie_response->getBody(), true);
        $movies = $movie_data['results'] ?? [];
        
        $tv_response = $client->request('GET', $tv_url, [
            'headers' => ['Authorization' => 'Bearer ' . $token, 'accept' => 'application/json'],
            'timeout' => 5,
        ]);
        $tv_data = json_decode($tv_response->getBody(), true);
        $tvshows = $tv_data['results'] ?? [];
        
        // Merge and sort by popularity
        $items = array_merge($movies, $tvshows);
        usort($items, function($a, $b) {
            return ($b['popularity'] ?? 0) <=> ($a['popularity'] ?? 0);
        });
        
        $total_pages = max($movie_data['total_pages'] ?? 1, $tv_data['total_pages'] ?? 1);
    }

} catch (\Exception $e) {
    $error_message = "Error: " . $e->getMessage();
}

$page_title = "New & Popular - KFLIX";
include 'layout/header.php';
?>

<!-- MAIN LAYOUT - Same as index.php -->
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
                    <input type="text" name="search" class="search-input" placeholder="Search movies & TV shows..."
                        value="<?php echo htmlspecialchars($search_query); ?>">
                    <?php if (!empty($search_query)): ?>
                        <a href="?" class="clear-search"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>
                <?php if ($selected_genre > 0): ?>
                    <input type="hidden" name="genre" value="<?php echo $selected_genre; ?>">
                <?php endif; ?>
                <?php if (!empty($filter_type) && empty($search_query)): ?>
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
                    class="time-btn <?php echo ($filter_type == 'day' && empty($search_query)) ? 'active' : ''; ?>">Today</a>
                <a href="?filter=week&<?php echo http_build_query(array_filter(['genre' => $selected_genre, 'search' => $search_query])); ?>"
                    class="time-btn <?php echo ($filter_type == 'week' && empty($search_query)) ? 'active' : ''; ?>">This Week</a>
            </div>
        </div>

        <!-- Clear Filters -->
        <?php if (!empty($search_query) || $selected_genre > 0 || $filter_type != 'day'): ?>
            <a href="?" class="clear-filters-btn"><i class="fas fa-undo-alt"></i> Clear All Filters</a>
        <?php endif; ?>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <!-- Results Header - Same as index.php -->
        <div class="results-header">
            <h1 class="section-title">
                <?php
                if (!empty($search_query)) {
                    echo "Search: \"" . htmlspecialchars($search_query) . "\"";
                } elseif ($selected_genre > 0) {
                    $genre_name = 'Selected Genre';
                    foreach ($genres as $genre) {
                        if ($genre['id'] == $selected_genre) { $genre_name = $genre['name']; break; }
                    }
                    echo htmlspecialchars($genre_name);
                } else {
                    echo $filter_type == 'day' ? '🔥 New & Popular Today' : '📅 New & Popular This Week';
                }
                ?>
            </h1>
            <span class="results-count">Showing <?php echo count($items); ?> items</span>
        </div>

        <!-- Error Message -->
        <?php if ($error_message): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- No Results -->
        <?php if (empty($items)): ?>
            <div class="no-results">
                <i class="fas fa-film"></i>
                <h3>No items found</h3>
                <p>Try adjusting your filters to find what you're looking for.</p>
            </div>
        <?php endif; ?>

        <!-- Movies Grid -->
        <div class="movie-grid" id="movieGrid">
            <?php foreach ($items as $item):
                $is_movie = isset($item['title']);
                $title = $is_movie ? $item['title'] : $item['name'];
                $poster = $item['poster_path'] ? "https://image.tmdb.org/t/p/w500{$item['poster_path']}" : "https://via.placeholder.com/500x750?text=No+Poster";
                $year = $is_movie 
                    ? (isset($item['release_date']) && !empty($item['release_date']) ? substr($item['release_date'], 0, 4) : 'N/A')
                    : (isset($item['first_air_date']) && !empty($item['first_air_date']) ? substr($item['first_air_date'], 0, 4) : 'N/A');
                $inWatchlist = in_array($item['id'], $watchlistIds);
                $type_label = $is_movie ? 'MOVIE' : 'TV';
                $type_param = $is_movie ? 'movie' : 'tv';
                
                $play_url = "play.php?id=" . $item['id'] . "&type=" . $type_param;
                if (!$is_movie) {
                    $play_url .= "&season=1&episode=1";
                }
            ?>
                <div class="movie-card" 
                     data-id="<?php echo $item['id']; ?>" 
                     data-title="<?php echo htmlspecialchars($title); ?>"
                     data-poster="<?php echo $item['poster_path']; ?>"
                     data-rating="<?php echo $item['vote_average']; ?>"
                     data-year="<?php echo $year; ?>"
                     data-type="<?php echo $type_param; ?>"
                     data-watchlist="<?php echo $inWatchlist ? '1' : '0'; ?>">
                    <div class="card-poster">
                        <img src="<?php echo $poster; ?>" alt="<?php echo htmlspecialchars($title); ?>" 
                             loading="lazy"
                             onerror="this.src='https://via.placeholder.com/500x750?text=No+Image'">
                        <!-- Type Badge -->
                        <span style="position: absolute; top: 10px; left: 10px; background: <?php echo $is_movie ? '#e50914' : '#4ecdc4'; ?>; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; z-index: 2; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                            <?php echo $type_label; ?>
                        </span>
                        
                        <div class="card-overlay">
                            <!-- PLAY BUTTON -->
                            <button class="play-icon" title="Play" onclick="event.stopPropagation(); window.location.href='<?php echo $play_url; ?>'">
                                <i class="fas fa-play"></i>
                            </button>
                            
                            <!-- WATCHLIST BUTTON - FIXED FOR watchlist.php -->
                            <button class="like-icon" title="Add to list" onclick="event.stopPropagation(); toggleWatchlist(<?php echo $item['id']; ?>, '<?php echo $title; ?>', '<?php echo $poster; ?>', <?php echo $item['vote_average']; ?>, '<?php echo $year; ?>', this)">
                                <i class="<?php echo $inWatchlist ? 'fas' : 'far'; ?> fa-heart" 
                                   style="<?php echo $inWatchlist ? 'color: #e50914;' : ''; ?>"></i>
                            </button>
                            
                            <!-- INFO BUTTON -->
                            <button class="info-icon" title="More info" onclick="event.stopPropagation(); openMovieModal(<?php echo $item['id']; ?>, '<?php echo $type_param; ?>')">
                                <i class="fas fa-info-circle"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="card-rating">
                            <span class="rating-badge">⭐ <?php echo number_format($item['vote_average'], 1); ?></span>
                        </div>
                        <p class="movie-title"><?php echo htmlspecialchars($title); ?></p>
                        <div class="movie-meta">
                            <span class="release-year"><?php echo $year; ?></span>
                            <span class="maturity-rating"><?php echo $item['adult'] ? 'R' : 'PG-13'; ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if (!empty($items) && $total_pages > 1): ?>
            <div class="pagination" id="pagination">
                <?php
                $query_params = array_filter([
                    'search' => $search_query,
                    'genre' => $selected_genre > 0 ? $selected_genre : null,
                    'filter' => empty($search_query) && $selected_genre == 0 ? $filter_type : null,
                ]);
                $query_prefix = !empty($query_params) ? '&' . http_build_query($query_params) : '';
                ?>

                <?php if ($current_page > 1): ?>
                    <a href="?page=<?php echo $current_page - 1 . $query_prefix; ?>" class="page-link prev"><i class="fas fa-chevron-left"></i> Previous</a>
                <?php endif; ?>

                <div class="page-numbers">
                    <?php
                    $start = max(1, $current_page - 2);
                    $end = min($total_pages, $current_page + 2);
                    if ($start > 1) {
                        echo '<a href="?page=1' . $query_prefix . '" class="page-num">1</a>';
                        if ($start > 2) echo '<span class="page-dots">...</span>';
                    }
                    for ($i = $start; $i <= $end; $i++) {
                        $active = $i == $current_page ? 'active' : '';
                        echo '<a href="?page=' . $i . $query_prefix . '" class="page-num ' . $active . '">' . $i . '</a>';
                    }
                    if ($end < $total_pages) {
                        if ($end < $total_pages - 1) echo '<span class="page-dots">...</span>';
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
// Auto-submit search on enter
document.querySelector('.search-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        this.form.submit();
    }
});

// ==================== WATCHLIST FUNCTION FOR watchlist.php ====================
function toggleWatchlist(id, title, poster, rating, year, button) {
    console.log('Toggling watchlist for ID:', id);
    
    // Disable button during request
    button.disabled = true;
    button.style.opacity = '0.7';
    
    const movieData = {
        id: id,
        title: title,
        poster_path: poster,
        vote_average: rating,
        release_date: year + '-01-01',
        media_type: 'movie'
    };
    
    fetch('api/watchlist.php', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(movieData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Watchlist response:', data);
        
        if (data.success) {
            // Update heart icon
            const icon = button.querySelector('i');
            
            if (data.action === 'added') {
                icon.className = 'fas fa-heart';
                icon.style.color = '#e50914';
                showNotification('Added to your watchlist!', 'success');
            } else {
                icon.className = 'far fa-heart';
                icon.style.color = '';
                showNotification('Removed from your watchlist!', 'info');
            }
        } else {
            showNotification('Failed to update watchlist: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating watchlist', 'error');
    })
    .finally(() => {
        // Re-enable button
        button.disabled = false;
        button.style.opacity = '1';
    });
}

// ==================== CHECK WATCHLIST STATUS ====================
function checkWatchlistStatus(id, button) {
    fetch(`api/watchlist.php?movie_id=${id}`, {
        method: 'PUT'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const icon = button.querySelector('i');
            if (data.inWatchlist) {
                icon.className = 'fas fa-heart';
                icon.style.color = '#e50914';
            } else {
                icon.className = 'far fa-heart';
                icon.style.color = '';
            }
        }
    })
    .catch(error => console.error('Error checking watchlist:', error));
}

// ==================== MODAL FUNCTION ====================
function openMovieModal(contentId, contentType) {
    console.log('Opening modal for ID:', contentId, 'Type:', contentType);
    
    const modal = document.getElementById('movieModal');
    const modalBody = document.getElementById('modalBody');
    const modalLoading = document.getElementById('modalLoading');
    const modalContent = document.getElementById('modalContent');
    const modalPlayBtn = document.getElementById('modalPlayBtn');
    const modalAddToList = document.getElementById('modalAddToList');
    
    if (!modal) {
        console.error('Modal not found!');
        return;
    }
    
    // Show modal and loading spinner
    modal.style.display = 'flex';
    modalLoading.style.display = 'flex';
    modalContent.style.display = 'none';
    
    // Fetch content details
    fetch(`api/get_content_details.php?id=${contentId}&type=${contentType}`)
        .then(response => response.json())
        .then(data => {
            console.log('Content details:', data);
            
            if (data.success) {
                // Hide loading, show content
                modalLoading.style.display = 'none';
                modalContent.style.display = 'block';
                
                // Build modal content
                const content = data.data;
                const title = contentType === 'movie' ? content.title : content.name;
                const year = contentType === 'movie' 
                    ? (content.release_date ? content.release_date.substring(0, 4) : 'N/A')
                    : (content.first_air_date ? content.first_air_date.substring(0, 4) : 'N/A');
                const runtime = contentType === 'movie' 
                    ? (content.runtime ? content.runtime + ' min' : 'N/A')
                    : (content.number_of_seasons ? content.number_of_seasons + ' seasons' : 'N/A');
                const rating = content.vote_average ? content.vote_average.toFixed(1) : 'N/A';
                
                let genres = '';
                if (content.genres && content.genres.length > 0) {
                    genres = content.genres.map(g => g.name).join(', ');
                }
                
                let cast = '';
                if (content.credits && content.credits.cast && content.credits.cast.length > 0) {
                    cast = content.credits.cast.slice(0, 5).map(c => c.name).join(', ');
                }
                
                let html = `
                    <div class="modal-movie-content">
                        <div class="modal-movie-poster">
                            <img src="https://image.tmdb.org/t/p/w500${content.poster_path}" alt="${title}">
                        </div>
                        <div class="modal-movie-info">
                            <h2 class="modal-movie-title">${title}</h2>
                            <div class="modal-movie-meta">
                                <span class="modal-movie-rating">⭐ ${rating}</span>
                                <span class="modal-movie-year">${year}</span>
                                <span class="modal-movie-runtime">${runtime}</span>
                            </div>
                            ${content.tagline ? `<p class="modal-movie-tagline">"${content.tagline}"</p>` : ''}
                            <p class="modal-movie-overview">${content.overview}</p>
                            <div class="modal-movie-details">
                                ${genres ? `<div class="modal-movie-detail"><strong>Genres:</strong> ${genres}</div>` : ''}
                                ${cast ? `<div class="modal-movie-detail"><strong>Cast:</strong> ${cast}</div>` : ''}
                            </div>
                        </div>
                    </div>
                `;
                
                modalContent.innerHTML = html;
                
                // Set up play button
                let playUrl = `play.php?id=${contentId}&type=${contentType}`;
                if (contentType === 'tv') {
                    playUrl += '&season=1&episode=1';
                }
                modalPlayBtn.onclick = function() {
                    window.location.href = playUrl;
                };
                
                // Set up add to list button
                const inWatchlist = <?php echo json_encode($watchlistIds); ?>.includes(contentId);
                const heartIcon = modalAddToList.querySelector('i');
                if (inWatchlist) {
                    heartIcon.className = 'fas fa-heart';
                    heartIcon.style.color = '#e50914';
                } else {
                    heartIcon.className = 'far fa-heart';
                    heartIcon.style.color = '';
                }
                
                modalAddToList.onclick = function() {
                    const poster = content.poster_path ? `https://image.tmdb.org/t/p/w500${content.poster_path}` : '';
                    toggleWatchlist(
                        contentId, 
                        title, 
                        poster, 
                        content.vote_average, 
                        year, 
                        this
                    );
                };
                
            } else {
                modalLoading.style.display = 'none';
                modalContent.style.display = 'block';
                modalContent.innerHTML = '<p style="color: white; text-align: center;">Error loading content details.</p>';
            }
        })
        .catch(error => {
            console.error('Error fetching content details:', error);
            modalLoading.style.display = 'none';
            modalContent.style.display = 'block';
            modalContent.innerHTML = '<p style="color: white; text-align: center;">Error loading content details.</p>';
        });
}

// Close modal
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('movieModal');
    const closeBtn = document.getElementById('closeModal');
    
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    // Close when clicking outside
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
});

// ==================== NOTIFICATION FUNCTION ====================
function showNotification(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) return;
    
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.style.backgroundColor = type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6';
    toast.textContent = message;
    
    toastContainer.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(() => {
            if (toastContainer.contains(toast)) {
                toastContainer.removeChild(toast);
            }
        }, 300);
    }, 3000);
}
</script>

<?php
include 'components/modal.php';
include 'layout/footer.php';
?>