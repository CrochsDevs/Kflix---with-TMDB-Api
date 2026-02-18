<?php
// watchlist.php
require_once('vendor/autoload.php');
require_once 'models/Watchlist.php';
include 'layout/header.php';
?>

<div class="main-layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3 style="color: var(--primary-color);"><i class="fas fa-sliders-h"></i> My Watchlist</h3>
        </div>

        <div class="filter-section">
            <h4><i class="fas fa-filter"></i> Sort By</h4>
            <select class="sort-select" id="watchlistSort">
                <option value="date-added">Date Added (Newest)</option>
                <option value="title">Title (A-Z)</option>
                <option value="rating">Rating (Highest)</option>
                <option value="year">Year (Newest)</option>
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
            <button class="clear-filters-btn" id="clearWatchlist">
                <i class="fas fa-trash-alt"></i> Clear All
            </button>
        </div>

        <div class="watchlist-stats" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color);">
            <p style="display: flex; justify-content: space-between;">
                <span>Total Movies:</span>
                <span id="totalCount" style="color: var(--primary-color); font-weight: bold;">0</span>
            </p>
            <p style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #888;">
                <span>Page:</span>
                <span><span id="currentPage">1</span> of <span id="totalPages">1</span></span>
            </p>
        </div>
    </aside>

    <main class="main-content">
        <div class="results-header">
            <h1 class="section-title">
                <i class="fas fa-bookmark" style="margin-right: 10px;"></i> My Watchlist
            </h1>
            <span class="results-count" id="watchlistCount">0 movies</span>
        </div>

        <!-- Loading Spinner -->
        <div id="watchlistLoading" style="display: none; text-align: center; padding: 50px;">
            <div class="spinner" style="margin: 0 auto;"></div>
            <p style="color: #888; margin-top: 15px;">Loading your watchlist...</p>
        </div>

        <!-- Empty Watchlist -->
        <div class="empty-watchlist" id="emptyWatchlist" style="display: none;">
            <i class="fas fa-bookmark"></i>
            <h3>Your watchlist is empty</h3>
            <p>Start adding movies to see them here!</p>
            <a href="index.php" class="clear-filters-btn" style="display: inline-block; width: auto; padding: 10px 30px; margin-top: 20px; text-decoration: none;">
                <i class="fas fa-film"></i> Browse Movies
            </a>
        </div>

        <!-- Watchlist Grid -->
        <div class="movie-grid" id="watchlistGrid"></div>

        <!-- Pagination -->
        <div class="pagination" id="watchlistPagination" style="display: none;">
            <button class="page-link prev" id="prevPage" disabled>
                <i class="fas fa-chevron-left"></i> Previous
            </button>
            <div class="page-numbers" id="pageNumbers"></div>
            <button class="page-link next" id="nextPage" disabled>
                Next <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </main>
</div>

<!-- Confirmation Modal -->
<div class="modal-overlay" id="confirmModal" style="display: none;">
    <div class="modal-container" style="max-width: 400px;">
        <div class="modal-body" style="text-align: center; padding: 30px;">
            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 15px;"></i>
            <h3 style="margin-bottom: 10px; color: #fff;">Clear Watchlist?</h3>
            <p style="color: var(--text-dim); margin-bottom: 25px;">Are you sure you want to remove all movies from your watchlist? This action cannot be undone.</p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <button id="cancelClear" class="clear-filters-btn" style="width: auto; padding: 10px 25px; margin: 0; background: transparent;">
                    Cancel
                </button>
                <button id="confirmClear" style="padding: 10px 25px; background: var(--primary-color); color: white; border: none; border-radius: var(--border-radius-md); cursor: pointer; font-weight: bold;">
                    Yes, Clear All
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Watchlist Card Template -->
<template id="watchlistCardTemplate">
    <div class="movie-card">
        <div class="card-poster">
            <img src="" alt="" class="watchlist-poster" loading="lazy">
            <div class="card-overlay">
                <button class="play-icon" title="Play"><i class="fas fa-play"></i></button>
                <button class="like-icon" title="Remove from list"><i class="fas fa-heart" style="color: var(--primary-color);"></i></button>
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
                <span class="added-date"></span>
            </div>
        </div>
    </div>
</template>

<script src="js/script.js"></script>

<?php include 'layout/footer.php'; ?>