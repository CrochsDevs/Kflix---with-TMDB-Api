<?php

namespace App\Http\Controllers;

use App\Services\TmdbService;
use Illuminate\Http\Request;

class HomeController extends Controller
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

            if (!empty($search)) {
                $data = $this->tmdb->searchMovies($search, $page, $genre, $sortBy);
            } elseif ($genre > 0) {
                $data = $this->tmdb->searchMovies('', $page, $genre, $sortBy);
            } else {
                $data = $this->tmdb->getTrendingMovies($filter, $page);
            }

            $movies = $data['results'] ?? [];
            $totalPages = min($data['total_pages'] ?? 1, 500);
            $totalResults = $data['total_results'] ?? 0;
            $genres = $this->tmdb->getGenres('movie');

            return view('movies.index', compact('movies', 'totalPages', 'totalResults', 'genres', 'search', 'genre', 'filter', 'sortBy', 'page'));
        } catch (\Exception $e) {
            return view('movies.index', [
                'movies' => [], 'totalPages' => 1, 'totalResults' => 0,
                'genres' => [], 'search' => '', 'genre' => 0,
                'filter' => 'day', 'sortBy' => 'popularity.desc', 'page' => 1,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function movies(Request $request)
    {
        return $this->index($request);
    }
}
