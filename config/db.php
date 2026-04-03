<?php
// config/db.php
// Use environment variables for database credentials with Docker-friendly defaults
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        $this->host = getenv('DB_HOST') ?: 'db';
        $this->db_name = getenv('DB_DATABASE') ?: 'kflix_db';
        $this->username = getenv('DB_USERNAME') ?: 'kflix_user';
        $this->password = getenv('DB_PASSWORD') ?: 'kflix_password';
    }

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
