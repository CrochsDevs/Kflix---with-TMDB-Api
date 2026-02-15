class WatchlistManager {
    constructor() {
        this.watchlistGrid = document.getElementById('watchlistGrid');
        this.emptyWatchlist = document.getElementById('emptyWatchlist');
        this.skeletonLoader = document.getElementById('skeletonLoader');
        this.watchlistCount = document.getElementById('watchlistCount');
        this.totalCount = document.getElementById('totalCount');
        this.totalPagesSpan = document.getElementById('totalPages');
        this.currentPageSpan = document.getElementById('currentPage');
        this.clearBtn = document.getElementById('clearWatchlist');
        this.sortSelect = document.getElementById('watchlistSort');
        this.itemsPerPageSelect = document.getElementById('itemsPerPage');
        this.pagination = document.getElementById('watchlistPagination');
        this.prevBtn = document.getElementById('prevPage');
        this.nextBtn = document.getElementById('nextPage');
        this.pageNumbers = document.getElementById('pageNumbers');
        this.confirmModal = document.getElementById('confirmModal');
        this.closeConfirmBtn = document.getElementById('closeConfirmModal');
        this.cancelClearBtn = document.getElementById('cancelClear');
        this.confirmClearBtn = document.getElementById('confirmClear');
        this.loadingSpinner = document.getElementById('loadingSpinner');
        
        this.watchlist = [];
        this.currentPage = 1;
        this.itemsPerPage = parseInt(localStorage.getItem('watchlistItemsPerPage')) || 12;
        this.totalPages = 1;
        this.isLoading = false;
        this.imageCache = new Map();
        this.movieDetailsCache = new Map();
        this.pendingImages = new Set();
        this.token = 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI2YjQxYTFjYzY0NzQyODc2ZWY2MmUxNzEwOGMxOGNjMyIsIm5iZiI6MTc3MTExNTQ1Ny42NTUsInN1YiI6IjY5OTExM2MxM2ZiNTkwYzNmNGZhMmMyOSIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.pxULVqOJMZJeRx1nVfQ0ynS_ZgYvbV86Uanoi1FqjsI';
        
        this.init();
        
        window.addEventListener('watchlistUpdated', (e) => {
            this.watchlist = e.detail.watchlist;
            this.currentPage = 1;
            this.displayWatchlist();
        });
    }

    init() {
        this.loadWatchlist();
        this.attachEvents();
        if (this.itemsPerPageSelect) {
            this.itemsPerPageSelect.value = this.itemsPerPage;
        }
        this.loadMovieDetailsFromCache();
    }

    loadWatchlist() {
        const saved = localStorage.getItem('kflixWatchlist');
        this.watchlist = saved ? JSON.parse(saved) : [];
        
        console.log('Watchlist loaded:', this.watchlist.length, 'items');
        
        if (this.watchlist.length === 0) {
            console.log('Watchlist is empty, showing empty state');
            this.showEmptyState();
            this.hideSkeleton();
        } else {
            console.log('Watchlist has items, displaying...');
            this.displayWatchlist();
        }
    }

    loadMovieDetailsFromCache() {
        const cached = localStorage.getItem('movieDetailsCache');
        if (cached) {
            try {
                const parsed = JSON.parse(cached);
                if (parsed.timestamp > Date.now() - 86400000) {
                    parsed.data.forEach(item => {
                        this.movieDetailsCache.set(item.id, item);
                    });
                }
            } catch (e) {
                console.error('Cache error:', e);
            }
        }
    }

    saveMovieDetailsToCache() {
        const cacheData = [];
        this.movieDetailsCache.forEach((value, key) => {
            cacheData.push(value);
        });
        
        localStorage.setItem('movieDetailsCache', JSON.stringify({
            data: cacheData.slice(-50),
            timestamp: Date.now()
        }));
    }

    displayWatchlist() {
        if (this.watchlist.length === 0) {
            this.showEmptyState();
            return;
        }

        this.emptyWatchlist.style.display = 'none';
        this.showSkeleton();
        
        setTimeout(() => {
            requestAnimationFrame(() => {
                const sorted = this.sortWatchlist(this.watchlist);
                
                this.totalPages = Math.ceil(sorted.length / this.itemsPerPage);
                if (this.currentPage > this.totalPages) {
                    this.currentPage = this.totalPages;
                }
                
                const start = (this.currentPage - 1) * this.itemsPerPage;
                const end = start + this.itemsPerPage;
                const paginatedMovies = sorted.slice(start, end);
                
                console.log(`Displaying page ${this.currentPage}: ${paginatedMovies.length} movies`);
                
                const fragment = document.createDocumentFragment();
                paginatedMovies.forEach(movie => {
                    const card = this.createMovieCard(movie);
                    fragment.appendChild(card);
                });
                
                this.watchlistGrid.innerHTML = '';
                this.watchlistGrid.appendChild(fragment);
                
                this.hideSkeleton();
                this.initLazyLoading();
                this.updateCounts();
                this.updatePagination();
                this.preloadNextPageImages(sorted, end);
            });
        }, 100);
    }

    preloadNextPageImages(sorted, currentEnd) {
        const nextEnd = currentEnd + this.itemsPerPage;
        const nextPageMovies = sorted.slice(currentEnd, nextEnd);
        
        nextPageMovies.forEach(movie => {
            if (movie.poster_path) {
                const imgUrl = `https://image.tmdb.org/t/p/w200${movie.poster_path}`;
                if (!this.imageCache.has(imgUrl)) {
                    const img = new Image();
                    img.src = imgUrl;
                    this.imageCache.set(imgUrl, true);
                }
            }
        });
    }

    createMovieCard(movie) {
        const template = document.getElementById('watchlistCardTemplate');
        const card = template.content.cloneNode(true).querySelector('.movie-card');
        
        card.dataset.id = movie.id;
        
        const poster = card.querySelector('.watchlist-poster');
        let posterUrl = 'https://via.placeholder.com/500x750?text=No+Poster';
        let thumbnailUrl = posterUrl;
        
        if (movie.poster_path) {
            thumbnailUrl = `https://image.tmdb.org/t/p/w200${movie.poster_path}`;
            posterUrl = `https://image.tmdb.org/t/p/w500${movie.poster_path}`;
        } else if (movie.poster) {
            posterUrl = movie.poster;
            thumbnailUrl = movie.poster;
        }
        
        poster.dataset.src = posterUrl;
        poster.dataset.thumbnail = thumbnailUrl;
        
        if (this.imageCache.has(thumbnailUrl)) {
            poster.src = thumbnailUrl;
            poster.classList.add('loaded');
        }
        
        let rating = 'N/A';
        if (movie.vote_average) {
            rating = movie.vote_average.toFixed(1);
        } else if (movie.rating) {
            rating = movie.rating;
        }
        card.querySelector('.movie-rating').textContent = rating;
        
        let year = 'N/A';
        if (movie.release_date) {
            year = new Date(movie.release_date).getFullYear();
        } else if (movie.year) {
            year = movie.year;
        }
        card.querySelector('.release-year').textContent = year;
        
        const title = movie.title || 'Unknown Title';
        card.querySelector('.movie-title').textContent = title.length > 25 ? title.substr(0, 22) + '...' : title;
        
        const maturity = movie.adult ? 'R' : 'PG-13';
        card.querySelector('.maturity-rating').textContent = maturity;
        
        const addedDate = movie.addedDate ? new Date(movie.addedDate).toLocaleDateString() : 'Just now';
        card.querySelector('.added-date').textContent = `Added: ${addedDate}`;
        
        this.attachCardEvents(card, movie);
        
        return card;
    }

    attachCardEvents(card, movie) {
        card.querySelector('.play-icon').addEventListener('click', (e) => {
            e.stopPropagation();
            this.showToast(`🎬 Playing: ${movie.title}`);
        });

        card.querySelector('.like-icon').addEventListener('click', (e) => {
            e.stopPropagation();
            this.removeFromWatchlist(movie.id);
        });

        card.querySelector('.info-icon').addEventListener('click', (e) => {
            e.stopPropagation();
            this.showMovieDetails(movie.id);
        });

        card.addEventListener('click', () => {
            this.showMovieDetails(movie.id);
        });
    }

    removeFromWatchlist(movieId) {
        this.watchlist = this.watchlist.filter(m => m.id != movieId);
        localStorage.setItem('kflixWatchlist', JSON.stringify(this.watchlist));
        
        console.log('Removed movie, new count:', this.watchlist.length);
        
        if (this.watchlist.length === 0) {
            this.showEmptyState();
        } else {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            if (start >= this.watchlist.length) {
                this.currentPage = Math.max(1, this.currentPage - 1);
            }
            this.displayWatchlist();
        }
        
        this.showToast('✅ Removed from watchlist');
        
        if (window.movieModal) {
            window.movieModal.watchlist = this.watchlist;
            window.movieModal.updateNavbarCount();
        }
    }

    sortWatchlist(list) {
        const sortBy = this.sortSelect ? this.sortSelect.value : 'date-added';
        
        return [...list].sort((a, b) => {
            switch(sortBy) {
                case 'title':
                    return (a.title || '').localeCompare(b.title || '');
                case 'rating':
                    const ratingA = a.vote_average || a.rating || 0;
                    const ratingB = b.vote_average || b.rating || 0;
                    return ratingB - ratingA;
                case 'year':
                    const yearA = a.release_date ? new Date(a.release_date).getFullYear() : 0;
                    const yearB = b.release_date ? new Date(b.release_date).getFullYear() : 0;
                    return yearB - yearA;
                default:
                    const dateA = a.addedDate ? new Date(a.addedDate) : new Date(0);
                    const dateB = b.addedDate ? new Date(b.addedDate) : new Date(0);
                    return dateB - dateA;
            }
        });
    }

    initLazyLoading() {
        const lazyImages = document.querySelectorAll('.lazy-image:not(.loaded)');
        
        if (lazyImages.length === 0) return;
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        this.loadImage(img);
                        imageObserver.unobserve(img);
                    }
                });
            }, {
                rootMargin: '100px 0px',
                threshold: 0.01
            });

            lazyImages.forEach(img => imageObserver.observe(img));
            
            setTimeout(() => {
                lazyImages.forEach(img => {
                    if (img.getBoundingClientRect().top < window.innerHeight + 100) {
                        this.loadImage(img);
                    }
                });
            }, 500);
        } else {
            lazyImages.forEach(img => this.loadImage(img));
        }
    }

    loadImage(img) {
        if (this.pendingImages.has(img)) return;
        
        const thumbnail = img.dataset.thumbnail;
        const full = img.dataset.src;
        
        if (!full) return;
        
        this.pendingImages.add(img);
        
        if (thumbnail && thumbnail !== full) {
            const thumbImg = new Image();
            thumbImg.onload = () => {
                img.src = thumbnail;
                img.classList.add('loaded');
                
                setTimeout(() => {
                    const fullImg = new Image();
                    fullImg.onload = () => {
                        img.src = full;
                        this.imageCache.set(full, true);
                        this.pendingImages.delete(img);
                    };
                    fullImg.src = full;
                }, 100);
            };
            thumbImg.src = thumbnail;
        } else {
            const tempImage = new Image();
            tempImage.onload = () => {
                img.src = full;
                img.classList.add('loaded');
                this.imageCache.set(full, true);
                this.pendingImages.delete(img);
            };
            tempImage.onerror = () => {
                img.src = 'https://via.placeholder.com/500x750?text=No+Image';
                img.classList.add('loaded');
                this.pendingImages.delete(img);
            };
            tempImage.src = full;
        }
    }

    updatePagination() {
        if (this.totalPages <= 1) {
            this.pagination.style.display = 'none';
            return;
        }

        this.pagination.style.display = 'flex';
        
        if (this.currentPageSpan) {
            this.currentPageSpan.textContent = this.currentPage;
        }
        
        if (this.totalPagesSpan) {
            this.totalPagesSpan.textContent = this.totalPages;
        }
        
        this.prevBtn.disabled = this.currentPage === 1;
        this.nextBtn.disabled = this.currentPage === this.totalPages;
        
        let pages = [];
        if (this.totalPages <= 7) {
            for (let i = 1; i <= this.totalPages; i++) {
                pages.push(i);
            }
        } else {
            if (this.currentPage <= 4) {
                pages = [1, 2, 3, 4, 5, '...', this.totalPages];
            } else if (this.currentPage >= this.totalPages - 3) {
                pages = [1, '...', this.totalPages - 4, this.totalPages - 3, this.totalPages - 2, this.totalPages - 1, this.totalPages];
            } else {
                pages = [1, '...', this.currentPage - 2, this.currentPage - 1, this.currentPage, this.currentPage + 1, this.currentPage + 2, '...', this.totalPages];
            }
        }
        
        this.pageNumbers.innerHTML = '';
        pages.forEach(page => {
            if (page === '...') {
                const span = document.createElement('span');
                span.className = 'page-dots';
                span.textContent = '...';
                this.pageNumbers.appendChild(span);
            } else {
                const btn = document.createElement('button');
                btn.className = `page-num ${page === this.currentPage ? 'active' : ''}`;
                btn.textContent = page;
                btn.addEventListener('click', () => {
                    this.currentPage = page;
                    this.displayWatchlist();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
                this.pageNumbers.appendChild(btn);
            }
        });
    }

    showSkeleton() {
        if (this.watchlist.length > 0) {
            this.skeletonLoader.style.display = 'grid';
            this.watchlistGrid.style.display = 'none';
        } else {
            this.skeletonLoader.style.display = 'none';
        }
    }

    hideSkeleton() {
        this.skeletonLoader.style.display = 'none';
        if (this.watchlist.length > 0) {
            this.watchlistGrid.style.display = 'grid';
        }
    }

    showEmptyState() {
        console.log('Showing empty state');
        this.emptyWatchlist.style.display = 'block';
        this.watchlistGrid.style.display = 'none';
        this.skeletonLoader.style.display = 'none';
        this.pagination.style.display = 'none';
        this.updateCounts();
    }

    updateCounts() {
        const count = this.watchlist.length;
        this.watchlistCount.textContent = `${count} movie${count !== 1 ? 's' : ''}`;
        if (this.totalCount) {
            this.totalCount.textContent = count;
        }
    }

    clearAllWatchlist() {
        this.watchlist = [];
        localStorage.removeItem('kflixWatchlist');
        console.log('Watchlist cleared');
        this.showEmptyState();
        this.showToast('🗑️ Watchlist cleared');
        this.closeConfirmModal();
        
        if (window.movieModal) {
            window.movieModal.watchlist = this.watchlist;
            window.movieModal.updateNavbarCount();
        }
    }

    showConfirmModal() {
        this.confirmModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    closeConfirmModal() {
        this.confirmModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    showMovieDetails(movieId) {
        if (window.movieModal) {
            const movie = this.watchlist.find(m => m.id == movieId);
            window.movieModal.open(movieId, movie?.title || 'Movie Details');
        }
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

    attachEvents() {
        if (this.clearBtn) {
            this.clearBtn.addEventListener('click', () => this.showConfirmModal());
        }

        if (this.sortSelect) {
            this.sortSelect.addEventListener('change', () => {
                this.currentPage = 1;
                this.displayWatchlist();
            });
        }

        if (this.itemsPerPageSelect) {
            this.itemsPerPageSelect.addEventListener('change', (e) => {
                this.itemsPerPage = parseInt(e.target.value);
                localStorage.setItem('watchlistItemsPerPage', this.itemsPerPage);
                this.currentPage = 1;
                this.displayWatchlist();
            });
        }

        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => {
                if (this.currentPage > 1) {
                    this.currentPage--;
                    this.displayWatchlist();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        }

        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => {
                if (this.currentPage < this.totalPages) {
                    this.currentPage++;
                    this.displayWatchlist();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        }

        if (this.closeConfirmBtn) {
            this.closeConfirmBtn.addEventListener('click', () => this.closeConfirmModal());
        }

        if (this.cancelClearBtn) {
            this.cancelClearBtn.addEventListener('click', () => this.closeConfirmModal());
        }

        if (this.confirmClearBtn) {
            this.confirmClearBtn.addEventListener('click', () => this.clearAllWatchlist());
        }

        if (this.confirmModal) {
            this.confirmModal.addEventListener('click', (e) => {
                if (e.target === this.confirmModal) {
                    this.closeConfirmModal();
                }
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.confirmModal.style.display === 'flex') {
                this.closeConfirmModal();
            }
            
            if (e.key === 'ArrowLeft' && this.pagination.style.display === 'flex' && !this.prevBtn.disabled) {
                this.prevBtn.click();
            }
            
            if (e.key === 'ArrowRight' && this.pagination.style.display === 'flex' && !this.nextBtn.disabled) {
                this.nextBtn.click();
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing WatchlistManager');
    window.watchlistManager = new WatchlistManager();
});