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
 * @date 09/06/14.06.2014 17:46
 */

namespace Mindy\Base\Interfaces;

/**
 * IBehavior interfaces is implemented by all behavior classes.
 *
 * A behavior is a way to enhance a component with additional methods that
 * are defined in the behavior class and not available in the component class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package Mindy\Base
 */
interface IBehavior
{
    /**
     * Attaches the behavior object to the component.
     * @param CComponent $component the component that this behavior is to be attached to.
     */
    public function attach($component);

    /**
     * Detaches the behavior object from the component.
     * @param CComponent $component the component that this behavior is to be detached from.
     */
    public function detach($component);

    /**
     * @return boolean whether this behavior is enabled
     */
    public function getEnabled();

    /**
     * @param boolean $value whether this behavior is enabled
     */
    public function setEnabled($value);
}
