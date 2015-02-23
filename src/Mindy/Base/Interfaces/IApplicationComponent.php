<?php

namespace Mindy\Base\Interfaces;

/**
 * IApplicationComponent is the interface that all application components must implement.
 *
 * After the application completes configuration, it will invoke the {@link init()}
 * method of every loaded application component.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package Mindy\Base
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
