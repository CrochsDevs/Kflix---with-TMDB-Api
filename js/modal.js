class MovieModal {
    constructor() {
        this.modal = document.getElementById('movieModal');
        this.modalTitle = document.getElementById('modalTitle');
        this.modalContent = document.getElementById('modalContent');
        this.modalLoading = document.getElementById('modalLoading');
        this.closeBtn = document.getElementById('closeModal');
        this.playBtn = document.getElementById('modalPlayBtn');
        this.addToListBtn = document.getElementById('modalAddToList');
        this.currentMovieId = null;
        this.currentMovieData = null;
        this.token = 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI2YjQxYTFjYzY0NzQyODc2ZWY2MmUxNzEwOGMxOGNjMyIsIm5iZiI6MTc3MTExNTQ1Ny42NTUsInN1YiI6IjY5OTExM2MxM2ZiNTkwYzNmNGZhMmMyOSIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.pxULVqOJMZJeRx1nVfQ0ynS_ZgYvbV86Uanoi1FqjsI';
        
        this.init();
        this.loadWatchlist();
    }

    init() {
        if (!this.modal) {
            console.error('Modal element not found!');
            return;
        }

        this.closeBtn.addEventListener('click', () => this.close());

        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) this.close();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.style.display === 'flex') {
                this.close();
            }
        });

        this.playBtn.addEventListener('click', () => {
            if (this.currentMovieId) {
                const movieTitle = document.querySelector('.detail-title')?.textContent || 'this movie';
                this.showToast(`🎬 Playing: ${movieTitle}`);
            }
        });

        this.addToListBtn.addEventListener('click', () => {
            this.toggleWatchlist();
        });

        this.attachToButtons();
    }

    loadWatchlist() {
        this.watchlist = JSON.parse(localStorage.getItem('kflixWatchlist')) || [];
        this.updateNavbarCount();
    }

    saveWatchlist() {
        localStorage.setItem('kflixWatchlist', JSON.stringify(this.watchlist));
        this.updateNavbarCount();
    }

    updateNavbarCount() {
        const count = this.watchlist.length;
        const myListLink = document.querySelector('a[href="watchlist.php"]');
        if (myListLink) {
            if (count > 0) {
                myListLink.innerHTML = `My List <span class="watchlist-badge">${count}</span>`;
            } else {
                myListLink.innerHTML = `My List`;
            }
        }
    }

    isInWatchlist(movieId) {
        return this.watchlist.some(item => item.id === movieId);
    }

    toggleWatchlist() {
        if (!this.currentMovieData) return;

        if (this.isInWatchlist(this.currentMovieId)) {
            this.watchlist = this.watchlist.filter(item => item.id !== this.currentMovieId);
            this.addToListBtn.innerHTML = '<i class="far fa-heart"></i> Add to My List';
            this.showToast('💔 Removed from watchlist');
        } else {
            const watchlistItem = {
                id: this.currentMovieData.id,
                title: this.currentMovieData.title,
                poster_path: this.currentMovieData.poster_path,
                vote_average: this.currentMovieData.vote_average,
                release_date: this.currentMovieData.release_date,
                adult: this.currentMovieData.adult,
                addedDate: new Date().toISOString()
            };
            this.watchlist.push(watchlistItem);
            this.addToListBtn.innerHTML = '<i class="fas fa-heart"></i> In Watchlist';
            this.showToast('❤️ Added to watchlist');
        }

        this.saveWatchlist();
        this.updateLikeButtons();
    }

    updateLikeButtons() {
        document.querySelectorAll('.like-icon').forEach(btn => {
            const card = btn.closest('.movie-card');
            if (card) {
                const movieId = parseInt(card.dataset.id);
                const icon = btn.querySelector('i');
                
                if (this.isInWatchlist(movieId)) {
                    icon.classList.replace('far', 'fas');
                    icon.style.color = '#e50914';
                } else {
                    icon.classList.replace('fas', 'far');
                    icon.style.color = '#fff';
                }
            }
        });
    }

    attachToButtons() {
        document.querySelectorAll('.btn-more').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const movieId = btn.dataset.id;
                const movieCard = document.querySelector(`.movie-card[data-id="${movieId}"]`);
                if (movieCard) {
                    const movieTitle = movieCard.dataset.title;
                    this.open(movieId, movieTitle);
                }
            });
        });

        document.querySelectorAll('.info-icon').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const movieCard = btn.closest('.movie-card');
                if (movieCard) {
                    const movieId = movieCard.dataset.id;
                    const movieTitle = movieCard.dataset.title;
                    this.open(movieId, movieTitle);
                }
            });
        });

        document.querySelectorAll('.like-icon').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const movieCard = btn.closest('.movie-card');
                if (movieCard) {
                    const movieId = parseInt(movieCard.dataset.id);
                    const movieData = {
                        id: movieId,
                        title: movieCard.dataset.title,
                        poster_path: movieCard.querySelector('img')?.dataset.src?.split('/').pop(),
                        vote_average: movieCard.querySelector('.rating-badge')?.textContent.replace('⭐', '').trim()
                    };
                    
                    this.currentMovieId = movieId;
                    this.currentMovieData = movieData;
                    this.toggleWatchlist();
                }
            });
        });
    }

    async open(movieId, movieTitle) {
        this.currentMovieId = movieId;
        this.modalTitle.textContent = movieTitle || 'Movie Details';
        this.modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        this.modalLoading.style.display = 'block';
        this.modalContent.style.display = 'none';
        
        await this.loadMovieDetails(movieId);
    }

    close() {
        this.modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        this.currentMovieId = null;
        this.currentMovieData = null;
    }

    async loadMovieDetails(movieId) {
        try {
            const response = await fetch(`https://api.themoviedb.org/3/movie/${movieId}?append_to_response=credits`, {
                headers: {
                    'Authorization': 'Bearer ' + this.token,
                    'accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            this.currentMovieData = data;
            this.displayMovieDetails(data);
            
            if (this.isInWatchlist(movieId)) {
                this.addToListBtn.innerHTML = '<i class="fas fa-heart"></i> In Watchlist';
            } else {
                this.addToListBtn.innerHTML = '<i class="far fa-heart"></i> Add to My List';
            }
            
        } catch (error) {
            console.error('Error loading movie details:', error);
            this.modalContent.innerHTML = '<p class="error-msg">Failed to load movie details. Please try again.</p>';
            this.modalLoading.style.display = 'none';
            this.modalContent.style.display = 'block';
        }
    }

    displayMovieDetails(movie) {
        const poster = movie.poster_path 
            ? `https://image.tmdb.org/t/p/w500${movie.poster_path}`
            : 'https://via.placeholder.com/500x750?text=No+Poster';
        
        const year = movie.release_date ? new Date(movie.release_date).getFullYear() : 'N/A';
        const runtime = movie.runtime ? `${Math.floor(movie.runtime / 60)}h ${movie.runtime % 60}m` : 'N/A';
        const rating = movie.vote_average ? movie.vote_average.toFixed(1) : 'N/A';
        
        const genres = movie.genres.map(g => `<span class="genre-tag">${g.name}</span>`).join('');
        
        const cast = movie.credits?.cast?.slice(0, 6).map(actor => `
            <div class="cast-item">
                <img src="${actor.profile_path ? 'https://image.tmdb.org/t/p/w45' + actor.profile_path : 'https://via.placeholder.com/45x45?text=No+Image'}" 
                     alt="${actor.name}">
                <div class="cast-info">
                    <div class="cast-name">${actor.name}</div>
                    <div class="cast-character">${actor.character}</div>
                </div>
            </div>
        `).join('') || '<p>No cast information available</p>';

        const html = `
            <div class="movie-detail-layout">
                <div class="movie-detail-poster">
                    <img src="${poster}" alt="${movie.title}">
                </div>
                <div class="movie-detail-info">
                    <h3 class="detail-title">${movie.title}</h3>
                    <div class="detail-meta">
                        <span><i class="fas fa-calendar"></i> ${year}</span>
                        <span><i class="fas fa-star"></i> ${rating}</span>
                        <span><i class="fas fa-clock"></i> ${runtime}</span>
                    </div>
                    <div class="detail-genres">${genres}</div>
                    <p class="detail-overview">${movie.overview || 'No overview available.'}</p>
                    <div class="detail-cast">
                        <h4>Cast</h4>
                        <div class="cast-list">${cast}</div>
                    </div>
                </div>
            </div>
        `;

        this.modalContent.innerHTML = html;
        this.modalLoading.style.display = 'none';
        this.modalContent.style.display = 'block';
    }

    showToast(message) {
        let toastContainer = document.querySelector('.toast-container');
        
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
        }
        
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        toastContainer.appendChild(toast);
        
        setTimeout(() => toast.remove(), 3000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.movieModal = new MovieModal();
});