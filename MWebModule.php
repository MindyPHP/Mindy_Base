<?php

class MWebModule extends CWebModule
{
    public $theme;

    public function getName()
    {
        return self::t(ucfirst($this->getId()));
    }

    public static function t($str, $params = array(), $dic = 'main')
    {
        return Yii::t(get_called_class() . "." . $dic, $str, $params);
    }

    /**
     * Return array of mail templates and his variables
     * @return array
     */
    public function getMailTemplates()
    {
        return array();
    }

    /**
     * Return array for MMenu {$see: MMenu} widget
     * @abstract
     * @return array
     */
    public function getMenu()
    {
        return array();
    }

    public function init()
    {
        $this->setImport(array(
            $this->id . '.models.*',
            $this->id . '.components.*',
        ));
    }
}

interface IMWebModule
{
    /**
     * Return array for MMenu {$see: MMenu} widget
     * @abstract
     * @return array
     */
    public function getMenu();

    /**
     * Return array of mail templates and his variables
     * @return array
     */
    public function getMailTemplates();
}
