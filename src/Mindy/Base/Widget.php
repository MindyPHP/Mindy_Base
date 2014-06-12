<?php

namespace Mindy\Base;

use Mindy\Utils\RenderTrait;

/**
 *
 * CoreWidget class file.
 *
 * @author Falaleev Maxim <max@studio107.com>
 * @link http://studio107.ru/
 * @copyright Copyright &copy; 2010-2012 Studio107
 * @license http://www.cms107.com/license/
 * @package modules.core.components
 * @since 1.1.1
 * @version 1.0
 *
 */
class Widget extends BaseController
{
    use RenderTrait;
    /**
     * @var string the prefix to the IDs of the {@link actions}.
     * When a widget is declared an action provider in {@link CController::actions},
     * a prefix can be specified to differentiate its action IDs from others.
     * The same prefix should then also be used to configure this property
     * when the widget is used in a view of the controller.
     */
    public $actionPrefix;
    /**
     * @var integer the counter for generating implicit IDs.
     */
    private static $_counter = 0;
    /**
     * @var string id of the widget.
     */
    private $_id;
    /**
     * @var CBaseController owner/creator of this widget. It could be either a widget or a controller.
     */
    private $_owner;

    public function render($view, array $data = [])
    {
        return $this->renderTemplate($view, $data);
    }

    /**
     * Returns a list of actions that are used by this widget.
     * The structure of this method's return value is similar to
     * that returned by {@link CController::actions}.
     *
     * When a widget uses several actions, you can declare these actions using
     * this method. The widget will then become an action provider, and the actions
     * can be easily imported into a controller.
     *
     * Note, when creating URLs referring to the actions listed in this method,
     * make sure the action IDs are prefixed with {@link actionPrefix}.
     *
     * @return array
     *
     * @see actionPrefix
     * @see CController::actions
     */
    public function actions()
    {
        return [];
    }

    /**
     * Constructor.
     * @param CBaseController $owner owner/creator of this widget. It could be either a widget or a controller.
     */
    public function __construct($owner = null)
    {
        $this->_owner = $owner === null ? Mindy::app()->getController() : $owner;
    }

    /**
     * Returns the owner/creator of this widget.
     * @return CBaseController owner/creator of this widget. It could be either a widget or a controller.
     */
    public function getOwner()
    {
        return $this->_owner;
    }

    /**
     * Returns the ID of the widget or generates a new one if requested.
     * @param boolean $autoGenerate whether to generate an ID if it is not set previously
     * @return string id of the widget.
     */
    public function getId($autoGenerate = true)
    {
        if ($this->_id !== null) {
            return $this->_id;
        } elseif ($autoGenerate) {
            return $this->_id = 'yw' . self::$_counter++;
        }
    }

    /**
     * Sets the ID of the widget.
     * @param string $value id of the widget.
     */
    public function setId($value)
    {
        $this->_id = $value;
    }

    /**
     * Returns the controller that this widget belongs to.
     * @return Controller the controller that this widget belongs to.
     */
    public function getController()
    {
        if ($this->_owner instanceof Controller)
            return $this->_owner;
        else
            return Mindy::app()->getController();
    }

    /**
     * Initializes the widget.
     * This method is called by {@link CBaseController::createWidget}
     * and {@link CBaseController::beginWidget} after the widget's
     * properties have been initialized.
     */
    public function init()
    {
    }

    /**
     * Executes the widget.
     * This method is called by {@link CBaseController::endWidget}.
     */
    public function run()
    {
    }
}
