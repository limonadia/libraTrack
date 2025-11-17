<?php
// CORS headers — must be before any output
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: https://libra-track-nu.vercel.app");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Max-Age: 3600");
    http_response_code(200);
    exit();
}

header("Access-Control-Allow-Origin: https://libra-track-nu.vercel.app");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Load Composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;

// Include database and user model
include_once '../config/database.php';
include_once '../models/user.php';
include_once '../config/core.php';

// Connect to the database
$database = new Database();
$db = $database->getConnection();

// Exit if DB connection fails
if (!$db) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed."]);
    exit();
}

// Create user object
$user = new User($db);

// Read JSON input
$data = json_decode(file_get_contents("php://input"));

// Validate input
if (!empty($data->email) && !empty($data->password)) {
    $user->email = $data->email;
    $user->password = $data->password;

    if ($user->login()) {
        $token = [
            "iat" => $issued_at,
            "exp" => $expiration_time,
            "iss" => $issuer,
            "data" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "role" => $user->role
            ]
        ];

        $jwt = JWT::encode($token, $key, 'HS256');

        http_response_code(200);
        echo json_encode([
            "message" => "Successful login.",
            "jwt" => $jwt,
            "id" => $user->id,
            "name" => $user->name,
            "email" => $user->email,
            "role" => $user->role,
            "avatar" => $user->avatar,
            "expireAt" => $expiration_time
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Login failed. Invalid email or password."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Unable to login. Data is incomplete."]);
}
