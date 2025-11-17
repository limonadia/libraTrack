<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$home_url = "http://localhost/libratrack/api/";

date_default_timezone_set('UTC');

$key = "your_secret_key"; 
$issued_at = time();
$expiration_time = $issued_at + (60 * 60 * 24); 
$issuer = "libratrack.com";
?>
