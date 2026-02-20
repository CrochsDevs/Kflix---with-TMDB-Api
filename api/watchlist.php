<?php
// api/watchlist.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../models/Watchlist.php';

$watchlist = new Watchlist();
$method = $_SERVER['REQUEST_METHOD'];

// Log the request
error_log("API Request: $method " . $_SERVER['REQUEST_URI']);

try {
    switch ($method) {
        case 'GET':
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $itemsPerPage = isset($_GET['itemsPerPage']) ? (int)$_GET['itemsPerPage'] : 12;
            $sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'date-added';
            
            $result = $watchlist->getWatchlistPaginated($page, $itemsPerPage, $sortBy);
            echo json_encode([
                'success' => true,
                'data' => $result['items'],
                'total' => $result['total'],
                'pages' => $result['pages'],
                'currentPage' => $page
            ]);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            error_log("POST data: " . print_r($data, true));
            
            if (!$data || !isset($data['id'])) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Invalid data: missing ID',
                    'received' => $data
                ]);
                break;
            }
            
            $result = $watchlist->addToWatchlist($data);
            error_log("Add result: " . print_r($result, true));
            echo json_encode($result);
            break;

        case 'DELETE':
            if (isset($_GET['movie_id'])) {
                $movie_id = (int)$_GET['movie_id'];
                error_log("Deleting movie ID: $movie_id");
                $result = $watchlist->removeFromWatchlist($movie_id);
            } else {
                error_log("Clearing all watchlist");
                $result = $watchlist->clearWatchlist();
            }
            echo json_encode($result);
            break;

        case 'PUT':
            $movie_id = isset($_GET['movie_id']) ? (int)$_GET['movie_id'] : 0;
            if ($movie_id) {
                $inWatchlist = $watchlist->isInWatchlist($movie_id);
                echo json_encode([
                    'success' => true,
                    'inWatchlist' => $inWatchlist,
                    'count' => $watchlist->getCount()
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Movie ID required']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>