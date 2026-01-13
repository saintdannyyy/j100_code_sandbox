<?php
// Load environment variables
require_once __DIR__ . '/Env.php';
Env::load();

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct()
    {
        // Load from environment variables with fallbacks
        $this->host = Env::get('DB_HOST', 'localhost');
        $this->db_name = Env::get('DB_NAME', 'redink_j100Coders');
        $this->username = Env::get('DB_USERNAME', 'root');
        $this->password = Env::get('DB_PASSWORD', '');
    }

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8mb4");
        } catch (PDOException $e) {
            // Only log detailed errors in debug mode
            if (Env::isDebug()) {
                error_log("Database connection error: " . $e->getMessage());
            } else {
                error_log("Database connection failed");
            }
            return null;
        }
        return $this->conn;
    }
}
