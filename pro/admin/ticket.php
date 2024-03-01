
<?php
if (!isset($file_access)) die("Direct File Access Denied");
$source = 'dynamic';
$me = "?page=$source";
?>

<div class="content">
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                All Tickets</h3>
                            <div class='float-right'>
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                    data-target="#add">
                                    Add New ticket
                            </div>
                        </div>

                        <div class="card-body">

                            <table id="example1" style="align-items: stretch;"
                                class="table table-hover w-100 table-bordered table-striped<?php //
                                                                                                                                            ?>">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Event</th>
                                        <th>VIP Fee</th>
                                        <th>Regular Fee</th>
                                        <th>Reservations Available</th>
                                        <th>Date/Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $row = $conn->query("SELECT * FROM ticket ORDER BY id DESC");

                                    if ($row->num_rows < 1) echo "No Records Yet";
                                    $sn = 0;
                                    while ($fetch = $row->fetch_assoc()) {
                                        $id = $fetch['id']; ?><tr>
                                        <td><?php echo ++$sn; ?></td>
                                        <td><?php echo getName($fetch['events_id']); ?></td>
                                        <td>Ksh <?php echo ($fetch['vip_fee']); ?></td>
                                        <td>Ksh <?php echo ($fetch['regular_fee']); ?></td>
                                        <td><?php $array = getTotalBookByType($id);
                                                echo (($array['vip'] - $array['vip_booked'])), " Ticket(s) Available for VIP Class" . "<hr/>" . ($array['regular'] - $array['regular_booked']) . " Ticket(s) Available for Regular Class";
                                                ?></td>
                                        <td><?php echo $fetch['date'], " / ", formatTime($fetch['time']); ?></td>

                                        <td>
                                            <form method="POST">
                                                <button type="button" class="btn btn-primary" data-toggle="modal"
                                                    data-target="#edit<?php echo $id ?>">
                                                    Edit
                                                </button> -

                                                <input type="hidden" class="form-control" name="del_events"
                                                    value="<?php echo $id ?>" required id="">
                                                <button type="submit"
                                                    onclick="return confirm('Are you sure about this?')"
                                                    class="btn btn-danger">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="edit<?php echo $id ?>">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Editing Ticket For</h4>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">


                                                    <form action="" method="post">
                                                        <input type="hidden" class="form-control" name="id"
                                                            value="<?php echo $id ?>" required id="">

                                                        <p>Event : <select class="form-control" name="events_id" required
                                                                id="">
                                                                <option value="">Select Event</option>
                                                                <?php
                                                                    $cons = connect()->query("SELECT * FROM events");
                                                                    while ($t = $cons->fetch_assoc()) {
                                                                        echo "<option " . ($fetch['events_id'] == $t['id'] ? 'selected="selected"' : '') . " value='" . $t['id'] . "'>" . $t['name'] . "</option>";
                                                                    }
                                                                    ?>
                                                            </select>
                                                        </p>

                                                        <p>
                                                            VIP Class Charge : <input class="form-control"
                                                                type="number" value="<?php echo $fetch['vip_fee'] ?>"
                                                                name="vip_fee" required id="">
                                                        </p>
                                                        <p>
                                                            Regular Class Charge : <input class="form-control"
                                                                type="number" value="<?php echo $fetch['regular_fee'] ?>"
                                                                name="regular_fee" required id="">
                                                        </p>
                                                        <p>
                                                            Date :
                                                            <input type="date" class="form-control"
                                                                onchange="check(this.value)" id="date"
                                                                placeholder="Date" name="date" required
                                                                value="<?php echo (date('Y-m-d', strtotime($fetch["date"]))) ?>">

                                                        </p>
                                                        <p>
                                                            Time : <input class="form-control" type="time"
                                                                value="<?php echo $fetch['time'] ?>" name="time"
                                                                required id="">
                                                        </p>
                                                        <p class="float-right"><input type="submit" name="edit"
                                                                class="btn btn-success" value="Edit ticket"></p>
                                                    </form>

                                                    <div class="modal-footer justify-content-between">
                                                        <button type="button" class="btn btn-default"
                                                            data-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                                <!-- /.modal-content -->
                                            </div>
                                            <!-- /.modal-dialog -->
                                        </div>
                                        <!-- /.modal -->
                                        <?php
                                    }
                                        ?>

                                </tbody>
                               
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
</div>
</div>
</div>
</section>
</div>

<div class="modal fade" id="add">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" align="center">
            <div class="modal-header">
                <h4 class="modal-title">Add New ticket</h4>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="post">
                    <div class="row">
                        <div class="col-sm-6">
                            Event(s) Without Ticket : <select class="form-control" name="events_id" required id="">
                                <option value="">Select Event</option>
                                <?php
                                $con = connect()->query("SELECT * FROM events WHERE id NOT IN (SELECT events_id FROM ticket)");
                                while ($row = $con->fetch_assoc()) {
                                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                                }
                                ?>
                            </select>

                        </div>

                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            VIP Class Charge : <input class="form-control" type="number" name="vip_fee" required
                                id="">
                        </div>
                        <div class="col-sm-6">

                            Regular Class Charge : <input class="form-control" type="number" name="regular_fee" required
                                id="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            Date : <input class="form-control" onchange="check(this.value)" type="date" name="date"
                                required id="date">
                        </div>
                        <div class="col-sm-6">

                            Time : <input class="form-control" type="time" name="time" required id="">
                        </div>
                    </div>
                    <hr>
                    <input type="submit" name="submit" class="btn btn-success" value="Add ticket"></p>
                </form>

                <script>
                function check(val) {
                    val = new Date(val);
                    var age = (Date.now() - val) / 31557600000;
                    var formDate = document.getElementById('date');
                    if (age > 0) {
                        alert("Past/Current Date not allowed");
                        formDate.value = "";
                        return false;
                    }
                }
                </script>

            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<?php

if (isset($_POST['submit'])) {
    $events_id = $_POST['events_id'];
    $vip_fee = $_POST['vip_fee'];
    $regular_fee = $_POST['regular_fee'];
    $date = $_POST['date'];
    $date = formatDate($date);
    // die($date);
    // $endDate = date('Y-m-d' ,strtotime( $data['automatic_until'] ));
    $time = $_POST['time'];
    if (!isset($events_id, $vip_fee, $regular_fee, $date, $time)) {
        alert("Fill Form Properly!");
    } else {
        $conn = connect();
        $ins = $conn->prepare("INSERT INTO `ticket`(`events_id`, `vip_fee`, `regular_fee`, `date`, `time`) VALUES (?,?,?,?,?)");
        $ins->bind_param("iiiss", $events_id, $vip_fee, $regular_fee, $date, $time);
        $ins->execute();
        alert("ticket Added!");
        load($_SERVER['PHP_SELF'] . "$me");
        
    }
}


if (isset($_POST['submit2'])) {
    $events_id = $_POST['events_id'];
    $vip_fee = $_POST['vip_fee'];
    $regular_fee = $_POST['regular_fee'];
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $every = $_POST['every'];

    $time = $_POST['time'];
    if (!isset( $events_id, $vip_fee, $regular_fee, $date, $time)) {
        alert("Fill Form Properly!");
    } else {


        $from_date = formatDate($from_date);
        $to_date = formatDate($to_date);
        $startDate = $from_date;
        $endDate = $to_date;
        $conn = connect();
        if ($every == 'Day') {
            for ($i = strtotime($startDate); $i <= strtotime($endDate); $i = strtotime('+1 day', $i)) {
                $date = date('d-m-Y', $i);
                $ins = $conn->prepare("INSERT INTO `ticket`(`events_id`, `date`, `time`, `vip_fee`, `regular_fee`) VALUES (?,?,?,?,?,?)");
                $ins->bind_param("iissii", $events_id,  $date, $time, $vip_fee, $regular_fee);
                $ins->execute();
            }
        } else {
            for ($i = strtotime($every, strtotime($startDate)); $i <= strtotime($endDate); $i = strtotime('+1 week', $i)) {
                $date = date('d-m-Y', $i);

                $ins = $conn->prepare("INSERT INTO `ticket`(`events_id`, `date`, `time`, `vip_fee`, `regular_fee`) VALUES (?,?,?,?,?,?)");
                $ins->bind_param("iissii", $events_id, $date, $time, $vip_fee, $regular_fee);
                $ins->execute();
            }
        }


        alert("tickets Added!");
        load($_SERVER['PHP_SELF'] . "$me");
    }
}


if (isset($_POST['edit'])) {
    $events_id = $_POST['events_id'];
    $vip_fee = $_POST['vip_fee'];
    $regular_fee = $_POST['regular_fee'];
    $date = $_POST['date'];
    $date = formatDate($date);
    $time = $_POST['time'];
    $id = $_POST['id'];
    if (!isset($events_id, $vip_fee, $regular_fee, $date, $time)) {
        alert("Fill Form Properly!");
    } else {
        $conn = connect();

        // Fetch event name based on events_id
        $event_query = $conn->query("SELECT name FROM events WHERE id = '$events_id'");
        $event_data = $event_query->fetch_assoc();
        $event_name = $event_data['name'];

        $ins = $conn->prepare("UPDATE `ticket` SET `events_id`=?,`date`=?,`time`=?,`vip_fee`=?,`regular_fee`=? WHERE id = ?");
        $ins->bind_param("isiiii", $events_id, $date, $time, $vip_fee, $regular_fee, $id);
        $ins->execute();

        $msg = "<html><body>";
        $msg .= "<p>Dear Customer,</p>";
        $msg .= "<p>We would like to inform you that there has been a change in the details of your ticket for the: $event_name.</p>";
        $msg .= "<ul>";
        $msg .= "<li>VIP Fee: Ksh $vip_fee </li>";
        $msg .= "<li>Regular Fee: Ksh $regular_fee </li>";
        $msg .= "<li>Date: $date </li>";
        $msg .= "<li>Time: $time </li>";
        $msg .= "</ul>";
        $msg .= "<p>If you have any questions or concerns, please feel free to contact us.</p>";
        $msg .= "<p>Thank you for your understanding.</p>";
        $msg .= "</body></html>";

        $e = $conn->query("SELECT attendee.email FROM attendee INNER JOIN bookedtickets ON bookedtickets.user_id = attendee.id WHERE bookedtickets.ticket_id = '$id' ");
        while($getter = $e->fetch_assoc()){
            @sendMail($getter['email'], "Change In Ticket Details", $msg);
        }
        alert("Ticket Modified!");
        load($_SERVER['PHP_SELF'] . "$me");
    }
}






if (isset($_POST['del_events'])) {
    $ticket_id = $_POST['del_events'];
    
    $con = connect();
    
    // Delete related records in the payment table
    $con->query("DELETE FROM payment WHERE ticket_id = '$ticket_id'");
    
    // Delete record in the ticket table
    $conn = $con->query("DELETE FROM ticket WHERE id = '$ticket_id'");
    
    if ($con->affected_rows < 1) {
        alert("ticket Could Not Be Deleted. Has Been Tied To Another Data!");
        load($_SERVER['PHP_SELF'] . "$me");
    } else {
        alert("ticket Deleted!");
        load($_SERVER['PHP_SELF'] . "$me");
    }
}

?>