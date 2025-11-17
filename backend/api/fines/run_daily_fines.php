<?php
require_once '../config/database.php';
require_once 'email_notifications.php';

$database = new Database();
$db = $database->getConnection();

sendReminderEmails($db);
sendOverdueEmails($db);
?>