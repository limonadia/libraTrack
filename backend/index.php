<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

echo json_encode(array(
    "name" => "LibraTrack API",
    "version" => "1.0.0",
    "description" => "RESTful API for LibraTrack Library Management System",
    "endpoints" => array(
        "auth" => array(
            "login" => "/api/auth/login.php",
            "register" => "/api/auth/register.php",
            "validate_token" => "/api/auth/validate_token.php"
        ),
        "books" => array(
            "read" => "/api/books/read.php",
            "read_one" => "/api/books/read_one.php?id={id}",
            "create" => "/api/books/create.php",
            "update" => "/api/books/update.php",
            "delete" => "/api/books/delete.php",
            "search" => "/api/books/search.php?q={query}&category={category}&available={true|false}"
        ),
        "reservations" => array(
            "create" => "/api/reservations/create.php",
            "read_by_user" => "/api/reservations/read_by_user.php",
            "update" => "/api/reservations/update.php"
        ),
        "reviews" => array(
            "create" => "/api/reviews/create.php",
            "read_by_book" => "/api/reviews/read_by_book.php?book_id={book_id}"
        ),
        "reading_lists" => array(
            "create" => "/api/reading_lists/create.php",
            "read_by_user" => "/api/reading_lists/read_by_user.php",
            "add_item" => "/api/reading_lists/add_item.php"
        ),
        "fines" => array(
            "calculate" => "/api/fines/calculate.php",
            "read_by_user" => "/api/fines/read_by_user.php"
        )
    )
));
?>
