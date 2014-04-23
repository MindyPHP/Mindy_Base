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
 * @date 03/04/14.04.2014 13:19
 */
class YiiUtils
{
    public static function t($text, $category, $params = array())
    {
        if ($category !== 'app') {
            if (!strpos($category, '.'))
                $category .= '.main';

        }
        $findCategory = explode('.', $category);
        if (Yii::app()->hasModule($findCategory[0]))
            return Yii::t(ucFirst($findCategory[0]) . 'Module.' . $findCategory[1], $text, $params);
        else
            return $text;
    }

    public static function createUrl($tmp, $params = array())
    {
        if (is_array($tmp)) {
            $route = $tmp[0];
            unset($tmp[0]);
            $params = $tmp;
        } else {
            $route = $tmp;
        }

        return Yii::app()->createUrl($route, $params);
    }

    public static function csrf()
    {
        $request = Yii::app()->request;
        return '<input type="hidden" value="' . $request->getCsrfToken() . '" name="' . $request->csrfTokenName . '" />';
    }

    public static function mdate($date, $format)
    {
        if (!is_int($date)) {
            $date = strtotime($date);
        }

        return Yii::app()->dateFormatter->format($format, $date);
    }
}
