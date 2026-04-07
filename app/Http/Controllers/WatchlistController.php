<?php

namespace App\Http\Controllers;

use App\Models\Watchlist;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    public function index()
    {
        $items = (new Watchlist())->getWatchlist();
        return view('watchlist.index', compact('items'));
    }
}
