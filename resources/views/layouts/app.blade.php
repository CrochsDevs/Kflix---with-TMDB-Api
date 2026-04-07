<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <title>@yield('title', 'KFLIX - Watch Free Movies')</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎬</text></svg>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @stack('styles')
</head>
<body>
    <!-- NAV -->
    <nav class="nav" id="mainNav">
        <div class="nav-inner">
            <div class="nav-brand"><a href="{{ route('home') }}">KFLIX</a></div>
            <button class="nav-hamburger" id="navToggle" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
            <div class="nav-menu" id="navMenu">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="fa-solid fa-house nav-link-icon"></i><span>Home</span></a>
                <a href="{{ route('tv') }}" class="nav-link {{ request()->routeIs('tv*') ? 'active' : '' }}">
                    <i class="fa-solid fa-tv nav-link-icon"></i><span>TV Shows</span></a>
                <a href="{{ route('movies') }}" class="nav-link {{ request()->routeIs('movies') ? 'active' : '' }}">
                    <i class="fa-solid fa-film nav-link-icon"></i><span>Movies</span></a>
                <a href="{{ route('tv.newpopular') }}" class="nav-link {{ request()->routeIs('tv.newpopular*') ? 'active' : '' }}">
                    <i class="fa-solid fa-fire nav-link-icon"></i><span>New & Popular</span></a>
                <a href="{{ route('watchlist') }}" class="nav-link {{ request()->routeIs('watchlist*') ? 'active' : '' }}">
                    <i class="fa-solid fa-heart nav-link-icon"></i><span>My List</span></a>
            </div>
        </div>
        <div class="nav-backdrop" id="navBackdrop"></div>
    </nav>

    <!-- PAGE -->
    <main class="page">@yield('content')</main>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-brand">KFLIX</div>
            <p class="footer-copy">&copy; 2026 KFLIX — CrochsDevs</p>
        </div>
    </footer>

    <!-- BOTTOM NAV (mobile) -->
    <nav class="bottom-nav">
        <div class="bottom-nav-items">
            <a href="{{ route('home') }}" class="bottom-nav-item {{ request()->routeIs('home*') || request()->routeIs('movies*') ? 'active' : '' }}">
                <i class="fa-solid fa-house"></i><span>Home</span></a>
            <a href="{{ route('tv') }}" class="bottom-nav-item {{ request()->routeIs('tv*') ? 'active' : '' }}">
                <i class="fa-solid fa-tv"></i><span>TV</span></a>
            <a href="{{ route('movies') }}" class="bottom-nav-item {{ request()->routeIs('movies') ? 'active' : '' }}">
                <i class="fa-solid fa-film"></i><span>Movies</span></a>
            <a href="{{ route('tv.newpopular') }}" class="bottom-nav-item {{ request()->routeIs('tv.newpopular*') ? 'active' : '' }}">
                <i class="fa-solid fa-fire"></i><span>Popular</span></a>
            <a href="{{ route('watchlist') }}" class="bottom-nav-item {{ request()->routeIs('watchlist*') ? 'active' : '' }}">
                <i class="fa-solid fa-heart"></i><span>My List</span></a>
        </div>
    </nav>

    <!-- MODAL OVERLAY -->
    <div class="modal-overlay" id="movieModal" role="dialog" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-loading" id="modalLoading"><div class="spinner"></div></div>
            <div class="modal-body" id="modalBody"></div>
            <div class="modal-actions" id="modalFooter">
                <button class="btn btn-play" id="modalPlayBtn"><i class="fa-solid fa-play"></i> Play</button>
                <button class="btn btn-outline" id="modalAddToList"><i class="fa-regular fa-heart"></i> Add to List</button>
            </div>
        </div>
    </div>

    <!-- TOAST -->
    <div class="toast-container" id="toastContainer"></div>

    <script src="{{ asset('js/script.js') }}"></script>
    @stack('scripts')
    <script>
    (function() {
        var t = document.getElementById('navToggle');
        var m = document.getElementById('navMenu');
        var b = document.getElementById('navBackdrop');
        function close() { m.classList.remove('open'); t.classList.remove('active'); b.classList.remove('show'); document.body.style.overflow = ''; }
        function open() { m.classList.add('open'); t.classList.add('active'); b.classList.add('show'); document.body.style.overflow = 'hidden'; }
        if (t) t.addEventListener('click', function(e) { e.stopPropagation(); m.classList.contains('open') ? close() : open(); });
        if (b) b.addEventListener('click', close);
        if (m) m.querySelectorAll('.nav-link').forEach(function(l) { l.addEventListener('click', close); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') close(); });
    })();
    </script>
</body>
</html>
