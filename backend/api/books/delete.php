<?php
header("Access-Control-Allow-Origin: https://libra-track-nu.vercel.app");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../config/cors.php';
include_once '../models/book.php';
include_once '../auth/validate_token.php';

$database = new Database();
$db = $database->getConnection();

$book = new Book($db);

$data = json_decode(file_get_contents("php://input"));

$book->id = $data->id;

if($book->delete()){
    http_response_code(200);

    echo json_encode(array("message" => "Book was deleted."));
}
else{
    http_response_code(503);

    echo json_encode(array("message" => "Unable to delete book."));
}
?>
