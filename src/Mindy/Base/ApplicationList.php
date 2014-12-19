<?php

namespace Mindy\Base;

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
    public function getApplications()
    {
        $modules = Mindy::app()->getModules();
        return $this->buildMenu($modules);
    }

    protected function buildMenu($modules = [])
    {
        $user = Mindy::app()->user;

        $array = [];
        foreach ($modules as $name => $config) {
            $adminCode = strtolower($name) . '.admin';

            $name = is_array($config) ? $name : $config;

            $module = Mindy::app()->getModule($name);

            if (method_exists($module, 'getMenu')) {
                $items = $module->getMenu();
                if (!empty($items)) {
                    $items['version'] = $module->getVersion();

                    $resultItems = [];

                    if (!isset($items['items'])) {
                        continue;
                    } else {
                        foreach ($items['items'] as $item) {
                            if (isset($item['adminClass']) && $user->can($adminCode . '.' . strtolower($item['adminClass'])) || !isset($item['code']) && $user->is_superuser) {
                                $resultItems[] = $item;
                            }
                        }
                    }

                    if (empty($resultItems)) {
                        continue;
                    }

                    $items['module'] = $name;
                    $items['items'] = $resultItems;
                    $array[] = $items;
                }
            }
        }

        return $array;
    }
}
