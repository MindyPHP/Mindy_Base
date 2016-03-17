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
 * @date 10/06/14.06.2014 17:58
 */

namespace Mindy\Base;

use Mindy\Base\Interfaces\IStatePersister;
use Mindy\Cache\FileDependency;
use Mindy\Exception\Exception;
use Mindy\Helper\Traits\Accessors;
use Mindy\Helper\Traits\Configurator;

/**
 * CStatePersister implements a file-based persistent data storage.
 *
 * It can be used to keep data available through multiple requests and sessions.
 *
 * By default, CStatePersister stores data in a file named 'state.bin' that is located
 * under the application {@link CApplication::getRuntimePath runtime path}.
 * You may change the location by setting the {@link stateFile} property.
 *
 * To retrieve the data from CStatePersister, call {@link load()}. To save the data,
 * call {@link save()}.
 *
 * Comparison among state persister, session and cache is as follows:
 * <ul>
 * <li>session: data persisting within a single user session.</li>
 * <li>state persister: data persisting through all requests/sessions (e.g. hit counter).</li>
 * <li>cache: volatile and fast storage. It may be used as storage medium for session or state persister.</li>
 * </ul>
 *
 * Since server resource is often limited, be cautious if you plan to use CStatePersister
 * to store large amount of data. You should also consider using database-based persister
 * to improve the throughput.
 *
 * CStatePersister is a core application component used to store global application state.
 * It may be accessed via {@link CApplication::getStatePersister()}.
 * page state persistent method based on cache.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package Mindy\Base
 * @since 1.0
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
            $cacheKey = 'Yii.CStatePersister.' . $stateFile;
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
