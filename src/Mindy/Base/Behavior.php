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
 * @date 09/06/14.06.2014 19:25
 */

namespace Mindy\Base;

/**
 * CBehavior class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
use Mindy\Base\Interfaces\IBehavior;
use Mindy\Helper\Traits\BehaviorAccessors;
use Mindy\Helper\Traits\Configurator;
use ReflectionClass;

/**
 * CBehavior is a convenient base class for behavior classes.
 *
 * @property Component $owner The owner component that this behavior is attached to.
 * @property boolean $enabled Whether this behavior is enabled.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package Mindy\Base
 */
class Behavior implements IBehavior
{
    use Configurator, BehaviorAccessors;

    private $_enabled = false;
    private $_owner;

    /**
     * Attaches the behavior object to the component.
     * The default implementation will set the {@link owner} property
     * and attach event handlers as declared in {@link events}.
     * This method will also set {@link enabled} to true.
     * Make sure you've declared handler as public and call the parent implementation if you override this method.
     * @param Component $owner the component that this behavior is to be attached to.
     */
    public function attach($owner)
    {
        $this->_enabled = true;
        $this->_owner = $owner;
    }

    /**
     * Detaches the behavior object from the component.
     * The default implementation will unset the {@link owner} property
     * and detach event handlers declared in {@link events}.
     * This method will also set {@link enabled} to false.
     * Make sure you call the parent implementation if you override this method.
     * @param Component $owner the component that this behavior is to be detached from.
     */
    public function detach($owner)
    {
        $this->_owner = null;
        $this->_enabled = false;
    }

    /**
     * @return Component the owner component that this behavior is attached to.
     */
    public function getOwner()
    {
        return $this->_owner;
    }

    /**
     * @return boolean whether this behavior is enabled
     */
    public function getEnabled()
    {
        return $this->_enabled;
    }

    /**
     * @param boolean $value whether this behavior is enabled
     */
    public function setEnabled($value)
    {
        $this->_enabled = (bool)$value;
    }
}
