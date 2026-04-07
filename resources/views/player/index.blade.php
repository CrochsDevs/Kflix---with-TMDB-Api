@extends("layouts.app")

@section("title", ($title ?? "") . " - Watch Free")

@section("content")
<div class="player-page">
    <a href="{{ request("type") == "tv" ? route("tv") : route("home") }}" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back</a>

    @if(request("type") == "tv" && count($seasons ?? []) > 0)
    <div class="episode-selector">
        <div class="ep-selector-header">
            <h4><i class="fa-solid fa-tv"></i> Episodes — Season {{ $season ?? 1 }}</h4>
            <select class="season-select" onchange="location.search='?id={{ $content["id"] ?? 0 }}&type=tv&season='+this.value+'&episode=1'">
                @foreach($seasons ?? [] as $s)
                @if($s["season_number"] > 0)
                <option value="{{ $s["season_number"] }}" {{ ($season ?? 1) == $s["season_number"] ? "selected" : "" }}>{{ $s["name"] ?? "Season " . $s["season_number"] }}</option>
                @endif
                @endforeach
            </select>
        </div>
        <div class="episode-scroll">
            @for($i = 1; $i <= 24; $i++)
            <a href="?id={{ $content["id"] ?? 0 }}&type=tv&season={{ $season ?? 1 }}&episode={{ $i }}&server={{ request("server", 1) }}" class="ep-btn {{ ($episode ?? 1) == $i ? "active" : "" }}">{{ $i }}</a>
            @endfor
        </div>
    </div>
    @endif

    <div class="video-wrapper">
        <iframe id="video-frame" src="{{ $serverUrl ?? "" }}" allowfullscreen scrolling="no" allow="autoplay; encrypted-media; fullscreen"></iframe>
    </div>

    <div class="server-bar">
        <span style="color:var(--clr-text-muted);font-size:.8rem;font-weight:600;">SERVERS:</span>
        @foreach($servers ?? [] as $i => $s)
        <a href="?id={{ $content["id"] ?? 0 }}&type={{ request("type", "movie") }}&season={{ $season ?? 1 }}&episode={{ $episode ?? 1 }}&server={{ $i + 1 }}" class="server-btn {{ (request("server", 1) - 1) == $i ? "active" : "" }}">{{ $s["name"] ?? "Server " . ($i+1) }}</a>
        @endforeach
    </div>

    @if(!empty($recommendations ?? []))
    <div class="recommendations">
        <h2>You May Also Like</h2>
        <div class="rec-grid">
            @foreach($recommendations ?? [] as $rec)
            @php $isTv = request("type") == "tv"; @endphp
            <div class="rec-card" onclick="location.href='{{ $isTv ? "/tv/" : route("player")."?id=" }}{{ $rec["id"] }}{{ $isTv ? "" : "" }}'">
                <img src="https://image.tmdb.org/t/p/w300{{ $rec["backdrop_path"] ?? $rec["poster_path"] }}" alt="{{ $rec["title"] ?? $rec["name"] }}" onerror="this.src='https://via.placeholder.com/300x170?text=No+Image'">
                <h3>{{ $rec["title"] ?? $rec["name"] }}</h3>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

@if(isset($trailer) && $trailer)
<div class="modal-overlay" id="trailerModal">
    <div class="modal-content">
        <div class="modal-body" style="padding:0;">
            <iframe width="100%" height="500" src="https://www.youtube.com/embed/{{ $trailer["key"] }}" frameborder="0" allowfullscreen></iframe>
        </div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="document.getElementById('trailerModal').classList.remove('show')">Close</button>
        </div>
    </div>
</div>
@endif
@endsection
