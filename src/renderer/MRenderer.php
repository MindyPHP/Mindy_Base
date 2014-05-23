<?php
use Mindy\Helper\Console;

/**
 * Twig view renderer
 *
 * @author Leonid Svyatov <leonid@svyatov.ru>
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @link http://github.com/yiiext/twig-renderer
 * @link http://twig.sensiolabs.org
 *
 * @version 1.1.2
 */
class MRenderer extends CApplicationComponent
{
    /**
     * @var string Path alias to Twig
     */
    public $twigPathAlias = 'mindy.vendors.Twig';
    /**
     * @var string Twig template files extension
     */
    public $fileExtension = '.twig';
    /**
     * @var array Twig environment options
     * @see http://twig.sensiolabs.org/doc/api.html#environment-options
     */
    public $options = [];
    /**
     * @var array Objects or static classes
     * Keys of array are names to call in template, values - objects or names of static class as string
     * Example: array('html'=>'CHtml', 'clientScript'=>Yii::app()->clientScript)
     * Than in template: {{ html.link('Login', 'site/login') }} or {{ clientScript.registerCssFile(...) }}
     */
    public $globals = [];
    /**
     * @var array Custom functions
     * Keys of array are names to call in template, values - names of functions or static methods of some class
     * Example: array('rot13'=>'str_rot13', 'link'=>'CHtml::link')
     * Than in template: {{ rot13('test') }} or {{ link('Login', 'site/login') }}
     */
    public $functions = [];
    /**
     * @var array Custom filters
     * Keys of array are names to call in template, values - names of functions or static methods of some class
     * Example: array('rot13'=>'str_rot13', 'jsonEncode'=>'CJSON::encode')
     * Then in template: {{ 'test'|rot13 }} or {{ model|jsonEncode }}
     */
    public $filters = [];
    /**
     * @var array Custom extensions
     * Example: array('Twig_Extension_Sandbox', 'Twig_Extension_Text')
     */
    public $extensions = [];
    /**
     * @var array Twig lexer options
     * @see http://twig.sensiolabs.org/doc/recipes.html#customizing-the-syntax
     * Example: Smarty-like syntax
     * array(
     *     'tag_comment'  => array('{*', '*}'),
     *     'tag_block'    => array('{', '}'),
     *     'tag_variable' => array('{$', '}')
     * )
     */
    public $lexerOptions = [];

    /**
     * @var Twig_Environment
     */
    private $_twig;

    private $_stringLoader;

    private $_templateLoader;

    /**
     * @var Twig_Loader_Filesystem
     */
    protected $loader;

    public function init()
    {
        $app = Yii::app();

        $defaultOptions = array(
            'autoescape' => true, // false because other way Twig escapes all HTML in templates
            'auto_reload' => true,
            'cache' => $app->getRuntimePath() . '/twig_cache/',
            'charset' => $app->charset,
        );

        $this->_twig = new Twig_Environment(new Twig_Loader_Chain([
            $this->getTemplateLoader(),
            $this->getStringLoader()
        ]), array_merge($defaultOptions, $this->options));

        // Adding Yii's core static classes proxy as 'C' shortcut (usage: {{C.Html.tag(...)}})
        $this->_twig->addGlobal('C', new ETwigViewRendererYiiCoreStaticClassesProxy());

        $this->addGlobals([
            'params' => $app->params,
            'request' => $app->request,
            'app' => $app,
        ]);

        if(Console::isCli() === false) {
            $this->addGlobals([
                'csrf_name' => $app->request->csrfTokenName,
                'csrf_token' => $app->request->getCsrfToken(),
            ]);
        }

        if($app->hasComponent('auth')) {
            $this->_twig->addGlobal('user', $app->auth->getModel());
        }

        $this->addFunctions([
            'can' => 'Yii::app()->user->can',
            'access' => 'Yii::app()->user->checkAccess',
            't' => 'YiiUtils::t',
            'url' => 'YiiUtils::createUrl',
            'breadcrumbs' => 'Yii::app()->controller->setBreadcrumbs',
            'dump' => 'd',
            'get_version' => 'Mindy::getVersion',
            'csrf' => 'YiiUtils::csrf',
            'get_class' => 'get_class',
            'time' => 'time',
            'date' => 'date',
            'is_file' => 'is_file',
            'debug_panel' => 'DebugPanel::render'
        ]);

        $this->addFilters([
            'limit' => 'TextHelper::limit',
            'revertLimit' => 'TextHelper::revertLimit',
            'limitword' => 'TextHelper::limitword',
            'typograph' => 'TextHelper::typograph',
            'is_array' => 'is_array',
            'is_integer' => 'is_integer',
            'mdate' => 'YiiUtils::mdate',
        ]);

        // Adding global 'void' function (usage: {{void(App.clientScript.registerScriptFile(...))}})
        // (@see ETwigViewRendererVoidFunction below for details)
        $this->_twig->addFunction('void', new Twig_Function_Function('ETwigViewRendererVoidFunction'));

        // Adding custom globals (objects or static classes)
        if (!empty($this->globals)) {
            $this->addGlobals($this->globals);
        }
        // Adding custom functions
        if (!empty($this->functions)) {
            $this->addFunctions($this->functions);
        }
        // Adding custom filters
        if (!empty($this->filters)) {
            $this->addFilters($this->filters);
        }
        // Adding custom extensions
        if (!empty($this->extensions)) {
            $this->addExtensions($this->extensions);
        }
        // Change lexer syntax
        if (!empty($this->lexerOptions)) {
            $this->setLexerOptions($this->lexerOptions);
        }

        return parent::init();
    }

    protected function getStringLoader()
    {
        if(!$this->_stringLoader) {
            $this->_stringLoader = new Twig_Loader_String();
        }
        return $this->_stringLoader;
    }

    protected function getTemplateLoader()
    {
        if(!$this->_templateLoader) {
            $this->_templateLoader = new Twig_Loader_Filesystem(array_merge(['/'], Yii::app()->finder->getPaths()));
        }
        return $this->_templateLoader;
    }

    public function render($sourceFile, array $data = [])
    {
        return $this->_twig->loadTemplate($sourceFile)->render($data);
    }

    /**
     * Adds global objects or static classes
     * @param array $globals @see self::$globals
     */
    public function addGlobals($globals)
    {
        foreach ($globals as $name => $value) {
            if (!is_object($value)) {
                $value = new ETwigViewRendererStaticClassProxy($value);
            }
            $this->_twig->addGlobal($name, $value);
        }
    }

    /**
     * Adds custom functions
     * @param array $functions @see self::$functions
     */
    public function addFunctions($functions)
    {
        $this->_addCustom('Function', $functions);
    }

    /**
     * Adds custom filters
     * @param array $filters @see self::$filters
     */
    public function addFilters($filters)
    {
        $this->_addCustom('Filter', $filters);
    }

    /**
     * Adds custom extensions
     * @param array $extensions @see self::$extensions
     */
    public function addExtensions($extensions)
    {
        foreach ($extensions as $extName) {
            $this->_twig->addExtension(new $extName());
        }
    }

    /**
     * Sets Twig lexer options to change templates syntax
     * @param array $options @see self::$lexerOptions
     */
    public function setLexerOptions($options)
    {
        $lexer = new Twig_Lexer($this->_twig, $options);
        $this->_twig->setLexer($lexer);
    }

    /**
     * Returns Twig object
     * @return Twig_Environment
     */
    public function getTwig()
    {
        return $this->_twig;
    }

    /**
     * Adds custom function or filter
     * @param string $classType 'Function' or 'Filter'
     * @param array $elements Parameters of elements to add
     * @throws CException
     */
    private function _addCustom($classType, $elements)
    {
        $classFunction = 'Twig_' . $classType . '_Function';

        foreach ($elements as $name => $func) {
            $twigElement = null;

            switch ($func) {
                // Just a name of function
                case is_string($func):
                    $twigElement = new $classFunction($func);
                    break;
                // Name of function + options array
                case is_array($func) && is_string($func[0]) && isset($func[1]) && is_array($func[1]):
                    $twigElement = new $classFunction($func[0], $func[1]);
                    break;
            }

            if ($twigElement !== null) {
                $this->_twig->{'add' . $classType}($name, $twigElement);
            } else {
                throw new CException(Yii::t('yiiext',
                    'Incorrect options for "{classType}" [{name}]',
                    array('{classType}' => $classType, '{name}' => $name)));
            }
        }
    }
}

/**
 * Class-proxy for static classes
 * Needed because you can't pass static class to Twig other way
 *
 * @author Leonid Svyatov <leonid@svyatov.ru>
 * @version 1.0.0
 */
class ETwigViewRendererStaticClassProxy
{
    private $_staticClassName;

    public function __construct($staticClassName)
    {
        $this->_staticClassName = $staticClassName;
    }

    public function __get($property)
    {
        $class = new ReflectionClass($this->_staticClassName);
        return $class->getStaticPropertyValue($property);
    }

    public function __set($property, $value)
    {
        $class = new ReflectionClass($this->_staticClassName);
        $class->setStaticPropertyValue($property, $value);
        return $value;
    }

    public function __call($method, $arguments)
    {
        if(!is_callable($this->_staticClassName)) {
            return null;
        } else {
            return call_user_func_array(array($this->_staticClassName, $method), $arguments);
        }
    }
}

/**
 * Class-proxy for Yii core static classes
 *
 * @author Leonid Svyatov <leonid@svyatov.ru>
 * @version 1.0.0
 */
class ETwigViewRendererYiiCoreStaticClassesProxy
{
    private $_classes = [];

    function __isset($className)
    {
        return (isset($_classes[$className]) || class_exists('C' . $className));
    }

    function __get($className)
    {
        if (!isset($this->_classes[$className])) {
            $this->_classes[$className] = new ETwigViewRendererStaticClassProxy('C' . $className);
        }

        return $this->_classes[$className];
    }

}

/**
 * Function for adding global 'void' function in Twig
 * Needed to make possible to call functions and methods which return non-string result (object, resources and etc.)
 * For example: {{ void(App.clientScript.registerScriptFile(...)) }}
 *
 * @param mixed $argument
 * @return string
 */
function ETwigViewRendererVoidFunction($argument)
{
    return '';
}
