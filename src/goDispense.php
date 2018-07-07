<?php
require '../vendor/autoload.php';
$phpunit = new PHPUnit_TextUI_TestRunner;
// exec('java.exe -jar selenium-server-standalone-3.13.0.jar');
try {
    $test_results = $phpunit->dorun($phpunit->getTest('../tests/integration/', '', 'LoginTest.php'));
} catch (PHPUnit_Framework_Exception $e) {
    print $e->getMessage() . "\n";
    die ("Unit tests failed.");
}

?>