<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/fine.php';
include_once '../auth/validate_token.php';

$database = new Database();
$db = $database->getConnection();

$fine = new Fine($db);

$fine->user_id = $decoded->data->id;

$stmt = $fine->readByUser();
$num = $stmt->rowCount();

if($num > 0){
    $fines_arr = array();
    $fines_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);

        $fine_item = array(
            "id" => $id,
            "user_id" => $user_id,
            "book_id" => $book_id,
            "amount" => $amount,
            "status" => $status,
            "due_date" => $due_date,
            "payment_date" => $payment_date,
            "created_at" => $created_at,
            "updated_at" => $updated_at,
            "book" => array(
                "title" => $book_title,
                "author" => $book_author
            )
        );

        array_push($fines_arr["records"], $fine_item);
    }

    http_response_code(200);

    echo json_encode($fines_arr);
}
else{
    http_response_code(404);

    echo json_encode(
        array("message" => "No fines found.")
    );
}
?>
