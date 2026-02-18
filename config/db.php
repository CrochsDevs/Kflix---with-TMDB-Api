<?php
// config/db.php
class Database {
    private $host = 'localhost';
    private $db_name = 'kflix_db';
    private $username = 'root';
    private $password = '';
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