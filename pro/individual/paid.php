<?php
if (!isset($file_access)) die("Direct File Access Denied");

if (isset($_POST['modify'])) {
    $pk = $_POST['pk'];
    $s = $_POST['s'];
    $db = connect();
    $stmt = $db->prepare("UPDATE bookedtickets SET ticket_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $s, $pk);
    if ($stmt->execute()) {
        echo "<script>alert('Modification Saved'); window.location.href = '$_SERVER[PHP_SELF]?page=paid';</script>";
        exit();
    } else {
        echo "<script>alert('Error Occurred While Trying To Save.');</script>";
    }
}
?>

<!-- Content Header (Page header) -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header alert-success">
                        <h5 class="m-0">Reserved Tickets</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered" id='example1'>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Ticket Code</th>
                                    <th>Event Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $conn = connect()->query("SELECT *, bookedtickets.id as id, payment.date as pd FROM `bookedtickets` INNER JOIN payment ON bookedtickets.payment_id = payment.id INNER JOIN ticket ON ticket.id = bookedtickets.ticket_id  WHERE payment.attendee_id = '$user_id' ORDER BY bookedtickets.id DESC");
                                $sn = 0;
                                while ($row = $conn->fetch_assoc()) {
                                    $id = $row['id'];
                                    $sn++;
                                    echo "<tr>
                                    <td>$sn</td>
                                    <td>" . $row['code'] . "</td>
                                    <td>" . $row['date'] . "</td>
                                    <td>" . ((isTicketActive($row['ticket_id']) ? '<span class="text-bold text-success">Active' : '<span class="text-bold text-danger">Expired')) . "</span></td>
                                    <td>
                                    <button type='button' class='btn btn-primary' data-toggle='modal'
                                    data-target='#view$id'>
                                    View
                                    </button>
                                    </td>
                                    </tr>";
                                ?>
                                <div class="modal fade" id="view<?php echo $id ?>">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title">Ticket(s) Details For - <?php echo $fullname; ?></h4>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <p><b>Event Name :</b><?php echo getName($row['events_id']); ?></p>
                                                <p><b>Class :</b><?php echo $row['class']; ?></p>
                                                <p><b>Ticket Code :</b><?php echo $row['code']?></p>
                                                <p><b>Seat Number :</b><?php echo $row['seat']; ?></p>
                                                <p><b>Reserved Date :</b><?php echo ($row['pd']); ?></p>
                                                <p><b>Amount Paid :</b> Ksh <?php echo ($row['amount']); ?></p>
                                                <p><b>Reservation Ref :</b><?php echo ($row['ref']); ?></p>

                                                <?php
                                                $fet = queryTicket('future');
                                                $msg = "";
                                                $output = "<option value=''>Choose One Or Skip To Leave As It Is</option>";
                                                if ($fet->num_rows < 1) $msg = "<span class='text-danger'>No Upcoming Tickets Yet</span>";
                                                while ($fetch = $fet->fetch_assoc()) {
                                                    $db_date = $fetch['date'];
                                                    if ($db_date == date('d-m-Y')) {
                                                        $db_time = $fetch['time'];
                                                        $current_time = date('H:i');
                                                        if ($current_time >= $db_time) {
                                                            continue;
                                                        }
                                                    }
                                                    $datetime = $fetch['date'] . " / " . formatTime($fetch['time']);
                                                    $output .= "<option value='$fetch[id]'>$fullname - $datetime</option>";
                                                }
                                                
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <br />
                </div>
            </div>
        </div>
    </div>
</section>
