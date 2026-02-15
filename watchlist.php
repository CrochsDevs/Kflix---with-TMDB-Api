<?php
require_once('vendor/autoload.php');

use GuzzleHttp\Client;

$client = new Client();
$watchlist_movies = [];
$error_message = "";

$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($current_page < 1)
    $current_page = 1;

$token = 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI2YjQxYTFjYzY0NzQyODc2ZWY2MmUxNzEwOGMxOGNjMyIsIm5iZiI6MTc3MTExNTQ1Ny42NTUsInN1YiI6IjY5OTExM2MxM2ZiNTkwYzNmNGZhMmMyOSIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.pxULVqOJMZJeRx1nVfQ0ynS_ZgYvbV86Uanoi1FqjsI';

include 'layout/header.php';
?>

<div class="main-layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3 style="color: red;"><i class="fas fa-sliders-h"></i> My Watchlist</h3>
        </div>

        <div class="filter-section">
            <h4><i class="fas fa-filter"></i> Sort By</h4>
            <select class="sort-select" id="watchlistSort">
                <option value="date-added">Date Added</option>
                <option value="title">Title A-Z</option>
                <option value="rating">Rating</option>
                <option value="year">Year</option>
            </select>
        </div>

        <div class="filter-section">
            <h4><i class="fas fa-eye"></i> Items Per Page</h4>
            <select class="sort-select" id="itemsPerPage">
                <option value="12">12 per page</option>
                <option value="24">24 per page</option>
                <option value="36">36 per page</option>
                <option value="48">48 per page</option>
            </select>
        </div>

        <div class="filter-section">
            <h4><i class="fas fa-trash"></i> Actions</h4>
            <button class="clear-filters-btn" id="clearWatchlist" style="margin-top: 0;">
                <i class="fas fa-trash-alt"></i> Clear All
            </button>
        </div>

        <div class="watchlist-stats" id="watchlistStats">
            <p>Total Movies: <span id="totalCount">0</span></p>
            <p>Current Page: <span id="currentPage">1</span> of <span id="totalPages">1</span></p>
        </div>
    </aside>

    <main class="main-content">
        <div class="results-header">
            <h1 class="section-title">
                <i class="fas fa-bookmark"></i> My Watchlist
            </h1>
            <span class="results-count" id="watchlistCount">0 movies</span>
        </div>

        <?php if ($error_message): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Skeleton Loader -->
        <div class="skeleton-grid" id="skeletonLoader" style="display: none;">
            <?php for ($i = 0; $i < 12; $i++): ?>
                <div class="skeleton-card">
                    <div class="skeleton-poster"></div>
                    <div class="skeleton-footer">
                        <div class="skeleton-rating"></div>
                        <div class="skeleton-title"></div>
                        <div class="skeleton-meta"></div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <div class="empty-watchlist" id="emptyWatchlist" style="display: none;">
            <i class="fas fa-bookmark"></i>
            <h3>Your watchlist is empty</h3>
            <p>Start adding movies by clicking the heart icon on any movie!</p>
            <a href="index.php" class="btn-play" style="display: inline-block; margin-top: 20px;">
                <i class="fas fa-film"></i> Browse Movies
            </a>
        </div>

        <div class="movie-grid" id="watchlistGrid"></div>

        <!-- Pagination -->
        <div class="pagination" id="watchlistPagination" style="display: none;">
            <button class="page-link prev" id="prevPage" disabled>
                <i class="fas fa-chevron-left"></i> Previous
            </button>

            <div class="page-numbers" id="pageNumbers"></div>

            <button class="page-link next" id="nextPage">
                Next <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <div class="modal-overlay" id="confirmModal" style="display: none;">
            <div class="modal-container" style="max-width: 400px;">
                <div class="modal-header">
                    <h2 class="modal-title">Clear Watchlist</h2>
                    <button class="modal-close" id="closeConfirmModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body" style="text-align: center;">
                    <i class="fas fa-exclamation-triangle"
                        style="font-size: 3rem; color: #e50914; margin-bottom: 15px;"></i>
                    <p style="color: #fff; font-size: 1.1rem;">Are you sure you want to remove all movies from your
                        watchlist?</p>
                </div>
                <div class="modal-footer" style="justify-content: center;">
                    <button class="btn-more" id="cancelClear">Cancel</button>
                    <button class="btn-play-modal" id="confirmClear">Yes, Clear All</button>
                </div>
            </div>
        </div>
    </main>
</div>

<template id="watchlistCardTemplate">
    <div class="movie-card" data-id="">
        <div class="card-poster">
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='500' height='750' viewBox='0 0 500 750'%3E%3Crect width='500' height='750' fill='%23333'/%3E%3C/svg%3E"
                data-src="" alt="" class="lazy-image watchlist-poster">
            <div class="card-overlay">
                <button class="play-icon" title="Play"><i class="fas fa-play"></i></button>
                <button class="like-icon active" title="Remove from list"><i class="fas fa-heart"></i></button>
                <button class="info-icon" title="More info"><i class="fas fa-info-circle"></i></button>
            </div>
        </div>
        <div class="card-footer">
            <div class="card-rating">
                <span class="rating-badge">⭐ <span class="movie-rating"></span></span>
                <span class="match-badge">In Watchlist</span>
            </div>
            <p class="movie-title"></p>
            <div class="movie-meta">
                <span class="release-year"></span>
                <span class="maturity-rating"></span>
                <span class="added-date"></span>
            </div>
        </div>
    </div>
</template>

<div id="loadingSpinner" class="loading-spinner" style="display: none;">
    <div class="spinner"></div>
</div>

<script src="js/watchlist.js"></script>
<?php include 'layout/footer.php'; ?>