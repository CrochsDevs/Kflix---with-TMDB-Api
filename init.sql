-- Initialize the watchlist table
CREATE TABLE IF NOT EXISTS watchlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    user_id INT NOT NULL DEFAULT 1,
    title VARCHAR(255) NOT NULL,
    poster_path VARCHAR(255) DEFAULT '',
    vote_average DECIMAL(3,1) DEFAULT 0.0,
    release_date VARCHAR(50) DEFAULT '',
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_movie (user_id, movie_id),
    INDEX idx_user_id (user_id),
    INDEX idx_added_date (added_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
