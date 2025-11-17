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
    $url = getenv('MYSQL_URL');

       if ($url) {
    $dbopts = parse_url($url);
    $this->host = $dbopts['host'];
    $this->port = $dbopts['port'] ?? 3306;
    $this->username = $dbopts['user'];
    $this->password = $dbopts['pass'];
    $this->db_name = ltrim($dbopts['path'], '/');
}

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