<?php

namespace App\Http\Controllers;

use App\Services\TmdbService;

class PlayerController extends Controller
{
    protected TmdbService $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    public function index()
    {
        $id = request('id');
        $type = request('type', 'movie');
        $season = (int) request('season', 1);
        $episode = (int) request('episode', 1);
        $server = (int) request('server', 1);

        if (!$id) return redirect('/');

        $content = $type === 'tv'
            ? $this->tmdb->getTVDetails($id)
            : $this->tmdb->getMovieDetails($id);

        $recommendations = $content['recommendations']['results']
            ?? $content['similar']['results'] ?? [];
        $recommendations = array_slice($recommendations, 0, 12);

        $videos = $content['videos']['results'] ?? [];

        $seasons = $content['seasons'] ?? [];

        // Build servers
        if ($type === 'tv') {
            $servers = [
                ['name' => 'Server 1', 'url' => "https://moviesapi.club/tv/{$id}-{$season}-{$episode}", 'status' => 'active'],
                ['name' => 'Server 2', 'url' => "https://www.2embed.cc/embedtv/{$id}&s={$season}&e={$episode}", 'status' => 'active'],
            ];
        } else {
            $servers = [
                ['name' => 'Server 1', 'url' => "https://moviesapi.club/movie/{$id}", 'status' => 'active'],
                ['name' => 'Server 2', 'url' => "https://www.2embed.cc/embed/{$id}", 'status' => 'active'],
            ];
        }

        $trailer = null;
        foreach ($videos as $video) {
            if ($video['type'] === 'Trailer' && $video['site'] === 'YouTube') {
                $trailer = $video;
                break;
            }
        }
        if (!$trailer && !empty($videos)) $trailer = $videos[0];

        $serverUrl = $servers[min($server, count($servers)) - 1]['url'] ?? $servers[0]['url'];

        $title = $type === 'tv' ? ($content['name'] ?? '') : ($content['title'] ?? '');

        return view('player.index', compact('content', 'type', 'season', 'episode', 'server', 'servers', 'recommendations', 'videos', 'seasons', 'trailer', 'serverUrl', 'title'));
    }
}
