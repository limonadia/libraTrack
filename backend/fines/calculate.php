<?php
header("Access-Control-Allow-Origin: https://libra-track-nu.vercel.app");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/fine.php';

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->due_date)){
    $fine_rate = isset($data->fine_rate) ? $data->fine_rate : 0.5;
    $fine_amount = Fine::calculateFine($data->due_date, $fine_rate);

    http_response_code(200);

    echo json_encode(array(
        "fine_amount" => $fine_amount,
        "fine_rate" => $fine_rate,
        "due_date" => $data->due_date,
        "days_overdue" => max(0, floor((time() - strtotime($data->due_date)) / (60 * 60 * 24)))
    ));
}
else{
    http_response_code(400);

    echo json_encode(array("message" => "Unable to calculate fine. Due date is required."));
}
?>
