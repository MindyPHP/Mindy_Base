<?php

namespace Mindy\Base;

use Mindy\Base\Mindy;
use Mindy\Base\Module;

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 03/04/14.04.2014 16:59
 */

trait ApplicationList
{
    public $exclude = [
        'gii',
        'dev'
    ];

    public function getApplications()
    {
        $modules = Mindy::app()->getModules();
        foreach($this->exclude as $exclude) {
            unset($modules[$exclude]);
        }

        return $this->buildMenu($modules);
    }

    protected function buildMenu($modules = array(), Module $parentModule = null)
    {
        $array = [];
        foreach ($modules as $name => $config) {
            $name = is_array($config) ? $name : $config;

            $module = $parentModule ? $parentModule->getModule($name) : Mindy::app()->getModule($name);

            if (method_exists($module, 'getMenu')) {
                $items = $module->getMenu();
                if (!empty($items)) {
                    if ($this->hasSubmodules($config)) {
                        $submodulesMenu = $this->getSubmodules($config, $module);
                        if (isset($menu['items'])) {
                            $menu['items'] = CMap::mergeArray($menu['items'], $submodulesMenu);
                        } else {
                            $menu['items'] = $submodulesMenu;
                        }
                    }
                    $items['version'] = $module->getVersion();
                    $array[] = $items;
                }
            }
        }
        return $array;
    }

    private function hasSubmodules($config)
    {
        return (is_array($config) && array_key_exists('modules', $config));
    }

    private function getSubmodules($config, $module)
    {
        return $this->buildMenu($config['modules'], $module);
    }

    public function findModule($moduleID)
    {
        return $this->findInModule(Mindy::app(), $moduleID);
    }

    private function findInModule($parent, $moduleID)
    {
        if ($parent->getModule($moduleID)) {
            return $parent->getModule($moduleID);
        } else {
            $modules = $parent->getModules();
            foreach ($modules as $mod => $conf) {
                $module = $this->findInModule($parent->getModule($mod), $moduleID);
                if ($module) {
                    return $module;
                }
            }
        }
        return null;
    }
}
