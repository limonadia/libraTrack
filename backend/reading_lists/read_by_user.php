<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
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

$reading_list->user_id = $decoded->data->id;

$stmt = $reading_list->readByUser();
$num = $stmt->rowCount();

if($num > 0){
    $reading_lists_arr = array();
    $reading_lists_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);

        $reading_list_item = array(
            "id" => $id,
            "name" => $name,
            "user_id" => $user_id,
            "is_public" => $is_public == 1 ? true : false,
            "created_at" => $created_at,
            "updated_at" => $updated_at,
            "item_count" => $item_count
        );

        array_push($reading_lists_arr["records"], $reading_list_item);
    }

    http_response_code(200);

    echo json_encode($reading_lists_arr);
}
else{
    http_response_code(404);

    echo json_encode(
        array("message" => "No reading lists found.")
    );
}
?>
