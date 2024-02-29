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
                                All eventss</h3>
                            <div class='float-right'>
                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                    data-target="#add">
                                    Add New events &#128645;
                                </button></div>
                        </div>

                        <div class="card-body">

                            <table id="example1" style="align-items: stretch;"
                                class="table table-hover w-100 table-bordered table-striped<?php //
                                                                                                                                            ?>">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>events Name</th>
                                        <th>VIP Class Seat</th>
                                        <th>REGULAR Class Seat</th>
                                        <th style="width: 30%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $row = $conn->query("SELECT * FROM events");
                                    if ($row->num_rows < 1) echo "No Records Yet";
                                    $sn = 0;
                                    while ($fetch = $row->fetch_assoc()) {
                                        $id = $fetch['id'];
                                    ?>

                                    <tr>
                                        <td><?php echo ++$sn; ?></td>
                                        <td><?php echo $fullname = $fetch['name']; ?></td>
                                        <td><?php echo $fetch['vip_seat']; ?></td>
                                        <td><?php echo $fetch['regular_seat']; ?></td>
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
                                                    <h4 class="modal-title">Editing <?php echo $fullname;


                                                                                        ?></h4>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="" method="post">
                                                        <input type="hidden" class="form-control" name="id"
                                                            value="<?php echo $id ?>" required id="">
                                                        <p>Event Name : <input type="text" class="form-control"
                                                                name="name" value="<?php echo $fetch['name'] ?>"
                                                                required minlength="3" id=""></p>
                                                        <p>VIP Class Capacity : <input type="number" min='0'
                                                                class="form-control"
                                                                value="<?php echo $fetch['vip_seat'] ?>"
                                                                name="vip_seat" required id="">
                                                        </p>
                                                        <p> Class Capacity : <input type="number" min='0'
                                                                class="form-control"
                                                                value="<?php echo $fetch['regular_seat'] ?>"
                                                                name="regular_seat" required id="">
                                                        </p>
                                                        <p>

                                                            <input class="btn btn-info" type="submit" value="Edit Event"
                                                                name='edit'>
                                                        </p>
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
                <h4 class="modal-title">Add New Event &#128646;
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="post">

                    <table class="table table-bordered">
                        <tr>
                            <th>Event Name</th>
                            <td><input type="text" class="form-control" name="name" required minlength="3" id=""></td>
                        </tr>
                        <tr>
                            <th>VIP Class Capacity</th>
                            <td><input type="number" min='0' class="form-control" name="vip_seat" required id=""></td>
                        </tr>
                        <tr>
                            <th>REGULAR Class Capacity</th>
                            <td><input type="number" min='0' class="form-control" name="regular_seat" required id="">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">

                                <input class="btn btn-info" type="submit" value="Add Event" name='submit'>
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

<?php

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $vip_seat = $_POST['vip_seat'];
    $regular_seat = $_POST['regular_seat'];
    if (!isset($name, $vip_seat, $regular_seat)) {
        alert("Fill Form Properly!");
    } else {
        $conn = connect();
        //Check if Event exists
        $check = $conn->query("SELECT * FROM events WHERE name = '$name' ")->num_rows;
        if ($check) {
            alert("Event exists");
        } else {
            $ins = $conn->prepare("INSERT INTO events (name, vip_seat, regular_seat) VALUES (?,?,?)");
            $ins->bind_param("sss", $name, $vip_seat, $regular_seat);
            $ins->execute();
            alert("Event Added!");
            load($_SERVER['PHP_SELF'] . "$me");
        }
    }
}

if (isset($_POST['edit'])) {
    $name = $_POST['name'];
    $vip_seat = $_POST['vip_seat'];
    $regular_seat = $_POST['regular_seat'];
    $id = $_POST['id'];
    if (!isset($name, $vip_seat, $regular_seat)) {
        alert("Fill Form Properly!");
    } else {
        $conn = connect();
        //Check if events exists
        $check = $conn->query("SELECT * FROM events WHERE name = '$name' ")->num_rows;
        if ($check == 2) {
            alert("Event name exists");
        } else {
            $ins = $conn->prepare("UPDATE events SET name = ?, vip_seat = ?, regular_seat = ? WHERE id = ?");
            $ins->bind_param("sssi", $name, $vip_seat, $regular_seat, $id);
            $ins->execute();
            alert("Event Modified!");
            load($_SERVER['PHP_SELF'] . "$me");
        }
    }
}

if (isset($_POST['del_events'])) {
    $eventId = $_POST['del_events'];
    $con = connect();

    // Check if there are associated tickets
    $checkTicketsQuery = "SELECT COUNT(*) AS ticket_count FROM ticket WHERE events_id = '$eventId'";
    $result = $con->query($checkTicketsQuery);
    $row = $result->fetch_assoc();
    $ticketCount = $row['ticket_count'];

    if ($ticketCount > 0) {
        alert("Event cannot be deleted! You will need to delete the associated tickets first.");
    } else {
        // Delete the event
        $deleteQuery = "DELETE FROM events WHERE id = '$eventId'";
        $conn = $con->query($deleteQuery);

        if ($con->affected_rows < 1) {
            alert("Event Could Not Be Deleted. This Event Has Been Tied To Another Data!");
        } else {
            alert("Event Deleted!");
        }
    }

    load($_SERVER['PHP_SELF'] . "$me");
}



?>