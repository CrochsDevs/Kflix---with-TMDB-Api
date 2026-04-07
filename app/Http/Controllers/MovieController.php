<?php

namespace App\Http\Controllers;

use App\Services\TmdbService;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    protected TmdbService $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    public function show($id)
    {
        $movie = $this->tmdb->getMovieDetails($id);
        return view('movies.detail', compact('movie'));
    }
}
