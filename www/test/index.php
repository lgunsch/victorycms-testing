<?php

/* Should normally resolve to the web document root,
 * ./../../ or you can just set it to "path/to/web_root/"
 */
$webRoot = ''.dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR;

/* Should normally resolve to the VictoryCMS test runner,
 * ./../../lib/VictoryCMSTestRunner.php or you can just set it to
 * "path/to/lib/VictoryCMSRunner.php"
 */
require_once $webRoot.'lib'.DIRECTORY_SEPARATOR.'VictoryCMSTestRunner.php';

use Vcms\VictoryCMSTestRunner;

$testRunner = new VictoryCMSTestRunner($webRoot.'config.json');
$testRunner->test();

?>