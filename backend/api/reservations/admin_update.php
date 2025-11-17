<?php
header("Access-Control-Allow-Origin: https://libra-track-nu.vercel.app");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../config/cors.php';
include_once '../models/reservation.php';

$database = new Database();
$db = $database->getConnection();
$reservation = new Reservation($db);

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id) || !isset($data->action)) {
    http_response_code(400);
    echo json_encode(["message" => "Reservation ID and action are required."]);
    exit;
}

$reservation->id = $data->id;
$action = strtolower($data->action);


if (!$reservation->readOne()) {
    http_response_code(404);
    echo json_encode(["message" => "Reservation not found."]);
    exit;
}

if ($action === 'approve') {
    $reservation->status = 'BORROWED';
} elseif ($action === 'cancel') {
    $reservation->status = 'CANCELLED';
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid action. Use 'approve' or 'cancel'."]);
    exit;
}

if ($reservation->update()) {
    echo json_encode(["message" => "Reservation " . $action . "d successfully."]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to " . $action . " reservation."]);
}
