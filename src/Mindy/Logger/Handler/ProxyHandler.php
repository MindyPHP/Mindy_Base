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
 * @date 12/06/14.06.2014 15:10
 */

namespace Mindy\Logger\Handler;


use Mindy\Core\Object;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;

abstract class ProxyHandler extends Object implements HandlerInterface
{
    public $name;
    public $level;
    public $bubble = true;
    public $handler;

    public function init()
    {
        $this->handler = $this->getHandler();
    }

    abstract public function getHandler();

    public function __call($name, $args)
    {
        return call_user_func_array([$this->handler, $name], $args);
    }

    public function getLevel()
    {
        switch ($this->level) {
            case "DEBUG":
                $level = Logger::DEBUG;
                break;
            case "NOTICE":
                $level = Logger::NOTICE;
                break;
            case "WARNING":
                $level = Logger::WARNING;
                break;
            case "ERROR":
                $level = Logger::ERROR;
                break;
            case "CRITICAL":
                $level = Logger::CRITICAL;
                break;
            case "ALERT":
                $level = Logger::ALERT;
                break;
            case "EMERGENCY":
                $level = Logger::EMERGENCY;
                break;
            case "INFO":
            default:
                $level = Logger::INFO;
                break;
        }
        return $level;
    }

    /**
     * Checks whether the given record will be handled by this handler.
     *
     * This is mostly done for performance reasons, to avoid calling processors for nothing.
     *
     * Handlers should still check the record levels within handle(), returning false in isHandling()
     * is no guarantee that handle() will not be called, and isHandling() might not be called
     * for a given record.
     *
     * @param array $record
     *
     * @return Boolean
     */
    public function isHandling(array $record)
    {
        // Unused method. See magic method __call().
    }

    /**
     * Handles a record.
     *
     * All records may be passed to this method, and the handler should discard
     * those that it does not want to handle.
     *
     * The return value of this function controls the bubbling process of the handler stack.
     * Unless the bubbling is interrupted (by returning true), the Logger class will keep on
     * calling further handlers in the stack with a given log record.
     *
     * @param  array $record The record to handle
     * @return Boolean true means that this handler handled the record, and that bubbling is not permitted.
     *                        false means the record was either not processed or that this handler allows bubbling.
     */
    public function handle(array $record)
    {
        // Unused method. See magic method __call().
    }

    /**
     * Handles a set of records at once.
     *
     * @param array $records The records to handle (an array of record arrays)
     */
    public function handleBatch(array $records)
    {
        // Unused method. See magic method __call().
    }

    /**
     * Adds a processor in the stack.
     *
     * @param  callable $callback
     * @return self
     */
    public function pushProcessor($callback)
    {
        // Unused method. See magic method __call().
    }

    /**
     * Removes the processor on top of the stack and returns it.
     *
     * @return callable
     */
    public function popProcessor()
    {
        // Unused method. See magic method __call().
    }

    /**
     * Sets the formatter.
     *
     * @param  FormatterInterface $formatter
     * @return self
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        // Unused method. See magic method __call().
    }

    /**
     * Gets the formatter.
     *
     * @return FormatterInterface
     */
    public function getFormatter()
    {
        // Unused method. See magic method __call().
    }
}
