<?php
// models/Watchlist.php
require_once __DIR__ . '/../config/db.php';

class Watchlist {
    private $conn;
    private $table = 'watchlist';
    private $user_id = 1; 

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        
        if (!$this->conn) {
            error_log("Watchlist: Database connection failed");
            throw new Exception("Database connection failed");
        }
    }

    /**
     * Get all watchlist items for current user
     */
    public function getWatchlist() {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE user_id = :user_id 
                      ORDER BY added_date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("getWatchlist error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get watchlist with pagination
     */
    public function getWatchlistPaginated($page = 1, $itemsPerPage = 12, $sortBy = 'date-added') {
        try {
            $offset = ($page - 1) * $itemsPerPage;
            
            // Determine sort order
            $orderBy = match($sortBy) {
                'title' => 'ORDER BY title ASC',
                'rating' => 'ORDER BY vote_average DESC',
                'year' => 'ORDER BY release_date DESC',
                default => 'ORDER BY added_date DESC'
            };
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE user_id = :user_id";
            $countStmt = $this->conn->prepare($countQuery);
            $countStmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
            $countStmt->execute();
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get paginated results
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE user_id = :user_id 
                      {$orderBy} 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'items' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'total' => (int)$total,
                'pages' => ceil($total / $itemsPerPage)
            ];
            
        } catch (PDOException $e) {
            error_log("getWatchlistPaginated error: " . $e->getMessage());
            return [
                'items' => [],
                'total' => 0,
                'pages' => 1
            ];
        }
    }

    /**
     * Add movie to watchlist
     */
    public function addToWatchlist($movieData) {
        try {
            // Check if already exists
            if ($this->isInWatchlist($movieData['id'])) {
                return ['success' => false, 'message' => 'Movie already in watchlist'];
            }

            $query = "INSERT INTO " . $this->table . " 
                      (movie_id, user_id, title, poster_path, vote_average, release_date) 
                      VALUES 
                      (:movie_id, :user_id, :title, :poster_path, :vote_average, :release_date)";

            $stmt = $this->conn->prepare($query);
            
            $movie_id = $movieData['id'];
            $title = $movieData['title'];
            $poster_path = $movieData['poster_path'] ?? '';
            $vote_average = $movieData['vote_average'] ?? 0;
            $release_date = $movieData['release_date'] ?? '';

            $stmt->bindParam(':movie_id', $movie_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':poster_path', $poster_path);
            $stmt->bindParam(':vote_average', $vote_average);
            $stmt->bindParam(':release_date', $release_date);

            if ($stmt->execute()) {
                return [
                    'success' => true, 
                    'message' => 'Added to watchlist',
                    'count' => $this->getCount()
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to add to watchlist'];
            
        } catch (PDOException $e) {
            error_log("addToWatchlist error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Remove movie from watchlist
     */
    public function removeFromWatchlist($movie_id) {
        try {
            $query = "DELETE FROM " . $this->table . " 
                      WHERE movie_id = :movie_id AND user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':movie_id', $movie_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Removed from watchlist',
                    'count' => $this->getCount()
                ];
            }
            
            return ['success' => false, 'message' => 'Failed to remove from watchlist'];
            
        } catch (PDOException $e) {
            error_log("removeFromWatchlist error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Check if movie is in watchlist
     */
    public function isInWatchlist($movie_id) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                      WHERE movie_id = :movie_id AND user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':movie_id', $movie_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("isInWatchlist error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get watchlist count
     */
    public function getCount() {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                      WHERE user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['count'];
            
        } catch (PDOException $e) {
            error_log("getCount error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Clear entire watchlist
     */
    public function clearWatchlist() {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Watchlist cleared'];
            }
            
            return ['success' => false, 'message' => 'Failed to clear watchlist'];
            
        } catch (PDOException $e) {
            error_log("clearWatchlist error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
?>