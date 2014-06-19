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
 * @date 12/06/14.06.2014 17:12
 */

namespace Mindy\Renderer;

/**
 * Class-proxy for Yii core static classes
 *
 * @author Leonid Svyatov <leonid@svyatov.ru>
 * @version 1.0.0
 */
class ETwigViewRendererYiiCoreStaticClassesProxy
{
    private $_classes = [];

    function __isset($className)
    {
        return (isset($_classes[$className]) || class_exists('C' . $className));
    }

    function __get($className)
    {
        if (!isset($this->_classes[$className])) {
            $this->_classes[$className] = new ETwigViewRendererStaticClassProxy('C' . $className);
        }

        return $this->_classes[$className];
    }

}
