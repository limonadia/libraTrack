<?php
require_once '../config/database.php';
require_once 'email_notifs.php';

$database = new Database();
$db = $database->getConnection();

sendReminderEmails($db);
sendOverdueEmails($db);

echo json_encode(["status" => "Maintenance tasks completed."]);
?>