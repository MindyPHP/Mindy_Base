<?php

require(__DIR__ . '/../vendor/autoload.php');

use Mindy\Base\Tests\TestApplication;

defined('MINDY_ENABLE_EXCEPTION_HANDLER') or define('MINDY_ENABLE_EXCEPTION_HANDLER', false);
defined('MINDY_ENABLE_ERROR_HANDLER') or define('MINDY_ENABLE_ERROR_HANDLER', false);
defined('MINDY_DEBUG') or define('MINDY_DEBUG', true);

$_SERVER['SCRIPT_NAME'] = '/' . basename(__FILE__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

new TestApplication();
