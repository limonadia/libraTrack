<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/reading_list.php';
include_once '../auth/validate_token.php';

$database = new Database();
$db = $database->getConnection();

require_once __DIR__ . '/../../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$jwt = null;
$authHeader = null;

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
} else {
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        }
    }
}

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["message" => "Access denied. No valid Authorization header found."]);
    exit();
}

$jwt = $matches[1];

include_once '../config/core.php'; 

try {
    $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
    $user_id_from_token = $decoded->data->id; 

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["message" => "Access denied. Invalid token.", "error" => $e->getMessage()]);
    exit();
}

$reading_list = new ReadingList($db);

$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->reading_list_id) &&
    !empty($data->book_id)
){
    $reading_list->id = $data->reading_list_id;
    $reading_list->user_id = $decoded->data->id;

    if($reading_list->readOne()){
        if($reading_list->addItems(array($data->book_id))){
            http_response_code(200);

            echo json_encode(array("message" => "Book was added to reading list."));
        }
        else{
            http_response_code(503);

            echo json_encode(array("message" => "Unable to add book to reading list."));
        }
    }
    else{
        http_response_code(404);

        echo json_encode(array("message" => "Reading list not found or access denied."));
    }
}
else{
    http_response_code(400);

    echo json_encode(array("message" => "Unable to add book to reading list. Data is incomplete."));
}
?>
