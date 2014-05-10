<?php
use Mindy\Form\BaseForm;
use Mindy\Form\Renderer\MindyRenderer;
use Mindy\Helper\Console;
use Mindy\Orm\Model;

/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 10/05/14.05.2014 15:20
 */
class MApplication extends CApplication
{
    /**
     * @return string the route of the default controller, action or module. Defaults to 'site'.
     */
    public $defaultController = 'site';
    /**
     * @var mixed the application-wide layout. Defaults to 'main' (relative to {@link getLayoutPath layoutPath}).
     * If this is false, then no layout will be used.
     */

    /**
     * @var array mapping from controller ID to controller configurations.
     * Each name-value pair specifies the configuration for a single controller.
     * A controller configuration can be either a string or an array.
     * If the former, the string should be the class name or
     * {@link YiiBase::getPathOfAlias class path alias} of the controller.
     * If the latter, the array must contain a 'class' element which specifies
     * the controller's class name or {@link YiiBase::getPathOfAlias class path alias}.
     * The rest name-value pairs in the array are used to initialize
     * the corresponding controller properties. For example,
     * <pre>
     * array(
     *   'post'=>array(
     *      'class'=>'path.to.PostController',
     *      'pageTitle'=>'something new',
     *   ),
     *   'user'=>'path.to.UserController',
     * )
     * </pre>
     *
     * Note, when processing an incoming request, the controller map will first be
     * checked to see if the request can be handled by one of the controllers in the map.
     * If not, a controller will be searched for under the {@link getControllerPath default controller path}.
     */
    public $controllerMap = array();
    /**
     * @var array the configuration specifying a controller which should handle
     * all user requests. This is mainly used when the application is in maintenance mode
     * and we should use a controller to handle all incoming requests.
     * The configuration specifies the controller route (the first element)
     * and GET parameters (the rest name-value pairs). For example,
     * <pre>
     * array(
     *     'offline/notice',
     *     'param1'=>'value1',
     *     'param2'=>'value2',
     * )
     * </pre>
     * Defaults to null, meaning catch-all is not effective.
     */
    public $catchAllRequest;

    /**
     * @var string Namespace that should be used when loading controllers.
     * Default is to use global namespace.
     * @since 1.1.11
     */
    public $controllerNamespace;

    private $_controllerPath;
    private $_viewPath;
    private $_systemViewPath;
    private $_controller;

    public $baseController = 'CBaseController';

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
    public $commandMap = array();

    private $_commandPath;
    private $_runner;

    public function __construct($config = null)
    {
        Yii::setApplication($this);

        // set basePath at early as possible to avoid trouble
        if (is_string($config)) {
            $config = require($config);
        }

        if (isset($config['basePath'])) {
            $this->setBasePath($config['basePath']);
            unset($config['basePath']);
        } else {
            $this->setBasePath('protected');
        }

        Yii::setPathOfAlias('application', $this->getBasePath());
        Yii::setPathOfAlias('webroot', dirname($_SERVER['SCRIPT_FILENAME']));
        Yii::setPathOfAlias('ext', $this->getBasePath() . DIRECTORY_SEPARATOR . 'extensions');

        $this->preinit();

        $this->initSystemHandlers();
        $this->registerCoreComponents();

        $this->configure($config);
        $this->attachBehaviors($this->behaviors);
        $this->preloadComponents();

        // Mindy ORM
        Model::setConnection($this->getDb());
        // Mindy form
        BaseForm::setRenderer(new MindyRenderer());

        $this->init();
    }

    /**
     * Processes the current request.
     * It first resolves the request into controller and action,
     * and then creates the controller to perform the action.
     */
    public function processRequest()
    {
        if (Console::isCli()) {
            $exitCode = $this->_runner->run($_SERVER['argv']);
            if (is_int($exitCode)) {
                $this->end($exitCode);
            }
        } else {
            if (is_array($this->catchAllRequest) && isset($this->catchAllRequest[0])) {
                $route = $this->catchAllRequest[0];
                foreach (array_splice($this->catchAllRequest, 1) as $name => $value)
                    $_GET[$name] = $value;
            } else
                $route = $this->getUrlManager()->parseUrl($this->getRequest());
            $this->runController($route);
        }
    }

    /**
     * Registers the core application components.
     * This method overrides the parent implementation by registering additional core components.
     * @see setComponents
     */
    protected function registerCoreComponents()
    {
        parent::registerCoreComponents();

        $components = array(
            'session' => array(
                'class' => 'CHttpSession',
            ),
            'user' => array(
                'class' => 'CWebUser',
            ),
            'widgetFactory' => array(
                'class' => 'CWidgetFactory',
            ),
        );

        $this->setComponents($components);
    }

    /**
     * @return CHttpSession the session component
     */
    public function getSession()
    {
        return $this->getComponent('session');
    }

    /**
     * @return CWebUser the user session information
     */
    public function getUser()
    {
        return $this->getComponent('auth')->getModel();
    }

    /**
     * Returns the view renderer.
     * If this component is registered and enabled, the default
     * view rendering logic defined in {@link CBaseController} will
     * be replaced by this renderer.
     * @return IViewRenderer the view renderer.
     */
    public function getViewRenderer()
    {
        return $this->getComponent('viewRenderer');
    }

    /**
     * Returns the widget factory.
     * @return IWidgetFactory the widget factory
     * @since 1.1
     */
    public function getWidgetFactory()
    {
        return $this->getComponent('widgetFactory');
    }

    /**
     * Creates the controller and performs the specified action.
     * @param string $route the route of the current request. See {@link createController} for more details.
     * @throws CHttpException if the controller could not be created.
     */
    public function runController($route)
    {
        if (($ca = $this->createController($route)) !== null) {
            list($controller, $actionID) = $ca;
            $oldController = $this->_controller;
            $this->_controller = $controller;
            $controller->init();
            $controller->run($actionID);
            $this->_controller = $oldController;
        } else
            throw new CHttpException(404, Yii::t('yii', 'Unable to resolve the request "{route}".',
                array('{route}' => $route === '' ? $this->defaultController : $route)));
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
     * @param string $route the route of the request.
     * @param CWebModule $owner the module that the new controller will belong to. Defaults to null, meaning the application
     * instance is the owner.
     * @return array the controller instance and the action ID. Null if the controller class does not exist or the route is invalid.
     */
    public function createController($route, $owner = null)
    {
        if ($owner === null)
            $owner = $this;
        if (($route = trim($route, '/')) === '')
            $route = $owner->defaultController;
        $caseSensitive = $this->getUrlManager()->caseSensitive;

        $route .= '/';
        while (($pos = strpos($route, '/')) !== false) {
            $id = substr($route, 0, $pos);
            if (!preg_match('/^\w+$/', $id))
                return null;
            if (!$caseSensitive)
                $id = strtolower($id);
            $route = (string)substr($route, $pos + 1);
            if (!isset($basePath)) // first segment
            {
                if (isset($owner->controllerMap[$id])) {
                    return array(
                        Yii::createComponent($owner->controllerMap[$id], $id, $owner === $this ? null : $owner),
                        $this->parseActionParams($route),
                    );
                }

                if (($module = $owner->getModule($id)) !== null)
                    return $this->createController($route, $module);

                $basePath = $owner->getControllerPath();
                $controllerID = '';
            } else
                $controllerID .= '/';
            $className = ucfirst($id) . 'Controller';
            $classFile = $basePath . DIRECTORY_SEPARATOR . $className . '.php';

            if ($owner->controllerNamespace !== null)
                $className = $owner->controllerNamespace . '\\' . $className;

            if (is_file($classFile)) {
                if (!class_exists($className, false))
                    require($classFile);

                // Fuck yii hardcode
                if (class_exists($className, false) && is_subclass_of($className, $this->baseController)) {
                    $id[0] = strtolower($id[0]);
                    return array(
                        new $className($controllerID . $id, $owner === $this ? null : $owner),
                        $this->parseActionParams($route),
                    );
                }
                return null;
            }
            $controllerID .= $id;
            $basePath .= DIRECTORY_SEPARATOR . $id;
        }
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
        } else
            return $pathInfo;
    }

    /**
     * @return CController the currently active controller
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * @param CController $value the currently active controller
     */
    public function setController($value)
    {
        $this->_controller = $value;
    }

    /**
     * @return string the directory that contains the controller classes. Defaults to 'protected/controllers'.
     */
    public function getControllerPath()
    {
        if ($this->_controllerPath !== null)
            return $this->_controllerPath;
        else
            return $this->_controllerPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'controllers';
    }

    /**
     * @param string $value the directory that contains the controller classes.
     * @throws CException if the directory is invalid
     */
    public function setControllerPath($value)
    {
        if (($this->_controllerPath = realpath($value)) === false || !is_dir($this->_controllerPath))
            throw new CException(Yii::t('yii', 'The controller path "{path}" is not a valid directory.',
                array('{path}' => $value)));
    }

    /**
     * @return string the root directory of view files. Defaults to 'protected/views'.
     */
    public function getViewPath()
    {
        if ($this->_viewPath !== null)
            return $this->_viewPath;
        else
            return $this->_viewPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'views';
    }

    /**
     * @param string $path the root directory of view files.
     * @throws CException if the directory does not exist.
     */
    public function setViewPath($path)
    {
        if (($this->_viewPath = realpath($path)) === false || !is_dir($this->_viewPath))
            throw new CException(Yii::t('yii', 'The view path "{path}" is not a valid directory.',
                array('{path}' => $path)));
    }

    /**
     * @return string the root directory of system view files. Defaults to 'protected/views/system'.
     */
    public function getSystemViewPath()
    {
        if ($this->_systemViewPath !== null)
            return $this->_systemViewPath;
        else
            return $this->_systemViewPath = $this->getViewPath() . DIRECTORY_SEPARATOR . 'system';
    }

    /**
     * @param string $path the root directory of system view files.
     * @throws CException if the directory does not exist.
     */
    public function setSystemViewPath($path)
    {
        if (($this->_systemViewPath = realpath($path)) === false || !is_dir($this->_systemViewPath))
            throw new CException(Yii::t('yii', 'The system view path "{path}" is not a valid directory.',
                array('{path}' => $path)));
    }

    /**
     * @return string the root directory of layout files. Defaults to 'protected/views/layouts'.
     */
    public function getLayoutPath()
    {
        if ($this->_layoutPath !== null)
            return $this->_layoutPath;
        else
            return $this->_layoutPath = $this->getViewPath() . DIRECTORY_SEPARATOR . 'layouts';
    }

    /**
     * @param string $path the root directory of layout files.
     * @throws CException if the directory does not exist.
     */
    public function setLayoutPath($path)
    {
        if (($this->_layoutPath = realpath($path)) === false || !is_dir($this->_layoutPath))
            throw new CException(Yii::t('yii', 'The layout path "{path}" is not a valid directory.',
                array('{path}' => $path)));
    }

    /**
     * The pre-filter for controller actions.
     * This method is invoked before the currently requested controller action and all its filters
     * are executed. You may override this method with logic that needs to be done
     * before all controller actions.
     * @param CController $controller the controller
     * @param CAction $action the action
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
     * @param CController $controller the controller
     * @param CAction $action the action
     */
    public function afterControllerAction($controller, $action)
    {
    }

    /**
     * Do not call this method. This method is used internally to search for a module by its ID.
     * @param string $id module ID
     * @return CWebModule the module that has the specified ID. Null if no module is found.
     */
    public function findModule($id)
    {
        if (($controller = $this->getController()) !== null && ($module = $controller->getModule()) !== null) {
            do {
                if (($m = $module->getModule($id)) !== null)
                    return $m;
            } while (($module = $module->getParentModule()) !== null);
        }
        if (($m = $this->getModule($id)) !== null)
            return $m;
    }

    /**
     * Creates the command runner instance.
     * @return CConsoleCommandRunner the command runner
     */
    protected function createCommandRunner()
    {
        return new CConsoleCommandRunner;
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
        if (count($trace) > 4)
            $trace = array_slice($trace, 4);
        foreach ($trace as $i => $t) {
            if (!isset($t['file']))
                $t['file'] = 'unknown';
            if (!isset($t['line']))
                $t['line'] = 0;
            if (!isset($t['function']))
                $t['function'] = 'unknown';
            echo "#$i {$t['file']}({$t['line']}): ";
            if (isset($t['object']) && is_object($t['object']))
                echo get_class($t['object']) . '->';
            echo "{$t['function']}()\n";
        }
    }

    /**
     * Displays the uncaught PHP exception.
     * This method displays the exception in console mode when there is
     * no active error handler.
     * @param Exception $exception the uncaught exception
     */
    public function displayException($exception)
    {
        echo $exception;
    }

    /**
     * @return string the directory that contains the command classes. Defaults to 'protected/commands'.
     */
    public function getCommandPath()
    {
        $applicationCommandPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'commands';
        if ($this->_commandPath === null && file_exists($applicationCommandPath))
            $this->setCommandPath($applicationCommandPath);
        return $this->_commandPath;
    }

    /**
     * @param string $value the directory that contains the command classes.
     * @throws CException if the directory is invalid
     */
    public function setCommandPath($value)
    {
        if (($this->_commandPath = realpath($value)) === false || !is_dir($this->_commandPath))
            throw new CException(Yii::t('yii', 'The command path "{path}" is not a valid directory.',
                array('{path}' => $value)));
    }

    /**
     * Returns the command runner.
     * @return CConsoleCommandRunner the command runner.
     */
    public function getCommandRunner()
    {
        return $this->_runner;
    }

    /**
     * Returns the currently running command.
     * This is shortcut method for {@link CConsoleCommandRunner::getCommand()}.
     * @return CConsoleCommand|null the currently active command.
     * @since 1.1.14
     */
    public function getCommand()
    {
        return $this->getCommandRunner()->getCommand();
    }

    /**
     * This is shortcut method for {@link CConsoleCommandRunner::setCommand()}.
     * @param CConsoleCommand $value the currently active command.
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
    protected function init()
    {
        parent::init();
        if (Console::isCli()) {
            if (!isset($_SERVER['argv'])) {
                die('This script must be run from the command line.');
            }
            $this->_runner = $this->createCommandRunner();
            $this->_runner->commands = $this->commandMap;
            $this->_runner->addCommands($this->getCommandPath());
        } else {
            // preload 'request' so that it has chance to respond to onBeginRequest event.
            $this->getRequest();
        }
    }
}

