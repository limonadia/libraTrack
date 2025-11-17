<?php
header("Access-Control-Allow-Origin: https://libra-track-nu.vercel.app");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/book.php';
include_once '../auth/validate_token.php';

$database = new Database();
$db = $database->getConnection();

$book = new Book($db);

$data = json_decode(file_get_contents("php://input"));

$book->id = $data->id;

$book->title = $data->title;
$book->author = $data->author;
$book->description = $data->description;
$book->category = $data->category;
$book->cover_image = $data->cover_image;
$book->available = $data->available;
$book->published_year = $data->published_year;
$book->isbn = $data->isbn;
$book->pages = $data->pages;
$book->publisher = $data->publisher;
$book->language = $data->language;
$book->tags = isset($data->tags) ? $data->tags : array();

if($book->update()){
    http_response_code(200);

    echo json_encode(array("message" => "Book was updated."));
}
else{
    http_response_code(503);

    echo json_encode(array("message" => "Unable to update book."));
}
?>
