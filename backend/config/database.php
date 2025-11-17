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
    $this->host     = getenv('RAILWAY_PRIVATE_DOMAIN');  
    $this->port     = getenv('MYSQLPORT') ?: 3306;
    $this->db_name  = getenv('MYSQL_DATABASE');
    $this->username = getenv('MYSQLUSER') ?: 'root';
    $this->password = getenv('MYSQL_ROOT_PASSWORD');

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