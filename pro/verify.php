<?php
require_once 'session.php';
require_once '../conn.php'; 
require_once '../constants.php';

// Start the session

// Set the previous page URL
if (!empty($_SERVER['HTTP_REFERER'])) {
    $_SESSION['previous_page'] = $_SERVER['HTTP_REFERER'];
} else {
    // Set a default page URL if HTTP_REFERER is empty
    $_SESSION['previous_page'] = "default.php";
}

// Simulate a successful payment verification
$email = $_SESSION['email'];
$reference = isset($_GET['reference']) ? $_GET['reference'] : 'test_reference'; // Use a default reference for testing
$uid = $_SESSION['user_id'];
$paid = isset($_SESSION['original']) ? $_SESSION['original'] : '';
$ticket_id = isset($_SESSION['ticket']) ? $_SESSION['ticket'] : '';
$number = isset($_SESSION['no']) ? $_SESSION['no'] : '';
$class = isset($_SESSION['class']) ? $_SESSION['class'] : '';
$amount = isset($_SESSION['amount']) ? $_SESSION['amount'] . "00" : '';


$paid = substr($paid, 0, -2);
$reference = strtoupper($reference);
$date = date("Y-m-d H:i:s");

// Check if a record with the same attendee_id and ticket_id already exists
$check_query = "SELECT COUNT(*) as count FROM payment WHERE attendee_id = ? AND ticket_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ss", $uid, $ticket_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$check_row = $check_result->fetch_assoc();

if ($check_row['count'] > 0) {
    // Display an alert message for duplicate entry
    echo '<script>alert("Duplicate entry, you have already bought ticket(s) for this event");</script>';
    // Redirect back to the previous page
    echo '<script>window.location.href = "' . $_SESSION['previous_page'] . '";</script>';
    exit(); // Stop script execution
} else {
    // Insert the new record into the payment table
    $stmt = $conn->prepare("INSERT INTO payment (attendee_id, ticket_id, amount, ref, date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $uid, $ticket_id, $paid, $reference, $date);
    $stmt->execute();
    $payment_id = $stmt->insert_id;
    $stmt->close();

    if ($payment_id > 0) {
        // Generate code and seat
        $code = genCode($ticket_id, $uid, $class);
        $seat = genSeat($ticket_id, $class, $number);

        // Insert into booked table using prepared statement
        $stmt = $conn->prepare("INSERT INTO bookedtickets (payment_id, ticket_id, user_id, code, class, no, date, seat) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $payment_id, $ticket_id, $uid, $code, $class, $number, $date, $seat);
        $stmt->execute();
        $stmt->close();

        // Unset session variables
        unset($_SESSION['discount']);
        unset($_SESSION['amount']);
        unset($_SESSION['original']);
        unset($_SESSION['ticket']);
        unset($_SESSION['no']);
        unset($_SESSION['class']);
        $_SESSION['pay_success'] = 'true';
        $_SESSION['has_paid'] = 'true';


        $event_name = getEventName($ticket_id);
        $name= getAttendeeName($uid);
        // Send confirmation email
        $subject = 'Ticket Booking Confirmation';
        $msg = '<html><head><style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                h1 { color: #333; }
                p { margin-bottom: 10px; }
                .footer { background-color: #f4f4f4; padding: 10px; text-align: center; }
                </style></head><body>';
        $msg .= '<div class="container">';
        $msg .= '<h1>Ticket Reservation Confirmation</h1>';
        $msg .= "<p>{$name}, your ticket booking was successful!</p>";
        $msg .= '<p><strong>Event Name:</strong> <span style="color: green; font-weight: bold; font-size: larger;">' . $event_name . '</span></p>';
        $msg .= '<p><strong>Ticket ID:</strong> ' . $ticket_id . '</p>';
        $msg .= '<p><strong>Ticket Code:</strong> ' . $code . '</p>';
        $msg .= '<p><strong>Class:</strong> <span style="color: green; font-weight: bold;">' . strtoupper($class) . '</span></p>';
        $msg .= '<p><strong>Amount:</strong> Ksh ' . $paid . '</p>';
        $msg .= '<p><strong>Number of Tickets:</strong> ' . $number . '</p>';
        $msg .= '<p><strong>Date:</strong> ' . $date . '</p>';
        $msg .= '<p><strong>Seat:</strong> ' . $seat . '</p>';
        
        $msg .= '</div>';
        $msg .= '<div class="footer">Best regards, Kelvin</div>';
        $msg .= '</body></html>';
        
        

        $to = $email;
        if (sendMail($to, $subject, $msg)) {
            echo 'Confirmation email sent!';
        } else {
            echo 'Failed to send confirmation email.';
        }

        
        // Display payment success message
        echo '<script>alert("Payment and booking successfully inserted!");</script>';
        
        // Redirect to payment success page
        header("Location: individual.php?page=paid&now=true");
        exit(); // Stop script execution
    }
}
?>
