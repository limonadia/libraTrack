<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/database.php';
include_once '../models/reservation.php';

require_once __DIR__ . '/../../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$database = new Database();
$db = $database->getConnection();

$reservation = new Reservation($db);

$jwt = null;
$authHeader = null;

if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
} else {
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        }
    }
}

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["message" => "Access denied. No valid Authorization header found (e.g., Bearer token missing or malformed)."]);
    exit();
}

$jwt = $matches[1]; 

$key = "your_secret_key"; 

try {
    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
    $reservation->user_id = $decoded->data->id;

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["message" => "Access denied. Invalid token.", "error" => $e->getMessage()]);
    exit();
}
$input = file_get_contents("php://input");

$data = json_decode($input);

if (empty($data->book_id) || empty($data->status)) { 
    http_response_code(400);
    echo json_encode(["message" => "Unable to create reservation. Data is incomplete. Required: book_id, status."]);
    exit();
}

$reservation->book_id = $data->book_id;
$reservation->status = $data->status;

if ($reservation->isAlreadyReserved()) {
    http_response_code(409);
    echo json_encode(["message" => "Book is already reserved by another user or is in a reserved state."]);
    exit();
}

if (!function_exists('generate_uuid')) {
    function generate_uuid() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
$reservation->id = generate_uuid();

if ($reservation->create()) {
    http_response_code(201);
    echo json_encode(["message" => "Reservation was created successfully.", "reservation_id" => $reservation->id]);
    exit();
} else {
    http_response_code(503); 
    echo json_encode(["message" => "Unable to create reservation due to a server error."]);
    exit();
}
?>