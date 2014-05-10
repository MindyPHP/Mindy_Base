<?php

use Mindy\Helper\Console;
use Mindy\Helper\Dumper;

Yii::setPathOfAlias('mindy', __DIR__);

$importAliases = [
    'mindy.m',
    'mindy.app.*',
    'mindy.db.*',
    'mindy.validators.*',
    'mindy.utils.*',
    'mindy.widget.*',
    'mindy.form.*',
    'mindy.form.inputs.*',
    'mindy.*',
];

foreach ($importAliases as $alias) {
    Yii::import($alias);
}

function d()
{
    $debug = debug_backtrace();
    $args = func_get_args();
    $data = array(
        'data' => $args,
        'debug' => array(
            'file' => $debug[0]['file'],
            'line' => $debug[0]['line'],
        )
    );
    Dumper::dump($data);
    die();
}

class Mindy extends Yii
{
    /**
     * @return string the version of Mindy
     */
    public static function getVersion()
    {
        return '0.9';
    }

    /**
     * Creates a Web application instance.
     * @param mixed $config application configuration.
     * If a string, it is treated as the path of the file that contains the configuration;
     * If an array, it is the actual configuration information.
     * Please make sure you specify the {@link CApplication::basePath basePath} property in the configuration,
     * which should point to the directory containing all application logic, template and data.
     * If not, the directory will be defaulted to 'protected'.
     * @return CWebApplication
     */
    public static function getInstance($config = null)
    {
        $app = self::createApplication('MApplication', $config);
        if(Console::isCli()) {
            // fix for fcgi
            defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
            $app->commandRunner->addCommands(YII_PATH . '/cli/commands');

            $env = @getenv('YII_CONSOLE_COMMANDS');
            if (!empty($env)) {
                $app->commandRunner->addCommands($env);
            }

            foreach ($app->modules as $name => $settings) {
                if ($modulePath = Yii::getPathOfAlias("application.modules.".$name)) {
                    $app->commandRunner->addCommands($modulePath . DIRECTORY_SEPARATOR . 'commands');
                }
            }
        }
        return $app;
    }

    public static function t($category, $message, $params = array(), $source = null, $language = null)
    {
        return parent::t($category, $message, $params, $source, $language);
    }
}
