<?php
class Database
{
    private $host = "gator4029.hostgator.com";
    private $db_name = "redink_codebin";
    private $username = "redink_J100";  // Change to your DB username
    private $password = "Ad_H1m5_U53r";      // Change to your DB password
    public $conn;

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
            error_log("Connection error: " . $e->getMessage());
        }
        return $this->conn;
    }
}
