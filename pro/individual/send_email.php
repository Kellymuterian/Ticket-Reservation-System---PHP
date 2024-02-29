<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'path/to/PHPMailer/src/Exception.php';
require 'path/to/PHPMailer/src/PHPMailer.php';
require 'path/to/PHPMailer/src/SMTP.php';

// Include your sendMail function
require 'constants.php';
$file_access = true;
include '../conn.php';
include 'session.php';
include '../constants.php';
// Check if the Reserve Now button was clicked
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['amount'])) {
    // Get the user's email
    $to = "user@example.com"; // Replace with the user's email

    // Email subject
    $subject = "Reservation Confirmation";

    // Email message
    $msg = "Thank you for your reservation. Your total amount is Ksh {$_SESSION['amount']}.";

    // Send email
    if (sendMail($to, $subject, $msg)) {
        // Email sent successfully
        echo "Reservation confirmed. Check your email for confirmation.";
    } else {
        // Email failed to send
        echo "Failed to send email. Please try again later.";
    }
} else {
    // Redirect to the previous page or handle the case where the button was not clicked
    header("Location: previous_page.php");
    exit();
}
?>
