// Netflix-inspired JavaScript - Database Version (COMPLETE FIX)

// ==================== META MASK FIX ====================
(function() {
    if (window.ethereum) {
        try {
            const originalEthereum = window.ethereum;
            delete window.ethereum;
            window.ethereum = originalEthereum;
            console.log('✅ MetaMask fix applied');
        } catch (e) {
            console.warn('⚠️ Could not fix MetaMask conflict:', e);
        }
    }
})();

document.addEventListener('DOMContentLoaded', function() {

    // ==================== GLOBAL VARIABLES ====================
    let watchlist = [];
    let currentPage = 1;
    let itemsPerPage = parseInt(localStorage.getItem('watchlistItemsPerPage')) || 12;
    let totalPages = 1;
    let isLoading = false;
    let currentMovieData = null;

    // ==================== ELEMENTS ====================
    const navbar = document.getElementById('netflixNav');
    const searchInput = document.querySelector('.search-input');
    const searchForm = document.getElementById('searchForm');
    const clearSearch = document.querySelector('.clear-search');
    const sortSelect = document.getElementById('sortSelect');
    const movieGrid = document.getElementById('movieGrid');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const notifIcon = document.querySelector('.notification-icon');
    const profileIcon = document.querySelector('.profile-icon');
    const socialIcons = document.querySelectorAll('.social-links i');
    const serviceCode = document.querySelector('.service-code span');
    const playBtn = document.querySelector('.btn-play');
    const moreInfoBtn = document.querySelector('.btn-more');

    // ==================== MODAL ELEMENTS ====================
    const modal = document.getElementById('movieModal');
    const modalBody = document.getElementById('modalBody');
    const modalContent = document.getElementById('modalContent');
    const modalLoading = document.getElementById('modalLoading');
    const closeModal = document.getElementById('closeModal');
    const modalPlayBtn = document.getElementById('modalPlayBtn');
    const modalAddToList = document.getElementById('modalAddToList');

    // Watchlist Elements
    const watchlistGrid = document.getElementById('watchlistGrid');
    const emptyWatchlist = document.getElementById('emptyWatchlist');
    const watchlistCount = document.getElementById('watchlistCount');
    const totalCount = document.getElementById('totalCount');
    const currentPageSpan = document.getElementById('currentPage');
    const totalPagesSpan = document.getElementById('totalPages');
    const watchlistSortSelect = document.getElementById('watchlistSort');
    const itemsPerPageSelect = document.getElementById('itemsPerPage');
    const confirmModal = document.getElementById('confirmModal');
    const template = document.getElementById('watchlistCardTemplate');
    const watchlistLoading = document.getElementById('watchlistLoading');
    const watchlistPagination = document.getElementById('watchlistPagination');

    // ==================== UTILITY FUNCTIONS ====================

    const debounce = (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    function showToast(message, type = 'success', duration = 3000) {
        let toastContainer = document.getElementById('toastContainer');

        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = 'toast';

        let icon = '✅';
        if (type === 'error') icon = '❌';
        if (type === 'warning') icon = '⚠️';
        if (type === 'info') icon = 'ℹ️';
        if (type === 'heart') icon = '❤️';

        toast.innerHTML = `${icon} ${message}`;
        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    function showLoading() {
        isLoading = true;
        if (loadingSpinner) loadingSpinner.style.display = 'flex';
        if (watchlistLoading) watchlistLoading.style.display = 'block';
        if (modalLoading) modalLoading.style.display = 'flex';
        if (modalContent) modalContent.style.display = 'none';
    }

    function hideLoading() {
        isLoading = false;
        if (loadingSpinner) loadingSpinner.style.display = 'none';
        if (watchlistLoading) watchlistLoading.style.display = 'none';
        if (modalLoading) modalLoading.style.display = 'none';
        if (modalContent) modalContent.style.display = 'block';
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return 'N/A';
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        } catch (e) {
            return 'N/A';
        }
    }

    // ==================== MODAL FUNCTIONS ====================

    /**
     * OPEN MOVIE MODAL
     */
    async function openMovieModal(movieId) {
        if (!modal || !modalContent) {
            console.error('Modal elements not found');
            return;
        }
        
        if (isLoading) return;
        
        console.log('📌 Opening modal for movie ID:', movieId);
        showLoading();
        
        try {
            // Fetch movie details from TMDB API
            const response = await fetch(`https://api.themoviedb.org/3/movie/${movieId}?append_to_response=credits,videos`, {
                headers: {
                    'Authorization': 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI2YjQxYTFjYzY0NzQyODc2ZWY2MmUxNzEwOGMxOGNjMyIsIm5iZiI6MTc3MTExNTQ1Ny42NTUsInN1YiI6IjY5OTExM2MxM2ZiNTkwYzNmNGZhMmMyOSIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.pxULVqOJMZJeRx1nVfQ0ynS_ZgYvbV86Uanoi1FqjsI',
                    'accept': 'application/json'
                }
            });
            
            const movieData = await response.json();
            console.log('📌 Movie data received:', movieData);
            
            // Check if movie is in watchlist
            let inWatchlist = false;
            try {
                const checkResponse = await fetch(`api/watchlist.php?movie_id=${movieId}`, {
                    method: 'PUT'
                });
                const checkData = await checkResponse.json();
                inWatchlist = checkData.success && checkData.inWatchlist;
            } catch (e) {
                console.warn('Could not check watchlist status:', e);
            }
            
            // Store current movie data for footer buttons
            currentMovieData = {
                id: movieData.id,
                title: movieData.title,
                poster_path: movieData.poster_path,
                vote_average: movieData.vote_average,
                release_date: movieData.release_date,
                in_watchlist: inWatchlist
            };
            
            // Populate modal with movie data
            modalContent.innerHTML = generateModalContent(movieData, inWatchlist);
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
            
            // Update footer buttons
            updateModalFooterButtons(inWatchlist);
            
        } catch (error) {
            console.error('❌ Error loading movie details:', error);
            showToast('Error loading movie details', 'error');
        } finally {
            hideLoading();
        }
    }

    /**
     * GENERATE MODAL CONTENT
     */
    function generateModalContent(movie, inWatchlist) {
        const poster = movie.poster_path 
            ? `https://image.tmdb.org/t/p/w500${movie.poster_path}`
            : 'https://via.placeholder.com/500x750?text=No+Poster';
        
        const year = movie.release_date ? movie.release_date.split('-')[0] : 'N/A';
        const runtime = movie.runtime ? `${movie.runtime} min` : 'N/A';
        const rating = movie.vote_average ? movie.vote_average.toFixed(1) : 'N/A';
        
        // Get genres
        const genres = movie.genres ? movie.genres.map(g => g.name).join(', ') : 'N/A';
        
        // Get director
        let director = 'N/A';
        if (movie.credits && movie.credits.crew) {
            const directorObj = movie.credits.crew.find(person => person.job === 'Director');
            director = directorObj ? directorObj.name : 'N/A';
        }
        
        // Get cast (top 5)
        let cast = 'N/A';
        if (movie.credits && movie.credits.cast) {
            cast = movie.credits.cast.slice(0, 5).map(actor => actor.name).join(', ');
        }
        
        return `
            <div class="modal-movie-content">
                <div class="modal-movie-poster">
                    <img src="${poster}" alt="${movie.title}" onerror="this.src='https://via.placeholder.com/500x750?text=No+Image'">
                </div>
                <div class="modal-movie-info">
                    <h2 class="modal-movie-title">${movie.title}</h2>
                    
                    <div class="modal-movie-meta">
                        <span class="modal-movie-rating">⭐ ${rating}</span>
                        <span class="modal-movie-year">${year}</span>
                        <span class="modal-movie-runtime">${runtime}</span>
                        <span class="modal-movie-maturity">${movie.adult ? 'R' : 'PG-13'}</span>
                    </div>
                    
                    ${movie.tagline ? `<p class="modal-movie-tagline">"${movie.tagline}"</p>` : ''}
                    
                    <p class="modal-movie-overview">${movie.overview || 'No overview available.'}</p>
                    
                    <div class="modal-movie-details">
                        ${movie.genres ? `<div class="modal-movie-detail"><strong>Genres:</strong> ${genres}</div>` : ''}
                        ${director !== 'N/A' ? `<div class="modal-movie-detail"><strong>Director:</strong> ${director}</div>` : ''}
                        ${cast !== 'N/A' ? `<div class="modal-movie-detail"><strong>Cast:</strong> ${cast}</div>` : ''}
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * UPDATE MODAL FOOTER BUTTONS
     */
    function updateModalFooterButtons(inWatchlist) {
        if (modalAddToList) {
            const icon = modalAddToList.querySelector('i');
            if (inWatchlist) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                modalAddToList.innerHTML = '<i class="fas fa-heart"></i> In Watchlist';
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                modalAddToList.innerHTML = '<i class="far fa-heart"></i> Add to List';
            }
        }
    }

    /**
     * CLOSE MOVIE MODAL
     */
    function closeMovieModal() {
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = ''; // Restore scrolling
            currentMovieData = null;
        }
    }

    // ==================== MODAL EVENT LISTENERS ====================

    // Close modal when clicking close button
    if (closeModal) {
        closeModal.addEventListener('click', closeMovieModal);
    }

    // Close modal when clicking overlay
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeMovieModal();
            }
        });
    }

    // Modal play button
    if (modalPlayBtn) {
        modalPlayBtn.addEventListener('click', function() {
            if (currentMovieData) {
                // Redirect to play page
                window.location.href = `play.php?id=${currentMovieData.id}`;
            }
        });
    }

    // Modal add to list button
    if (modalAddToList) {
        modalAddToList.addEventListener('click', async function() {
            if (!currentMovieData || isLoading) return;
            
            const wasInWatchlist = currentMovieData.in_watchlist;
            
            if (wasInWatchlist) {
                const success = await removeFromWatchlist(currentMovieData.id);
                if (success) {
                    currentMovieData.in_watchlist = false;
                    updateModalFooterButtons(false);
                }
            } else {
                const success = await addToWatchlist(currentMovieData);
                if (success) {
                    currentMovieData.in_watchlist = true;
                    updateModalFooterButtons(true);
                }
            }
        });
    }

    // ==================== WATCHLIST FUNCTIONS ====================

    /**
     * LOAD WATCHLIST
     */
    async function loadWatchlist() {
        if (!watchlistGrid) {
            console.log('watchlistGrid not found');
            return;
        }

        console.log('📌 Loading watchlist...');
        showLoading();

        try {
            const response = await fetch('api/watchlist.php');
            const data = await response.json();
            
            console.log('📌 API Response:', data);
            
            if (data.success) {
                const movies = data.data || [];
                console.log(`📌 Found ${movies.length} movies`);
                
                if (movies.length === 0) {
                    watchlistGrid.style.display = 'none';
                    if (emptyWatchlist) emptyWatchlist.style.display = 'block';
                    if (watchlistPagination) watchlistPagination.style.display = 'none';
                    
                    if (totalCount) totalCount.textContent = '0';
                    if (watchlistCount) watchlistCount.textContent = '0 movies';
                    
                } else {
                    watchlistGrid.style.display = 'grid';
                    if (emptyWatchlist) emptyWatchlist.style.display = 'none';
                    if (watchlistPagination) watchlistPagination.style.display = 'flex';
                    
                    watchlistGrid.innerHTML = '';
                    
                    movies.forEach(movie => {
                        const card = createWatchlistCard(movie);
                        watchlistGrid.appendChild(card);
                    });
                    
                    if (totalCount) totalCount.textContent = movies.length;
                    if (watchlistCount) watchlistCount.textContent = `${movies.length} movie${movies.length !== 1 ? 's' : ''}`;
                    
                    if (currentPageSpan) currentPageSpan.textContent = '1';
                    if (totalPagesSpan) totalPagesSpan.textContent = '1';
                }
            } else {
                console.error('❌ API returned success: false', data);
                showToast('Failed to load watchlist', 'error');
            }
            
        } catch (error) {
            console.error('❌ Error loading watchlist:', error);
            showToast('Error loading watchlist', 'error');
        } finally {
            hideLoading();
        }
    }

    /**
     * CREATE WATCHLIST CARD
     */
    function createWatchlistCard(movie) {
        const card = document.createElement('div');
        card.className = 'movie-card';
        card.dataset.id = movie.movie_id || movie.id || '';
        card.dataset.title = movie.title || '';
        
        const posterBase = 'https://image.tmdb.org/t/p/w500';
        let posterUrl = 'https://via.placeholder.com/500x750?text=No+Poster';
        
        if (movie.poster_path) {
            const path = movie.poster_path.startsWith('/') ? movie.poster_path : '/' + movie.poster_path;
            posterUrl = posterBase + path;
        }
        
        const rating = parseFloat(movie.vote_average || 0).toFixed(1);
        
        let year = 'N/A';
        if (movie.release_date) {
            year = movie.release_date.split('-')[0];
        }
        
        const addedDate = movie.added_date ? formatDate(movie.added_date) : 'N/A';
        
        card.innerHTML = `
            <div class="card-poster">
                <img src="${posterUrl}" alt="${movie.title || 'Movie'}" 
                     loading="lazy"
                     onerror="this.src='https://via.placeholder.com/500x750?text=No+Image'">
                <div class="card-overlay">
                    <button class="play-icon" title="Play"><i class="fas fa-play"></i></button>
                    <button class="like-icon" title="Remove from list"><i class="fas fa-heart" style="color: var(--primary-color);"></i></button>
                    <button class="info-icon" title="More info"><i class="fas fa-info-circle"></i></button>
                </div>
            </div>
            <div class="card-footer">
                <div class="card-rating">
                    <span class="rating-badge">⭐ ${rating}</span>
                    <span class="match-badge">In Watchlist</span>
                </div>
                <p class="movie-title">${movie.title || 'Unknown Title'}</p>
                <div class="movie-meta">
                    <span class="release-year">${year}</span>
                    <span class="added-date">${addedDate}</span>
                </div>
            </div>
        `;
        
        const likeBtn = card.querySelector('.like-icon');
        if (likeBtn) {
            likeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                removeFromWatchlist(movie.movie_id || movie.id);
            });
        }
        
        const infoBtn = card.querySelector('.info-icon');
        if (infoBtn) {
            infoBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                openMovieModal(movie.movie_id || movie.id);
            });
        }
        
        const playBtn = card.querySelector('.play-icon');
        if (playBtn) {
            playBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                // Redirect to play page
                window.location.href = `play.php?id=${movie.movie_id || movie.id}`;
            });
        }
        
        return card;
    }

    /**
     * ADD TO WATCHLIST
     */
    async function addToWatchlist(movieData) {
        if (!movieData || !movieData.id || isLoading) {
            console.log('❌ Invalid movie data:', movieData);
            return false;
        }
        
        console.log('📌 Adding movie:', movieData);
        showLoading();
        
        try {
            const response = await fetch('api/watchlist.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: movieData.id,
                    title: movieData.title,
                    poster_path: movieData.poster_path,
                    vote_average: movieData.vote_average,
                    release_date: movieData.release_date
                })
            });
            
            const data = await response.json();
            console.log('📌 Add response:', data);
            
            if (data.success) {
                showToast(data.message, 'heart');
                
                // Update badge
                const myListLink = document.getElementById('myListLink');
                if (myListLink) {
                    const existingBadge = myListLink.querySelector('.watchlist-badge');
                    if (existingBadge) {
                        existingBadge.textContent = data.count;
                    } else {
                        const badge = document.createElement('span');
                        badge.className = 'watchlist-badge';
                        badge.textContent = data.count;
                        myListLink.appendChild(badge);
                    }
                }
                
                // Update heart icon on main page
                const heartIcon = document.querySelector(`.movie-card[data-id="${movieData.id}"] .like-icon i`);
                if (heartIcon) {
                    heartIcon.classList.remove('far');
                    heartIcon.classList.add('fas');
                    heartIcon.style.color = 'var(--primary-color)';
                }
                
                // Update data-watchlist attribute
                const movieCard = document.querySelector(`.movie-card[data-id="${movieData.id}"]`);
                if (movieCard) {
                    movieCard.dataset.watchlist = '1';
                }
                
                return true;
            } else {
                showToast(data.message || 'Failed to add', 'warning');
                return false;
            }
            
        } catch (error) {
            console.error('❌ Error adding:', error);
            showToast('Error adding to watchlist', 'error');
            return false;
        } finally {
            hideLoading();
        }
    }

    /**
     * REMOVE FROM WATCHLIST
     */
    async function removeFromWatchlist(movieId) {
        if (!movieId || isLoading) return false;
        
        console.log('📌 Removing movie:', movieId);
        showLoading();
        
        try {
            const response = await fetch(`api/watchlist.php?movie_id=${movieId}`, {
                method: 'DELETE'
            });
            const data = await response.json();
            
            console.log('📌 Remove response:', data);
            
            if (data.success) {
                showToast(data.message, 'heart');
                
                // Update badge
                const myListLink = document.getElementById('myListLink');
                if (myListLink) {
                    const badge = myListLink.querySelector('.watchlist-badge');
                    if (badge) {
                        if (data.count > 0) {
                            badge.textContent = data.count;
                        } else {
                            badge.remove();
                        }
                    }
                }
                
                // Update heart on main page
                const heartIcon = document.querySelector(`.movie-card[data-id="${movieId}"] .like-icon i`);
                if (heartIcon) {
                    heartIcon.classList.remove('fas');
                    heartIcon.classList.add('far');
                    heartIcon.style.color = '';
                }
                
                // Update data-watchlist attribute
                const movieCard = document.querySelector(`.movie-card[data-id="${movieId}"]`);
                if (movieCard) {
                    movieCard.dataset.watchlist = '0';
                }
                
                // If on watchlist page, reload
                if (window.location.pathname.includes('watchlist.php')) {
                    loadWatchlist();
                }
                
                return true;
            } else {
                showToast(data.message || 'Failed to remove', 'error');
                return false;
            }
            
        } catch (error) {
            console.error('❌ Error removing:', error);
            showToast('Error removing from watchlist', 'error');
            return false;
        } finally {
            hideLoading();
        }
    }

    /**
     * CHECK WATCHLIST STATUS
     */
    async function checkWatchlistStatus(movieId) {
        if (!movieId) return;
        
        try {
            const response = await fetch(`api/watchlist.php?movie_id=${movieId}`, {
                method: 'PUT'
            });
            const data = await response.json();
            
            if (data.success && data.inWatchlist) {
                const heartIcon = document.querySelector(`.movie-card[data-id="${movieId}"] .like-icon i`);
                if (heartIcon) {
                    heartIcon.classList.remove('far');
                    heartIcon.classList.add('fas');
                    heartIcon.style.color = 'var(--primary-color)';
                }
            }
        } catch (error) {
            console.error('Error checking status:', error);
        }
    }

    /**
     * FETCH WATCHLIST COUNT
     */
    async function fetchWatchlistCount() {
        try {
            const response = await fetch('api/watchlist.php');
            const data = await response.json();
            
            if (data.success) {
                const count = data.total || 0;
                
                const myListLink = document.getElementById('myListLink');
                if (myListLink) {
                    const existingBadge = myListLink.querySelector('.watchlist-badge');
                    if (existingBadge) existingBadge.remove();
                    
                    if (count > 0) {
                        const badge = document.createElement('span');
                        badge.className = 'watchlist-badge';
                        badge.textContent = count;
                        myListLink.appendChild(badge);
                    }
                }
            }
        } catch (error) {
            console.error('Error fetching count:', error);
        }
    }

    /**
     * CLEAR WATCHLIST
     */
    async function clearWatchlist() {
        if (isLoading) return;
        
        showLoading();
        
        try {
            const response = await fetch('api/watchlist.php', {
                method: 'DELETE'
            });
            const data = await response.json();
            
            if (data.success) {
                confirmModal.style.display = 'none';
                showToast(data.message, 'warning');
                
                // Remove badge
                const myListLink = document.getElementById('myListLink');
                if (myListLink) {
                    const badge = myListLink.querySelector('.watchlist-badge');
                    if (badge) badge.remove();
                }
                
                // Update all hearts
                document.querySelectorAll('.movie-card .like-icon i').forEach(icon => {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    icon.style.color = '';
                });
                
                // Update data-watchlist attributes
                document.querySelectorAll('.movie-card').forEach(card => {
                    card.dataset.watchlist = '0';
                });
                
                // Reload watchlist
                if (window.location.pathname.includes('watchlist.php')) {
                    loadWatchlist();
                }
            }
            
        } catch (error) {
            console.error('Error clearing:', error);
            showToast('Failed to clear watchlist', 'error');
        } finally {
            hideLoading();
        }
    }

    // ==================== PAGE DETECTION ====================

    // Check if we're on watchlist page
    if (window.location.pathname.includes('watchlist.php')) {
        console.log('📌 WATCHLIST PAGE DETECTED');
        
        setTimeout(() => {
            loadWatchlist();
        }, 100);
        
        if (watchlistSortSelect) {
            watchlistSortSelect.addEventListener('change', () => {
                console.log('Sort changed to:', watchlistSortSelect.value);
                loadWatchlist();
            });
        }
        
        if (itemsPerPageSelect) {
            itemsPerPageSelect.addEventListener('change', (e) => {
                itemsPerPage = parseInt(e.target.value);
                localStorage.setItem('watchlistItemsPerPage', itemsPerPage);
                loadWatchlist();
            });
            itemsPerPageSelect.value = itemsPerPage;
        }
        
        const clearBtn = document.getElementById('clearWatchlist');
        const cancelBtn = document.getElementById('cancelClear');
        const confirmBtn = document.getElementById('confirmClear');
        
        if (clearBtn && confirmModal) {
            clearBtn.addEventListener('click', () => {
                confirmModal.style.display = 'flex';
            });
        }
        
        if (cancelBtn && confirmModal) {
            cancelBtn.addEventListener('click', () => {
                confirmModal.style.display = 'none';
            });
        }
        
        if (confirmBtn && confirmModal) {
            confirmBtn.addEventListener('click', clearWatchlist);
        }
        
        window.addEventListener('click', (e) => {
            if (e.target === confirmModal) {
                confirmModal.style.display = 'none';
            }
        });
    }

    // ==================== MAIN PAGE ====================
    // PLAY BUTTON LANG ANG MAGBUBUKAS NG PLAY PAGE

    if (movieGrid && !window.location.pathname.includes('watchlist.php')) {
        console.log('📌 MAIN PAGE DETECTED');
        
        fetchWatchlistCount();
        
        movieGrid.addEventListener('click', async (e) => {
            const card = e.target.closest('.movie-card');
            if (!card || isLoading) return;
            
            const movieId = card.dataset.id;
            const movieTitle = card.dataset.title;
            
            const movieData = {
                id: parseInt(movieId),
                title: movieTitle,
                poster_path: card.dataset.poster,
                vote_average: parseFloat(card.dataset.rating) || 0,
                release_date: card.dataset.year ? card.dataset.year + '-01-01' : null
            };
            
            // PLAY ICON - Redirect to play page (ITO LANG ANG MAGBUBUKAS NG PLAY PAGE)
            if (e.target.closest('.play-icon')) {
                e.stopPropagation();
                e.preventDefault();
                window.location.href = `play.php?id=${movieId}`;
                return;
            }
            
            // LIKE ICON - Add/remove from watchlist
            if (e.target.closest('.like-icon')) {
                e.stopPropagation();
                e.preventDefault();
                const likeBtn = e.target.closest('.like-icon');
                const icon = likeBtn.querySelector('i');
                
                if (icon.classList.contains('far')) {
                    await addToWatchlist(movieData);
                } else {
                    await removeFromWatchlist(movieId);
                }
                return;
            }
            
            // INFO ICON - Open modal
            if (e.target.closest('.info-icon')) {
                e.stopPropagation();
                e.preventDefault();
                openMovieModal(movieId);
                return;
            }
            
            // ❌ WALA NANG IBANG MAGBUBUKAS - HINDI MAGBUBUKAS ANG PLAY PAGE O MODAL
            // Ang play page ay sa PLAY ICON LANG nagbubukas
        });
        
        setTimeout(() => {
            document.querySelectorAll('.movie-card').forEach(card => {
                const movieId = card.dataset.id;
                if (movieId) {
                    checkWatchlistStatus(movieId);
                }
            });
        }, 500);
    }

    // ==================== HERO BUTTONS ====================

    if (playBtn) {
        playBtn.addEventListener('click', () => {
            const movieId = playBtn.dataset.id;
            if (movieId) {
                window.location.href = `play.php?id=${movieId}`;
            }
        });
    }

    if (moreInfoBtn) {
        moreInfoBtn.addEventListener('click', () => {
            const movieId = moreInfoBtn.dataset.id;
            openMovieModal(movieId);
            moreInfoBtn.style.transform = 'scale(0.95)';
            setTimeout(() => moreInfoBtn.style.transform = 'scale(1)', 200);
        });
    }

    // ==================== NAVBAR SCROLL ====================

    let ticking = false;
    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                if (window.scrollY > 100) {
                    navbar?.classList.add('scrolled');
                } else {
                    navbar?.classList.remove('scrolled');
                }
                ticking = false;
            });
            ticking = true;
        }
    });

    // ==================== SEARCH ====================

    if (searchInput) {
        const handleSearch = debounce((query) => {
            if (query.length > 2) {
                showLoading();
                searchForm?.submit();
            } else if (query.length === 0 && window.location.search.includes('search=')) {
                showLoading();
                window.location.href = '?';
            }
        }, 800);

        searchInput.addEventListener('input', (e) => {
            handleSearch(e.target.value.trim());
        });

        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = e.target.value.trim();
                if (query.length > 0) {
                    showLoading();
                    searchForm?.submit();
                }
            }
        });
    }

    if (clearSearch) {
        clearSearch.addEventListener('click', (e) => {
            e.preventDefault();
            showLoading();
            window.location.href = '?';
        });
    }

    // ==================== SORT ====================

    if (sortSelect && !window.location.pathname.includes('watchlist.php')) {
        sortSelect.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', this.value);
            showLoading();
            window.location.href = url.toString();
        });
    }

    // ==================== ICONS ====================

    if (notifIcon) {
        notifIcon.addEventListener('click', () => {
            showToast('No new notifications', 'info');
            notifIcon.style.transform = 'rotate(15deg)';
            setTimeout(() => notifIcon.style.transform = 'rotate(0deg)', 200);
        });
    }

    if (profileIcon) {
        profileIcon.addEventListener('click', () => {
            showToast('Profile settings coming soon!', 'info');
        });
    }

    if (serviceCode) {
        serviceCode.addEventListener('click', () => {
            showToast('Service code: KFLIX-2024', 'info');
        });
    }

    socialIcons.forEach(icon => {
        icon.addEventListener('click', () => {
            const platform = icon.classList[1].replace('fa-', '');
            showToast(`Follow us on ${platform}!`, 'info');
        });
    });

    // ==================== IMAGE ERRORS ====================

    document.querySelectorAll('img').forEach(img => {
        img.addEventListener('error', function() {
            this.src = 'https://via.placeholder.com/500x750?text=Image+Not+Found';
        });
    });

    // ==================== CLOSE MODAL WITH ESC KEY ====================

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
            closeMovieModal();
        }
    });

    // ==================== INIT ====================

    function init() {
        console.log('📌 KFLIX initialized');
        
        const currentPath = window.location.pathname.split('/').pop();
        document.querySelectorAll('.nav-links a').forEach(link => {
            const href = link.getAttribute('href');
            if (href === currentPath || (currentPath === '' && href === 'index.php')) {
                link.classList.add('active');
            }
        });
        
        window.addEventListener('load', hideLoading);
    }

    init();
});

// Add slideOut animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);