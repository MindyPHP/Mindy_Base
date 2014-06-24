<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 09/06/14.06.2014 18:47
 */

/**
 * ConsoleCommandRunner class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Mindy\Base;

use Mindy\Base\Commands\HelpCommand;
use Mindy\Base\Exception\Exception;
use Mindy\Helper\Creator;

/**
 * ConsoleCommandRunner manages commands and executes the requested command.
 *
 * @property string $scriptName The entry script name.
 * @property ConsoleCommand $command The currently active command.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.console
 * @since 1.0
 */
class ConsoleCommandRunner extends Component
{
    /**
     * @var array list of all available commands (command name=>command configuration).
     * Each command configuration can be either a string or an array.
     * If the former, the string should be the class name or
     * {@link YiiBase::getPathOfAlias class path alias} of the command.
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
     *   'log'=>'path.to.LoggerCommand',
     * )
     * </pre>
     */
    public $commands = [];

    private $_scriptName;
    private $_command;

    /**
     * Executes the requested command.
     * @param array $args list of user supplied parameters (including the entry script name and the command name).
     * @return integer|null application exit code returned by the command.
     * if null is returned, application will not exit explicitly. See also {@link CConsoleApplication::processRequest()}.
     * (return value is available since version 1.1.11)
     */
    public function run($args)
    {
        $this->_scriptName = $args[0];
        array_shift($args);
        if (isset($args[0])) {
            $name = $args[0];
            array_shift($args);
        } else {
            $name = 'help';
        }

        $oldCommand = $this->_command;
        if (($command = $this->createCommand($name)) === null) {
            $command = $this->createCommand('help');
        }
        $this->_command = $command;
        $command->init();
        $exitCode = $command->run($args);
        $this->_command = $oldCommand;
        return $exitCode;
    }

    /**
     * @return string the entry script name
     */
    public function getScriptName()
    {
        return $this->_scriptName;
    }

    /**
     * Returns the currently running command.
     * @return ConsoleCommand|null the currently active command.
     * @since 1.1.14
     */
    public function getCommand()
    {
        return $this->_command;
    }

    /**
     * @param ConsoleCommand $value the currently active command.
     * @since 1.1.14
     */
    public function setCommand($value)
    {
        $this->_command = $value;
    }

    /**
     * Searches for commands under the specified directory.
     * @param string $path the directory containing the command class files.
     * @return array list of commands (command name=>command class file)
     */
    public function findCommands($path)
    {
        if (($dir = @opendir($path)) === false) {
            return [];
        }
        $commands = [];
        while (($name = readdir($dir)) !== false) {
            $file = $path . DIRECTORY_SEPARATOR . $name;
            if (!strcasecmp(substr($name, -11), 'Command.php') && is_file($file)) {
                $commands[strtolower(substr($name, 0, -11))] = $file;
            }
        }
        closedir($dir);
        return $commands;
    }

    /**
     * Adds commands from the specified command path.
     * If a command already exists, the new one will be ignored.
     * @param string $path the alias of the directory containing the command class files.
     */
    public function addCommands($path)
    {
        if (($commands = $this->findCommands($path)) !== []) {
            foreach ($commands as $name => $file) {
                if (!isset($this->commands[$name])) {
                    $this->commands[$name] = $file;
                }
            }
        }
    }

    /**
     * @param $code string
     * @return array
     */
    public function getClassesFromCode($code)
    {
        $classes = [];

        $namespace = 0;
        $tokens = token_get_all($code);
        $count = count($tokens);
        $dlm = false;
        for ($i = 2; $i < $count; $i++) {
            if (
                (isset($tokens[$i - 2][1]) && ($tokens[$i - 2][1] == "phpnamespace" || $tokens[$i - 2][1] == "namespace")) ||
                ($dlm && $tokens[$i - 1][0] == T_NS_SEPARATOR && $tokens[$i][0] == T_STRING)
            ) {
                if (!$dlm) {
                    $namespace = 0;
                }
                if (isset($tokens[$i][1])) {
                    $namespace = $namespace ? $namespace . "\\" . $tokens[$i][1] : $tokens[$i][1];
                    $dlm = true;
                }
            } elseif ($dlm && ($tokens[$i][0] != T_NS_SEPARATOR) && ($tokens[$i][0] != T_STRING)) {
                $dlm = false;
            }

            if (
                ($tokens[$i - 2][0] == T_CLASS || (isset($tokens[$i - 2][1]) && $tokens[$i - 2][1] == "phpclass")) &&
                $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING
            ) {
                $class_name = $tokens[$i][1];
                $classes[] = $namespace . '\\' . $class_name;
            }
        }
        return $classes;
    }

    /**
     * @param string $name command name (case-insensitive)
     * @throws Exception
     * @return ConsoleCommand|null the command object. Null if the name is invalid.
     */
    public function createCommand($name)
    {
        $name = strtolower($name);

        $command = null;
        if (isset($this->commands[$name])) {
            $command = $this->commands[$name];
        } else {
            $commands = array_change_key_case($this->commands);
            if (isset($commands[$name])) {
                $command = $commands[$name];
            }
        }

        if ($command !== null) {
            $classes = $this->getClassesFromCode(file_get_contents($command));
            return Creator::createObject(array_shift($classes), $name, $this);
        } elseif ($name === 'help')
            return new HelpCommand('help', $this);
        else {
            return null;
        }
    }
}
