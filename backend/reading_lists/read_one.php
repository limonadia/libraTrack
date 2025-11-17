<?php
header("Access-Control-Allow-Origin: https://libra-track-nu.vercel.app");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/database.php';
include_once '../models/reading_list.php';
require_once __DIR__ . '/../../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$database = new Database();
$db = $database->getConnection();

$reading_list = new ReadingList($db);

$id = isset($_GET['id']) ? $_GET['id'] : die();

$reading_list->id = $id;

if ($reading_list->readOne()) {
    if ((string)$reading_list->user_id !== (string)$user_id_from_token && !$reading_list->is_public) {
        http_response_code(403);
        echo json_encode(array("message" => "Access denied. This reading list is private."));
        exit();
    }

    $reading_list_arr = array(
        "id" => $reading_list->id,
        "name" => $reading_list->name,
        "user_id" => $reading_list->user_id,
        "is_public" => (bool)$reading_list->is_public,
        "created_at" => $reading_list->created_at,
        "updated_at" => $reading_list->updated_at,
        "items" => $reading_list->items 
    );

    http_response_code(200);
    echo json_encode($reading_list_arr);
} else {
    http_response_code(404); 
    echo json_encode(array("message" => "Reading list not found."));
}
?>