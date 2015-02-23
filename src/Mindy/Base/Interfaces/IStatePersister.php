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
 * @date 09/06/14.06.2014 17:44
 */

namespace Mindy\Base\Interfaces;

/**
 * IStatePersister is the interface that must be implemented by state persister classes.
 *
 * This interface must be implemented by all state persister classes (such as
 * {@link CStatePersister}.
 *
 * @package Mindy\Base
 * @since 1.0
 */
interface IStatePersister
{
    /**
     * Loads state data from a persistent storage.
     * @return mixed the state
     */
    public function load();

    /**
     * Saves state data into a persistent storage.
     * @param mixed $state the state to be saved
     */
    public function save($state);
}
