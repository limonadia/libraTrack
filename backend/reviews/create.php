<?php
header("Access-Control-Allow-Origin: https://libra-track-nu.vercel.app");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/review.php';
include_once '../auth/validate_token.php';

$database = new Database();
$db = $database->getConnection();

$review = new Review($db);

$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->book_id) &&
    !empty($data->rating) &&
    !empty($data->content)
){
    $review->book_id = $data->book_id;
    $review->user_id = $decoded->data->id; 
    $review->rating = $data->rating;
    $review->content = $data->content;

    if($review->create()){
        http_response_code(201);

        echo json_encode(array("message" => "Review was created."));
    }
    else{
        http_response_code(503);

        echo json_encode(array("message" => "Unable to create review."));
    }
}
else{
    http_response_code(400);

    echo json_encode(array("message" => "Unable to create review. Data is incomplete."));
}
?>
