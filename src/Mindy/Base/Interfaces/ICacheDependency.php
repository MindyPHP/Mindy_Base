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
 * @date 09/06/14.06.2014 17:43
 */

namespace Mindy\Base\Interfaces;

/**
 * ICacheDependency is the interface that must be implemented by cache dependency classes.
 *
 * This interface must be implemented by classes meant to be used as
 * cache dependencies.
 *
 * Objects implementing this interface must be able to be serialized and unserialized.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package Mindy\Base
 * @since 1.0
 */
interface ICacheDependency
{
    /**
     * Evaluates the dependency by generating and saving the data related with dependency.
     * This method is invoked by cache before writing data into it.
     */
    public function evaluateDependency();

    /**
     * @return boolean whether the dependency has changed.
     */
    public function getHasChanged();
}
