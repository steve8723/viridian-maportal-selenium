<?php

$fp = fopen('results.json', 'w');
fwrite($fp, json_encode($_REQUEST['order']));
fclose($fp);