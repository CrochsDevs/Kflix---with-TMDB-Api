<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'KFLIX - Watch Free Movies')</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎬</text></svg>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @stack('styles')
    <style>
        body {
            padding-top: 70px;
            margin: 0;
            background-color: #0a0a0a;
            color: #ffffff;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .netflix-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: linear-gradient(to bottom, #000, #0a0a0a);
            border-bottom: 1px solid #333;
            height: 70px;
        }
        
        .nav-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .logo-section h1 {
            color: #e50914;
            font-size: 1.8rem;
            margin: 0;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .nav-links {
            display: flex;
            gap: 25px;
        }
        
        .nav-links a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            transition: color 0.3s;
        }
        
        .nav-links a:hover,
        .nav-links a.active {
            color: #e50914;
        }
    </style>
    <script>
        (function() {
            document.title = "KFLIX - Watch Free Movies";
            let currentTitle = "KFLIX - Watch Free Movies";
            Object.defineProperty(document, 'title', {
                get: function() { return currentTitle; },
                set: function(value) {
                    console.log('Title change attempted:', value);
                    if (value !== "Google") {
                        currentTitle = value;
                    } else {
                        console.warn('Blocked attempt to set title to Google');
                        currentTitle = "KFLIX - Watch Free Movies";
                    }
                    document.querySelector('title').textContent = currentTitle;
                }
            });
        })();
    </script>
</head>
<body>
    <nav class="netflix-nav" id="netflixNav">
        <div class="nav-content">
            <div class="logo-section">
                <h1 class="netflix-logo">KFLIX</h1>
            </div>
            <div class="nav-links">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('tv') }}" class="{{ request()->routeIs('tv*') ? 'active' : '' }}">TV Shows</a>
                <a href="{{ route('movies') }}" class="{{ request()->routeIs('movies*') ? 'active' : '' }}">Movies</a>
                <a href="{{ route('tv.newpopular') }}" class="{{ request()->routeIs('tv.newpopular*') ? 'active' : '' }}">New & Popular</a>
                <a href="{{ route('watchlist') }}" class="{{ request()->routeIs('watchlist*') ? 'active' : '' }}">My List</a>
            </div>
        </div>
    </nav>

    @yield('content')

    <!-- Footer -->
    <footer class="netflix-footer">
        <div class="footer-content">
            <div class="social-links">
                <i class="fab fa-facebook-f"></i>
                <i class="fab fa-instagram"></i>
                <i class="fab fa-twitter"></i>
                <i class="fab fa-youtube"></i>
            </div>
            <div class="footer-links">
                <div class="footer-column">
                    <a href="#">Audio and Subtitles</a>
                    <a href="#">Media Center</a>
                    <a href="#">Privacy</a>
                    <a href="#">Contact Us</a>
                </div>
                <div class="footer-column">
                    <a href="#">Audio Description</a>
                    <a href="#">Investor Relations</a>
                    <a href="#">Legal Notices</a>
                    <a href="#">Help Center</a>
                </div>
                <div class="footer-column">
                    <a href="#">Gift Cards</a>
                    <a href="#">Terms of Use</a>
                    <a href="#">Corporate Information</a>
                </div>
            </div>
            <div class="service-code">
                <span>Service Code</span>
            </div>
            <p class="copyright">&copy; 2026 KFLIX - CrochsDevs, Inc.</p>
        </div>
    </footer>

    @include('partials.modal')

    <script src="{{ asset('js/script.js') }}"></script>
    @stack('scripts')
</body>
</html>
