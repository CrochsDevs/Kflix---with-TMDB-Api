<?php
    // play.php - NOW SUPPORTS BOTH MOVIES AND TV SHOWS
    require_once 'vendor/autoload.php';
    use GuzzleHttp\Client;

    require_once 'config/db.php';
    require_once 'models/Watchlist.php';

    // Get parameters
    $movie_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $type = isset($_GET['type']) ? $_GET['type'] : 'movie'; // 'movie' or 'tv'
    $season = isset($_GET['season']) ? (int) $_GET['season'] : 1;
    $episode = isset($_GET['episode']) ? (int) $_GET['episode'] : 1;
    $server = isset($_GET['server']) ? (int) $_GET['server'] : 1;

    if (! $movie_id) {
        header('Location: index.php');
        exit;
    }

    $client = new Client();
    $content = null; // Will hold movie or TV show data
    $recommendations = [];
    $videos = [];
    $seasons = []; // For TV shows only
    $api_key = '6b41a1cc64742876ef62e17108c18cc3';

    try {
        // Get content details based on type
        if ($type === 'tv') {
            // TV Show details with videos, credits, recommendations
            $response = $client->request('GET', "https://api.themoviedb.org/3/tv/{$movie_id}?api_key={$api_key}&language=en-US&append_to_response=videos,credits,recommendations,similar", [
                'timeout' => 10,
            ]);
            $content = json_decode($response->getBody(), true);
            $seasons = $content['seasons'] ?? [];
        } else {
            // Movie details with videos, credits, recommendations
            $response = $client->request('GET', "https://api.themoviedb.org/3/movie/{$movie_id}?api_key={$api_key}&language=en-US&append_to_response=videos,credits,recommendations,similar", [
                'timeout' => 10,
            ]);
            $content = json_decode($response->getBody(), true);
        }

        // Get recommendations
        if (isset($content['recommendations']['results']) && ! empty($content['recommendations']['results'])) {
            $recommendations = array_slice($content['recommendations']['results'], 0, 12);
        } elseif (isset($content['similar']['results']) && ! empty($content['similar']['results'])) {
            $recommendations = array_slice($content['similar']['results'], 0, 12);
        }

        // Get videos (trailers, teasers, etc.)
        if (isset($content['videos']['results'])) {
            $videos = $content['videos']['results'];
        }

    } catch (\Exception $e) {
        error_log('TMDB API Error: ' . $e->getMessage());
    }

    if (! $content) {
        // Redirect based on type
        if ($type === 'tv') {
            header('Location: tvshow.php');
        } else {
            header('Location: index.php');
        }
        exit;
    }

    $watchlistModel = new Watchlist();
    $inWatchlist = $watchlistModel->isInWatchlist($movie_id);

    // Find trailer
    $trailer = null;
    foreach ($videos as $video) {
        if ($video['type'] === 'Trailer' && $video['site'] === 'YouTube') {
            $trailer = $video;
            break;
        }
    }

    // If no trailer found, get any video
    if (!$trailer && !empty($videos)) {
        $trailer = $videos[0];
    }

    // WORKING SERVERS - Support both movies and TV shows
    if ($type === 'tv') {
        // TV Show servers with season & episode
        $servers = [
            [
                'name' => 'Server 1',
                'url' => 'https://moviesapi.club/tv/' . $movie_id . '-' . $season . '-' . $episode,
                'status' => 'active'
            ],
            [
                'name' => 'Server 2',
                'url' => 'https://www.2embed.cc/embedtv/' . $movie_id . '&s=' . $season . '&e=' . $episode,
                'status' => 'active'
            ]
        ];
    } else {
        // Movie servers
        $servers = [
            [
                'name' => 'Server 1',
                'url' => 'https://moviesapi.club/movie/' . $movie_id,
                'status' => 'active'
            ],
            [
                'name' => 'Server 2',
                'url' => 'https://www.2embed.cc/embed/' . $movie_id,
                'status' => 'active'
            ]
        ];
    }

    // Set page title
    $title = $type === 'tv' ? $content['name'] : $content['title'];
    $page_title = htmlspecialchars($title) . ' - Watch Free';
    include 'layout/header.php';
?>

<style>
    :root {
        --primary: #e50914;
        --primary-dark: #b2070f;
        --bg-dark: #0a0a0a;
        --card-bg: #141414;
        --card-hover: #1f1f1f;
        --text-main: #ffffff;
        --text-dim: #b3b3b3;
        --border-color: #333;
        --shadow: 0 8px 20px rgba(0,0,0,0.5);
        --success: #10b981;
        --header-height: 70px;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        background-color: var(--bg-dark);
        color: var(--text-main);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        line-height: 1.6;
    }

    body {
        padding-top: var(--header-height);
    }

    .play-page-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
        min-height: calc(100vh - var(--header-height));
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    /* Search Bar */
    .search-section {
        margin-bottom: 25px;
        width: 100%;
    }

    .search-form {
        display: flex;
        align-items: center;
        background: #1a1a1a;
        border: 1px solid var(--border-color);
        border-radius: 40px;
        padding: 5px 5px 5px 20px;
        max-width: 600px;
        margin: 0 auto;
        transition: all 0.3s ease;
    }

    .search-form:hover,
    .search-form:focus-within {
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(229, 9, 20, 0.2);
    }

    .search-form i {
        color: var(--text-dim);
        font-size: 1rem;
    }

    .search-input {
        flex: 1;
        background: transparent;
        border: none;
        padding: 12px 15px;
        color: var(--text-main);
        font-size: 1rem;
        outline: none;
    }

    .search-input::placeholder {
        color: var(--text-dim);
        font-style: italic;
    }

    .search-btn {
        background: var(--primary);
        border: none;
        color: white;
        padding: 10px 25px;
        border-radius: 40px;
        cursor: pointer;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .search-btn:hover {
        background: var(--primary-dark);
        transform: scale(1.02);
    }

    .search-btn i {
        color: white;
        font-size: 0.9rem;
    }

    /* Back Button */
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: var(--text-main);
        text-decoration: none;
        font-size: 1rem;
        margin-bottom: 25px;
        padding: 10px 20px;
        background: rgba(229, 9, 20, 0.15);
        border: 1px solid var(--primary);
        border-radius: 30px;
        transition: all 0.3s ease;
        align-self: flex-start;
        font-weight: 500;
        letter-spacing: 0.3px;
        box-shadow: 0 2px 8px rgba(229, 9, 20, 0.2);
    }

    .back-link i {
        font-size: 0.9rem;
        transition: transform 0.2s ease;
        color: var(--primary);
    }

    .back-link:hover {
        background: var(--primary);
        color: white;
        transform: translateX(-5px);
        box-shadow: 0 4px 12px rgba(229, 9, 20, 0.4);
    }

    .back-link:hover i {
        transform: translateX(-3px);
        color: white;
    }

    /* Player Section */
    .main-player-section {
        margin-bottom: 30px;
        border-radius: 12px;
        overflow: hidden;
        background: #000;
        box-shadow: var(--shadow);
        width: 100%;
    }

    .video-wrapper {
        position: relative;
        padding-top: 56.25%;
        background: #000;
    }

    #video-frame {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
    }

    /* Episode Selector - Only visible for TV shows */
    .episode-selector {
        background: #1a1a1a;
        padding: 15px 20px;
        display: <?php echo $type === 'tv' ? 'flex' : 'none'; ?>;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        border-top: 1px solid var(--border-color);
        border-bottom: 1px solid var(--border-color);
    }

    .episode-label {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-dim);
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .episode-label i {
        color: var(--primary);
        font-size: 1rem;
    }

    .episode-controls {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .episode-select {
        background: #2a2a2a;
        border: 1px solid #444;
        color: var(--text-main);
        padding: 8px 15px;
        border-radius: 25px;
        cursor: pointer;
        font-size: 0.9rem;
        outline: none;
        min-width: 150px;
    }

    .episode-select:hover {
        border-color: var(--primary);
    }

    .episode-btn {
        background: var(--primary);
        border: none;
        color: white;
        padding: 8px 20px;
        border-radius: 25px;
        cursor: pointer;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .episode-btn:hover {
        background: var(--primary-dark);
        transform: scale(1.02);
    }

    .episode-btn i {
        font-size: 0.85rem;
    }

    /* Server Controls */
    .server-controls {
        background: #1a1a1a;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        border-top: 1px solid var(--border-color);
        border-bottom: 1px solid var(--border-color);
    }

    .server-label {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-dim);
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .server-label i {
        color: var(--primary);
        font-size: 1rem;
    }

    .server-buttons {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .server-btn {
        background: #2a2a2a;
        border: 1px solid #444;
        color: var(--text-dim);
        padding: 8px 18px;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.9rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .server-btn i {
        font-size: 0.85rem;
        color: var(--primary);
    }

    .server-btn.active {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .server-btn.active i {
        color: white;
    }

    .server-btn:hover:not(.active) {
        background: #333;
        border-color: var(--primary);
        color: white;
    }

    .server-status {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--success);
        margin-left: 5px;
        box-shadow: 0 0 8px var(--success);
    }

    /* Player Controls */
    .player-controls {
        background: #1a1a1a;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }

    .quality-badge {
        background: rgba(255,255,255,0.1);
        padding: 6px 14px;
        border-radius: 25px;
        font-size: 0.85rem;
        color: var(--text-dim);
        border: 1px solid var(--border-color);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .quality-badge i {
        color: var(--primary);
    }

    .control-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .fullscreen-btn {
        background: none;
        border: 1px solid var(--border-color);
        color: var(--text-dim);
        padding: 6px 16px;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .fullscreen-btn:hover {
        background: rgba(255,255,255,0.1);
        color: white;
        border-color: var(--primary);
    }

    /* Trailer Modal */
    .trailer-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.95);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .trailer-modal.active {
        display: flex;
    }

    .modal-content {
        position: relative;
        width: 90%;
        max-width: 1000px;
        background: #000;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0,0,0,0.5);
    }

    .modal-header {
        background: #1a1a1a;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border-color);
    }

    .modal-header h3 {
        color: white;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modal-header h3 i {
        color: var(--primary);
        font-size: 1.2rem;
    }

    .close-modal {
        background: none;
        border: none;
        color: var(--text-dim);
        font-size: 1.3rem;
        cursor: pointer;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .close-modal:hover {
        background: rgba(255,255,255,0.1);
        color: white;
        transform: rotate(90deg);
    }

    .modal-body {
        position: relative;
        padding-top: 56.25%;
    }

    #trailer-frame {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
    }

    /* Content Info */
    .content-info {
        display: flex;
        gap: 30px;
        margin: 30px 0 40px;
        background: linear-gradient(to bottom, rgba(20,20,20,0.95), rgba(10,10,10,0.98));
        border-radius: 16px;
        padding: 30px;
        border: 1px solid var(--border-color);
        flex: 1 0 auto;
        width: 100%;
        box-shadow: var(--shadow);
    }

    .info-poster {
        flex: 0 0 220px;
    }

    .info-poster img {
        width: 100%;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.6);
        transition: transform 0.3s ease;
    }

    .info-poster img:hover {
        transform: scale(1.02);
    }

    .info-details {
        flex: 1;
    }

    .movie-title-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .movie-title-row h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
        background: linear-gradient(to right, #fff, #e0e0e0);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        line-height: 1.2;
    }

    .watchlist-btn {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        color: white;
        padding: 10px 22px;
        border-radius: 30px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 1rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .watchlist-btn:hover {
        background: rgba(255,255,255,0.15);
        border-color: rgba(255,255,255,0.2);
        transform: translateY(-2px);
    }

    .watchlist-btn.in-list {
        background: rgba(229, 9, 20, 0.15);
        border-color: var(--primary);
    }

    .watchlist-btn.in-list i {
        color: var(--primary);
    }

    .meta-data {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        color: var(--text-dim);
        font-size: 0.95rem;
        padding: 15px 0;
        border-top: 1px solid var(--border-color);
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 20px;
    }

    .rating-badge {
        color: #ffad1f;
        display: flex;
        align-items: center;
        gap: 5px;
        background: rgba(255, 173, 31, 0.1);
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 600;
    }

    .genre-pill {
        background: #2a2a2a;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        color: var(--text-dim);
        border: 1px solid #444;
        transition: all 0.2s ease;
    }

    .genre-pill:hover {
        background: #333;
        color: white;
        border-color: var(--primary);
    }

    .overview-text {
        line-height: 1.7;
        font-size: 1rem;
        color: #e0e0e0;
        margin-bottom: 25px;
    }

    .cast-info {
        color: var(--text-dim);
        font-size: 0.95rem;
        padding: 15px 0;
        border-top: 1px solid var(--border-color);
    }

    .cast-info strong {
        color: white;
        margin-right: 10px;
        font-size: 1rem;
    }

    .cast-names span {
        display: inline-block;
        margin-right: 5px;
    }

    .cast-names span:not(:last-child):after {
        content: "•";
        margin: 0 8px;
        color: var(--primary);
    }

    /* Recommendations Section */
    .recommendations-header {
        margin: 40px 0 25px;
        width: 100%;
    }

    .recommendations-header h2 {
        font-size: 1.8rem;
        font-weight: 600;
        border-left: 4px solid var(--primary);
        padding-left: 20px;
        background: linear-gradient(to right, #fff, #ccc);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .rec-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 20px;
        margin-bottom: 60px;
        width: 100%;
    }

    .rec-card {
        background: var(--card-bg);
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        border: 1px solid transparent;
        width: 100%;
        aspect-ratio: 2/3;
        display: flex;
        flex-direction: column;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }

    .rec-card:hover {
        transform: translateY(-10px);
        border-color: var(--primary);
        box-shadow: 0 20px 30px rgba(229, 9, 20, 0.3);
        z-index: 10;
    }

    .rec-poster {
        position: relative;
        width: 100%;
        height: 0;
        padding-bottom: 150%;
        overflow: hidden;
        background: #1a1a1a;
    }

    .rec-poster img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .rec-card:hover .rec-poster img {
        transform: scale(1.1);
    }

    .play-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 2;
    }

    .rec-card:hover .play-overlay {
        opacity: 1;
    }

    .play-overlay i {
        font-size: 3.5rem;
        color: white;
        filter: drop-shadow(0 4px 8px rgba(229, 9, 20, 0.6));
        transform: scale(0.9);
        transition: transform 0.2s ease;
    }

    .rec-card:hover .play-overlay i {
        transform: scale(1.1);
    }

    .rec-info {
        padding: 15px;
        background: linear-gradient(to top, var(--card-bg), #1a1a1a);
        position: relative;
        z-index: 1;
        margin-top: auto;
    }

    .rec-title {
        font-size: 1rem;
        font-weight: 600;
        margin: 0 0 8px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: white;
        letter-spacing: 0.3px;
    }

    .rec-rating {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: #ffad1f;
        font-size: 0.9rem;
        background: rgba(0, 0, 0, 0.5);
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 600;
    }

    .rec-rating i {
        color: #ffad1f;
        font-size: 0.85rem;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .rec-grid {
            grid-template-columns: repeat(5, 1fr);
        }
    }

    @media (max-width: 992px) {
        .rec-grid {
            grid-template-columns: repeat(4, 1fr);
        }
        
        .content-info {
            flex-direction: column;
        }
        
        .info-poster {
            max-width: 250px;
            margin: 0 auto;
        }
        
        .movie-title-row h1 {
            font-size: 2rem;
        }
    }

    @media (max-width: 768px) {
        .rec-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .movie-title-row {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .movie-title-row h1 {
            font-size: 1.8rem;
        }
        
        .watchlist-btn {
            width: 100%;
            justify-content: center;
        }
        
        .server-controls {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .server-buttons {
            width: 100%;
        }
        
        .server-btn {
            flex: 1;
            justify-content: center;
        }
        
        .recommendations-header h2 {
            font-size: 1.5rem;
        }
        
        .episode-controls {
            width: 100%;
        }
        
        .episode-select {
            flex: 1;
        }
    }

    @media (max-width: 576px) {
        body {
            padding-top: 60px;
        }
        
        .play-page-container {
            padding: 15px;
        }
        
        .rec-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 40px;
        }
        
        .movie-title-row h1 {
            font-size: 1.5rem;
        }
        
        .meta-data {
            gap: 10px;
            font-size: 0.85rem;
        }
        
        .rec-info {
            padding: 10px;
        }
        
        .rec-title {
            font-size: 0.9rem;
        }
        
        .rec-rating {
            font-size: 0.8rem;
            padding: 3px 10px;
        }
        
        .play-overlay i {
            font-size: 2.5rem;
        }
        
        .back-link {
            padding: 8px 16px;
            font-size: 0.9rem;
        }
        
        .search-btn span {
            display: none;
        }
        
        .search-btn {
            padding: 10px 15px;
        }
        
        .episode-selector {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .episode-controls {
            flex-direction: column;
        }
        
        .episode-select {
            width: 100%;
        }
        
        .episode-btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 375px) {
        .rec-grid {
            gap: 8px;
        }
        
        .rec-title {
            font-size: 0.8rem;
        }
        
        .rec-rating {
            font-size: 0.7rem;
            padding: 2px 8px;
        }
        
        .play-overlay i {
            font-size: 2rem;
        }
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .loading-pulse {
        animation: pulse 1.5s ease-in-out infinite;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    footer, .footer {
        margin-top: auto;
        padding: 30px 0 20px;
        background: var(--bg-dark);
        border-top: 1px solid var(--border-color);
        width: 100%;
        clear: both;
    }
</style>

<!-- Trailer Modal -->
<div class="trailer-modal" id="trailerModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fab fa-youtube"></i>
                <?php echo $trailer ? $trailer['type'] : 'Trailer'; ?> - <?php echo htmlspecialchars($title); ?>
            </h3>
            <button class="close-modal" onclick="closeTrailer()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <iframe id="trailer-frame" 
                    src="" 
                    allowfullscreen 
                    allow="autoplay; encrypted-media; picture-in-picture"
                    loading="lazy"></iframe>
        </div>
    </div>
</div>

<div class="play-page-container">
    <!-- Search Bar - FIXED: Now redirects to the correct page -->
    <div class="search-section">
        <form action="<?php echo $type === 'tv' ? 'tvshow.php' : 'movie.php'; ?>" method="GET" class="search-form">
            <i class="fas fa-search"></i>
            <input type="text" name="search" class="search-input" placeholder="Search <?php echo $type === 'tv' ? 'TV shows' : 'movies'; ?>..." autocomplete="off">
            <button type="submit" class="search-btn"><i class="fas fa-search"></i> <span>Search</span></button>
        </form>
    </div>

    <!-- Back Button -->
    <a href="<?php echo $type === 'tv' ? 'tvshow.php' : 'movie.php'; ?>" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to <?php echo $type === 'tv' ? 'TV Shows' : 'Movies'; ?>
    </a>

    <!-- Main Player Section -->
    <div class="main-player-section">
        <div class="video-wrapper">
            <iframe id="video-frame" 
                    src="<?php echo $servers[0]['url']; ?>"
                    allowfullscreen
                    allow="autoplay; encrypted-media; picture-in-picture"
                    loading="lazy"></iframe>
        </div>

        <!-- Episode Selector - Only for TV shows -->
        <?php if ($type === 'tv'): ?>
        <div class="episode-selector">
            <div class="episode-label">
                <i class="fas fa-list"></i>
                <span>EPISODE:</span>
            </div>
            <div class="episode-controls">
                <select id="seasonSelect" class="episode-select">
                    <?php foreach ($seasons as $s): 
                        if ($s['season_number'] > 0): ?>
                        <option value="<?php echo $s['season_number']; ?>" <?php echo $season == $s['season_number'] ? 'selected' : ''; ?>>
                            Season <?php echo $s['season_number']; ?> (<?php echo $s['episode_count']; ?> eps)
                        </option>
                    <?php endif; endforeach; ?>
                </select>
                
                <select id="episodeSelect" class="episode-select">
                    <?php for ($e = 1; $e <= 20; $e++): ?>
                        <option value="<?php echo $e; ?>" <?php echo $episode == $e ? 'selected' : ''; ?>>
                            Episode <?php echo $e; ?>
                        </option>
                    <?php endfor; ?>
                </select>
                
                <button class="episode-btn" onclick="changeEpisode()">
                    <i class="fas fa-play"></i> Go to Episode
                </button>
            </div>
        </div>
        <?php endif; ?>

        <!-- SERVER BUTTONS -->
        <div class="server-controls">
            <div class="server-label">
                <i class="fas fa-server"></i>
                <span>SERVER:</span>
            </div>
            <div class="server-buttons">
                <?php foreach ($servers as $index => $srv): ?>
                    <button class="server-btn <?php echo ($index + 1) == $server ? 'active' : ''; ?>"
                            onclick="switchServer('<?php echo $srv['url']; ?>', <?php echo $index + 1; ?>, this)">
                        <i class="fas fa-play"></i>
                        <?php echo $srv['name']; ?>
                        <span class="server-status"></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Player Controls -->
        <div class="player-controls">
            <div class="quality-badge">
                <i class="fas fa-hd"></i> HD Quality
            </div>
            <div class="control-right">
                <button class="fullscreen-btn" onclick="toggleFullScreen()">
                    <i class="fas fa-expand"></i> Fullscreen
                </button>
                <?php if ($trailer): ?>
                <button class="fullscreen-btn" onclick="openTrailer('<?php echo $trailer['key']; ?>')">
                    <i class="fab fa-youtube" style="color: #ff0000;"></i> Watch Trailer
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Content Details Section -->
    <div class="content-info">
        <div class="info-poster">
            <img src="https://image.tmdb.org/t/p/w500<?php echo $content['poster_path']; ?>"
                 alt="<?php echo htmlspecialchars($title); ?>"
                 loading="lazy">
        </div>

        <div class="info-details">
            <div class="movie-title-row">
                <h1><?php echo htmlspecialchars($title); ?></h1>
                <button class="watchlist-btn <?php echo $inWatchlist ? 'in-list' : ''; ?>"
                        id="watchlistBtn"
                        onclick="toggleWatchlist(<?php echo $movie_id; ?>)">
                    <i class="<?php echo $inWatchlist ? 'fas' : 'far'; ?> fa-heart"></i>
                    <span><?php echo $inWatchlist ? 'In My List' : 'Add to List'; ?></span>
                </button>
            </div>

            <div class="meta-data">
                <span class="rating-badge">
                    <i class="fas fa-star"></i> <?php echo number_format($content['vote_average'], 1); ?>/10
                </span>
                
                <?php if ($type === 'tv'): ?>
                    <span><i class="far fa-calendar-alt"></i> <?php echo substr($content['first_air_date'], 0, 4); ?></span>
                    <span><i class="fas fa-calendar"></i> <?php echo $content['number_of_seasons']; ?> Seasons</span>
                <?php else: ?>
                    <span><i class="far fa-calendar-alt"></i> <?php echo date('Y', strtotime($content['release_date'])); ?></span>
                    <span><i class="far fa-clock"></i> <?php echo $content['runtime']; ?> min</span>
                <?php endif; ?>
                
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <?php foreach (array_slice($content['genres'], 0, 3) as $genre): ?>
                        <span class="genre-pill"><?php echo htmlspecialchars($genre['name']); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <p class="overview-text"><?php echo htmlspecialchars($content['overview']); ?></p>

            <?php if (isset($content['credits']['cast']) && ! empty($content['credits']['cast'])): ?>
            <div class="cast-info">
                <strong><i class="fas fa-users"></i> Cast:</strong>
                <span class="cast-names">
                    <?php
                        $cast = array_slice($content['credits']['cast'], 0, 8);
                        $castNames = array_map(function ($c) {
                            return '<span>' . htmlspecialchars($c['name']) . '</span>';
                        }, $cast);
                        echo implode(' ', $castNames);
                    ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recommendations Section -->
    <?php if (! empty($recommendations)): ?>
    <div class="recommendations-header">
        <h2><i class="fas fa-film" style="margin-right: 10px; color: var(--primary);"></i> You Might Also Like</h2>
    </div>

    <div class="rec-grid">
        <?php foreach ($recommendations as $rec):
                if (empty($rec['poster_path'])) continue;
                
                // Determine if recommendation is movie or TV show
                $rec_type = isset($rec['title']) ? 'movie' : 'tv';
                $rec_title = $rec_type === 'tv' ? $rec['name'] : $rec['title'];
                $rec_id = $rec['id'];
        ?>
            <div class="rec-card" onclick="location.href='play.php?id=<?php echo $rec_id; ?>&type=<?php echo $rec_type; ?>&server=1'">
                <div class="rec-poster">
                    <img src="https://image.tmdb.org/t/p/w342<?php echo $rec['poster_path']; ?>"
                         alt="<?php echo htmlspecialchars($rec_title); ?>"
                         loading="lazy">
                    <div class="play-overlay">
                        <i class="fas fa-play-circle"></i>
                    </div>
                </div>
                <div class="rec-info">
                    <p class="rec-title"><?php echo htmlspecialchars($rec_title); ?></p>
                    <span class="rec-rating">
                        <i class="fas fa-star"></i> <?php echo number_format($rec['vote_average'], 1); ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
// Get current type
const contentType = '<?php echo $type; ?>';
const contentId = <?php echo $movie_id; ?>;
const currentSeason = <?php echo $season; ?>;
const currentEpisode = <?php echo $episode; ?>;

// Function to change episode (only for TV shows)
function changeEpisode() {
    if (contentType !== 'tv') return;
    
    const season = document.getElementById('seasonSelect').value;
    const episode = document.getElementById('episodeSelect').value;
    const currentServer = <?php echo $server; ?>;
    
    window.location.href = `play.php?id=${contentId}&type=tv&season=${season}&episode=${episode}&server=${currentServer}`;
}

// Server Switcher
function switchServer(url, serverNum, btn) {
    const videoFrame = document.getElementById('video-frame');
    if (!videoFrame) return;
    
    videoFrame.style.opacity = '0.5';
    videoFrame.classList.add('loading-pulse');
    videoFrame.src = url;

    document.querySelectorAll('.server-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('server', serverNum);
    window.history.replaceState({}, '', window.location.pathname + '?' + urlParams.toString());

    setTimeout(() => {
        videoFrame.style.opacity = '1';
        videoFrame.classList.remove('loading-pulse');
    }, 800);

    showNotification(`Server ${serverNum} connected`, 'success');
}

// Fullscreen
function toggleFullScreen() {
    const iframe = document.getElementById('video-frame');
    if (iframe.requestFullscreen) iframe.requestFullscreen();
    else if (iframe.webkitRequestFullscreen) iframe.webkitRequestFullscreen();
    else if (iframe.msRequestFullscreen) iframe.msRequestFullscreen();
}

// Trailer
function openTrailer(videoKey) {
    document.getElementById('trailer-frame').src = `https://www.youtube.com/embed/${videoKey}?autoplay=1&rel=0&modestbranding=1&showinfo=0`;
    document.getElementById('trailerModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeTrailer() {
    document.getElementById('trailer-frame').src = '';
    document.getElementById('trailerModal').classList.remove('active');
    document.body.style.overflow = '';
}

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeTrailer();
    if (e.key === 'f' || e.key === 'F') toggleFullScreen();
});

// Close modal when clicking outside
document.getElementById('trailerModal').addEventListener('click', function(e) {
    if (e.target === this) closeTrailer();
});

// Watchlist
function toggleWatchlist(contentId) {
    const btn = document.getElementById('watchlistBtn');
    const icon = btn.querySelector('i');
    const span = btn.querySelector('span');

    btn.disabled = true;
    btn.style.opacity = '0.7';

    fetch('api/toggle_watchlist.php', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ movie_id: contentId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (data.action === 'added') {
                btn.classList.add('in-list');
                icon.className = 'fas fa-heart';
                span.textContent = 'In My List';
                showNotification('Added to watchlist!', 'success');
            } else {
                btn.classList.remove('in-list');
                icon.className = 'far fa-heart';
                span.textContent = 'Add to List';
                showNotification('Removed from watchlist!', 'info');
            }
        }
    })
    .catch(() => showNotification('Error updating watchlist', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.style.opacity = '1';
    });
}

// Save history
document.addEventListener('DOMContentLoaded', () => {
    fetch('api/save_history.php', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ 
            movie_id: <?php echo $movie_id; ?>,
            type: contentType
        })
    }).catch(() => {});
    
    const serverParam = new URLSearchParams(window.location.search).get('server');
    if (serverParam && (serverParam == 1 || serverParam == 2)) {
        const btn = document.querySelectorAll('.server-btn')[serverParam - 1];
        if (btn) btn.click();
    }
});

// Notification
function showNotification(message, type = 'info') {
    const existingNotif = document.querySelector('.notification');
    if (existingNotif) existingNotif.remove();

    const notif = document.createElement('div');
    notif.className = 'notification';
    notif.style.cssText = `
        position: fixed; 
        top: 80px; 
        right: 20px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white; 
        padding: 12px 24px; 
        border-radius: 8px;
        z-index: 9999; 
        animation: slideIn 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.95rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        border: 1px solid rgba(255,255,255,0.1);
    `;
    notif.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-info-circle'}"></i> ${message}`;
    document.body.appendChild(notif);
    
    setTimeout(() => {
        notif.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(() => notif.remove(), 300);
    }, 3000);
}

// Add slideOut animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);
</script>

<?php include 'layout/footer.php'; ?>