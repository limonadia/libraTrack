<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

include_once '../config/database.php';
include_once '../models/book.php';
include_once '../config/cors.php';

$database = new Database();
$db = $database->getConnection();

$book = new Book($db);

$book->id = isset($_GET['id']) ? $_GET['id'] : die();

$book->readOne();

if($book->title != null){
    $book_arr = array(
        "id" => $book->id,
        "title" => $book->title,
        "author" => $book->author,
        "description" => html_entity_decode($book->description),
        "category" => $book->category,
        "cover_image" => $book->cover_image,
        "available" => $book->available == 1 ? true : false,
        "published_year" => $book->published_year,
        "isbn" => $book->isbn,
        "pages" => $book->pages,
        "publisher" => $book->publisher,
        "language" => $book->language,
        "added_by" => $book->added_by,
        "created_at" => $book->created_at,
        "updated_at" => $book->updated_at,
        "rating" => $book->rating,
        "tags" => $book->tags
    );

    http_response_code(200);

    echo json_encode($book_arr);
}
else{
    http_response_code(404);

    echo json_encode(array("message" => "Book does not exist."));
}
?>
