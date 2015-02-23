<?php

namespace Mindy\Base;

/**
 * Class Module
 * @package Mindy\Base
 */
class Module extends BaseModule
{
    public function getVersion()
    {
        return '1.0';
    }

    /**
     * Return array for MMenu {$see: MMenu} widget
     * @return array
     */
    public function getMenu()
    {
        return [];
    }

    /**
     * Return array of mail templates and his variables
     * @return array
     */
    public function getMailTemplates()
    {
        return [];
    }

    /**
     * Install module
     * @void
     */
    public function install()
    {
    }

    /**
     * Uninstall module. Delete tables from database.
     * @void
     */
    public function uninstall()
    {
    }

    /**
     * Upgrade module to new version. Run migrations, update sql.
     * @void
     */
    public function upgrade()
    {
    }

    /**
     * Downgrade module to old version. Delete tables from database if need.
     * @void
     */
    public function downgrade()
    {
    }
}
