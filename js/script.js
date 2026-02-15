// Netflix-inspired JavaScript - Complete Optimized Version

document.addEventListener('DOMContentLoaded', function() {
    
    // ==================== ELEMENTS ====================
    const navbar = document.getElementById('netflixNav');
    const movieCards = document.querySelectorAll('.movie-card');
    const searchInput = document.querySelector('.search-input');
    const searchForm = document.getElementById('searchForm');
    const clearSearch = document.querySelector('.clear-search');
    const sortSelect = document.getElementById('sortSelect');
    const movieGrid = document.getElementById('movieGrid');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const searchIcon = document.querySelector('.search-icon');
    const notifIcon = document.querySelector('.notification-icon');
    const profileIcon = document.querySelector('.profile-icon');
    const socialIcons = document.querySelectorAll('.social-links i');
    const serviceCode = document.querySelector('.service-code span');
    const playBtn = document.querySelector('.btn-play');
    const moreInfoBtn = document.querySelector('.btn-more');
    
    // ==================== UTILITY FUNCTIONS ====================
    
    /**
     * Debounce function to limit function calls
     */
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

    /**
     * Show toast notification
     */
    function showToast(message, duration = 3000) {
        // Create toast container if it doesn't exist
        let toastContainer = document.querySelector('.toast-container');
        
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
        }
        
        // Create toast
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        
        // Add to container
        toastContainer.appendChild(toast);
        
        // Remove after duration
        setTimeout(() => {
            toast.style.animation = 'slideIn 0.3s reverse';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    /**
     * Show loading spinner
     */
    function showLoading() {
        if (loadingSpinner) {
            loadingSpinner.style.display = 'flex';
        }
    }

    /**
     * Hide loading spinner
     */
    function hideLoading() {
        if (loadingSpinner) {
            loadingSpinner.style.display = 'none';
        }
    }

    /**
     * Format date
     */
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    // ==================== PERFORMANCE OPTIMIZATIONS ====================
    
    /**
     * Navbar scroll effect with requestAnimationFrame for performance
     */
    let ticking = false;
    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                if (window.scrollY > 100) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
                ticking = false;
            });
            ticking = true;
        }
    });

    /**
     * Lazy loading for images using Intersection Observer
     */
    const lazyImages = document.querySelectorAll('.lazy-image');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    
                    // Create new image object to preload
                    const tempImage = new Image();
                    tempImage.onload = () => {
                        img.src = img.dataset.src;
                        img.classList.add('loaded');
                    };
                    tempImage.src = img.dataset.src;
                    
                    imageObserver.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.01
        });

        lazyImages.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for older browsers
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
            img.classList.add('loaded');
        });
    }

    // ==================== SEARCH FUNCTIONALITY ====================
    
    if (searchInput) {
        /**
         * Handle search input with debounce
         */
        const handleSearch = debounce((query) => {
            if (query.length > 2) {
                showLoading();
                searchForm.submit();
            } else if (query.length === 0 && window.location.search.includes('search=')) {
                // Clear search if query is empty and we're on a search page
                showLoading();
                window.location.href = '?';
            }
        }, 800);

        searchInput.addEventListener('input', (e) => {
            handleSearch(e.target.value.trim());
        });

        /**
         * Search on enter key
         */
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = e.target.value.trim();
                if (query.length > 0) {
                    showLoading();
                    searchForm.submit();
                }
            }
        });

        /**
         * Auto-focus search on slash key
         */
        document.addEventListener('keydown', (e) => {
            if (e.key === '/' && !e.ctrlKey && !e.metaKey && !e.target.matches('input, textarea')) {
                e.preventDefault();
                searchInput.focus();
            }
        });
    }

    /**
     * Clear search
     */
    if (clearSearch) {
        clearSearch.addEventListener('click', (e) => {
            e.preventDefault();
            showLoading();
            window.location.href = '?';
        });
    }

    /**
     * Search icon click
     */
    if (searchIcon) {
        searchIcon.addEventListener('click', () => {
            if (searchInput) {
                searchInput.focus();
                searchInput.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }
        });
    }

    // ==================== FILTER & SORT FUNCTIONALITY ====================
    
    /**
     * Sort functionality
     */
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', this.value);
            showLoading();
            window.location.href = url.toString();
        });
    }

    /**
     * Filter buttons active state
     */
    const filterButtons = document.querySelectorAll('.filter-btn, .time-btn, .genre-item');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Don't show loading for active filters
            if (!this.classList.contains('active')) {
                showLoading();
            }
        });
    });

    // ==================== MOVIE CARD INTERACTIONS ====================
    
    /**
     * Movie card interactions using event delegation for better performance
     */
    if (movieGrid) {
        movieGrid.addEventListener('click', (e) => {
            const card = e.target.closest('.movie-card');
            if (!card) return;

            const movieId = card.dataset.id;
            const movieTitle = card.dataset.title || card.querySelector('.movie-title')?.textContent || 'this movie';

            // Play button
            if (e.target.closest('.play-icon')) {
                e.stopPropagation();
                showToast(`🎬 Playing: ${movieTitle}`);
                
                // Optional: Add ripple effect
                const icon = e.target.closest('.play-icon');
                icon.style.transform = 'scale(0.9)';
                setTimeout(() => icon.style.transform = 'scale(1)', 200);
                
                return;
            }

            // Like button
            if (e.target.closest('.like-icon')) {
                e.stopPropagation();
                const likeBtn = e.target.closest('.like-icon');
                const icon = likeBtn.querySelector('i');
                
                if (icon.classList.contains('far')) {
                    icon.classList.replace('far', 'fas');
                    icon.style.color = '#e50914';
                    likeBtn.style.transform = 'scale(1.2)';
                    setTimeout(() => likeBtn.style.transform = 'scale(1)', 200);
                    showToast('❤️ Added to My List');
                    
                    // Optional: Save to localStorage
                    let myList = JSON.parse(localStorage.getItem('myList') || '[]');
                    if (!myList.includes(movieId)) {
                        myList.push(movieId);
                        localStorage.setItem('myList', JSON.stringify(myList));
                    }
                } else {
                    icon.classList.replace('fas', 'far');
                    icon.style.color = '#fff';
                    showToast('💔 Removed from My List');
                    
                    // Optional: Remove from localStorage
                    let myList = JSON.parse(localStorage.getItem('myList') || '[]');
                    myList = myList.filter(id => id != movieId);
                    localStorage.setItem('myList', JSON.stringify(myList));
                }
                return;
            }

            // Info button
            if (e.target.closest('.info-icon')) {
                e.stopPropagation();
                showToast(`ℹ️ More info about: ${movieTitle}`);
                
                // Optional: Show modal with movie details
                showMovieDetails(movieId);
                return;
            }

            // Card click
            showToast(`📽️ Opening details for: ${movieTitle}`);
        });

        /**
         * Mouse enter/leave effects for movie cards (optimized)
         */
        let hoverTimeout;
        movieCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                clearTimeout(hoverTimeout);
                hoverTimeout = setTimeout(() => {
                    movieCards.forEach(c => {
                        if (c !== this) {
                            c.style.transform = 'scale(1)';
                            c.style.zIndex = '1';
                        }
                    });
                    this.style.zIndex = '10';
                }, 50);
            });
        });
    }

    /**
     * Function to show movie details (can be expanded)
     */
    function showMovieDetails(movieId) {
        // This can be expanded to show a modal with movie details
        console.log('Showing details for movie ID:', movieId);
        // You can fetch more details from API here
    }

    // ==================== HERO SECTION BUTTONS ====================

    if (playBtn) {
        playBtn.addEventListener('click', () => {
            const title = document.querySelector('.hero-title')?.textContent || 'this movie';
            showToast(`🎬 Playing: ${title}`);
            
            // Add button animation
            playBtn.style.transform = 'scale(0.95)';
            setTimeout(() => playBtn.style.transform = 'scale(1)', 200);
        });
    }

    if (moreInfoBtn) {
        moreInfoBtn.addEventListener('click', () => {
            const title = document.querySelector('.hero-title')?.textContent || 'this movie';
            showToast(`ℹ️ More info about: ${title}`);
            
            // Add button animation
            moreInfoBtn.style.transform = 'scale(0.95)';
            setTimeout(() => moreInfoBtn.style.transform = 'scale(1)', 200);
        });
    }

    // ==================== NAVIGATION ICONS ====================

    if (notifIcon) {
        notifIcon.addEventListener('click', () => {
            showToast('🔔 No new notifications');
            notifIcon.style.transform = 'rotate(15deg)';
            setTimeout(() => notifIcon.style.transform = 'rotate(0deg)', 200);
        });
    }

    if (profileIcon) {
        profileIcon.addEventListener('click', () => {
            showToast('👤 Profile settings coming soon!');
        });
    }

    if (serviceCode) {
        serviceCode.addEventListener('click', () => {
            showToast('🔑 Service code: KFLIX-2024');
        });
    }

    socialIcons.forEach(icon => {
        icon.addEventListener('click', () => {
            const platform = icon.classList[1].replace('fa-', '');
            showToast(`📱 Follow us on ${platform}!`);
            
            // Open social media in new tab (optional)
            // const urls = {
            //     'facebook': 'https://facebook.com',
            //     'instagram': 'https://instagram.com',
            //     'twitter': 'https://twitter.com',
            //     'youtube': 'https://youtube.com'
            // };
            // if (urls[platform]) window.open(urls[platform], '_blank');
        });
    });

    // ==================== KEYBOARD NAVIGATION ====================
    
    document.addEventListener('keydown', (e) => {
        // Skip if user is typing in an input
        if (e.target.matches('input, textarea, select')) return;
        
        // Arrow right for next page
        if (e.key === 'ArrowRight') {
            const nextBtn = document.querySelector('.page-link.next');
            if (nextBtn) {
                showLoading();
                window.location.href = nextBtn.href;
            }
        }
        
        // Arrow left for previous page
        if (e.key === 'ArrowLeft') {
            const prevBtn = document.querySelector('.page-link.prev');
            if (prevBtn) {
                showLoading();
                window.location.href = prevBtn.href;
            }
        }
        
        // 'H' key for home
        if (e.key === 'h' || e.key === 'H') {
            window.location.href = '?';
        }
        
        // 'F' key for focus search
        if (e.key === 'f' || e.key === 'F') {
            e.preventDefault();
            if (searchInput) searchInput.focus();
        }
    });

    // ==================== PAGINATION ====================
    
    /**
     * Handle pagination clicks
     */
    document.querySelectorAll('.page-link, .page-num').forEach(link => {
        link.addEventListener('click', (e) => {
            // Don't show loading for current page
            if (!link.classList.contains('active')) {
                showLoading();
            }
        });
    });

    // ==================== DYNAMIC CONTENT ====================
    
    /**
     * Dynamic copyright year
     */
    const copyrightElement = document.querySelector('.copyright');
    if (copyrightElement) {
        copyrightElement.textContent = `© ${new Date().getFullYear()} KFLIX, Inc. All rights reserved.`;
    }

    /**
     * Tooltip for truncated titles
     */
    document.querySelectorAll('.movie-title').forEach(title => {
        if (title.scrollWidth > title.clientWidth) {
            title.setAttribute('title', title.textContent);
        }
    });

    /**
     * Add "My List" count if available
     */
    const myList = JSON.parse(localStorage.getItem('myList') || '[]');
    if (myList.length > 0) {
        const myListLink = document.querySelector('a[href="#"]'); // Update with actual My List link
        if (myListLink && myListLink.textContent.includes('My List')) {
            myListLink.innerHTML = `My List <span class="badge">${myList.length}</span>`;
        }
    }

    // ==================== LOADING STATES ====================
    
    /**
     * Hide loading spinner when page is fully loaded
     */
    window.addEventListener('load', () => {
        hideLoading();
        
        // Add loaded class to body for any initial animations
        document.body.classList.add('loaded');
    });

    /**
     * Show loading on form submissions
     */
    if (searchForm) {
        searchForm.addEventListener('submit', () => {
            showLoading();
        });
    }

    // ==================== TOUCH DEVICES ====================
    
    /**
     * Improve hover effects for touch devices
     */
    if ('ontouchstart' in window) {
        document.body.classList.add('touch-device');
        
        // Remove hover effects that might cause issues on touch
        movieCards.forEach(card => {
            card.addEventListener('touchstart', function() {
                movieCards.forEach(c => c.classList.remove('touch-hover'));
                this.classList.add('touch-hover');
            });
        });
    }

    // ==================== PERFORMANCE MONITORING ====================
    
    /**
     * Log performance metrics (optional - remove in production)
     */
    if (window.performance) {
        const perfData = window.performance.timing;
        const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
        console.log(`Page load time: ${pageLoadTime}ms`);
    }

    // ==================== ERROR HANDLING ====================
    
    /**
     * Global error handler for images
     */
    document.querySelectorAll('img').forEach(img => {
        img.addEventListener('error', function() {
            this.src = 'https://via.placeholder.com/500x750?text=Image+Not+Found';
            this.classList.add('image-error');
        });
    });

    /**
     * Handle offline/online status
     */
    window.addEventListener('offline', () => {
        showToast('📡 You are offline. Some features may not work.');
    });

    window.addEventListener('online', () => {
        showToast('📡 You are back online!');
    });

    // ==================== INITIALIZATION ====================
    
    /**
     * Initialize any components that need setup
     */
    function init() {
        console.log('KFLIX initialized');
        
        // Check if user prefers reduced motion
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.body.classList.add('reduce-motion');
        }
        
        // Set active navigation based on current page
        const currentPath = window.location.pathname;
        document.querySelectorAll('.nav-links a').forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('active');
            }
        });
    }
    
    init();
});

// ==================== ADDITIONAL UTILITIES ====================

/**
 * Smooth scroll to top
 */
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

/**
 * Get URL parameters
 */
function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    return Object.fromEntries(params.entries());
}

/**
 * Format number with commas
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * Truncate text
 */
function truncateText(text, length = 100) {
    if (text.length <= length) return text;
    return text.substr(0, length) + '...';
}

// Export for module usage (if needed)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { showToast, formatDate, getUrlParams, formatNumber, truncateText };
}