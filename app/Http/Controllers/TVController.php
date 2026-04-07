<?php

namespace App\Http\Controllers;

use App\Services\TmdbService;
use Illuminate\Http\Request;

class TVController extends Controller
{
    protected TmdbService $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    public function index(Request $request)
    {
        try {
            $search = trim($request->get('search', ''));
            $genre = (int) $request->get('genre', 0);
            $filter = $request->get('filter', 'day');
            $sortBy = $request->get('sort', 'popularity.desc');
            $page = max(1, (int) $request->get('page', 1));

            $data = $search || $genre > 0
                ? $this->tmdb->searchTV($search, $page, $genre, $sortBy)
                : $this->tmdb->getTrendingTV($filter, $page);

            $tvshows = $data['results'] ?? [];
            $totalPages = min($data['total_pages'] ?? 1, 500);
            $genres = $this->tmdb->getGenres('tv');

            $genreName = '';
            if ($genre > 0) {
                foreach ($genres as $g) { if ($g['id'] == $genre) { $genreName = $g['name']; break; } }
            }

            return view('tv.index', compact('tvshows', 'totalPages', 'genres', 'genreName', 'search', 'genre', 'filter', 'sortBy', 'page'));
        } catch (\Exception $e) {
            return view('tv.index', [
                'tvshows' => [], 'totalPages' => 1,
                'genres' => [], 'search' => '', 'genre' => 0,
                'filter' => 'day', 'sortBy' => 'popularity.desc', 'page' => 1,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function show($id)
    {
        $tv = $this->tmdb->getTVDetails($id);
        return view('tv.detail', compact('tv'));
    }

    public function newPopular(Request $request)
    {
        try {
            $page = max(1, (int) $request->get('page', 1));
            $sortBy = $request->get('sort', 'popularity.desc');
            $data = $this->tmdb->getNewPopularTV($sortBy, $page);
            $tvshows = $data['results'] ?? [];
            $totalPages = min($data['total_pages'] ?? 1, 500);
            $genres = $this->tmdb->getGenres('tv');
            return view('tv.newpopular', compact('tvshows', 'totalPages', 'genres', 'page', 'sortBy'));
        } catch (\Exception $e) {
            return view('tv.newpopular', [
                'tvshows' => [], 'totalPages' => 1,
                'genres' => [], 'page' => 1, 'sortBy' => 'popularity.desc',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
