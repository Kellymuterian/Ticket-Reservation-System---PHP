<?php
if (!isset($file_access)) die("Direct File Access Denied");
?>

<div class="content">
    <div class="container-fluid">
        <?php
        if (!isset($_POST['submit'])) {
        ?>
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-header alert-success">
                        <h5 class="m-0">Quick Tips</h5>
                    </div>
                    <div class="card-body">
                        Use the links at the left.
                        <br />You can see list of events by clicking on "New Reservation". The system will display list
                        of available events and their tickets for you which you can view and make Reservations from. 
                        <br>After a successful reservation, you will get an email notification. <br>You are
                        allowed to view all your Reservation history by clicking on "View Reservations".
                    </div>
                </div>
            </div><?php
                    } else {
                        $class = $_POST['class'];
                        $number = $_POST['number'];
                        $ticket_id = $_POST['id'];
                        if ($number < 1) die("Invalid Number");
                        ?>

            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-header alert-success">
                            <h5 class="m-0">Reservation Preview</h5>
                        </div>
                        <div class="card-body">
                            <div class="callout callout-info">
                                <h5><i class="fas fa-info"></i> <?php echo ucwords($class), " Class" ?>:</h5>
                                You are about to reserve
                                <?php echo $number, " Ticket", $number > 1 ? 's' : '', ' for ' ;?>
                                <br />

                                <?php

                                    $fee = ($_SESSION['amount'] = getFee($ticket_id, $class));
                                    echo $number, " x Ksh ", $fee, " = Ksh ", ($fee * $number), "<hr/>";
                                    $fee = $fee * $number;
                                    $amount = intval($fee);
                                    echo "Total = Ksh ", $total = $amount ;
                                    $fee =  intval($total) . "00";
                                    $_SESSION['amount'] =  $total;
                                    $_SESSION['original'] =  $fee;
                                    $_SESSION['ticket'] =  $ticket_id;
                                    $_SESSION['no'] =  $number;
                                    $_SESSION['class'] =  $class;
                                    ?>
                            </div>
                            <a href="pay.php"><button
                                    onclick="return confirm('You will recieve an email for a successful reservation.\nThis finalizes your reservation!')"
                                    class="btn btn-primary">Reserve Now</button></a>
                        </div>
                    </div>
                </div>
                    
                <?php
                    }
                ?>
            </div>

        </div>
    </div>
</div>