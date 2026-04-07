<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Watchlist;
use Illuminate\Http\Request;

class WatchlistApiController extends Controller
{
    public function index(Request $request)
    {
        $watchlist = new Watchlist();
        $method = $request->method();

        try {
            switch ($method) {
                case 'GET':
                    $page = (int) $request->get('page', 1);
                    $perPage = (int) $request->get('itemsPerPage', 12);
                    $sortBy = $request->get('sortBy', 'date-added');
                    $result = $watchlist->getWatchlistPaginated($page, $perPage, $sortBy);
                    return response()->json([
                        'success' => true,
                        'data' => $result['items'],
                        'total' => $result['total'],
                        'pages' => $result['pages'],
                        'currentPage' => $page,
                    ]);

                case 'POST':
                    $data = $request->json()->all();
                    if (!$data || !isset($data['id'])) {
                        return response()->json(['success' => false, 'message' => 'Invalid data: missing ID', 'received' => $data]);
                    }
                    return response()->json($watchlist->addToWatchlist($data));

                case 'DELETE':
                    $movieId = (int) $request->get('movie_id', 0);
                    if ($movieId) {
                        return response()->json($watchlist->removeFromWatchlist($movieId));
                    }
                    return response()->json($watchlist->clearWatchlist());

                case 'PUT':
                    $movieId = (int) $request->get('movie_id', 0);
                    if ($movieId) {
                        return response()->json([
                            'success' => true,
                            'inWatchlist' => $watchlist->isInWatchlist($movieId),
                            'count' => $watchlist->getCount(),
                        ]);
                    }
                    return response()->json(['success' => false, 'message' => 'Movie ID required']);

                default:
                    return response()->json(['success' => false, 'message' => 'Method not allowed'], 405);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
}
