<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
if (!class_exists('PHPMailer\PHPMailer\Exception')){


require_once 'PHPMailer/src/Exception.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';
    }else{
        die("HArd");
    }


include_once 'config.php';

define("SITE_NAME", $title);
date_default_timezone_set("Africa/Nairobi");
$date = date('D, d-M-Y h:i:s A');;
$date_small = date('d-M-Y');;

if (!function_exists('connect')) {

    function connect()
    {
        $con = new mysqli("localhost", "root", "", "ticketsys");
        if (!$con) die("Database is being upgraded!");
        return $con;
    }
}


function sendMail($to, $subject, $msg)
{
    global $title;
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = 0; // Enable verbose debug output
        $mail->isSMTP(); // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = "muteriank17@gmail.com"; // SMTP username
        $mail->Password = "zrhvivfmjyzkaoud"; // SMTP password
        $mail->SMTPSecure = 'tls'; 
        $mail->Port = 587; // TCP port to connect to

        //Recipients
        $from_name = 'TICKET SYSTEM ';
        $mail->setFrom($mail->Username, $from_name);
        $mail->addAddress($to); 
        $mail->addReplyTo("muteriank17@mail.com"); // Email address only

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body = $msg;
        $mail->AltBody = $msg;

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log error or handle it accordingly
        return false;
    }
}





function genSeat($id, $type, $number)
{
    $conn = connect();
    $type_seat_query = $conn->query("SELECT events.vip_seat as vip, events.regular_seat as regular FROM ticket INNER JOIN events ON events.id = ticket.events_id WHERE ticket.id = '$id'");
    if (!$type_seat_query) {
        // Handle query error
        return false;
    }
    $type_seat = $type_seat_query->fetch_assoc();

    // Check if the key exists in the $type_seat array
    if (!isset($type_seat[$type])) {
        // Handle case where key does not exist
        return false;
    }

    $me = $type_seat[$type];
    $query = $conn->query("SELECT SUM(no) AS no FROM bookedtickets WHERE ticket_id = '$id' AND class = '$type'");
    if (!$query) {
        // Handle query error
        return false;
    }
    $query_result = $query->fetch_assoc();
    $no = $query_result['no'];
    if ($no == null) $no = 1;
    else $no++;
    //Multiple Seats or Not
    if ($number == 1) {
        while (strlen($no) != strlen($me)) {
            $no = "0" . $no;
        }
        return strtoupper(substr($type, 0, 1)) . "$no";
    } else {
        $start = $no;
        $end = $no + ($number - 1);
        while (strlen($start) != strlen($me)) {
            $start = "0" . $start;
        }
        while (strlen($end) != strlen($me)) {
            $end = "0" . $end;
        }
        $append = strtoupper(substr($type, 0, 1));

        return $append . $start . " - " . $append . $end;
    }
}


function getAttendeeName($uid) {


    $conn = connect();

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT name FROM attendee WHERE id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the result
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["name"];
    } else {
        return "Attendee not found";
    }
}



function genCode($id, $user, $class)
{
    $conn = connect();
    $query = $conn->query("SELECT SUM(no) AS no FROM bookedtickets WHERE ticket_id = '$id'")->fetch_assoc();
    $no = $query['no'];
    if ($no == null) $no = 1;
    else $no++;
    $number = "";
    switch (strlen($id)) {
        case 1:
            $number = "00";
            break;
        case 2:
            $number = "0";
            break;
        default:
            $number = "0";
            break;
    }
    $code = date('Y') . "/$number" . $id . "/" . $no . mt_rand(1, 882);
    return $code;
}

function login($username, $password)
{
    $password = md5($password);
    $q = connect()->query("SELECT * FROM attendee WHERE email = '$username' AND password = '$password' AND status = '1' ")->num_rows;
    if ($q == 1) return 1;
    return 0;
}

function adminLogin($username, $password)
{
    $q = connect()->query("SELECT * FROM users WHERE username = '$username' AND password = '$password' ")->num_rows;
    if ($q == 1) return 1;
    return 0;
}

function getIndividualName($id, $conn = null)
{
    $conn = connect();
    $q = $conn->query("SELECT * FROM attendee WHERE id = '$id'")->fetch_assoc();
    return $q['name'];
}



function uploadFile($file)
{

    $loc = genRand() . "." . strtolower(pathinfo(@$_FILES[$file]['name'], PATHINFO_EXTENSION));
    $valid_extension = array("jpg", "png", "jpeg");
    //Check for valid file size
    if (($_FILES[$file]['size'] && !in_array(strtolower(pathinfo(@$_FILES[$file]['name'], PATHINFO_EXTENSION)), $valid_extension)) || ($_FILES[$file]['size'] && $_FILES[$file]['error']) > 0) {
        return -1;
    }
    $upload = move_uploaded_file(@$_FILES[$file]['tmp_name'], "uploads/" . $loc);
    if ($upload) {
        chmod("uploads/" . $loc, 0777);
        return $loc;
    } else {
        return -1;
    }
}

function genRand()
{
    return md5(mt_rand(1, 3456789) . date('dmyhmis'));
}

function getImage($id, $conn)
{
    $row = $conn->query("SELECT loc FROM attendee WHERE id = '$id'")->fetch_assoc();
    if (strlen($row['loc']) < 10) return "images/ticketlg.png";
    else return "uploads/" . $row['loc'];
}

function formatDate($date)
{
    return date('d-m-Y', strtotime($date));
}



function formatTime($time)
{
    $time = explode(":", $time);
    if (isset($time[0]) && isset($time[1])) {
        if ($time[0] > 12) {
            $string = ($time[0] - 12) . ":" . $time[1] . " PM";
        } else {
            $string = ($time[0]) . ":" . $time[1] . " AM";
        }
        return $string;
    }
    return "";
}



function getToday()
{
    return date('d-m-Y');
}

function getTime()
{
    return date('H:i');
}

function queryTicket($type)
{
    $today = getToday();
    $conn = connect();
    $row = 0;
    if ($type == 'future') {
        $row = $conn->query("SELECT * FROM `ticket` WHERE STR_TO_DATE(`date`,'%d-%m-%Y') >= STR_TO_DATE('$today','%d-%m-%Y')");
    } else {
        $row = $conn->query("SELECT * FROM `ticket` WHERE STR_TO_DATE(`date`,'%d-%m-%Y') <= STR_TO_DATE('$today','%d-%m-%Y')");
    }
    return $row;
}

function queryEvents($type)
{
    $today = getToday();
    $conn = connect();
    $row = 0;
    if ($type == 'future') {
        $row = $conn->query("SELECT * FROM `schedule` WHERE STR_TO_DATE(`date`,'%d-%m-%Y') >= STR_TO_DATE('$today','%d-%m-%Y')");
    } else {
        $row = $conn->query("SELECT * FROM `schedule` WHERE STR_TO_DATE(`date`,'%d-%m-%Y') <= STR_TO_DATE('$today','%d-%m-%Y')");
    }
    return $row;
}



function getFee($id, $type = 'regular')
{
    if ($type == 'regular') {
        $type = 'regular_fee';
    } else {
        $type = 'vip_fee';
    }
    $q = connect()->query("SELECT $type FROM ticket WHERE id = '$id'")->fetch_assoc();
    return $q[$type];
}

function getTotalBookByType($id)
{
    $con = connect()->query("SELECT SUM(no) as no FROM `bookedtickets` WHERE ticket_id = '$id' AND class = 'regular'")->fetch_assoc();
    $con2 = connect()->query("SELECT SUM(no) as no FROM `bookedtickets` WHERE ticket_id = '$id' AND class = 'vip'")->fetch_assoc();

    $num = isset($con['no']) ? intval($con['no']) : 0;
    $num2 = isset($con2['no']) ? intval($con2['no']) : 0;

    $qu = connect()->query("SELECT events.vip_seat as vip, events.regular_seat as regular FROM ticket INNER JOIN events ON events.id = ticket.events_id WHERE ticket.id = '$id'")->fetch_assoc();

    $vip_seat = intval($qu['vip']);
    $regular_seat = intval($qu['regular']);

    return array("vip" => $vip_seat, "regular" => $regular_seat, "vip_booked" => $num2, "regular_booked" => $num);
}


function isTicketActive($id)
{
    $today = getToday();
    $con = connect();
    $conn = $con->query("SELECT * FROM `ticket` WHERE STR_TO_DATE(`date`,'%d-%m-%Y') >= STR_TO_DATE('$today','%d-%m-%Y') AND `id` = '$id'");
    if ($conn->num_rows == 1) {
        $row = $conn->fetch_assoc();
        $time = getTime();
        $ticket_date = $row['date'];
        $ticket_time = $row['time'];
        if ($ticket_date == $today) {
            if (strtotime($ticket_time) <= strtotime($time)) return false;
        }
        return true;
    }
    return false;
}

function getName($id)
{
    if (empty($id) || !is_numeric($id)) {
        return "Unknown";
    }

    $conn = connect(); // Assuming connect() returns the database connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $result = $conn->query("SELECT name FROM events WHERE id = '$id'");
    if (!$result) {
        die("Error executing query: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        $val = $result->fetch_assoc();
        return $val['name'];
    } else {
        return "Unknown";
    }
}



function alert($msg)
{
    echo "<script>alert('$msg')</script>";
}

function load($link)
{
    echo "<script>window.location = ('$link')</script>";
}

function getTicket($id)
{
    $val = connect()->query("SELECT * FROM ticket WHERE id = '$id'")->fetch_assoc();
    return $val['start'] . " to " . $val['stop'];
}
function getEventName($ticket_id) {
    $con = connect();

    // Prepare and execute SQL query
    $sql = "SELECT e.name 
            FROM ticket t
            JOIN events e ON t.events_id = e.id
            WHERE t.id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $stmt->bind_result($event_name);
    $stmt->fetch();

    // Close statement and connection
    $stmt->close();
    $con->close();

    return $event_name;
}

function getTicketFromEvents($id)
{
    $q = connect()->query("SELECT id FROM ticket WHERE id = '$id'")->fetch_assoc();
   return getTicket($q['id']);
}



function sum($id, $type = null)
{
    $conn = connect();
    if ($type == null) {
        $row = $conn->query("SELECT SUM(amount) as amount FROM `payment` INNER JOIN bookedtickets ON bookedtickets.payment_id = payment.id AND bookedtickets.ticket_id = payment.ticket_id WHERE payment.ticket_id = '$id'")->fetch_assoc();
    } else {
        $row = $conn->query("SELECT SUM(amount) as amount FROM `payment` INNER JOIN bookedtickets ON bookedtickets.payment_id = payment.id AND bookedtickets.ticket_id = payment.ticket_id WHERE payment.ticket_id = '$id' AND bookedtickets.class = '$type'")->fetch_assoc();
    }
    return $row['amount'] == null ? 0 : $row['amount'];
}



function sendFeedback($msg)
{
    $me = $_SESSION['user_id'];
    $msg = connect()->real_escape_string($msg);
    $stmt = connect()->query("INSERT INTO feedback (user_id, message) VALUES ('$me', '$msg')");
    if ($stmt) return 1;
    return 0;
}

function getFeedbacks()
{
    $me = $_SESSION['user_id'];
    return connect()->query("SELECT * FROM feedback WHERE user_id = '$me'");
}

function replyTo($id, $reply)
{
    $con = connect();
    $reply = $con->real_escape_string($reply);
    $sql = $con->query("UPDATE feedback SET response = '$reply' WHERE id = '$id' ");
    if ($sql) return 1;
    return 0;
}
