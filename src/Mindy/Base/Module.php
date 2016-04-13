<?php

namespace Mindy\Base;

use Mindy\Helper\Alias;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

/**
 * Class Module
 * @package Mindy\Base
 */
class Module extends BaseModule
{
    public function getVersion()
    {
        return '1.0';
    }

    /**
     * Return array for MMenu {$see: MMenu} widget
     * @return array
     */
    public function getMenu()
    {
        return [];
    }

    /**
     * Return array of mail templates and his variables
     * @return array
     */
    public function getMailTemplates()
    {
        return [];
    }

    /**
     * Install module
     * @void
     */
    public function install()
    {
    }

    /**
     * Uninstall module. Delete tables from database.
     * @void
     */
    public function uninstall()
    {
    }

    /**
     * Upgrade module to new version. Run migrations, update sql.
     * @void
     */
    public function upgrade()
    {
    }

    /**
     * Downgrade module to old version. Delete tables from database if need.
     * @void
     */
    public function downgrade()
    {
    }

    /**
     * @return \Mindy\Orm\Model[]
     */
    public function getModels()
    {
        $object = new ReflectionClass(get_called_class());
        $path = dirname($object->getFilename()) . DIRECTORY_SEPARATOR . 'Models';
        if (is_dir($path) === false) {
            return [];
        }

        $files = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        /** @var RecursiveDirectoryIterator $it */
        while ($it->valid()) {
            if (!$it->isDot() && substr($it->getSubPathName(), 0, 1) !== '_') {
                $files[] = str_replace('.php', '', $it->getSubPathName());
            }
            $it->next();
        }
        $basePath = str_replace(Alias::get('App'), '', $path);
        $modelClasses = [];
        foreach ($files as $file) {
            $modelClasses[] = str_replace('/', '\\', $basePath . DIRECTORY_SEPARATOR . $file);
        }

        $models = [];
        foreach ($modelClasses as $cls) {
            if (is_subclass_of($cls, '\Mindy\Orm\Base')) {
                $reflectClass = new ReflectionClass($cls);
                if ($reflectClass->isAbstract()) {
                    continue;
                }
                if (call_user_func([$cls, 'tableName'])) {
                    $models[$cls] = new $cls;
                }
            }
        }

        return $models;
    }
}
