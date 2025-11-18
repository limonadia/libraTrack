<?php
header("Access-Control-Allow-Origin: https://libra-track-nu.vercel.app");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, DELETE"); 
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/database.php';
include_once '../models/reading_list.php';
require_once __DIR__ . '/../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$database = new Database();
$db = $database->getConnection();

$reading_list = new ReadingList($db);

$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->reading_list_id) &&
    !empty($data->book_id)
){
    $reading_list->id = $data->reading_list_id;

    if($reading_list->readOne()){
        if ((string)$reading_list->user_id !== (string)$user_id_from_token) {
            http_response_code(403); 
            echo json_encode(array("message" => "Access denied. You can only remove from your own reading lists."));
            exit();
        }

        if($reading_list->removeItem($data->book_id)){
            http_response_code(200);
            echo json_encode(array("message" => "Book was removed from reading list."));
        }
        else{
            http_response_code(503);
            echo json_encode(array("message" => "Unable to remove book from reading list."));
        }
    }
    else{
        http_response_code(404); 
        echo json_encode(array("message" => "Reading list not found."));
    }
}
else{
    http_response_code(400); 
    echo json_encode(array("message" => "Unable to remove book from reading list. Data is incomplete."));
}
?>