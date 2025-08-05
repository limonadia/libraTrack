<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
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
    $headers = apache_request_headers(); 
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    }
}

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["message" => "Access denied. No valid Authorization header found (e.g., Bearer token missing)."]);
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

$stmt = $reservation->readByUser();
$num = $stmt->rowCount();

if($num > 0){
    $reservations_arr = array();
    $reservations_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);

        $reservation_item = array(
            "id" => $id,
            "book_id" => $book_id,
            "user_id" => $user_id,
            "status" => $status,
            "date" => $date,
            "due_date" => $due_date,
            "expiry_date" => $expiry_date,
            "created_at" => $created_at,
            "updated_at" => $updated_at,
            "book" => array(
                "title" => $book_title,
                "author" => $book_author,
                "cover_image" => $book_cover
            )
        );

        array_push($reservations_arr["records"], $reservation_item);
    }

    http_response_code(200);

    echo json_encode($reservations_arr);
}
else{
    http_response_code(200); 
    echo json_encode(
        array("message" => "No reservations found.")
    );
}
?>