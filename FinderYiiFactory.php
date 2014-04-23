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
 * @date 02/04/14.04.2014 15:23
 */

use Mindy\Finder\AppTemplateFinder;
use Mindy\Finder\Finder;
use Mindy\Finder\TemplateFinder;

class FinderYiiFactory extends CApplicationComponent
{
    private $_finder;

    public function __construct(array $finders = [])
    {
        $appPath = Yii::getPathOfAlias('application');
        $modulesPath = Yii::getPathOfAlias('application.modules');

        $this->_finder = new Finder([
            new TemplateFinder($appPath),
            new AppTemplateFinder($appPath, $modulesPath)
        ]);
    }

    public function __set($key, $value)
    {
        $this->_finder->__set($key, $value);
    }

    public function __get($name)
    {
        return $this->_finder->__get($name);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->_finder, $name], $arguments);
    }
}
