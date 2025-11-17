<?php
class Database {
    // Use environment variables with fallback to default values
    private $host;
    private $db_name;
    private $username;
    private $password;

    private $port;

    public $conn;

    public function __construct() {
    $this->host     = getenv('MYSQLHOST');
    $this->port     = getenv('MYSQLPORT');
    $this->db_name  = getenv('MYSQLDATABASE');
    $this->username = getenv('MYSQLUSER');
    $this->password = getenv('MYSQLPASSWORD');
}

    public function getConnection() {
    $this->conn = null;
    try {
        $this->conn = new PDO(
            "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4",
            $this->username,
            $this->password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    } catch (PDOException $exception) {
        error_log("Database connection error: " . $exception->getMessage());
        echo "DB connection error: " . $exception->getMessage(); 
        exit();
    }
    return $this->conn;
}

}
?>