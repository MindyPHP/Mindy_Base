<?php

namespace Mindy\Base;

use Mindy\Base\Interfaces\IStatePersister;
use Mindy\Cache\FileDependency;
use Mindy\Exception\Exception;
use Mindy\Helper\Traits\Accessors;
use Mindy\Helper\Traits\Configurator;

/**
 * Class StatePersister
 * @package Mindy\Base
 */
class StatePersister implements IStatePersister
{
    use Configurator, Accessors;

    /**
     * @var string the file path storing the state data. Make sure the directory containing
     * the file exists and is writable by the Web server process. If using relative path, also
     * make sure the path is correct.
     */
    public $stateFile;
    /**
     * @var string the ID of the cache application component that is used to cache the state values.
     * Defaults to 'cache' which refers to the primary cache application component.
     * Set this property to false if you want to disable caching state values.
     */
    public $cacheID = 'cache';

    /**
     * Initializes the component.
     * This method overrides the parent implementation by making sure {@link stateFile}
     * contains valid value.
     */
    public function init()
    {
        if ($this->stateFile === null) {
            $this->stateFile = Mindy::app()->getRuntimePath() . DIRECTORY_SEPARATOR . 'state.bin';
        }
        $dir = dirname($this->stateFile);
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new Exception(Mindy::t('base', 'Unable to create application state file "{file}". Make sure the directory containing the file exists and is writable by the Web server process.',
                ['{file}' => $this->stateFile]));
        }
    }

    /**
     * Loads state data from persistent storage.
     * @return mixed state data. Null if no state data available.
     */
    public function load()
    {
        $stateFile = $this->stateFile;
        if (!is_file($stateFile)) {
            file_put_contents($stateFile, '');
        }
        if ($this->cacheID !== false && ($cache = Mindy::app()->getComponent($this->cacheID)) !== null) {
            $cacheKey = 'persister.state.storage.' . $stateFile;
            if (($value = $cache->get($cacheKey)) !== false) {
                return unserialize($value);
            } elseif (($content = @file_get_contents($stateFile)) !== false) {
                $cache->set($cacheKey, $content, 0, new FileDependency(['fileName' => $stateFile]));
                return unserialize($content);
            } else {
                return null;
            }
        } elseif (($content = @file_get_contents($stateFile)) !== false) {
            return unserialize($content);
        } else {
            return null;
        }
    }

    /**
     * Saves application state in persistent storage.
     * @param mixed $state state data (must be serializable).
     */
    public function save($state)
    {
        file_put_contents($this->stateFile, serialize($state), LOCK_EX);
    }
}
