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
 * @date 09/04/14.04.2014 18:49
 */
class MUrlRule extends CUrlRule
{
    const PANEL_TAG = '!';

    public $keepSlashes;

    public function __construct($route, $pattern)
    {
        /*
         * Не самая красивая но достаточно простая реализация динамических урл панели управления
         */
        $pattern = str_replace(self::PANEL_TAG, m::param('core.admin_url'), $pattern);

        if (is_array($route) && isset($route['keepSlashes']))
            $this->keepSlashes = $route['keepSlashes'];

        parent::__construct($route, $pattern);
    }

    public function createUrl($manager, $route, $params, $ampersand)
    {
        if ($manager->caseSensitive && $this->caseSensitive === null || $this->caseSensitive)
            $case = '';
        else
            $case = 'i';

        $tr = array();
        if ($route !== $this->route) {
            if ($this->routePattern !== null && preg_match($this->routePattern . $case, $route, $matches)) {
                foreach ($this->references as $key => $name)
                    $tr[$name] = $matches[$key];
            } else
                return false;
        }

        foreach ($this->defaultParams as $key => $value) {
            if (isset($params[$key]) && $params[$key] == $value)
                unset($params[$key]);
            else
                return false;
        }

        foreach ($this->params as $key => $value)
            if (!isset($params[$key]))
                return false;

        if ($manager->matchValue && $this->matchValue === null || $this->matchValue) {
            foreach ($this->params as $key => $value) {
                if (!preg_match('/' . $value . '/' . $case, $params[$key]))
                    return false;
            }
        }

        // <Мои изменения>
        foreach ($this->params as $key => $value) {
            if ($manager->keepSlashes && $this->keepSlashes === null || $this->keepSlashes) {
                $encodedParam = implode('/', array_map('urlencode', explode('/', $params[$key])));
            } else {
                $encodedParam = urlencode($params[$key]);
            }
            $tr["<$key>"] = $encodedParam;
            unset($params[$key]);
        }
        // </Мои изменения>

        $suffix = $this->urlSuffix === null ? $manager->urlSuffix : $this->urlSuffix;

        $url = strtr($this->template, $tr);

        if ($this->hasHostInfo) {
            $hostInfo = Yii::app()->getRequest()->getHostInfo();
            if (strpos($url, $hostInfo) === 0)
                $url = substr($url, strlen($hostInfo));
        }

        if (empty($params))
            return $url !== '' ? $url . $suffix : $url;

        if ($this->append)
            $url .= '/' . $manager->createPathInfo($params, '/', '/') . $suffix;
        else {
            if ($url !== '')
                $url .= $suffix;
            $url .= '?' . $manager->createPathInfo($params, '=', $ampersand);
        }

        return $url;
    }
}
