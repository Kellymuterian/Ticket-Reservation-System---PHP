<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
if (!class_exists('PHPMailer\PHPMailer\Exception')){

    // require_once('PHPMailer_5.2.2/class.phpmailer.php');
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
//INSERT YOUR OWN PAYSTACK API KEYS
//$paystack = "#YOUR_API_KEY"; //Do not change this! Redirect URL http://localhost/events/pro/verify.php
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
        $mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587; // TCP port to connect to

        //Recipients
        $from_name = 'TICKET SYSTEM ';
        $mail->setFrom($mail->Username, $from_name);
        $mail->addAddress($to); // Name is optional
        $mail->addReplyTo("muteriank17@mail.com"); // Email address only

        // Attachments
        // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
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

// function getRoutePath($id)
// {
//     $val = connect()->query("SELECT * FROM route WHERE id = '$id'")->fetch_assoc();
//     return $val['start'] . " to " . $val['stop'];
// }

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


// function getRouteFromticket($id)
// {
//     $q = connect()->query("SELECT route_id as id FROM ticket WHERE id = '$id'")->fetch_assoc();
//     return getRoutePath($q['id']);
// }

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



function printClearance($id)
{
    ob_start();
    $con = connect();
    $me = $_SESSION['user_id'];
    $getCount = (connect()->query("SELECT ticket.id as ticket_id, attendee.name as fullname, attendee.email as email, attendee.phone as phone, attendee.loc as loc, payment.amount as amount, payment.ref as ref, payment.date as payment_date, ticket.events_id as events_id, bookedtickets.code as code, bookedtickets.no as no, bookedtickets.class as class, bookedtickets.seat as seat, ticket.date as date, ticket.time as time FROM bookedtickets INNER JOIN ticket on bookedtickets.ticket_id = ticket.id INNER JOIN payment ON payment.id = bookedtickets.payment_id INNER JOIN attendee ON attendee.id = bookedtickets.user_id WHERE bookedtickets.id = '$id'"));
    if ($getCount->num_rows != 1) die("Denied");
    $row = $getCount->fetch_assoc();
    $attendee_name = substr($fullname = ($row['fullname']), 0, 15);
    $name = $fullname;
    $phone = $row['phone'];
    $email = $row['email'];
    $timeframe = '<tr><th style="text-align:center"><b>Project Phase</b></th><th style="text-align:center"><b>Date Accepted</b></th></tr>';
    $date = $row['date'];
    $time = formatTime($row['time']);
    $uniqueCode = $row['code'];
    $ticket = getTicketFromEvents($row['ticket_id']);

    $date = date("D d, M Y", strtotime($date));

    $barcode = "$fullname Ticket For - $date by $time. Ticket ID : $uniqueCode";
    $barcodeOutput = generateQR($id, $barcode);
    $loc = $row['loc'];
    $seat = $row['seat'];
    $events = getName($row['events_id']);
    $class = $row['class'];
    $payment_date = $row['payment_date'];
    $amount = $row['amount'];
    $file_name = preg_replace('/[^a-z0-9]+/', '-', strtolower($attendee_name)) . ".pdf";
    require_once 'PDF/tcpdf_config_alt.php';

    // Include the main TCPDF library (search the library on the following directories).
    $tcpdf_include_dirs = array(
        realpath('PDF/tcpdf.php'),
        '/usr/share/php/tcpdf/tcpdf.php',
        '/usr/share/tcpdf/tcpdf.php',
        '/usr/share/php-tcpdf/tcpdf.php',
        '/var/www/tcpdf/tcpdf.php',
        '/var/www/html/tcpdf/tcpdf.php',
        '/usr/local/apache2/htdocs/tcpdf/tcpdf.php',
    );
    foreach ($tcpdf_include_dirs as $tcpdf_include_path) {
        if (@file_exists($tcpdf_include_path)) {
            require_once $tcpdf_include_path;
            break;
        }
    }

    class MYPDF extends TCPDF
    {
        //Page header
        public function Header()
        {
            // get the current page break margin
            $bMargin = $this->getBreakMargin();
            // get current auto-page-break mode
            $auto_page_break = $this->AutoPageBreak;
            // disable auto-page-break
            $this->SetAutoPageBreak(false, 0);
            // set bacground image
            $img_file = K_PATH_IMAGES . "watermark.jpg";
            // die($img_file);
            $this->Image($img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
            $this->SetAlpha(0.5);

            // restore auto-page-break status
            $this->SetAutoPageBreak($auto_page_break, $bMargin);
            // set the starting point for the page content
            $this->setPageMark();
        }
    }
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    // $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $pageLayout, true, 'UTF-8', false);
    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($fullname);
    $pdf->SetTitle($fullname . " Ticket");
    $pdf->SetSubject(SITE_NAME);
    $pdf->SetKeywords("events Booking System, Rail, Rails, Railway, Booking, Project, System, Website, Portal ");


    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, 7, PDF_MARGIN_RIGHT);
    // $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    // $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(true, 5);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // set some language-dependent strings (optional)
    if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
        require_once dirname(__FILE__) . '/lang/eng.php';
        $pdf->setLanguageArray($l);
    }

    // ---------------------------------------------------------

    // set default font subsetting mode
    $pdf->setFontSubsetting(true);

    // Set font
    // dejavusans is a UTF-8 Unicode font, if you only need to
    // print standard ASCII chars, you can use core fonts like
    // helvetica or times to reduce file size.
    $pdf->SetFont('dejavusans', '', 14, '', true);

    // Add a page
    // This method has several options, check the source code documentation for more information.
    $pdf->AddPage();
    $src = $barcodeOutput;
    // set text shadow effect
    $pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));
    // Set some content to print
    $html = <<<EOD
<style>
table th{font-weight:italic}
</style>
<h1 style="text-align:center"><img src="images/ticketlg.png" width="100" height="100"/><br/>ONLINE TICKET RESERVATION SYSTEM<br/> events TICKET</h1> <div style="text-align:right; font-family:courier;font-weight:bold"><font size="+6">Ticket N<u>o</u>: $uniqueCode </font></div>
<table width="100%" border="1">
<tr><th colspan="2" style="text-align:center"><b>Personal Data</b></th></tr>
<tr><th><b>Full Name:</b></th><td>$fullname</td></tr>
<tr><th><b>Email:</b></th><td>$email</td></tr>
<tr><th><b>Contact:</b></th><td>$phone</td></tr>
<tr><td colspan="2" style="text-align:center"><b>Trip Detail</b></td></tr>
<tr><th><b>events:</b></th><td>$events</td></tr>
<tr><th><b>Class:</b></th><td>$class Class</td></tr>
<tr><th><b>Seat Number:</b></th><td>$seat</td></tr>
<tr><th><b>Date:</b></th><td>$date</td></tr>
<tr><th><b>Time:</b></th><td>$time</td></tr>
<tr><th colspan="2"  style="text-align:center"><b>Payment</b></th></tr>
<tr><th><b>Amount:</b></th><td>$ $amount</td></tr>
<tr><th><b>Payment Date:</b></th><td>$payment_date</td></tr>


</table>

EOD;

    // @unlink($barcodeOutput);
    $html .= <<<EOD
<table width="100%">
<tr><td colspan="2"><p>&nbsp;</p></td></tr>
<tr><td colspan="2" style="text-align:center"><font size="-3"><i><em><strong>CAUTION: </strong></em> Any person who (1) Falsifies any of the data on this ticket or (2) uses a falsified ticket as true, Knowing it to be false is liable to prosecution. </i></font></td></tr>
<tr><td colspan="2" style="text-align:center"><font size="-3"><i><em><strong>NOTE: </strong></em> Be an hour early for all neccessary proceedings! </i></font></td></tr>
    
    <tr>
    
    <td style="text-align:left">
<img weight="180" height="180" src="uploads/$loc"></td>
    <td style="text-align:right">
    <img weight="180" height="180" src="$src"></td></tr></table>
EOD;
    // die($html);
    // Print text using writeHTMLCell()
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
    // ---------------------------------------------------------

    // Close and output PDF document
    // This method has several options, check the source code documentation for more information.
    $pdf->Output($file_name, 'D');
    @unlink($src);
}

function generateQR($id, $data)
{
    $imgname = intval($id) . ".png";
    // === Create qrcode image
    include 'phpqrcode/qrlib.php';
    QRcode::png($data, $imgname, QR_ECLEVEL_L, 11.45, false);

    // === Adding image to qrcode
    $QR = imagecreatefrompng($imgname);

    imagefilter($QR, IMG_FILTER_COLORIZE, 41, 255, 111); //     rgb(197, 167, 95) || rgb(27, 78, 25) || rgb(41, 22, 111) || rgb(15, 81, 22)
    imagealphablending($QR, false);

    // === Change image color
    $im = imagecreatefrompng($imgname);
    //This changes the color
    $r = 41;
    $g = 22;
    $b = 111;
    for ($x = 0; $x < imagesx($im); ++$x) {
        for ($y = 0; $y < imagesy($im); ++$y) {
            //imagefilter($im, IMG_FILTER_COLORIZE, 0, 255, 0); //This changes the color
            $index = imagecolorat($im, $x, $y);
            $c = imagecolorsforindex($im, $index);
            if (($c['red'] < 100) && ($c['green'] < 100) && ($c['blue'] < 100)) { // dark colors
                // here we use the new color, but the original alpha channel
                $colorB = imagecolorallocatealpha($im, 0x12, 0x2E, 0x31, $c['alpha']);
                imagesetpixel($im, $x, $y, $colorB);
            }
        }
    }

    imagepng($im, $imgname);
    imagedestroy($im);

    // === Convert Image to base64
    $type = pathinfo($imgname, PATHINFO_EXTENSION);
    $data = file_get_contents($imgname);
    $imgbase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    chmod($imgname, 0777);

    // === Show image
    // $html = <<<EOD
    // <img src="$imgbase64" style="position:relative;display:block;width:200px;height:200px;margin:auto;">
    // EOD;

    // return array($imgname,$imgbase64);
    return $imgname;
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





function printReport($id)
{
    ob_start();
    $con = connect();
    $getCount = (connect()->query("SELECT ticket.date as date, ticket.time as time, ticket.events_id as events, bookedtickets.seat as seat, attendee.name as fullname, bookedtickets.code as code, bookedtickets.class as class FROM bookedtickets INNER JOIN ticket ON ticket.id = bookedtickets.ticket_id INNER JOIN attendee ON attendee.id = bookedtickets.user_id WHERE bookedtickets.ticket_id = '$id' ORDER BY class "));

    $output = "<style>
    .a {
        text-align:left;
        width: 10%;
    }
    .b{
        width: 20%
    }.c{
    width:30%;
    }
    table {
        border: 1px solid green;
        border-collapse: collapse;
        width: 100%;
        white-space: nowrap;
      }
      
   
          table th.shrink {
        white-space: nowrap
      }
      th{
        font-weight:bolder;

      }
      .shrink {
        white-space: nowrap;
        width: 40%;

      }
      
      table td.expand {
        width: 99%
      }
 
    </style>";
    $sn = 0;
    $ticket = getTicketFromEvents($id);

    // Check if the ticket has attendees
    if (!$ticket) {
        echo "<script>alert('No attendee yet for this Ticket!');window.location='admin.php?page=report'</script>";
        exit;
    }
    
    // Assuming $getCount is defined and contains attendee information
    while ($row = $getCount->fetch_assoc()) {
        $date = $row['date'];
        $time = $row['time'];
        $eventName = getName($row['events']);
        $ticketId = $row['ticket_id'];
        $time = formatTime($time);
        $sn++;
        $output .= '<tr><td class="a">' . $sn . '</td><td class="c">' . substr(ucwords(strtolower($row['fullname'])), 0, 15) . '</td><td class="shrink">' . $row['code'] . ' (' . ucwords(strtolower($row['class'])) . ')</td><td class="b">' . (strtoupper($row['seat'])) . '</td></tr>';
    }
    // $ticket = getRouteFromticket($id);
    // if ($getCount->num_rows < 1) {
    //     echo "<script>alert('No attendee yet for this ticket!');window.location='admin.php?page=report'</script>";
    //     exit;
    // }
    // while ($row = $getCount->fetch_assoc()) {
    //     $date = $row['date'];
    //     $time = $row['time'];
    //     $events = getEventName($row['events']);
    //     $route = getRouteFromticket($id);
    //     $time = formatTime($time);
    //     $sn++;
    //     $output .= '<tr><td class="a">' . $sn . '</td><td class="c">' . substr(ucwords(strtolower($row['fullname'])), 0, 15) . '</td><td class="shrink">' . $row['code'] . ' (' . ucwords(strtolower($row['class'])) . ')</td><td class="b">' . (strtoupper($row['seat'])) . '</td></tr>';
    // }
    $start = '<table class="table" width="100%" border="1"><tr><th class="a">SN</th><th  class="c">Full Name</th><th  class="shrink">Code/Class</th><th  class="b">Seat No</th></tr>';
    $end = '</table>';
    $result = $start . $output . $end;
    // die($result);
    $file_name = preg_replace('/[^a-z0-9]+/', '-', strtolower('events_booking')) . ".pdf";
    require_once 'PDF/tcpdf_config_alt.php';

    // Include the main TCPDF library (search the library on the following directories).
    $tcpdf_include_dirs = array(
        realpath('PDF/tcpdf.php'),
        '/usr/share/php/tcpdf/tcpdf.php',
        '/usr/share/tcpdf/tcpdf.php',
        '/usr/share/php-tcpdf/tcpdf.php',
        '/var/www/tcpdf/tcpdf.php',
        '/var/www/html/tcpdf/tcpdf.php',
        '/usr/local/apache2/htdocs/tcpdf/tcpdf.php',
    );
    foreach ($tcpdf_include_dirs as $tcpdf_include_path) {
        if (@file_exists($tcpdf_include_path)) {
            require_once $tcpdf_include_path;
            break;
        }
    }

    // class MYPDF extends TCPDF
    // {
    //     //Page header
    //     public function Header()
    //     {
    //         // get the current page break margin
    //         $bMargin = $this->getBreakMargin();
    //         // get current auto-page-break mode
    //         $auto_page_break = $this->AutoPageBreak;
    //         // disable auto-page-break
    //         $this->SetAutoPageBreak(false, 0);
    //         // set bacground image
    //         $img_file = K_PATH_IMAGES . "watermark.jpg";
    //         // die($img_file);
    //         $this->Image($img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
    //         $this->SetAlpha(0.5);

    //         // restore auto-page-break status
    //         $this->SetAutoPageBreak($auto_page_break, $bMargin);
    //         // set the starting point for the page content
    //         $this->setPageMark();
    //     }
    // }
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    // $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $pageLayout, true, 'UTF-8', false);
    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor("Admin");
    $pdf->SetTitle("events Bookings " . " Ticket");
    $pdf->SetSubject(SITE_NAME);
    $pdf->SetKeywords("events Booking System, Rail, Rails, Railway, Booking, Project, System, Website, Portal ");


    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, 7, PDF_MARGIN_RIGHT);
    // $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    // $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(true, 5);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // set some language-dependent strings (optional)
    if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
        require_once dirname(__FILE__) . '/lang/eng.php';
        $pdf->setLanguageArray($l);
    }

    // ---------------------------------------------------------

    // set default font subsetting mode
    $pdf->setFontSubsetting(true);

    // Set font
    // dejavusans is a UTF-8 Unicode font, if you only need to
    // print standard ASCII chars, you can use core fonts like
    // helvetica or times to reduce file size.
    $pdf->SetFont('dejavusans', '', 12, '', true);

    // Add a page
    // This method has several options, check the source code documentation for more information.
    $pdf->AddPage();
    // set text shadow effect
    $pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));
    // Set some content to print
    $html = '<h5 style="text-align:center"><img src="images/ticketlg.png" width="80" height="80"/><br/>ONLINE TICKET RESERVATION SYSTEM<br/> LIST OF BOOKINGS  FOR ' . $date . ' (' . $time . ')</h5> <div style="text-align:right; font-family:courier;font-weight:bold"><font size="+1">events ' . $events . ' (' . $sn . ' attendees) : ' . $ticket . ' </font></div>' . $result;
    // die($html);
    // Print text using writeHTMLCell()
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
    // ---------------------------------------------------------

    // Close and output PDF document
    // This method has several options, check the source code documentation for more information.
    $pdf->Output($file_name, 'D');
    @unlink($src);
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
