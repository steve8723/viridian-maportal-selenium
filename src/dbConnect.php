<?php
    include_once('../tests/integration/config.php');

	$servername = $config['servername'] ;
    $username = $config['db_username'];
    $password = $config['db_password'];
    $dbname = $config['dbname'];

    // Create DB connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);

    // Check DB connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
?>