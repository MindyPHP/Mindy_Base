<?php

require_once __DIR__ . "/../vendor/aura/autoload/src.php";
$loader = new \Aura\Autoload\Loader();
$loader->add('Mindy\\', __DIR__ . '/../src/');
$loader->add('Mindy\\Core\\', __DIR__ . '/../vendor/mindy/core/src');
$loader->add('Mindy\\Helper\\', __DIR__ . '/../vendor/mindy/helper/src');
$loader->add('Mindy\\Utils\\', __DIR__ . '/../vendor/mindy/utils/src');
$loader->register();

defined('YII_ENABLE_EXCEPTION_HANDLER') or define('YII_ENABLE_EXCEPTION_HANDLER', false);
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', false);
defined('YII_DEBUG') or define('YII_DEBUG', true);
$_SERVER['SCRIPT_NAME'] = '/' . basename(__FILE__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

require_once(dirname(__FILE__) . '/TestApplication.php');
// Support PHPUnit <=3.7 and >=3.8
if (@include_once ('PHPUnit/Framework/TestCase.php') === false) { // <= 3.7
    require_once('src/Framework/TestCase.php'); // >= 3.8
}

// make sure non existing PHPUnit classes do not break with Yii autoloader
\Mindy\Base\Mindy::setPathOfAlias('tests', dirname(__FILE__));

class CTestCase extends PHPUnit_Framework_TestCase
{
}


class CActiveRecordTestCase extends CTestCase
{
}

new TestApplication();
