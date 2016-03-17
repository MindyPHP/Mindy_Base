<?php

namespace Mindy\Base;

/**
 * Class Generator
 * @package Mindy\Base
 */
class Generator
{
    /**
     * substr offset for .php extension
     */
    const EXT_OFFSET = -4;

    /**
     * Возвращает все действия (actions) переданных контроллеров
     * @param null $items
     * @return array|null
     */
    public function getControllerActions($items = null)
    {
        if ($items === null) {
            $items = $this->getControllers();
        }

        foreach ($items['controllers'] as $controllerName => $controller) {
            $actions = [];
            $file = fopen($controller['path'], 'r');
            $lineNumber = 0;
            while (feof($file) === false) {
                ++$lineNumber;
                $line = fgets($file);
                preg_match('/public[ \t]+function[ \t]+action([A-Z]{1}[a-zA-Z0-9]+)[ \t]*\(/', $line, $matches);
                if ($matches !== []) {
                    $name = $matches[1];
                    $actions[strtolower($name)] = array(
                        'name' => $name,
                        'line' => $lineNumber
                    );
                }
            }

            $items['controllers'][$controllerName]['actions'] = $actions;
        }

        foreach ($items['modules'] as $moduleName => $module) {
            $items['modules'][$moduleName] = $this->getControllerActions($module);
        }

        return $items;
    }

    /**
     * Возвращает все контроллеры системы
     * @return array
     */
    public function getControllers($flat = false)
    {
        $basePath = Mindy::app()->basePath;

        $controllers = $this->getFilesInPath($basePath . DIRECTORY_SEPARATOR . 'controllers', $flat, 'controller', -14);
        $modules = $this->getFilesInModules($basePath, 'controllers', $flat, 'controller', -14);

        return ['controllers' => $controllers, 'modules' => $modules];
    }

    /**
     * Возвращает все модели системы
     * @return array
     */
    public function getModels($flat = false)
    {
        $basePath = Mindy::app()->basePath;

        $models = $this->getFilesInPath($basePath . DIRECTORY_SEPARATOR . 'models', $flat);
        $modules = $this->getFilesInModules($basePath, 'models', $flat);

        return ['models' => $models, 'modules' => $modules];
    }

    /**
     * Возвращает все модели системы с настройками модулей
     * @param bool $flat возвращает плоский массив
     * @return array
     */
    public function getSettingModels($flat = false)
    {
        $basePath = Mindy::app()->basePath;

        $models = $this->getFilesInPath($basePath . DIRECTORY_SEPARATOR . 'models', $flat, 'settings', -12);
        $modules = $this->getFilesInModules($basePath, 'models', $flat, 'settings', -12);
        return $flat === true ? array_merge($models, $modules) : [
            'models' => $models,
            'modules' => $modules
        ];
    }

    /**
     * Поиск файлов по заданному условию
     * @param $path
     * @param string $condition
     * @param int $offset negative offset for substr
     * @return array
     */
    protected function getFilesInPath($path, $flat = false, $condition = '', $offset = self::EXT_OFFSET)
    {
        $files = [];

        if (file_exists($path) === true) {
            $filesDirectory = scandir($path);
            foreach ($filesDirectory as $entry) {
                if ($entry{0} !== '.') {
                    $entryPath = $path . DIRECTORY_SEPARATOR . $entry;

                    if (strpos(strtolower($entry), $condition) !== false || empty($condition)) {
                        $name = substr($entry, 0, $offset);
                        if ($flat) {
                            $files = substr($entry, 0, self::EXT_OFFSET);
                        } else {
                            $fileName = strpos($entryPath, 'admin/') ? 'admin.' . strtolower($name) : strtolower($name);
                            $files[$fileName] = [
                                'name' => $name,
                                'baseName' => substr($entry, 0, self::EXT_OFFSET),
                                'file' => $entry,
                                'path' => $entryPath,
                            ];
                        }
                    }

                    if (is_dir($entryPath) === true) {
                        foreach ($this->getFilesInPath($entryPath, $flat, $condition, $offset) as $fileName => $file) {
                            $files[$fileName] = $file;
                        }
                    }
                }
            }
        }

        return $files;
    }

    /**
     * Возвращает все файлы по указанному пути в модуле
     * @param $path
     * @param $subdir
     * @param bool $flat
     * @param string $condition
     * @param int $offset negative offset for substr
     * @return array
     */
    protected function getFilesInModules($path, $subdir, $flat = false, $condition = '', $offset = self::EXT_OFFSET)
    {
        $items = [];

        $modulePath = $path . DIRECTORY_SEPARATOR . 'modules';
        if (file_exists($modulePath) === true) {
            $moduleDirectory = scandir($modulePath);
            foreach ($moduleDirectory as $entry) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }

                if (substr($entry, 0, 1) !== '.') {
                    $subModulePath = $modulePath . DIRECTORY_SEPARATOR . $entry;
                    if (file_exists($subModulePath) === true) {
                        if ($flat) {
                            $filesInPath = $this->getFilesInPath($subModulePath . DIRECTORY_SEPARATOR . $subdir, $flat, $condition, $offset);
                            if ($filesInPath !== []) {
                                $items[$entry][] = $filesInPath;
                            }

                            $filesInModules = $this->getFilesInModules($subModulePath, $subdir, $flat, $condition, $offset);
                            if ($filesInModules !== []) {
                                $items[$entry][] = $filesInModules;
                            }
                        } else {
                            $items[$entry][$subdir] = $this->getFilesInPath($subModulePath . DIRECTORY_SEPARATOR . $subdir, $flat, $condition, $offset);
                            $items[$entry]['modules'] = $this->getFilesInModules($subModulePath, $subdir, $flat, $condition, $offset);
                        }
                    }
                }
            }
        }

        return $items;
    }
}
