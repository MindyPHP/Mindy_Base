<?php

namespace Mindy\Base\App;

use Mindy\Base\Exception\Exception;
use Mindy\Base\Exception\HttpException;
use Mindy\Base\Mindy;
use Mindy\Console\ConsoleCommandRunner;
use Mindy\Helper\Alias;
use Mindy\Helper\Console;
use Mindy\Helper\Creator;

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 10/05/14.05.2014 15:20
 */
class Application extends BaseApplication
{
    private $_controllerPath;
    private $_controller;

    /**
     * @var array mapping from command name to command configurations.
     * Each command configuration can be either a string or an array.
     * If the former, the string should be the file path of the command class.
     * If the latter, the array must contain a 'class' element which specifies
     * the command's class name or {@link YiiBase::getPathOfAlias class path alias}.
     * The rest name-value pairs in the array are used to initialize
     * the corresponding command properties. For example,
     * <pre>
     * array(
     *   'email'=>array(
     *      'class'=>'path.to.Mailer',
     *      'interval'=>3600,
     *   ),
     *   'log'=>'path/to/LoggerCommand.php',
     * )
     * </pre>
     */
    public $commandMap = [];

    private $_commandPath;
    private $_runner;

    /**
     * Processes the current request.
     * It first resolves the request into controller and action,
     * and then creates the controller to perform the action.
     */
    public function processRequest()
    {
        $this->signal->send($this, 'onProcessRequest');

        if (Console::isCli()) {
            $exitCode = $this->_runner->run($_SERVER['argv']);
            if (is_int($exitCode)) {
                $this->end($exitCode);
            }
        } else {
            $route = $this->getUrlManager()->parseUrl($this->getRequest());
            $this->runController($route);
        }
    }

    public function setComponents($components, $merge = true)
    {
        foreach ($components as $name => $component) {
            $this->locator->set($name, $component);
        }
    }

    /**
     * @throws \Mindy\Base\Exception\Exception
     * @return \Modules\User\Models\User instance the user session information
     */
    public function getUser()
    {
        $auth = $this->getComponent('auth');
        if (!$auth) {
            throw new Exception("Auth component not initialized");
        }
        return $auth->getModel();
    }

    public function getComponent($id, $createIfNull = true)
    {
        return $this->locator->get($id, false);
    }

    /**
     * Creates the controller and performs the specified action.
     * @param string $route the route of the current request. See {@link createController} for more details.
     * @throws HttpException if the controller could not be created.
     */
    public function runController($route)
    {
        if (($ca = $this->createController($route)) !== null) {
            list($controller, $actionID, $params) = $ca;
            $_GET = array_merge($_GET, $params);
            $csrfExempt = $controller->getCsrfExempt();
            if(!Console::isCli() && !in_array($actionID, $csrfExempt) && $this->getComponent('request')->enableCsrfValidation) {
                $this->getComponent('request')->csrf->validate();
            }
            $oldController = $this->_controller;
            $this->_controller = $controller;
            $controller->init();
            $controller->run($actionID, $params);
            $this->_controller = $oldController;
        } else {
//            $msg = Mindy::t('yii', 'Unable to resolve the request "{route}".', [
//                '{route}' => $this->request->getPath()
//            ]);
            $msg = strtr('Unable to resolve the request "{route}".', [
                '{route}' => $this->request->getPath()
            ]);
            throw new HttpException(404, $msg);
        }
    }

    /**
     * Creates a controller instance based on a route.
     * The route should contain the controller ID and the action ID.
     * It may also contain additional GET variables. All these must be concatenated together with slashes.
     *
     * This method will attempt to create a controller in the following order:
     * <ol>
     * <li>If the first segment is found in {@link controllerMap}, the corresponding
     * controller configuration will be used to create the controller;</li>
     * <li>If the first segment is found to be a module ID, the corresponding module
     * will be used to create the controller;</li>
     * <li>Otherwise, it will search under the {@link controllerPath} to create
     * the corresponding controller. For example, if the route is "admin/user/create",
     * then the controller will be created using the class file "protected/controllers/admin/UserController.php".</li>
     * </ol>
     * @param \Aura\Router\Route $route the route of the request.
     * @param \Mindy\Base\Module $owner the module that the new controller will belong to. Defaults to null, meaning the application
     * instance is the owner.
     * @return array the controller instance and the action ID. Null if the controller class does not exist or the route is invalid.
     */
    public function createController($route, $owner = null)
    {
        if ($owner === null) {
            $owner = $this;
        }

        if ($route) {
            list($handler, $vars) = $route;
            list($className, $actionName) = $handler;
            $controller = Creator::createObject($className, time(), $owner === $this ? null : $owner, $this->getComponent('request'));
            return [$controller, $actionName, array_merge($_GET, $vars)];
        }

        return null;
    }

    /**
     * Parses a path info into an action ID and GET variables.
     * @param string $pathInfo path info
     * @return string action ID
     */
    protected function parseActionParams($pathInfo)
    {
        if (($pos = strpos($pathInfo, '/')) !== false) {
            $manager = $this->getUrlManager();
            $manager->parsePathInfo((string)substr($pathInfo, $pos + 1));
            $actionID = substr($pathInfo, 0, $pos);
            return $manager->caseSensitive ? $actionID : strtolower($actionID);
        } else {
            return $pathInfo;
        }
    }

    /**
     * @return \Mindy\Controller\Controller the currently active controller
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * @param \Mindy\Controller\Controller $value the currently active controller
     */
    public function setController($value)
    {
        $this->_controller = $value;
    }

    /**
     * @param string $value the directory that contains the controller classes.
     * @throws Exception if the directory is invalid
     */
    public function setControllerPath($value)
    {
        if (($this->_controllerPath = realpath($value)) === false || !is_dir($this->_controllerPath)) {
            throw new Exception(Mindy::t('yii', 'The controller path "{path}" is not a valid directory.', [
                '{path}' => $value
            ]));
        }
    }

    /**
     * The pre-filter for controller actions.
     * This method is invoked before the currently requested controller action and all its filters
     * are executed. You may override this method with logic that needs to be done
     * before all controller actions.
     * @param \Mindy\Controller\Controller $controller the controller
     * @param \Mindy\Controller\Action $action the action
     * @return boolean whether the action should be executed.
     */
    public function beforeControllerAction($controller, $action)
    {
        return true;
    }

    /**
     * The post-filter for controller actions.
     * This method is invoked after the currently requested controller action and all its filters
     * are executed. You may override this method with logic that needs to be done
     * after all controller actions.
     * @param \Mindy\Controller\Controller $controller the controller
     * @param \Mindy\Controller\Action $action the action
     */
    public function afterControllerAction($controller, $action)
    {
    }

    /**
     * Do not call this method. This method is used internally to search for a module by its ID.
     * @param string $id module ID
     * @return \Mindy\Base\Module the module that has the specified ID. Null if no module is found.
     */
    public function findModule($id)
    {
        if (($controller = $this->getController()) !== null && ($module = $controller->getModule()) !== null) {
            do {
                if (($m = $module->getModule($id)) !== null) {
                    return $m;
                }
            } while (($module = $module->getParentModule()) !== null);
        }
        if (($m = $this->getModule($id)) !== null) {
            return $m;
        }
    }

    /**
     * Creates the command runner instance.
     * @return ConsoleCommandRunner the command runner
     */
    public function createCommandRunner()
    {
        return new ConsoleCommandRunner;
    }

    /**
     * Displays the captured PHP error.
     * This method displays the error in console mode when there is
     * no active error handler.
     * @param integer $code error code
     * @param string $message error message
     * @param string $file error file
     * @param string $line error line
     */
    public function displayError($code, $message, $file, $line)
    {
        echo "PHP Error[$code]: $message\n";
        echo "    in file $file at line $line\n";
        $trace = debug_backtrace();
        // skip the first 4 stacks as they do not tell the error position
        if (count($trace) > 4) {
            $trace = array_slice($trace, 4);
        }
        foreach ($trace as $i => $t) {
            if (!isset($t['file'])) {
                $t['file'] = 'unknown';
            }
            if (!isset($t['line'])) {
                $t['line'] = 0;
            }
            if (!isset($t['function'])) {
                $t['function'] = 'unknown';
            }
            echo "#$i {$t['file']}({$t['line']}): ";
            if (isset($t['object']) && is_object($t['object'])) {
                echo get_class($t['object']) . '->';
            }
            echo "{$t['function']}()\n";
        }
    }

    /**
     * @return string the directory that contains the command classes. Defaults to 'protected/commands'.
     */
    public function getCommandPath()
    {
        $applicationCommandPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'Commands';
        if ($this->_commandPath === null && file_exists($applicationCommandPath)) {
            $this->setCommandPath($applicationCommandPath);
        }
        return $this->_commandPath;
    }

    /**
     * @param string $value the directory that contains the command classes.
     * @throws Exception if the directory is invalid
     */
    public function setCommandPath($value)
    {
        if (($this->_commandPath = realpath($value)) === false || !is_dir($this->_commandPath)) {
            throw new Exception(Mindy::t('yii', 'The command path "{path}" is not a valid directory.', [
                '{path}' => $value
            ]));
        }
    }

    /**
     * Returns the command runner.
     * @return \Mindy\Console\ConsoleCommandRunner the command runner.
     */
    public function getCommandRunner()
    {
        return $this->_runner;
    }

    /**
     * Returns the currently running command.
     * This is shortcut method for {@link CConsoleCommandRunner::getCommand()}.
     * @return \Mindy\Console\ConsoleCommand|null the currently active command.
     * @since 1.1.14
     */
    public function getCommand()
    {
        return $this->getCommandRunner()->getCommand();
    }

    /**
     * This is shortcut method for {@link CConsoleCommandRunner::setCommand()}.
     * @param \Mindy\Console\ConsoleCommand $value the currently active command.
     * @since 1.1.14
     */
    public function setCommand($value)
    {
        $this->getCommandRunner()->setCommand($value);
    }

    /**
     * Initializes the application.
     * This method overrides the parent implementation by preloading the 'request' component.
     */
    public function init()
    {
        if (Console::isCli()) {
            // fix for fcgi
            defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));

            if (!isset($_SERVER['argv'])) {
                die('This script must be run from the command line.');
            }
            $this->_runner = $this->createCommandRunner();
            $this->_runner->commands = $this->commandMap;
            $this->_runner->addCommands($this->getCommandPath());

            $this->_runner->addCommands(__DIR__ . '/../Commands');

            $env = @getenv('CONSOLE_COMMANDS');
            if (!empty($env)) {
                $this->_runner->addCommands($env);
            }

            foreach ($this->modules as $name => $settings) {
                $modulePath = Alias::get("Modules." . $name . ".Commands");
                if ($modulePath) {
                    $this->_runner->addCommands($modulePath);
                }
            }
        } else {
            // preload 'request' so that it has chance to respond to onBeginRequest event.
            $this->request;
        }
    }
}

