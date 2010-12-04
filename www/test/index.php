<?php

/* Should normally resolve to the web document root,
 * ./../../lib/VictoryCMSTestRunner.php or you can just set it to
 * "path/to/web_root/test_lib/VictoryCMSRunner.php"
 */
$webRoot = ''.dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR;

$appPath = $webRoot.'app';
$libPath = $webRoot.'lib';

require_once $webRoot.'lib'.DIRECTORY_SEPARATOR.'VictoryCMSTestRunner.php';

use VictoryCMSTesting\VictoryCMSTestRunner;

$testRunner = new VictoryCMSTestRunner($libPath, $appPath);
$testRunner->run();

?>