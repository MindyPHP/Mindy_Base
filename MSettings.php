<?php

/**
 *
 * @author Falaleev Maxim <max@studio107.com>
 * @link http://studio107.ru/
 * @copyright Copyright &copy; 2010-2012 Studio107
 * @license http://www.cms107.com/license/
 * @package modules.core.models
 * @since 1.1.1
 * @version 1.0
 */
class MSettings extends CFormModel
{
    /**
     * @string алиас до файла настроек.
     * {@example core.config.params}
     */
    public $configFile;

    /**
     * @array непосредственно сам массив настроек
     */
    public $params = array();

    private $file;

    public function __construct($scenario = '')
    {
        $this->file = Yii::getPathOfAlias($this->configFile) . '.php';

        // Если файла настроек не отдаем 500 ошибка
        if (file_exists($this->file) === false) {
            $modelName = get_class($this);
            throw new CHttpException(500, "Params file for model {$modelName} not set or not exists");
        }

        $this->params = $this->openConfig();
        parent::__construct($scenario);
    }

    public function openConfig()
    {
        return include($this->file);
    }

    public function setAttributes($values, $safeOnly = true)
    {
        foreach ($this->params as $key => $data) {
            if (isset($values[$key])) {
                $this->params[$key] = $values[$key];
            }
        }
    }

    public function getAttributes($names = null)
    {
        return $this->_params;
    }

    public function __get($name)
    {
        if (isset($this->params[$name]))
            return $this->params[$name];

        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if (isset($this->params[$name]))
            $this->params[$name] = $value;
        else
            parent::__set($name, $value);
    }

    /**
     * По умолчанию все поля являются обязательными для заполнения
     * @return array
     */
    public function rules()
    {
        return array(
            array(
                implode(',', array_keys($this->params)), 'required', 'message' => CoreModule::t('Attribute {attribute} cannot be blank.', array(), 'settings')
            )
        );
    }

    public function save($runValidation = true, $attributes = null)
    {
        if (!$runValidation || $this->validate($attributes))
            return file_put_contents($this->file, "<?php \n\nreturn " . var_export($this->params, true) . ";");
        else
            return false;
    }

    public function getName()
    {
        return get_class($this);
    }
}
