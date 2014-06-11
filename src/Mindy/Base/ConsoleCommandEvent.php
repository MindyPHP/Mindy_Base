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
 * @date 10/06/14.06.2014 18:24
 */

namespace Mindy\Base;

    /**
     * CConsoleCommandEvent class file.
     *
     * @author Evgeny Blinov <e.a.blinov@gmail.com>
     * @link http://www.yiiframework.com/
     * @copyright 2008-2013 Yii Software LLC
     * @license http://www.yiiframework.com/license/
     */

/**
 * CConsoleCommandEvent class.
 *
 * CConsoleCommandEvent represents the event parameters needed by events raised by a console command.
 *
 * @author Evgeny Blinov <e.a.blinov@gmail.com>
 * @package system.console
 * @since 1.1.11
 */
class ConsoleCommandEvent extends Event
{
    /**
     * @var string the action name
     */
    public $action;
    /**
     * @var boolean whether the action should be executed.
     * If this property is set true by the event handler, the console command action will quit after handling this event.
     * If false, which is the default, the normal execution cycles will continue, including performing the action and calling
     * {@link CConsoleCommand::afterAction}.
     */
    public $stopCommand = false;
    /**
     * @var integer exit code of application.
     * This property is available in {@link CConsoleCommand::onAfterAction} event and will be set to the exit code
     * returned by the console command action. You can set it to change application exit code.
     */
    public $exitCode;

    /**
     * Constructor.
     * @param mixed $sender sender of the event
     * @param string $params the parameters to be passed to the action method.
     * @param string $action the action name
     * @param integer $exitCode the application exit code
     */
    public function __construct($sender = null, $params = null, $action = null, $exitCode = 0)
    {
        parent::__construct($sender, $params);
        $this->action = $action;
        $this->exitCode = $exitCode;
    }
}
