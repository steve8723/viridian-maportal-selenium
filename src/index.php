<?php
    session_start();
    if (!$_SESSION['viridian_loggedin'])
        header('Location: ./login/viridian_login.php');
    else 
        header('Location: viridianOrders.php');
?>
