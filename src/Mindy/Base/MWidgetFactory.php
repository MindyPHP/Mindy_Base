<?php

/**
 *
 * CoreWidgetFactory class file.
 *
 * @author Falaleev Maxim <max@studio107.com>
 * @link http://studio107.ru/
 * @copyright Copyright &copy; 2010-2012 Studio107
 * @license http://www.cms107.com/license/
 * @package modules.core.components
 * @since 1.1.1
 * @version 1.0
 *
 */
class MWidgetFactory extends CWidgetFactory
{
    public function createWidget($owner, $className, $properties = array())
    {
        $commonProperties = 'CJuiWidget';
        $applyTo = 'CJui';

        $widgetName = Yii::import($className);
        if (isset($this->widgets[$commonProperties]) && strpos($widgetName, $applyTo) === 0) {
            // Merge widget class specific factory config and the $properties parameter
            // into $properties.
            if (isset($this->widgets[$widgetName]))
                $properties = $properties === array() ? $this->widgets[$widgetName]
                    : CMap::mergeArray($this->widgets[$widgetName], $properties);

            // Merge CJui common factory config and the $properties parameter
            // into the $properties parameter of parent call.
            return parent::createWidget($owner, $className, CMap::mergeArray($this->widgets[$commonProperties], $properties));
        }

        return parent::createWidget($owner, $className, $properties);
    }
}
