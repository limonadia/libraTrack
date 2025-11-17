<?php
header("Access-Control-Allow-Origin: https://libra-track-nu.vercel.app");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/database.php';
include_once '../models/book.php';

$database = new Database();
$db = $database->getConnection();

$book = new Book($db);

$stmt = $book->read();
$num = $stmt->rowCount();

if($num > 0){
    $books_arr = array();
    $books_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);
        $book->id = $id;
        $tags = [];

        $book_item = array(
            "id" => $id,
            "title" => $title,
            "author" => $author,
            "description" => html_entity_decode($description),
            "category" => $category,
            "cover_image" => $cover_image,
            "available" => $available == 1 ? true : false,
            "published_year" => $published_year,
            "isbn" => $isbn,
            "pages" => $pages,
            "publisher" => $publisher,
            "language" => $language,
            "added_by" => $added_by,
            "created_at" => $created_at,
            "updated_at" => $updated_at,
            "rating" => isset($rating) ? $rating : null,
            "tags" => $tags
        );

        array_push($books_arr["records"], $book_item);
    }

    http_response_code(200);

    echo json_encode($books_arr);
}
else{
    http_response_code(404);
    echo json_encode(
        array("message" => "No books found.")
    );
}
?>
