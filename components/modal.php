<?php
$modal_movie = isset($movie) ? $movie : null;
$modal_id = isset($movie_id) ? $movie_id : 'modal-' . uniqid();
?>

<div class="modal-overlay" id="movieModal" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-brand" id="modalBrand">MOVIE DETAILS</div>
            <button class="modal-close" id="closeModal">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="modal-body" id="modalBody">
            <div class="modal-loading" id="modalLoading">
                <div class="spinner"></div>
            </div>

            <div class="modal-content" id="modalContent" style="display: none;"></div>
        </div>

        <div class="modal-footer">
            <button class="btn-play-modal" id="modalPlayBtn">
                <i class="fas fa-play"></i> Watch Now
            </button>
            <button class="btn-add-list" id="modalAddToList">
                <i class="far fa-bookmark"></i> My List
            </button>
        </div>
    </div>
</div>

<template id="movieDetailTemplate">
    <div class="movie-hero-section">
        <div class="hero-backdrop">
            <img src="" alt="" id="detailPoster">
            <div class="hero-overlay"></div>
        </div>
        
        <div class="hero-info">
            <h1 class="detail-title" id="detailTitle"></h1>
            
            <div class="detail-meta">
                <span><i class="far fa-calendar"></i> <span id="yearVal"></span></span>
                <span class="rating-badge"><i class="fas fa-star"></i> <span id="ratingVal"></span></span>
                <span><i class="far fa-clock"></i> <span id="runtimeVal"></span></span>
            </div>

            <div class="detail-genres" id="genresContainer"></div>
            
            <p class="detail-overview" id="detailOverview"></p>

            <div class="cast-section">
                <h3>Cast</h3>
                <div class="cast-list" id="castList"></div>
            </div>
        </div>
    </div>
</template>