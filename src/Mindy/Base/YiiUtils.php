<?php

namespace Mindy\Base;

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
 * @date 03/04/14.04.2014 13:19
 */
class YiiUtils
{
    public static function t($text, $category, $params = [])
    {
        if ($category !== 'app' && !strpos($category, '.')) {
            $category .= '.main';
        }
        $findCategory = explode('.', $category);
        if(Mindy::app()->hasModule($findCategory[0])) {
            $module = Mindy::app()->getModule($findCategory[0]);
            $moduleName = get_class($module) . '.' . $findCategory[1];
            return Mindy::t($moduleName, $text, $params);
        } else {
            return $text;
        }
    }

    public static function createUrl($route, $data = null)
    {
        return Mindy::app()->urlManager->createUrl($route, $data);
    }

    public static function csrf()
    {
        $request = Mindy::app()->request;
        return '<input type="hidden" value="' . $request->getCsrfToken() . '" name="' . $request->csrfTokenName . '" />';
    }

    public static function mdate($date, $format)
    {
        if (!is_int($date)) {
            $date = strtotime($date);
        }

        return Mindy::app()->dateFormatter->format($format, $date);
    }
}
