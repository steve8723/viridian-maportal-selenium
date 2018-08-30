<?php
	session_start();
	require '../vendor/autoload.php';
	$phpunit = new PHPUnit_TextUI_TestRunner;
	$_SESSION['username'] =  isset($_REQUEST['username']) ? $_REQUEST['username'] : $_SESSION['username'];
	$_SESSION['pass'] =  isset($_REQUEST['pass']) ? $_REQUEST['pass'] : $_SESSION['pass'];
	try {
	    $test_results = $phpunit->dorun($phpunit->getTest('../tests/integration/', '', 'LoginTest.php'));
	} catch (PHPUnit_Framework_Exception $e) {
	    print $e->getMessage() . "\n";
	    die ("Unit tests failed.");
	}

?>