<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\TVController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\Api\WatchlistApiController;

// Main pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/movies', [HomeController::class, 'movies'])->name('movies');
Route::get('/tv', [TVController::class, 'index'])->name('tv');
Route::get('/tv/new-popular', [TVController::class, 'newPopular'])->name('tv.newpopular');
Route::get('/tv/{id}', [TVController::class, 'show'])->name('tv.show')->where('id', '[0-9]+');
Route::get('/movie/{id}', [MovieController::class, 'show'])->name('movie.show')->where('id', '[0-9]+');
Route::get('/watch', [PlayerController::class, 'index'])->name('player');
Route::get('/watchlist', [WatchlistController::class, 'index'])->name('watchlist');

// Watchlist API
Route::any('/api/watchlist', [WatchlistApiController::class, 'index']);
