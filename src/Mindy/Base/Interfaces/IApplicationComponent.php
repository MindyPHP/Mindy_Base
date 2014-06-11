<?php

namespace Mindy\Base\Interfaces;

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
 * @date 09/06/14.06.2014 17:42
 */

/**
 * IApplicationComponent is the interface that all application components must implement.
 *
 * After the application completes configuration, it will invoke the {@link init()}
 * method of every loaded application component.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */
interface IApplicationComponent
{
    /**
     * Initializes the application component.
     * This method is invoked after the application completes configuration.
     */
    public function init();

    /**
     * @return boolean whether the {@link init()} method has been invoked.
     */
    public function getIsInitialized();
}
