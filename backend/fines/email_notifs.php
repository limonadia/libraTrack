<?php
require_once '../config/database.php';
require_once '../models/reservation.php';
require_once '../utils/send_emails.php'; 

function sendReminderEmails($db) {
    $reservation = new Reservation($db);
    $reminders = $reservation->getRemindersDueIn(2); 

    foreach ($reminders as $item) {
        $subject = "📘 Reminder: Book Due Soon";
        $message = "
            Hello {$item['user_name']},<br>
            This is a reminder that your book <strong>{$item['book_title']}</strong> is due in 2 days.<br>
            Please return it on time to avoid late fees.
        ";
        sendEmail($item['email'], $subject, $message);
    }
}

function sendOverdueEmails($db) {
    $reservation = new Reservation($db);
    $overdues = $reservation->getOverdueBooks();

    foreach ($overdues as $item) {
        $daysLate = floor((strtotime(date('Y-m-d')) - strtotime($item['due_date'])) / 86400);
        $fine = $daysLate * 0.5;

        $subject = "❗ Overdue Book Notice";
        $message = "
            Hello {$item['user_name']},<br>
            Your book <strong>{$item['book_title']}</strong> is overdue by {$daysLate} days.<br>
            Your current fine is <strong>\${$fine}</strong>.<br>
            Please return the book as soon as possible.
        ";

        sendEmail($item['email'], $subject, $message);

        $reservation->updateFine($item['id'], $fine);
    }
}
?>