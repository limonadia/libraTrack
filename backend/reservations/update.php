<?php
header("Access-Control-Allow-Origin: https://libra-track-nu.vercel.app");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600"); 
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once '../config/database.php';
include_once '../models/reservation.php';
include_once '../config/core.php'; 

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
    error_log("UPDATE_ERROR: Access denied. No valid Authorization header.");
    http_response_code(401);
    echo json_encode(["message" => "Access denied. No valid Authorization header found (e.g., Bearer token missing or malformed)."]);
    exit();
}

$jwt = $matches[1];

error_log("UPDATE_DEBUG: JWT received: " . $jwt);

try {
    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
    $user_id_from_token = $decoded->data->id;
    error_log("UPDATE_DEBUG: User ID from Token (decoded JWT): " . $user_id_from_token);

} catch (Exception $e) {
    error_log("UPDATE_ERROR: Access denied. Invalid token. Error: " . $e->getMessage());
    http_response_code(401);
    echo json_encode(["message" => "Access denied. Invalid token.", "error" => $e->getMessage()]);
    exit();
}


$input_data = file_get_contents("php://input");
error_log("UPDATE_DEBUG: Received input JSON payload: " . $input_data);
$data = json_decode($input_data);

if (is_null($data) || !isset($data->id) || !isset($data->book_id) || !isset($data->status)) {
    error_log("UPDATE_ERROR: Unable to update reservation. Data is incomplete or malformed JSON. Input: " . $input_data);
    http_response_code(400); 
    echo json_encode(["message" => "Unable to update reservation. Data is incomplete or malformed JSON. Required fields: id, book_id, status."]);
    exit();
}

$reservation->id = $data->id;
error_log("UPDATE_DEBUG: Reservation ID received from frontend for update: " . $reservation->id);

if (!$reservation->readOne()) {
    error_log("UPDATE_ERROR: Reservation ID " . $reservation->id . " not found in database.");
    http_response_code(404);
    echo json_encode(["message" => "Reservation not found."]);
    exit();
}

error_log("UPDATE_DEBUG: Reservation details loaded from DB via readOne():");
error_log("UPDATE_DEBUG: DB Reservation ID: " . $reservation->id);
error_log("UPDATE_DEBUG: DB User ID for this reservation: " . $reservation->user_id);
error_log("UPDATE_DEBUG: DB Book ID for this reservation: " . $reservation->book_id);
error_log("UPDATE_DEBUG: DB Status for this reservation: " . $reservation->status);


error_log("UPDATE_DEBUG: Comparing Token User ID ('" . $user_id_from_token . "') with DB User ID ('" . $reservation->user_id . "').");

if ((string)$reservation->user_id !== (string)$user_id_from_token) {
    error_log("UPDATE_ERROR: User ID mismatch detected! Token user: " . $user_id_from_token . " vs DB user: " . $reservation->user_id);
    http_response_code(403); 
    echo json_encode(["message" => "Access denied. You can only update your own reservations."]);
    exit();
} else {
    error_log("UPDATE_DEBUG: User IDs match! Proceeding with update.");
}

$reservation->book_id = $data->book_id; 
$reservation->status = $data->status;

if ($reservation->update()) {
    http_response_code(200); 
    echo json_encode(["message" => "Reservation was updated."]);
} else {
    error_log("UPDATE_ERROR: Unable to update reservation. Database error during update() call.");
    http_response_code(503);
    echo json_encode(["message" => "Unable to update reservation. Database error."]);
}
?>