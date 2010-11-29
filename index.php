<?php

require_once 'VictoryCMSTestRunner.php';
use VictoryCMSTesting\VictoryCMSTestRunner;

/*
 * This file is normally at web_root/www/test/index.php, so
 * our root path will then be ../../
 */
$testRunner = new VictoryCMSTestRunner('../../');
$testRunner->run();

?>