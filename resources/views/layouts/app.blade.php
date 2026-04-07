/* =============================================
   KFLIX — Complete Responsive Design System
   ============================================= */

/* ——— VARIABLES ——— */
:root {
  --clr-primary: #e50914;
  --clr-primary-dark: #b8070f;
  --clr-primary-soft: rgba(229, 9, 20, .15);
  --clr-primary-glow: rgba(229, 9, 20, .35);
  --clr-bg: #0a0a0a;
  --clr-surface: #141414;
  --clr-surface-alt: #1c1c1c;
  --clr-border: #2a2a2a;
  --clr-text: #ffffff;
  --clr-text-muted: #9e9e9e;
  --clr-text-dim: #666;
  --r-sm: 6px;
  --r-md: 10px;
  --r-lg: 14px;
  --r-pill: 999px;
  --shadow-sm: 0 2px 10px rgba(0,0,0,.4);
  --shadow-md: 0 4px 20px rgba(0,0,0,.5);
  --shadow-lg: 0 8px 30px rgba(0,0,0,.6);
  --font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  --nav-h: 60px;
  --bnav-h: 62px;
  --content-max: 1440px;
  --ease: .25s cubic-bezier(.4,0,.2,1);
}

/* ——— RESET ——— */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; -webkit-tap-highlight-color: transparent; }
body {
  font-family: var(--font);
  background: var(--clr-bg);
  color: var(--clr-text);
  line-height: 1.6;
  min-height: 100vh;
  overflow-x: hidden;
}
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background: radial-gradient(ellipse at 50% 0%, rgba(229,9,20,.06) 0%, transparent 60%);
  pointer-events: none;
  z-index: 0;
}
a { color: inherit; text-decoration: none; }
button { cursor: pointer; border: none; background: none; font: inherit; color: inherit; }
img { display: block; max-width: 100%; }

/* ——— TOP NAV ——— */
.nav {
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 1000;
  height: var(--nav-h);
  background: rgba(10,10,10,.92);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-bottom: 1px solid rgba(255,255,255,.06);
}
.nav-inner {
  max-width: var(--content-max);
  margin: 0 auto;
  padding: 0 20px;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}
.nav-brand a {
  font-size: 1.5rem;
  font-weight: 800;
  letter-spacing: -1px;
  color: var(--clr-primary);
  text-shadow: 0 0 24px var(--clr-primary-glow);
  text-transform: uppercase;
}

/* Desktop links */
.nav-menu {
  display: flex;
  gap: 4px;
}
.nav-link {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  font-size: .9rem;
  font-weight: 500;
  color: var(--clr-text-muted);
  border-radius: var(--r-sm);
  transition: var(--ease);
  white-space: nowrap;
  position: relative;
}
.nav-link:hover { color: var(--clr-text); background: rgba(255,255,255,.06); }
.nav-link.active { color: #fff; }
.nav-link.active::after {
  content: '';
  position: absolute;
  bottom: 0; left: 50%; transform: translateX(-50%);
  width: 20px; height: 2px;
  background: var(--clr-primary);
  border-radius: 1px;
}
.nav-link-icon { font-size: .85rem; }

/* Hamburger */
.nav-hamburger {
  display: none;
  flex-direction: column;
  gap: 5px;
  padding: 6px;
  z-index: 1003;
}
.nav-hamburger span {
  display: block;
  width: 22px; height: 2px;
  background: var(--clr-text);
  border-radius: 1px;
  transition: var(--ease);
}
.nav-hamburger.active span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.nav-hamburger.active span:nth-child(2) { opacity: 0; }
.nav-hamburger.active span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

/* Mobile drawer backdrop */
.nav-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.65);
  z-index: 998;
  opacity: 0;
  pointer-events: none;
  transition: opacity .3s;
}
.nav-backdrop.show { opacity: 1; pointer-events: auto; }

/* ——— MOBILE BOTTOM NAV ——— */
.bottom-nav {
  display: none;
  position: fixed;
  bottom: 0; left: 0; right: 0;
  height: var(--bnav-h);
  background: rgba(12,12,12,.95);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-top: 1px solid rgba(255,255,255,.08);
  z-index: 1000;
  padding-bottom: env(safe-area-inset-bottom, 0);
}
.bottom-nav-items {
  display: flex;
  height: 100%;
}
.bottom-nav-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 3px;
  font-size: .7rem;
  color: var(--clr-text-muted);
  transition: var(--ease);
}
.bottom-nav-item i { font-size: 1.15rem; }
.bottom-nav-item:active { color: var(--clr-primary); }
.bottom-nav-item.active { color: var(--clr-primary); }

/* ——— PAGE ——— */
.page {
  position: relative;
  z-index: 1;
  min-height: calc(100vh - var(--nav-h) - 60px);
}

/* ——— MAIN LAYOUT ——— */
.main-layout {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 24px;
  max-width: var(--content-max);
  margin: 0 auto;
  padding: 20px;
}

/* ——— SIDEBAR ——— */
.sidebar {
  position: sticky;
  top: calc(var(--nav-h) + 20px);
  align-self: start;
  background: var(--clr-surface);
  border-radius: var(--r-md);
  border: 1px solid var(--clr-border);
  padding: 20px;
  max-height: calc(100vh - var(--nav-h) - 40px);
  overflow-y: auto;
}
.sidebar h3 {
  font-size: .95rem;
  font-weight: 600;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid var(--clr-border);
  display: flex;
  align-items: center;
  gap: 6px;
}
.sidebar h3 i { color: var(--clr-primary); font-size: .9rem; }

/* Search */
.search-wrapper {
  display: flex;
  align-items: center;
  background: var(--clr-surface-alt);
  border: 1px solid var(--clr-border);
  border-radius: var(--r-pill);
  padding: 0 12px;
  margin-bottom: 16px;
  transition: var(--ease);
}
.search-wrapper:focus-within {
  border-color: var(--clr-primary);
  box-shadow: 0 0 0 3px var(--clr-primary-soft);
}
.search-wrapper i { color: var(--clr-text-dim); font-size: .85rem; flex-shrink: 0; }
.search-input {
  flex: 1;
  background: none;
  border: none;
  padding: 10px 8px;
  color: var(--clr-text);
  font-size: .9rem;
  outline: none;
}
.clear-search {
  color: var(--clr-text-dim);
  padding: 8px;
  font-size: .8rem;
}
.clear-search:hover { color: var(--clr-primary); }

/* Filter sections */
.filter-section { margin-bottom: 20px; }
.filter-section h4 {
  font-size: .8rem;
  font-weight: 600;
  color: var(--clr-text-muted);
  text-transform: uppercase;
  letter-spacing: .5px;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.genres { display: flex; flex-wrap: wrap; gap: 6px; }
.genre-item {
  padding: 5px 12px;
  font-size: .78rem;
  background: var(--clr-surface-alt);
  border: 1px solid var(--clr-border);
  border-radius: var(--r-pill);
  color: var(--clr-text-muted);
  transition: var(--ease);
  white-space: nowrap;
}
.genre-item:hover { border-color: var(--clr-primary); color: #fff; }
.genre-item.active {
  background: var(--clr-primary);
  border-color: var(--clr-primary);
  color: #fff;
}
.time-filters { display: flex; gap: 6px; }
.time-btn {
  flex: 1;
  padding: 8px;
  font-size: .8rem;
  text-align: center;
  background: var(--clr-surface-alt);
  border: 1px solid var(--clr-border);
  border-radius: var(--r-sm);
  color: var(--clr-text-muted);
  transition: var(--ease);
}
.time-btn:hover { border-color: var(--clr-primary); }
.time-btn.active { background: var(--clr-primary); border-color: var(--clr-primary); color: #fff; }
.sort-select {
  width: 100%;
  padding: 8px 12px;
  background: var(--clr-surface-alt);
  border: 1px solid var(--clr-border);
  border-radius: var(--r-sm);
  color: var(--clr-text);
  font-size: .85rem;
  appearance: none;
  cursor: pointer;
  outline: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23666' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
}
.clear-filters-btn {
  display: block;
  text-align: center;
  padding: 10px;
  font-size: .8rem;
  color: var(--clr-text-dim);
  border: 1px dashed var(--clr-border);
  border-radius: var(--r-sm);
  margin-top: 12px;
  transition: var(--ease);
}
.clear-filters-btn:hover { color: var(--clr-primary); border-color: var(--clr-primary); }

/* ——— MAIN CONTENT AREA ——— */
.content-area { min-width: 0; }

/* ——— RESULTS HEADER ——— */
.results-header {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  margin-bottom: 16px;
  flex-wrap: wrap;
  gap: 8px;
}
.section-title {
  font-size: 1.35rem;
  font-weight: 700;
  line-height: 1.3;
}
.results-info { font-size: .85rem; color: var(--clr-text-muted); }

/* ——— HERO ——— */
.hero-section {
  background-size: cover;
  background-position: center top;
  padding: 48px 24px;
  border-radius: var(--r-md);
  margin-bottom: 24px;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  min-height: 280px;
  position: relative;
  overflow: hidden;
}
.hero-section::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, var(--clr-bg) 0%, rgba(10,10,10,.55) 55%, rgba(0,0,0,.15) 100%);
}
.hero-content { position: relative; z-index: 1; max-width: 580px; }
.hero-title { font-size: 1.85rem; font-weight: 800; margin-bottom: 8px; line-height: 1.15; }
.hero-description { color: var(--clr-text-muted); margin-bottom: 16px; font-size: .95rem; line-height: 1.6; }
.hero-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
.btn-play {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 28px;
  background: var(--clr-primary);
  color: #fff;
  border-radius: var(--r-sm);
  font-weight: 600;
  font-size: .95rem;
  transition: var(--ease);
  box-shadow: 0 0 18px var(--clr-primary-glow);
}
.btn-play:hover { background: var(--clr-primary-dark); transform: translateY(-2px); box-shadow: 0 4px 24px var(--clr-primary-glow); }

/* ——— MOVIE GRID ——— */
.movie-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
  gap: 18px;
}
.movie-card {
  position: relative;
  background: var(--clr-surface);
  border-radius: var(--r-md);
  overflow: hidden;
  border: 1px solid transparent;
  transition: var(--ease);
  cursor: pointer;
}
.movie-card:hover { transform: translateY(-4px); border-color: var(--clr-border); box-shadow: var(--shadow-md); }
.card-poster { position: relative; aspect-ratio: 2/3; overflow: hidden; background: var(--clr-surface-alt); }
.card-poster img { width: 100%; height: 100%; object-fit: cover; transition: transform .35s var(--ease); }
.movie-card:hover .card-poster img { transform: scale(1.06); }
.card-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(180deg, transparent 30%, rgba(0,0,0,.88) 100%);
  display: flex;
  align-items: flex-end;
  justify-content: center;
  padding: 16px 10px 14px;
  gap: 8px;
  opacity: 0;
  transition: opacity .3s var(--ease);
}
.movie-card:hover .card-overlay { opacity: 1; }
.overlay-btn {
  width: 36px; height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-size: .82rem;
  transition: var(--ease);
  border: 1px solid rgba(255,255,255,.15);
}
.overlay-btn.play {
  width: 42px; height: 42px;
  background: var(--clr-primary);
  border-color: var(--clr-primary);
  color: #fff;
}
.overlay-btn.play:hover { background: #fff; color: var(--clr-primary); }
.overlay-btn.heart, .overlay-btn.info {
  background: rgba(255,255,255,.12);
  backdrop-filter: blur(4px);
}
.overlay-btn.heart:hover { background: var(--clr-primary); border-color: var(--clr-primary); }
.overlay-btn.info:hover { background: rgba(255,255,255,.25); }
.card-footer { padding: 10px 10px 12px; }
.movie-title {
  font-size: .84rem;
  font-weight: 500;
  margin-bottom: 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.movie-meta { display: flex; align-items: center; justify-content: space-between; gap: 4px; }
.rating-badge { display: flex; align-items: center; gap: 3px; font-size: .75rem; color: #f5c518; font-weight: 600; }
.release-year { font-size: .73rem; color: var(--clr-text-dim); }

/* ——— PAGINATION ——— */
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 28px 0;
}
.page-link, .page-num {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 38px; height: 38px;
  padding: 0 10px;
  background: var(--clr-surface);
  border: 1px solid var(--clr-border);
  border-radius: var(--r-sm);
  font-size: .85rem;
  font-weight: 500;
  color: var(--clr-text-muted);
  transition: var(--ease);
}
.page-link:hover, .page-num:hover { border-color: var(--clr-primary); color: #fff; }
.page-num.active { background: var(--clr-primary); border-color: var(--clr-primary); color: #fff; }
.page-dots { display: flex; align-items: center; color: var(--clr-text-dim); padding: 0 6px; }

/* ——— PLAYER PAGE ——— */
.player-page {
  max-width: var(--content-max);
  margin: 0 auto;
  padding: 20px;
}
.player-page .search-section {
  max-width: 560px;
  margin: 0 auto 16px;
}
.back-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 16px;
  padding: 8px 16px;
  background: var(--clr-surface);
  border: 1px solid var(--clr-border);
  border-radius: var(--r-pill);
  font-size: .85rem;
  font-weight: 500;
  color: var(--clr-text-muted);
  transition: var(--ease);
}
.back-link:hover { color: #fff; border-color: var(--clr-primary); transform: translateX(-4px); }
.video-wrapper {
  position: relative;
  border-radius: var(--r-md);
  overflow: hidden;
  background: #000;
  box-shadow: var(--shadow-lg);
  aspect-ratio: 16/9;
  margin-bottom: 12px;
}
.video-wrapper iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: none; }
.server-bar {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  padding: 14px;
  background: var(--clr-surface);
  border-radius: var(--r-md);
  border: 1px solid var(--clr-border);
  margin-bottom: 24px;
}
.server-btn {
  padding: 8px 20px;
  background: var(--clr-surface-alt);
  border: 1px solid var(--clr-border);
  border-radius: var(--r-sm);
  font-size: .85rem;
  font-weight: 500;
  color: var(--clr-text-muted);
  transition: var(--ease);
}
.server-btn:hover { border-color: var(--clr-primary); color: #fff; }
.server-btn.active { background: var(--clr-primary); border-color: var(--clr-primary); color: #fff; }
.recommendations { margin-top: 28px; }
.recommendations h2 { font-size: 1.15rem; font-weight: 600; margin-bottom: 14px; }
.rec-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 14px; }
.rec-card {
  cursor: pointer;
  border-radius: var(--r-md);
  overflow: hidden;
  background: var(--clr-surface);
  border: 1px solid transparent;
  transition: var(--ease);
}
.rec-card:hover { transform: translateY(-3px); border-color: var(--clr-border); }
.rec-card img { width: 100%; aspect-ratio: 16/9; object-fit: cover; }
.rec-card h3 { padding: 8px 10px; font-size: .84rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ——— EPISODE SELECTOR ——— */
.episode-selector {
  background: var(--clr-surface);
  border: 1px solid var(--clr-border);
  border-radius: var(--r-md);
  padding: 14px;
  margin-bottom: 12px;
}
.ep-selector-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
  flex-wrap: wrap;
  gap: 12px;
}
.ep-selector-header h4 { font-size: .95rem; font-weight: 600; display: flex; align-items: center; gap: 6px; }
.ep-selector-header h4 i { color: var(--clr-primary); }
.season-select {
  padding: 6px 12px;
  background: var(--clr-surface-alt);
  border: 1px solid var(--clr-border);
  border-radius: var(--r-sm);
  color: var(--clr-text);
  font-size: .85rem;
  outline: none;
}
.episode-scroll {
  display: flex;
  gap: 6px;
  overflow-x: auto;
  padding-bottom: 6px;
  scrollbar-width: thin;
}
.ep-btn {
  flex-shrink: 0;
  min-width: 46px; height: 46px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--clr-surface-alt);
  border: 1px solid var(--clr-border);
  border-radius: var(--r-sm);
  font-size: .84rem;
  font-weight: 600;
  transition: var(--ease);
}
.ep-btn:hover { border-color: var(--clr-primary); }
.ep-btn.active { background: var(--clr-primary); border-color: var(--clr-primary); color: #fff; }

/* ——— MOVIE DETAIL ——— */
.detail-page { max-width: var(--content-max); margin: 0 auto; padding: 20px; }
.detail-hero {
  position: relative;
  background-size: cover;
  background-position: center;
  border-radius: var(--r-md);
  overflow: hidden;
  margin-bottom: 28px;
}
.detail-hero::before {
  content: '';
  position: absolute; inset: 0;
  background: linear-gradient(90deg, var(--clr-bg) 0%, var(--clr-bg) 45%, transparent 100%);
}
.detail-hero-inner {
  position: relative;
  z-index: 1;
  display: flex;
  gap: 24px;
  padding: 24px;
  align-items: flex-end;
}
.detail-poster {
  flex-shrink: 0;
  width: 220px;
  border-radius: var(--r-md);
  overflow: hidden;
  box-shadow: var(--shadow-lg);
}
.detail-poster img { width: 100%; display: block; }
.detail-info { flex: 1; min-width: 0; }
.detail-info h1 { font-size: 1.7rem; font-weight: 800; margin-bottom: 8px; line-height: 1.2; }
.detail-badges { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
.badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  background: var(--clr-surface-alt);
  border: 1px solid var(--clr-border);
  border-radius: var(--r-pill);
  font-size: .78rem;
  color: var(--clr-text-muted);
}
.badge.r { color: #f5c518; border-color: rgba(245,197,24,.3); }
.detail-overview { line-height: 1.7; color: var(--clr-text-muted); margin-bottom: 18px; max-width: 600px; }
.detail-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 26px;
  border-radius: var(--r-sm);
  font-weight: 600;
  font-size: .9rem;
  transition: var(--ease);
}
.btn-outline {
  border: 2px solid var(--clr-text-muted);
  color: var(--clr-text);
}
.btn-outline:hover { border-color: #fff; background: rgba(255,255,255,.06); }
.detail-cast { margin: 28px 0; }
.detail-cast h2 { font-size: 1.1rem; font-weight: 600; margin-bottom: 14px; }
.cast-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(105px, 1fr)); gap: 14px; }
.cast-card { text-align: center; }
.cast-card img { width: 100%; aspect-ratio: 2/3; object-fit: cover; border-radius: var(--r-sm); margin-bottom: 6px; }
.cast-card p { font-size: .78rem; color: var(--clr-text-muted); }

/* ——— WATCHLIST PAGE ——— */
.watchlist-page { max-width: var(--content-max); margin: 0 auto; padding: 20px; }
.watchlist-page h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
.watchlist-page h1 i { color: var(--clr-primary); }

/* ——— NO RESULTS ——— */
.no-results {
  text-align: center;
  padding: 48px 16px;
}
.no-results i { font-size: 2.8rem; color: var(--clr-border); margin-bottom: 14px; }
.no-results h3 { font-size: 1.1rem; margin-bottom: 6px; }
.no-results p { color: var(--clr-text-dim); }

/* ——— MODAL ——— */
.modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  z-index: 2000;
  background: rgba(0,0,0,.78);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.modal-overlay.show { display: flex; }
.modal-content {
  background: var(--clr-surface);
  border-radius: var(--r-md);
  border: 1px solid var(--clr-border);
  max-width: 760px;
  width: 100%;
  max-height: 85vh;
  overflow-y: auto;
  box-shadow: var(--shadow-lg);
}
.modal-body { padding: 20px; }
.modal-loading { display: none; align-items: center; justify-content: center; padding: 80px 0; }
.modal-actions {
  display: flex;
  gap: 10px;
  padding: 14px 24px;
  background: var(--clr-surface-alt);
  border-top: 1px solid var(--clr-border);
}

/* ——— TOAST ——— */
.toast-container { position: fixed; bottom: 80px; right: 16px; z-index: 3000; display: flex; flex-direction: column; gap: 8px; pointer-events: none; }
.toast {
  padding: 10px 18px;
  background: var(--clr-surface);
  border: 1px solid var(--clr-border);
  border-radius: var(--r-sm);
  font-size: .88rem;
  box-shadow: var(--shadow-md);
  animation: toastIn .3s var(--ease) forwards;
  pointer-events: auto;
}
.toast.out { animation: toastOut .3s var(--ease) forwards; }
@keyframes toastIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
@keyframes toastOut { to { transform: translateX(100%); opacity: 0; } }
.spinner { width: 36px; height: 36px; border: 3px solid var(--clr-border); border-top-color: var(--clr-primary); border-radius: 50%; animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ——— FOOTER ——— */
.footer {
  background: var(--clr-surface);
  border-top: 1px solid rgba(255,255,255,.06);
  padding: 28px 20px;
  text-align: center;
  position: relative;
  z-index: 1;
  margin-bottom: 40px;
}
.footer-brand { font-size: 1.3rem; font-weight: 800; color: var(--clr-primary); margin-bottom: 6px; }
.footer-copy { font-size: .8rem; color: var(--clr-text-dim); }

/* ==============================================
   MOBILE — phones
   ============================================== */
@media (max-width: 768px) {
  .nav-menu {
    display: none;
    position: fixed;
    top: var(--nav-h);
    left: 0; right: 0;
    bottom: 0;
    flex-direction: column;
    gap: 4px;
    padding: 12px 16px;
    background: rgba(14,14,14,.98);
    z-index: 1002;
    overflow-y: auto;
  }
  .nav-menu.open { display: flex; }
  .nav-link { font-size: 1rem; padding: 14px 16px; border-radius: var(--r-sm); }
  .nav-link.active::after { display: none; }
  .nav-link.active { background: var(--clr-primary-soft); color: var(--clr-primary); }
  .nav-link:hover { background: rgba(255,255,255,.04); }
  .nav-hamburger { display: flex; }

  /* Show bottom nav on mobile */
  .bottom-nav { display: block; }
  .bottom-nav-items { display: flex; }
  .footer { margin-bottom: 62px; }

  /* Layout single column */
  .main-layout {
    grid-template-columns: 1fr;
    gap: 16px;
    padding: 12px;
  }
  .sidebar {
    position: relative;
    top: auto;
    max-height: none;
    order: 2;
    border: none;
    background: transparent;
    padding: 0;
  }
  /* sidebar on mobile — collapsible card */
  .sidebar > *:not(h3) { display: none; }
  .sidebar.collapsed > *:not(h3) { display: none; }
  .sidebar.expanded > *:not(h3) { display: block; }
  .content-area { order: 1; }

  /* Grid 3-cols on mobile */
  .movie-grid { grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px; }

  /* Hero */
  .hero-section { padding: 32px 16px 24px; min-height: 220px; }
  .hero-title { font-size: 1.35rem; }
  .hero-description { font-size: .88rem; }

  /* Detail */
  .detail-hero::before { background: linear-gradient(to top, var(--clr-bg) 0%, rgba(10,10,10,.65) 50%, transparent); }
  .detail-hero-inner { flex-direction: column; align-items: flex-start; gap: 16px; }
  .detail-poster { width: 140px; }
  .detail-info h1 { font-size: 1.35rem; }
  .rec-grid { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); }
  .cast-grid { grid-template-columns: repeat(auto-fill, minmax(85px, 1fr)); }
  .pagination { flex-wrap: wrap; gap: 4px; }
  .section-title { font-size: 1.1rem; }
  .toast-container { bottom: 78px; }

  /* Player */
  .player-page { padding: 12px; }
  .server-bar { flex-direction: column; }

  /* Episode selector */
  .ep-btn { min-width: 42px; height: 42px; }
}

/* Tablets */
@media (min-width: 769px) and (max-width: 1024px) {
  .main-layout { grid-template-columns: 1fr; gap: 24px; padding: 16px; }
  .sidebar { position: relative; top: auto; max-height: none; margin-bottom: 20px; }
  .content-area { order: -1; }
  .movie-grid { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); }
  .nav-hamburger { display: none; }
}

/* Landscape phones with small height */
@media (max-height: 600px) {
  .hero-section { min-height: 160px; padding: 20px 16px; }
  .nav-menu { max-height: calc(100vh - var(--nav-h)); }
}
