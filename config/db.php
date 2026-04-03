<?php
// config/db.php
// Use environment variables for database credentials with Docker-friendly defaults
class Database {
    private $host = getenv('DB_HOST') ?: 'db';
    private $db_name = getenv('DB_DATABASE') ?: 'kflix_db';
    private $username = getenv('DB_USERNAME') ?: 'kflix_user';
    private $password = getenv('DB_PASSWORD') ?: 'kflix_password';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("SET NAMES utf8mb4");
            
        } catch(PDOException $e) {
            error_log("Connection error: " . $e->getMessage());
            die("Database connection failed: " . $e->getMessage());
        }
        return $this->conn;
    }
}
?>
