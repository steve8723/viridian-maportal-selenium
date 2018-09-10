<?php
	session_start();
	if (!isset($_SESSION['vg_username']) || !isset($_SESSION['vg_pass'])) {
		header('Location: login/vg_login.php');
	}
	$_SESSION['currentPatient'] = 0;
	if (isset($_REQUEST['patientID'])) {
		$_SESSION['currentPatient'] = $_REQUEST['patientID'];
	}

	require '../vendor/autoload.php';
	$phpunit = new PHPUnit_TextUI_TestRunner;
	try {
	    $test_results = $phpunit->dorun($phpunit->getTest('../tests/integration/', '', 'LoginTest.php'));
	} catch (PHPUnit_Framework_Exception $e) {
	    print $e->getMessage() . "\n";
	    die ("Unit tests failed.");
	}

?>