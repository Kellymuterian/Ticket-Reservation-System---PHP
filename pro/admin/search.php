<?php
if (!isset($file_access)) die("Direct File Access Denied");
$source = 'events';
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
                                Search</h3>
                            <div class='float-right'>
                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                    data-target="#add">
                                    New Search
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <?php
                            if (isset($_POST['submit'])) {
                                $ticket = $_POST['ticket'];
                                $conn = connect();
                                //Check if ticket exists
                                $check = $conn->query("SELECT * FROM bookedtickets WHERE code = '$ticket' ");
                                if ($check->num_rows != 1) {
                                    alert("Invalid Ticket Number Provided");
                                } else {
                                    $id = $check->fetch_assoc()['id'];
                                    $row = $conn->query("SELECT ticket.id as ticket_id, attendee.name as fullname, attendee.email as email, attendee.phone as phone, attendee.loc as loc, payment.amount as amount, payment.ref as ref, payment.date as payment_date, ticket.events_id as events_id, bookedtickets.code as code, bookedtickets.no as no, bookedtickets.class as class, bookedtickets.seat as seat, ticket.date as date, ticket.time as time FROM bookedtickets INNER JOIN ticket on bookedtickets.ticket_id = ticket.id INNER JOIN payment ON payment.id = bookedtickets.payment_id INNER JOIN attendee ON attendee.id = bookedtickets.user_id WHERE bookedtickets.id = '$id'")->fetch_assoc();
                                    echo '<table id="example1" style="align-items: stretch;" class="table table-hover w-100 table-bordered table-striped">';
                                    echo "
                                    <tr><td colspan='2' class='text-center'><img src='uploads/$row[loc]' class='img img-thumbnail' width='200' height='200'></td></tr>
        <tr><th>Full Name</th><td>$row[fullname]</td></tr>
        <tr><th>Email</th><td>$row[email]</td></tr>
        <tr><th>Phone</th><td>$row[phone]</td></tr>
        <tr><th>Event</th><td>" . getName($row['events_id']) . "</td></tr>
        <tr><th>Ticket Code</th><td>$row[code]</td></tr>
        <tr><th>Class</th><td>$row[class]</td></tr>
        <tr><th>Seat(s)</th><td>$row[seat]</td></tr>
        <tr><th>Event Date/TIme</th><td>" . date("D d, M Y", strtotime($row['date'])) . " / $row[time]</td></tr>
        <tr><th>Amount To Be Paid</th><td>Ksh $row[amount]</td></tr>
        <tr><th>Reservation Date</th><td>$row[payment_date]</td></tr>
        <tr><th>Reservation Ref</th><td>$row[ref]</td></tr>
        
        </table>";
                                }
                            }

                            ?>
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
                <h4 class="modal-title">Search Attendee With Ticket Code
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="post">

                    <table class="table table-bordered">
                        <tr>
                            <th>Enter Ticket Code</th>
                            <td><input type="text" class="form-control" name="ticket" required minlength="3" id=""></td>
                        </tr>
                        <td colspan="2">

                            <input class="btn btn-info" type="submit" value="Search" name='submit'>
                        </td>
                        </tr>
                    </table>
                </form>



            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>