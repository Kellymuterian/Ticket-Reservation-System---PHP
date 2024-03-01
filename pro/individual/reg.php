<?php
if (!isset($file_access)) die("Direct File Access Denied");
?>
<?php

$me = $_SESSION['user_id'];

?>

<div class="content">



    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><b>Reserve Event Tickets</b></h3>
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
                                <th>Availabe Tickets</th>
                                <th>Event Date</th>        
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $row = queryTicket('future');
                            if ($row->num_rows < 1) {
                                echo "<div class='alert alert-danger' role='alert'>Sorry, There are no Events at the moment! Please visit after some time.</div>";
                            } else {
                                $sn = 0;
                                while ($fetch = $row->fetch_assoc()) {
                                    $db_date = $fetch['date'];
                                    $db_time = $fetch['time'];
                                    if ($db_date == date('d-m-Y') && $db_time <= date('H:i')) {
                                        continue; // Skip events that have already passed today
                                    }

                                    $id = $fetch['id'];
                            ?>
                                    <tr>
                                        <td><?php echo ++$sn; ?></td>
                                        <td><?php echo getName($fetch['events_id']); ?></td>
                                        <td>Ksh <?php echo ($fetch['vip_fee']); ?></td>
                                        <td>Ksh <?php echo ($fetch['regular_fee']); ?></td>
                                        <td>
                                            <?php
                                            $array = getTotalBookByType($id);
                                            echo ($max_vip = ($array['vip'] - $array['vip_booked'])), " Ticket(s) Available for VIP Class" . "<hr/>" . ($max_regular = ($array['regular'] - $array['regular_booked'])) . " Ticket(s) Available for regular Class";
                                            ?>
                                        </td>
                                        <td><?php echo $db_date, " / ", formatTime($db_time); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#book<?php echo $id ?>">
                                                Reserve
                                            </button>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="book<?php echo $id ?>">
                                        <!-- Modal content here -->
                                        <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Reserve For <?php echo $fullname;


                                                                                    ?> </h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">


                                            <form action="<?php echo $_SERVER['PHP_SELF'] . "?loc=$id" ?>"
                                                method="post">
                                                <input type="hidden" class="form-control" name="id"
                                                    value="<?php echo $id ?>" required id="">

                                                    <p>Number of Tickets (Max allowed is 5) :
                                                        <input type="number" min='1' value="1" max="5" name="number" class="form-control" id="">
                                                    </p>

                                                <p>
                                                    Class : <select name="class" required class="form-control" id="">
                                                        <option value="">-- Select Class --</option>
                                                        <option value="vip">VIP Class (Ksh
                                                            <?php echo ($fetch['vip_fee']); ?>)</option>
                                                        <option value="regular">Regular Class (Ksh
                                                            <?php echo ($fetch['regular_fee']); ?>)</option>
                                                    </select>
                                                </p>
                                                <input type="submit" name="submit" class="btn btn-success"
                                                    value="Proceed">

                                            </form>

                                        </div>
                                        <!-- /.modal-content -->
                                    </div>
                                    <!-- /.modal-dialog -->
                                </div>
                                    </div>
                            <?php
                                    }
                                }
                                ?>
                        </tbody>
                                                        
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </section>

    </form>

</div>