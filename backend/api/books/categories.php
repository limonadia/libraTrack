<?php
header("Access-Control-Allow-Origin: https://libra-track-nu.vercel.app");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
include_once '../config/cors.php';

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT DISTINCT category FROM books ORDER BY category";
$stmt = $db->prepare($query);
$stmt->execute();

$categories = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $categories[] = $row['category'];
}

http_response_code(200);
echo json_encode($categories);
