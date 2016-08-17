<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 09/08/16
 * Time: 15:13
 */

namespace Mindy\Base;

use Mindy\Helper\Alias;

trait ParamsTrait
{
    /**
     * @var array
     */
    private $_params = [];
    /**
     * @var bool
     */
    private $_collected = false;

    /**
     * Returns user-defined parameters.
     * @return array the list of user-defined parameters
     */
    public function getParams()
    {
        if ($this->_collected) {
            $this->_params = $this->collectParams();
            $this->_collected = true;
        }
        return $this->_params;
    }

    /**
     * Sets user-defined parameters.
     * @param array $value user-defined parameters. This should be in name-value pairs.
     */
    public function setParams(array $value)
    {
        foreach ($value as $k => $v) {
            $this->_params[$k] = $v;
        }
    }

    /**
     * @param $key
     * @param null $defaultValue
     * @return mixed|null
     */
    public function getParam($key, $defaultValue = null)
    {
        if (strpos($key, '.') === false) {
            return (isset($this->_params[$key])) ? $this->_params[$key] : $defaultValue;
        }

        $keys = explode('.', $key);

        if (!isset($this->_params[$keys[0]])) {
            return $defaultValue;
        }

        $value = $this->_params[$keys[0]];
        unset($keys[0]);

        foreach ($keys as $k) {
            if(!is_array($value)) {
                return $defaultValue;
            }
            if (!isset($value[$k]) && !array_key_exists($k, $value)) {
                return $defaultValue;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * @return array
     */
    protected function collectParams()
    {
        $path = Alias::get('Modules');
        if (!is_dir($path)) {
            return [];
        }

        $params = [];
        foreach (glob($path . '/*/params.php') as $file) {
            $temp = require_once($file);
            if (is_array($temp)) {
                $params[basename(dirname($file))] = $temp;
            }
        }
        return $params;
    }
}