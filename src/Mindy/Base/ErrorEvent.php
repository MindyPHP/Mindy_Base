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
 * @date 10/06/14.06.2014 17:04
 */

namespace Mindy\Base;

/**
     * CErrorEvent class file.
     *
     * @author Qiang Xue <qiang.xue@gmail.com>
     * @link http://www.yiiframework.com/
     * @copyright 2008-2013 Yii Software LLC
     * @license http://www.yiiframework.com/license/
     */

/**
 * CErrorEvent represents the parameter for the {@link CApplication::onError onError} event.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */
class ErrorEvent extends Event
{
    /**
     * @var string error code
     */
    public $code;
    /**
     * @var string error message
     */
    public $message;
    /**
     * @var string error message
     */
    public $file;
    /**
     * @var string error file
     */
    public $line;

    /**
     * Constructor.
     * @param mixed $sender sender of the event
     * @param string $code error code
     * @param string $message error message
     * @param string $file error file
     * @param integer $line error line
     */
    public function __construct($sender, $code, $message, $file, $line)
    {
        $this->code = $code;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
        parent::__construct($sender);
    }
}
