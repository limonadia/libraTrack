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
        $this->username = getenv('MYSQLUSER');     
        $this->password = getenv('MYSQL_ROOT_PASSWORD');
        $this->host     = getenv('RAILWAY_TCP_PROXY_DOMAIN');  
        $this->port = getenv('RAILWAY_TCP_PROXY_PORT');
        $this->db_name  = getenv('MYSQL_DATABASE');  
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

        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            throw new Exception("Unable to connect to database. Please try again later.");
        }
        return $this->conn;
    }
}
?>