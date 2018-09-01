<?php
	session_start();

	require '../vendor/autoload.php';
	$phpunit = new PHPUnit_TextUI_TestRunner;
	try {
	    $test_results = $phpunit->dorun($phpunit->getTest('../tests/integration/', '', 'LoginTest.php'));
	} catch (PHPUnit_Framework_Exception $e) {
	    print $e->getMessage() . "\n";
	    die ("Unit tests failed.");
	}

?>