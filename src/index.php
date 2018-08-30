<?php
    session_start();
    if (!$_SESSION['loggedin'])
    header('Location: ./login/index.php');
    else {
        header('Location: goDispense.php');
    }
?>
