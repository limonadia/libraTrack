<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        // Try to parse MYSQL_URL first (Railway format)
        $mysql_url = getenv('MYSQL_URL');
        
        if ($mysql_url) {
            // Parse mysql://user:password@host:port/database
            $url_parts = parse_url($mysql_url);
            $this->host     = $url_parts['host'] ?? 'localhost';
            $this->port     = $url_parts['port'] ?? 3306;
            $this->username = $url_parts['user'] ?? 'root';
            $this->password = $url_parts['pass'] ?? '';
            $this->db_name  = ltrim($url_parts['path'] ?? '/libratrack', '/');
        } else {
            // Fallback to individual environment variables
            $this->host     = getenv('MYSQLHOST') ?: 'localhost';
            $this->port     = getenv('MYSQLPORT') ?: 3306;
            $this->db_name  = getenv('MYSQLDATABASE') ?: 'libratrack';
            $this->username = getenv('MYSQLUSER') ?: 'root';
            $this->password = getenv('MYSQLPASSWORD') ?: '';
        }
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
            
            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 10
                ]
            );
            
            error_log("Database connected successfully");
        } catch (PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            // Don't expose sensitive info in production
            http_response_code(500);
            echo json_encode(["message" => "Database connection failed"]);
            exit();
        }
        return $this->conn;
    }
}
?>