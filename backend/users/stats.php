<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$allowedOrigins = ['http://localhost:4200'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

include_once '../config/database.php';
include_once '../models/reservation.php';

$authHeader = null;
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
} else if (function_exists('getallheaders')) {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    }
}

if (!$authHeader) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized, no token"]);
    exit();
}

if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized, invalid token format"]);
    exit();
}

$token = $matches[1];

try {
    $secretKey = "your_secret_key";
    $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
    
    $userId = $decoded->data->id ?? null;
    if (!$userId) {
        throw new Exception("User ID missing in token");
    }
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized, invalid token: " . $e->getMessage()]);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$reservation = new Reservation($db);

try {
    $borrowedCount = $reservation->countByStatusAndUser('BORROWED', $userId);
    $overdueCount = $reservation->countOverdueByUser($userId);
    $reservationsCount = $reservation->countByStatusAndUser('RESERVED', $userId);

    echo json_encode([
        "borrowedBooks" => $borrowedCount,
        "overdueBooks" => $overdueCount,
        "reservations" => $reservationsCount,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error retrieving stats"]);
}
