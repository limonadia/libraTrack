<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../config/cors.php';
include_once '../models/reservation.php';
include_once '../models/book.php';

session_start();

$database = new Database();
$db = $database->getConnection();

$reservation = new Reservation($db);
$book = new Book($db);

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id) || !isset($data->book_id)) {
    http_response_code(400);
    echo json_encode(["message" => "Reservation ID and Book ID are required."]);
    exit;
}

$reservation->id = $data->id;
$bookId = $data->book_id;

if (!$reservation->readOne()) {
    http_response_code(404);
    echo json_encode(["message" => "Reservation not found."]);
    exit;
}

$reservation->status = 'RETURNED';

$db->beginTransaction();

try {
    if (!$reservation->update()) {
        throw new Exception("Failed to update reservation status.");
    }

    $book->id = $bookId;
    $book->status = 'AVAILABLE';

    $db->commit();

    echo json_encode(["message" => "Book marked as returned and available successfully."]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(["message" => $e->getMessage()]);
}
