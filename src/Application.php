<?php

declare(strict_types = 1);

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 09/06/14.06.2014 17:22
 */

namespace Mindy\Base;

use Mindy\Console\ConsoleCommandRunner;
use Mindy\ErrorHandler\ErrorHandler;
use Mindy\Di\ServiceLocator;
use Mindy\Exception\Exception;
use Mindy\Exception\HttpException;
use Mindy\Helper\Alias;
use Mindy\Helper\Creator;
use Mindy\Helper\Traits\Accessors;
use Mindy\Helper\Traits\Configurator;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * @property string $id The unique identifier for the application.
 * @property string $basePath The root directory of the application. Defaults to 'protected'.
 * @property string $runtimePath The directory that stores runtime files. Defaults to 'protected/runtime'.
 * @property string $extensionPath The directory that contains all extensions. Defaults to the 'extensions' directory under 'protected'.
 * @property string $timeZone The time zone used by this application.
 * @property \Mindy\Event\EventManager $signal The event system component.
 * @property \Mindy\Query\ConnectionManager $db The database connection.
 * @property \Modules\Auth\Components\Auth $auth The auth component.
 * @property \Mindy\Logger\LoggerManager $logger The logging component.
 * @property \Mindy\ErrorHandler\ErrorHandler $errorHandler The error handler application component.
 * @property \Mindy\Security\SecurityManager $securityManager The security manager application component.
 * @property \Mindy\StatePersister\StatePersister $statePersister The state persister application component.
 * @property \Mindy\Cache\Cache $cache The cache application component. Null if the component is not enabled.
 * @property \Mindy\Mail\Mailer|\Modules\Mail\Components\DbMailer $mail The mail application component. Null if the component is not enabled.
 * @property \Mindy\Locale\Locale $translate The application translate component.
 * @property \Mindy\Http\Http $request The request component.
 * @property \Mindy\Template\Renderer $template The template engine component.
 * @property \Mindy\Router\UrlManager $urlManager The URL manager component.
 * @property \Mindy\Controller\BaseController $controller The currently active controller. Null is returned in this base class.
 * @property string $baseUrl The relative URL for the application.
 * @property string $homeUrl The homepage URL.
 */
class Application
{
    use GlobalStateAware;
    use Configurator;
    use Accessors;
    use ParamsTrait;

    /**
     * @var array
     */
    public $managers = [];
    /**
     * @var array
     */
    public $admins = [];
    /**
     * @var string the application name. Defaults to 'My Application'.
     */
    public $name = 'My Application';
    /**
     * @var array
     */
    public $locale = [];
    /**
     * @var array the IDs of the application components that should be preloaded.
     */
    public $preload = [];
    /**
     * @var array
     */
    public $commandMap = [];
    /**
     * @var string
     */
    private $_id;
    /**
     * @var string
     */
    private $_basePath;
    /**
     * @var string
     */
    private $_modulePath;
    /**
     * @var string
     */
    private $_runtimePath;
    /**
     * @var string
     */
    private $_homeUrl;
    /**
     * @var ConsoleCommandRunner
     */
    private $_consoleRunner;
    /**
     * @var string
     */
    private $_commandPath;
    /**
     * @var
     */
    private $_controller;
    /**
     * @var ServiceLocator
     */
    private $_componentLocator;
    /**
     * @var ServiceLocator
     */
    private $_moduleLocator;

    /**
     * Constructor.
     * @param array|string $config application configuration.
     * If a string, it is treated as the path of the file that contains the configuration;
     * If an array, it is the actual configuration information.
     * Please make sure you specify the {@link getBasePath basePath} property in the configuration,
     * which should point to the directory containing all application logic, template and data.
     * If not, the directory will be defaulted to 'protected'.
     * @throws \Mindy\Exception\Exception
     */
    public function __construct($config = null)
    {
        $this->registerErrorHandler();

        Mindy::setApplication($this);

        // set basePath at early as possible to avoid trouble
        if (is_string($config)) {
            $config = require($config);
        } else if ($config === null) {
            $config = [];
        }

        if (isset($config['basePath'])) {
            $this->setBasePath($config['basePath']);
            unset($config['basePath']);
        } else {
            $this->setBasePath('protected');
        }

        if (!is_array($config)) {
            throw new Exception('Unknown config type');
        }

        $this->initAliases($config);

        $this->getComponentLocator()->set('translate', isset($config['locale']) ? array_merge($config['locale'], [
            'class' => '\Mindy\Locale\Locale',
        ]) : ['class' => '\Mindy\Locale\Locale']);

        $this->preinit();
        $this->configure($config);
        $this->preloadComponents();

        /**
         * Raise preConfigure method
         * on every iterable module
         */
        $this->init();
    }

    /**
     * @param array $components
     * @return $this
     */
    public function setComponents(array $components)
    {
        $this->getComponentLocator()->setComponents(array_merge($this->getCoreComponents(), $components));
        return $this;
    }

    /**
     * @param array $modules
     * @return $this
     */
    public function setModules(array $modules)
    {
        $modulesDefinitions = [];
        foreach ($modules as $module => $config) {
            if (is_numeric($module) && is_string($config)) {
                $module = $config;
                $className = $this->getDefaultModuleClassNamespace($config);
                $config = ['class' => $className];
            } else if (is_array($config)) {
                if (isset($config['class'])) {
                    $className = $config['class'];
                } else {
                    $className = $this->getDefaultModuleClassNamespace($module);
                    $config['class'] = $className;
                }
            } else {
                throw new RuntimeException('Unknown module config format');
            }

            $path = Alias::get('modules') . DIRECTORY_SEPARATOR . ucfirst($module);
            Alias::set(ucfirst($module), $path);
            $modulesDefinitions[ucfirst($module)] = function () use ($className, $module, $config) {
                return Creator::createObject($config, $module);
            };
            call_user_func([$className, 'preConfigure']);
        }

        $this->getModuleLocator()->setComponents($modulesDefinitions);
        return $this;
    }

    protected function getComponentLocator() : ServiceLocator
    {
        if ($this->_componentLocator === null) {
            $this->_componentLocator = new ServiceLocator();
        }
        return $this->_componentLocator;
    }

    protected function getModuleLocator() : ServiceLocator
    {
        if ($this->_moduleLocator === null) {
            $this->_moduleLocator = new ServiceLocator();
        }
        return $this->_moduleLocator;
    }

    /**
     * Init system aliases
     *
     * Defines the root aliases.
     * @param array $mappings list of aliases to be defined. The array keys are root aliases,
     * while the array values are paths or aliases corresponding to the root aliases.
     * For example,
     * <pre>
     * array(
     *    'alias'=>'absolute/path'
     * )
     * </pre>
     *
     * @param array $config
     * @throws Exception
     */
    protected function initAliases($config)
    {
        Alias::set('App', $this->getBasePath());
        Alias::set('app', $this->getBasePath());
        Alias::set('application', $this->getBasePath());

        Alias::set('Modules', $this->getBasePath() . DIRECTORY_SEPARATOR . 'Modules');

        if (isset($config['webPath'])) {
            $path = realpath($config['webPath']);
            if (!is_dir($path)) {
                throw new Exception("Incorrent web path " . $config['webPath']);
            }
            Alias::set('www', $path);
            Alias::set('webroot', $path);
            unset($config['webPath']);
        } else {
            Alias::set('www', realpath(dirname($_SERVER['SCRIPT_FILENAME'])));
            Alias::set('webroot', dirname($_SERVER['SCRIPT_FILENAME']));
        }

        if (isset($config['aliases'])) {
            foreach ($config['aliases'] as $name => $alias) {
                if (($path = Alias::get($alias)) !== false) {
                    Alias::set($name, $path);
                } else {
                    Alias::set($name, $alias);
                }
            }
            unset($config['aliases']);
        }
    }

    /**
     * @param $name
     * @return string module namespace
     */
    protected function getDefaultModuleClassNamespace(string $name) : string
    {
        return '\\Modules\\' . ucfirst($name) . '\\' . ucfirst($name) . 'Module';
    }

    /**
     * Preinitializes the module.
     * This method is called at the beginning of the module constructor.
     * You may override this method to do some customized preinitialization work.
     * Note that at this moment, the module is not configured yet.
     * @see init
     */
    protected function preinit()
    {
    }

    /**
     * Loads static application components.
     */
    protected function preloadComponents()
    {
        if (php_sapi_name() !== 'cli') {
            $this->getComponent('request');
        }

        foreach ($this->preload as $id) {
            $this->getComponent($id);
        }
    }

    /**
     * Retrieves the named application module.
     * The module has to be declared in {@link modules}. A new instance will be created
     * when calling this method with the given ID for the first time.
     * @param string $id application module ID (case-sensitive)
     * @return Module the module instance, null if the module is disabled or does not exist.
     */
    public function getModule(string $id) : Module
    {
        $id = ucfirst($id);
        return $this->getModuleLocator()->get($id);
    }

    /**
     * Returns a value indicating whether the specified module is installed.
     * @param string $id the module ID
     * @return boolean whether the specified module is installed.
     * @since 1.1.2
     */
    public function hasModule($id)
    {
        return $this->getModuleLocator()->has($id);
    }

    /**
     * Returns the configuration of the currently installed modules.
     * @return array the configuration of the currently installed modules (module ID => configuration)
     */
    public function getModules()
    {
        return $this->getModuleLocator()->getComponents();
    }

    /**
     * Returns the directory that contains the application modules.
     * @return string the directory that contains the application modules. Defaults to the 'modules' subdirectory of {@link basePath}.
     */
    public function getModulePath()
    {
        if ($this->_modulePath !== null) {
            return $this->_modulePath;
        } else {
            return $this->_modulePath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'Modules';
        }
    }

    /**
     * Sets the directory that contains the application modules.
     * @param string $value the directory that contains the application modules.
     * @throws Exception if the directory is invalid
     */
    public function setModulePath($value)
    {
        if (($this->_modulePath = realpath($value)) === false || !is_dir($this->_modulePath)) {
            throw new Exception('The module path "' . $value . '" is not a valid directory.');
        }
    }

    public function __call($name, $args)
    {
        if (empty($args) && strpos($name, 'get') === 0) {
            $tmp = lcfirst(str_replace('get', '', $name));

            if ($this->hasComponent($tmp)) {
                return $this->getComponent($tmp);
            }
        }

        return $this->__callInternal($name, $args);
    }

    public function __get($name)
    {
        if ($this->getComponentLocator()->has($name)) {
            return $this->getComponentLocator()->get($name);
        } else {
            $getter = 'get' . $name;
            if (method_exists($this, $getter)) {
                return $this->$getter();
            } elseif (method_exists($this, 'set' . $name)) {
                throw new Exception('Getting write-only property: ' . get_class($this) . '::' . $name);
            } else {
                throw new Exception('Getting unknown property: ' . get_class($this) . '::' . $name);
            }
        }
    }

    /**
     * Runs the application.
     * This method loads static application components. Derived classes usually overrides this
     * method to do more application-specific tasks.
     * Remember to call the parent implementation so that static application components are loaded.
     */
    public function run()
    {
        register_shutdown_function([$this, 'end'], 0, false);

        if (php_sapi_name() === 'cli') {
            $this->runCli();
        } else {
            $this->runWeb();
        }
    }

    /**
     * @return ConsoleCommandRunner
     */
    public function getCommandRunner()
    {
        if ($this->_consoleRunner === null) {
            $this->_consoleRunner = new ConsoleCommandRunner;
        }
        return $this->_consoleRunner;
    }

    /**
     * @return \Mindy\Controller\BaseController
     */
    public function getController()
    {
        return $this->_controller;
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
            throw new Exception(Mindy::t('base', 'The command path "{path}" is not a valid directory.', [
                '{path}' => $value
            ]));
        }
    }

    protected function runCli()
    {
        // fix for fcgi
        defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));

        if (!isset($_SERVER['argv'])) {
            die('This script must be run from the command line.');
        }
        $runner = $this->getCommandRunner();
        $runner->setCommandMap($this->commandMap);
        $runner->addCommands($this->getCommandPath());

        if ($env = @getenv('CONSOLE_COMMANDS')) {
            $runner->addCommands($env);
        }

        foreach ($this->modules as $name => $settings) {
            $modulePath = Alias::get("Modules." . $name . ".Commands");
            if ($modulePath) {
                $runner->addCommands($modulePath, $name);
            }
        }

        $runner->run($_SERVER['argv']);
    }

    protected function runWeb()
    {
        $route = $this->parseRoute();
        $controllerParams = $this->createController($route);
        if ($controllerParams === null) {
            throw new HttpException(404, Mindy::t('base', 'Unable to resolve the request "{route}".', [
                '{route}' => $this->request->getPath()
            ]));
        }

        /** @var \Mindy\Controller\BaseController $controller */
        list($controller, $actionID, $routeParams) = $controllerParams;

        // Fix for compatibility
        $_GET = array_merge($_GET, $routeParams);

        $queryParams = $this->request->getRequest()->getQueryParams();
        $newRequest = $this->request->getRequest()->withQueryParams(array_merge($queryParams, $routeParams));
        $this->request->setRequest($newRequest);

        $excludeCsrfVerify = $controller->getCsrfExempt();
        if (in_array($actionID, $excludeCsrfVerify) === false) {
            if ($this->request->enableCsrfValidation && $this->request->csrf->isValid() === false) {
                throw new HttpException(400, 'The CSRF token could not be verified.');
            }
        }

        $this->_controller = $controller;
        $controller->init();
        ob_start();
        $response = $controller->run($actionID, $routeParams);
        $html = ob_get_clean();
        if ($response instanceof ResponseInterface) {
            $this->request->send($response);
        } else {
            $this->request->html($html);
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
            $manager = $this->urlManager;
            $manager->parsePathInfo((string)substr($pathInfo, $pos + 1));
            $actionID = substr($pathInfo, 0, $pos);
            return $manager->caseSensitive ? $actionID : strtolower($actionID);
        } else {
            return $pathInfo;
        }
    }

    public function createController($route, $owner = null)
    {
        if ($owner === null) {
            $owner = $this;
        }

        if ($route) {
            list($handler, $vars) = $route;
            if ($handler instanceof Closure) {
                $handler->__invoke($this->getComponent('request'));
                $this->end();
            } else {
                list($className, $actionName) = $handler;
                $controller = Creator::createObject($className, time(), $owner === $this ? null : $owner, $this->getComponent('request'));
                return [$controller, $actionName, $vars];
            }
        }

        return null;
    }

    /**
     * Terminates the application.
     * This method replaces PHP's exit() function by calling
     * {@link onEndRequest} before exiting.
     * @param integer $status exit status (value 0 means normal exit while other values mean abnormal exit).
     * @param boolean $exit whether to exit the current request. This parameter has been available since version 1.1.5.
     * It defaults to true, meaning the PHP's exit() function will be called at the end of this method.
     */
    public function end($status = 0, $exit = true)
    {
        if ($exit) {
            exit($status);
        }
    }

    /**
     * Returns the unique identifier for the application.
     * @return string the unique identifier for the application.
     */
    public function getId()
    {
        if ($this->_id !== null) {
            return $this->_id;
        } else {
            return $this->_id = sprintf('%x', crc32($this->getBasePath() . $this->name));
        }
    }

    /**
     * Sets the unique identifier for the application.
     * @param string $id the unique identifier for the application.
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * Returns the root path of the application.
     * @return string the root directory of the application. Defaults to 'protected'.
     */
    public function getBasePath()
    {
        return $this->_basePath;
    }

    /**
     * Sets the root directory of the application.
     * This method can only be invoked at the begin of the constructor.
     * @param string $path the root directory of the application.
     * @throws Exception if the directory does not exist.
     */
    public function setBasePath($path)
    {
        if (($this->_basePath = realpath($path)) === false || !is_dir($this->_basePath)) {
            throw new Exception(Mindy::t('base', 'Application base path "{path}" is not a valid directory.', ['{path}' => $path]));
        }
    }

    /**
     * Returns the directory that stores runtime files.
     * @return string the directory that stores runtime files. Defaults to 'protected/runtime'.
     */
    public function getRuntimePath()
    {
        if ($this->_runtimePath !== null) {
            return $this->_runtimePath;
        } else {
            $this->setRuntimePath($this->getBasePath() . DIRECTORY_SEPARATOR . 'runtime');
            return $this->_runtimePath;
        }
    }

    /**
     * Sets the directory that stores runtime files.
     * @param string $path the directory that stores runtime files.
     * @throws Exception if the directory does not exist or is not writable
     */
    public function setRuntimePath($path)
    {
        if (($runtimePath = realpath($path)) === false || !is_dir($runtimePath) || !is_writable($runtimePath)) {
            throw new Exception(Mindy::t('base', 'Application runtime path "{path}" is not valid. Please make sure it is a directory writable by the Web server process.', ['{path}' => $path]));
        }
        $this->_runtimePath = $runtimePath;
    }

    /**
     * Returns the time zone used by this application.
     * This is a simple wrapper of PHP function date_default_timezone_get().
     * @return string the time zone used by this application.
     * @see http://php.net/manual/en/function.date-default-timezone-get.php
     */
    public function getTimeZone()
    {
        return date_default_timezone_get();
    }

    /**
     * Sets the time zone used by this application.
     * This is a simple wrapper of PHP function date_default_timezone_set().
     * @param string $value the time zone used by this application.
     * @see http://php.net/manual/en/function.date-default-timezone-set.php
     */
    public function setTimeZone($value)
    {
        date_default_timezone_set($value);
    }

    /**
     * Returns the relative URL for the application.
     * This is a shortcut method to {@link CHttpRequest::getBaseUrl()}.
     * @param boolean $absolute whether to return an absolute URL. Defaults to false, meaning returning a relative one.
     * @return string the relative URL for the application
     * @see CHttpRequest::getBaseUrl()
     */
    public function getBaseUrl($absolute = false)
    {
        return $this->request->http->getBaseUrl($absolute);
    }

    /**
     * @return string the homepage URL
     */
    public function getHomeUrl()
    {
        return $this->_homeUrl === null ? '/' : $this->_homeUrl;
    }

    /**
     * @param string $value the homepage URL
     */
    public function setHomeUrl($value)
    {
        $this->_homeUrl = $value;
    }

    /**
     * Initializes the error handlers.
     * @void
     */
    protected function registerErrorHandler()
    {
        if (MINDY_ENABLE_EXCEPTION_HANDLER || MINDY_ENABLE_ERROR_HANDLER) {
            $handler = new ErrorHandler();
            if (MINDY_ENABLE_EXCEPTION_HANDLER) {
                set_exception_handler([$handler, 'handleException']);
            }
            if (MINDY_ENABLE_ERROR_HANDLER) {
                set_error_handler([$handler, 'handleError'], error_reporting());
            }
        }
    }

    /**
     * Registers the core application components.
     */
    protected function getCoreComponents()
    {
        return [
            'securityManager' => [
                'class' => '\Mindy\Security\SecurityManager',
            ],
            'statePersister' => [
                'class' => '\Mindy\Base\StatePersister',
            ],

            'urlManager' => [
                'class' => '\Mindy\Router\UrlManager'
            ],
            'request' => [
                'class' => '\Mindy\Http\Request',
            ],

            'signal' => [
                'class' => '\Mindy\Event\EventManager',
            ],

            // TODO remove me
            'mail' => [
                'class' => '\Mindy\Mail\Mailer',
            ],

            // TODO remove me
            'logger' => [
                'class' => '\Mindy\Logger\LoggerManager',
                'handlers' => [
                    'null' => [
                        'class' => '\Mindy\Logger\Handler\NullHandler',
                        'level' => 'ERROR'
                    ],
                    'console' => [
                        'class' => '\Mindy\Logger\Handler\StreamHandler',
                    ],
                    'users' => [
                        'class' => '\Mindy\Logger\Handler\RotatingFileHandler',
                        'alias' => 'application.runtime.users',
                        'level' => 'INFO',
                        'formatter' => 'users'
                    ],
                ],
                'formatters' => [
                    'default' => [
                        'class' => '\Mindy\Logger\Formatters\LineFormatter',
                    ],
                    'users' => [
                        'class' => '\Mindy\Logger\Formatters\LineFormatter',
                        'format' => "%datetime% %message%\n"
                    ]
                ],
                'loggers' => [
                    'users' => [
                        'class' => '\Monolog\Logger',
                        'handlers' => ['users'],
                    ],
                ]
            ],
        ];
    }

    /**
     * @return \Mindy\Router\Route
     */
    public function parseRoute()
    {
        $request = $this->request->getRequest();
        return $this->urlManager->dispatch($request->getMethod(), $request->getRequestTarget());
    }

    /**
     * @throws \Mindy\Exception\Exception
     * @return \Modules\User\Models\User instance the user session information
     */
    public function getUser()
    {
        return $this->auth->getModel();
    }

    //////////////////
    // DEPRECATED
    //////////////////

    /**
     * @param $id
     * @return object|null
     */
    public function getComponent($id)
    {
        return $this->getComponentLocator()->get($id);
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasComponent($id)
    {
        return $this->getComponentLocator()->has($id);
    }

    /**
     * @param $id
     * @param $config
     * @void
     */
    public function setComponent($id, $config)
    {
        $this->getComponentLocator()->set($id, $config);
    }

    /**
     * @param bool $definitions
     * @return array
     */
    public function getComponents($definitions = true)
    {
        return $this->getComponentLocator()->getComponents($definitions);
    }
}
