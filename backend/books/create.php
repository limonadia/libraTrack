<?php
header("Access-Control-Allow-Origin: https://libra-track-nu.vercel.app");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../config/cors.php';
include_once '../config/core.php';
include_once '../models/book.php';
include_once '../auth/validate_token.php';
require_once __DIR__ . '/../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$jwt = null;
$key="your_secret_key";
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

if ($authHeader) {
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $jwt = $matches[1];
    }
}

if ($jwt) {
    try {
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["message" => "Access denied.", "error" => $e->getMessage()]);
        exit();
    }
} else {
    http_response_code(401);
    echo json_encode(["message" => "Access denied. Token missing."]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$book = new Book($db);

$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->title) &&
    !empty($data->author) &&
    !empty($data->category)
){

    function generate_uuid_v4() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    $book->id = generate_uuid_v4();
    $book->title = $data->title;
    $book->author = $data->author;
    $book->description = $data->description;
    $book->category = $data->category;
    $book->cover_image = $data->cover_image;
    $book->available = isset($data->available) ? $data->available : true;
    $book->published_year = $data->published_year;
    $book->isbn = $data->isbn;
    $book->pages = $data->pages;
    $book->publisher = $data->publisher;
    $book->language = $data->language;
    $book->added_by = $decoded->data->id; 
    $book->tags = isset($data->tags) ? $data->tags : array();

    if($book->create()){
        http_response_code(201);

        echo json_encode(array("message" => "Book was created."));
    }
    else{
        http_response_code(503);

        echo json_encode(array("message" => "Unable to create book."));
    }
}
else{
    http_response_code(400);

    echo json_encode(array("message" => "Unable to create book. Data is incomplete."));
}
?>
