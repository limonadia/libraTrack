<?php

$data = json_decode(file_get_contents("php://input"));

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: https://libra-track-nu.vercel.app");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Max-Age: 3600");
    http_response_code(200);
    exit();
}

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/user.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed."]);
    exit();
}

$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->name) &&
    !empty($data->email) &&
    !empty($data->password)
){
    
    $user->email = $data->email;
    if($user->emailExists()){
        http_response_code(400);

        echo json_encode(array("message" => "Registration failed. Email already exists."));
        exit;
    }

    $user->name = $data->name;
    $user->email = $data->email;
    $user->password = $data->password;
    $user->role = isset($data->role) ? $data->role : "STUDENT";
    $user->active = true;
    $user->avatar = isset($data->avatar) ? $data->avatar : null;

    if($user->create()){
        http_response_code(201);

        echo json_encode(array("message" => "User was created."));
    }
    else{
        http_response_code(503);

        echo json_encode(array("message" => "Unable to create user."));
    }
}
else{
    http_response_code(400);

    echo json_encode(array("message" => "Unable to create user. Data is incomplete."));
}
?>
