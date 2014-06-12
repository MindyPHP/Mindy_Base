<?php
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
 * @date 11/06/14.06.2014 17:23
 */

namespace Mindy\Logger;

use Closure;
use Exception;
use InvalidArgumentException;
use Mindy\Base\Component;
use Mindy\Helper\Creator;
use Monolog\Logger as MonoLogger;
use ReflectionClass;
use ReflectionMethod;

class LoggerManager extends Component
{
    /**
     * @var array
     */
    public $formatters = [];
    /**
     * @var \Monolog\Formatter\NormalizerFormatter[]
     */
    private $_formatters = [];
    /**
     * @var array
     */
    public $handlers = [];
    /**
     * @var \Monolog\Handler\AbstractHandler[]
     */
    private $_handlers = [];
    /**
     * @var array
     */
    public $loggers = [];
    /**
     * @var \Monolog\Logger[]
     */
    private $_loggers = [];

    private $defaultLogger = [
        'default' => [
            'class' => '\Monolog\Logger',
            'handlers' => ['default']
        ],
    ];

    private $defaultHandler = [
        'default' => [
            'class' => '\Mindy\Logger\Handler\StreamHandler',
            'level' => 'DEBUG'
        ]
    ];

    public function init()
    {
        foreach ($this->formatters as $name => $data) {
            $this->_formatters[$name] = Creator::createObject($data);
        }

        $this->handlers = array_merge($this->defaultHandler, $this->handlers);
        foreach ($this->handlers as $name => $data) {
            $formatter = null;
            if(isset($data['formatter'])) {
                $formatter = $data['formatter'];
                unset($data['formatter']);
            }
            $this->_handlers[$name] = Creator::createObject($data);
            if($formatter) {
                if(!isset($this->_formatters[$formatter])) {
                    throw new Exception("Formatter $formatter not initialized");
                }
                $this->_handlers[$name]->setFormatter($formatter);
            }
        }

        $this->loggers = array_merge($this->defaultLogger, $this->loggers);
        foreach($this->loggers as $name => $data) {
            $handlers = null;
            if(isset($data['handlers'])) {
                $handlers = $data['handlers'];
                unset($data['handlers']);
            }
            $this->_loggers[$name] = Creator::createObject($data, $name);
            foreach($handlers as $name) {
                if(!isset($this->_handlers[$name])) {
                    throw new Exception("Handler $name not initialized");
                }
                $this->_loggers[$name]->pushHandler($this->_handlers[$name]);
            }
        }
    }

    protected function getLogger($loggerName)
    {
        $log = null;
        foreach($this->_loggers as $name => $logger) {
            if($name == $loggerName) {
                $log = $logger;
                break;
            }

            if(strpos($loggerName, $name) === 0) {
                $log = $logger;
                break;
            }
        }
        if($log === null) {
            $log = $this->getDefaultLogger();
        }
        return $log;
    }

    protected function getDefaultLogger()
    {
        return $this->_loggers['default'];
    }

    public function error($message, $logger, array $context = [])
    {
        $this->getLogger($logger)->addError($message, $context);
    }

    public function warning($message, $logger, array $context = [])
    {
        $this->getLogger($logger)->addWarning($message, $context);
    }

    public function notice($message, $logger, array $context = [])
    {
        $this->getLogger($logger)->addNotice($message, $context);
    }

    public function critical($message, $logger, array $context = [])
    {
        $this->getLogger($logger)->addCritical($message, $context);
    }

    public function debug($message, $logger, array $context = [])
    {
        $this->getLogger($logger)->addDebug($message, $context);
    }

    public function alert($message, $logger, array $context = [])
    {
        $this->getLogger($logger)->addAlert($message, $context);
    }

    public function emergency($message, $logger, array $context = [])
    {
        $this->getLogger($logger)->addEmergency($message, $context);
    }

    public function info($message, $logger, array $context = [])
    {
        $this->getLogger($logger)->addInfo($message, $context);
    }
}
