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
 * @date 09/06/14.06.2014 17:47
 */

namespace Mindy\Base\Interfaces;

/**
 * IWidgetFactory is the interface that must be implemented by a widget factory class.
 *
 * When calling {@link CBaseController::createWidget}, if a widget factory is available,
 * it will be used for creating the requested widget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since 1.1
 */
interface IWidgetFactory
{
    /**
     * Creates a new widget based on the given class name and initial properties.
     * @param CBaseController $owner the owner of the new widget
     * @param string $className the class name of the widget. This can also be a path alias (e.g. system.web.widgets.COutputCache)
     * @param array $properties the initial property values (name=>value) of the widget.
     * @return CWidget the newly created widget whose properties have been initialized with the given values.
     */
    public function createWidget($owner, $className, $properties = array());
}
