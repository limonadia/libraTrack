<?php
header("Access-Control-Allow-Origin: https://libra-track-nu.vercel.app");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';
include_once '../models/review.php';

$database = new Database();
$db = $database->getConnection();

$review = new Review($db);

$review->book_id = isset($_GET['book_id']) ? $_GET['book_id'] : die();

$stmt = $review->readByBook();
$num = $stmt->rowCount();

if($num > 0){
    $reviews_arr = array();
    $reviews_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);

        $review_item = array(
            "id" => $id,
            "book_id" => $book_id,
            "user_id" => $user_id,
            "rating" => $rating,
            "content" => html_entity_decode($content),
            "date" => $date,
            "user" => array(
                "name" => $user_name,
                "avatar" => $user_avatar
            )
        );

        array_push($reviews_arr["records"], $review_item);
    }

    http_response_code(200);

    echo json_encode($reviews_arr);
}
else{
    http_response_code(404);

    echo json_encode(
        array("message" => "No reviews found.")
    );
}
?>
