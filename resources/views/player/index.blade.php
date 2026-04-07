@extends('layouts.app')

@section('title', $title . ' - Watch Free')

@section('content')
<style>
    :root { --primary: #e50914; --primary-dark: #b2070f; --bg-dark: #0a0a0a; --card-bg: #141414; --card-hover: #1f1f1f; --text-main: #ffffff; --text-dim: #b3b3b3; --border-color: #333; --shadow: 0 8px 20px rgba(0,0,0,0.5); --success: #10b981; --header-height: 70px; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body { height: 100%; margin: 0; padding: 0; background-color: var(--bg-dark); color: var(--text-main); font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; }
    body { padding-top: var(--header-height); }
    .play-page-container { max-width: 1400px; margin: 0 auto; padding: 20px; min-height: calc(100vh - var(--header-height)); display: flex; flex-direction: column; width: 100%; }
    .search-section { margin-bottom: 25px; width: 100%; }
    .search-form { display: flex; align-items: center; background: #1a1a1a; border: 1px solid var(--border-color); border-radius: 40px; padding: 5px 5px 5px 20px; max-width: 600px; margin: 0 auto; transition: all 0.3s ease; }
    .search-form:hover, .search-form:focus-within { border-color: var(--primary); box-shadow: 0 0 0 2px rgba(229,9,20,0.2); }
    .search-form i { color: var(--text-dim); font-size: 1rem; }
    .search-input { flex: 1; background: transparent; border: none; padding: 12px 15px; color: var(--text-main); font-size: 1rem; outline: none; }
    .search-input::placeholder { color: var(--text-dim); font-style: italic; }
    .search-btn { background: var(--primary); border: none; color: white; padding: 10px 25px; border-radius: 40px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: all 0.2s ease; }
    .search-btn:hover { background: var(--primary-dark); transform: scale(1.02); }
    .search-btn i { color: white; font-size: 0.9rem; }
    .back-link { display: inline-flex; align-items: center; gap: 10px; color: var(--text-main); text-decoration: none; font-size: 1rem; margin-bottom: 25px; padding: 10px 20px; background: rgba(229,9,20,0.15); border: 1px solid var(--primary); border-radius: 30px; transition: all 0.3s ease; align-self: flex-start; font-weight: 500; letter-spacing: 0.3px; box-shadow: 0 2px 8px rgba(229,9,20,0.2); }
    .back-link i { font-size: 0.9rem; transition: transform 0.2s ease; color: var(--primary); }
    .back-link:hover { background: var(--primary); color: white; transform: translateX(-5px); box-shadow: 0 4px 12px rgba(229,9,20,0.4); }
    .back-link:hover i { transform: translateX(-3px); color: white; }
    .main-player-section { margin-bottom: 30px; border-radius: 12px; overflow: hidden; background: #000; box-shadow: var(--shadow); width: 100%; }
    .video-wrapper { position: relative; padding-top: 56.25%; background: #000; }
    #video-frame { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; }
    .server-controls { background: #1a1a1a; padding: 15px 20px; display: flex; align-items: center; flex-wrap: wrap; gap: 20px; border-bottom: 1px solid var(--border-color); }
    .server-label { display: flex; align-items: center; gap: 8px; color: var(--text-dim); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .server-label i { color: var(--primary); font-size: 1rem; }
    .server-btn { background: #2a2a2a; color: var(--text-main); border: 1px solid #444; padding: 8px 20px; border-radius: 25px; cursor: pointer; font-size: 0.9rem; transition: all 0.2s ease; }
    .server-btn:hover { border-color: var(--primary); background: #333; }
    .server-btn.active { background: var(--primary); border-color: var(--primary); }
    .recommendations-section { padding: 30px 0; }
    .recommendations-section h2 { font-size: 1.5rem; margin-bottom: 20px; }
    .rec-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 15px; }
    .rec-card { cursor: pointer; transition: transform 0.2s; }
    .rec-card:hover { transform: scale(1.05); }
    .rec-card img { width: 100%; border-radius: 8px; }
    .rec-card p { font-size: 0.85rem; margin-top: 5px; text-align: center; }

    @if($type == 'tv')
    .episode-selector { background: linear-gradient(145deg, #1a1a1a, #151515); padding: 20px 25px; display: block; border-top: 2px solid var(--primary); border-bottom: 1px solid var(--border-color); }
    @else
    .episode-selector { display: none; }
    @endif
    .episode-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; margin-bottom: 20px; }
    .episode-title { display: flex; align-items: center; gap: 15px; }
    .episode-icon { width: 45px; height: 45px; background: rgba(229,9,20,0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1px solid var(--primary); }
    .episode-icon i { color: var(--primary); font-size: 1.3rem; }
    .episode-text h4 { color: white; font-size: 1rem; margin: 0 0 5px 0; font-weight: 600; }
    .episode-text p { color: var(--text-dim); font-size: 0.85rem; margin: 0; }
    .season-selector { display: flex; align-items: center; gap: 15px; background: #252525; padding: 10px 20px; border-radius: 40px; border: 1px solid var(--border-color); }
    .season-selector label { color: var(--text-dim); font-size: 0.9rem; font-weight: 500; }
    .season-dropdown { background: #2a2a2a; border: 1px solid #444; color: var(--text-main); padding: 8px 20px; border-radius: 25px; cursor: pointer; font-size: 0.95rem; outline: none; min-width: 180px; }
    .episode-grid { display: flex; flex-wrap: wrap; gap: 10px; max-height: 120px; overflow-y: auto; padding: 5px; background: #1f1f1f; border-radius: 12px; border: 1px solid var(--border-color); }
    .episode-item { min-width: 60px; height: 60px; background: #2a2a2a; border: 1px solid #444; border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease; padding: 5px; }
    .episode-item:hover { border-color: var(--primary); transform: translateY(-2px); background: #333; }
    .episode-item.active { background: var(--primary); border-color: var(--primary); }
</style>

<div class="play-page-container">
    <div class="search-section">
        <form class="search-form" action="/" method="GET">
            <i class="fas fa-search"></i>
            <input type="text" name="search" class="search-input" placeholder="Search movies...">
            <button type="submit" class="search-btn"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>

    <a href="{{ $type == 'tv' ? '/tv' : '/' }}" class="back-link"><i class="fas fa-arrow-left"></i> Back</a>

    <div class="main-player-section">
        <div class="video-wrapper">
            <iframe id="video-frame" src="{{ $serverUrl }}" allowfullscreen></iframe>
        </div>

        @if($type == 'tv' && count($seasons) > 0)
        <div class="episode-selector">
            <div class="episode-header">
                <div class="episode-title">
                    <div class="episode-icon"><i class="fas fa-tv"></i></div>
                    <div class="episode-text"><h4>Episodes</h4><p>Select an episode to watch</p></div>
                </div>
                <div class="season-selector">
                    <label>Season:</label>
                    <select class="season-dropdown" id="seasonSelect">
                        @foreach($seasons as $s)
                            @if($s['season_number'] > 0)
                            <option value="{{ $s['season_number'] }}" {{ $season == $s['season_number'] ? 'selected' : '' }}>{{ $s['name'] }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="episode-list">
                <div class="episode-grid" id="episodeGrid">
                    @for($i = 1; $i <= 24; $i++)
                    <div class="episode-item {{ $episode == $i ? 'active' : '' }}" onclick="changeEpisode({{ $i }})"><span class="episode-number">{{ $i }}</span><span class="episode-label">EP</span></div>
                    @endfor
                </div>
            </div>
        </div>
        @endif

        <div class="server-controls">
            <div class="server-label"><i class="fas fa-server"></i> Servers</div>
            <div class="server-buttons">
                @foreach($servers as $i => $s)
                <a href="/watch?id={{ $content['id'] }}&type={{ $type }}&season={{ $season }}&episode={{ $episode }}&server={{ $i + 1 }}" class="server-btn {{ request('server', 1) == $i + 1 ? 'active' : '' }}">{{ $s['name'] }}</a>
                @endforeach
            </div>
        </div>
    </div>

    @if(!empty($recommendations))
    <div class="recommendations-section">
        <h2>You May Also Like</h2>
        <div class="rec-grid">
            @foreach($recommendations as $rec)
            <div class="rec-card" onclick="window.location.href='{{ $type == 'tv' ? '/tv/' : '/watch?id=' }}{{ $rec['id'] }}'">
                <img src="https://image.tmdb.org/t/p/w200{{ $rec['backdrop_path'] ?? $rec['poster_path'] }}" alt="{{ $rec['title'] ?? $rec['name'] }}" onerror="this.src='https://via.placeholder.com/200x120?text=No+Image'">
                <p>{{ $rec['title'] ?? $rec['name'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

@if($trailer)
<style>
.modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; justify-content: center; align-items: center; }
.modal.active { display: flex; }
.modal-content { position: relative; width: 90%; max-width: 900px; }
.modal-close { position: absolute; top: -40px; right: 0; color: white; font-size: 2rem; cursor: pointer; background: none; border: none; }
</style>
<div class="modal" id="trailerModal">
    <div class="modal-content">
        <button class="modal-close" onclick="document.getElementById('trailerModal').classList.remove('active')">&times;</button>
        <iframe id="trailer-frame" width="100%" height="500" src="https://www.youtube.com/embed/{{ $trailer['key'] }}?autoplay=1" frameborder="0" allowfullscreen></iframe>
    </div>
</div>
@endif

<script>
document.addEventListener('keydown', function(e) {
    var tag = e.target.tagName.toLowerCase();
    var isInput = tag === 'input' || tag === 'textarea';
    if (!isInput && (e.key === 'f' || e.key === 'F')) { var el = document.getElementById('video-frame'); if (el.requestFullscreen) el.requestFullscreen(); else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen(); }
});
document.getElementById('seasonSelect') && document.getElementById('seasonSelect').addEventListener('change', function() { location.search = '?id={{ $content['id'] }}&type={{ $type }}&season=' + this.value + '&episode=1'; });
function changeEpisode(ep) { location.search = '?id={{ $content['id'] }}&type={{ $type }}&season={{ $season }}&episode=' + ep; }
</script>
@endsection
