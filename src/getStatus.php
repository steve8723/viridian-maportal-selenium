<?php
    include_once('dbConnect.php');
    $orderID = $_REQUEST['orderID'];

    $sql = "SELECT * FROM order_status WHERE order_id!=$orderID";
    $result = $conn->query($sql);
    $orderStatus = 'pending';

    if ($result) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            if ($row) $orderStatus = $row["order_status"];
        }
    }
    echo $orderStatus;
?>