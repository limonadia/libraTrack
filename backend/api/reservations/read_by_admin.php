<?php
header("Access-Control-Allow-Origin: https://libra-track-nu.vercel.app");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';
include_once '../config/cors.php';
include_once '../models/reservation.php';

$database = new Database();
$db = $database->getConnection();

$reservation = new Reservation($db);

$stmt = $reservation->read();
$num = $stmt->rowCount();

if ($num > 0) {
    $reservations_arr = array();
    $reservations_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
            "book_title" => $book_title,
            "book_author" => $book_author,
            "user_name" => $user_name
        );

        array_push($reservations_arr["records"], $reservation_item);
    }

    http_response_code(200);

    echo json_encode($reservations_arr);
} else {
    http_response_code(404);
    echo json_encode(
        array("message" => "No reservations found.")
    );
}
?>
