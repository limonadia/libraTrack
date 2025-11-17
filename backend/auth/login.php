<?php

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

require_once __DIR__ . './vendor/autoload.php';
use \Firebase\JWT\JWT;

include_once '../config/database.php';
include_once '../models/user.php';
include_once '../config/core.php'; 

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

if (!$db) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed."]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->email) &&
    !empty($data->password)
){
    $user->email = $data->email;
    $user->password = $data->password;

    if($user->login()){

        $token = array(
            "iat" => $issued_at,         
            "exp" => $expiration_time,  
            "iss" => $issuer,           
            "data" => array(
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "role" => $user->role
            )
        );

        $jwt = JWT::encode($token, $key, 'HS256');

        http_response_code(200);

        echo json_encode(
            array(
                "message" => "Successful login.",
                "jwt" => $jwt,
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "role" => $user->role,
                "avatar" => $user->avatar,
                "expireAt" => $expiration_time 
            )
        );
    }
    else{
        http_response_code(401);

        echo json_encode(array("message" => "Login failed. Invalid email or password."));
    }
}
else{
    http_response_code(400);

    echo json_encode(array("message" => "Unable to login. Data is incomplete."));
}
?>