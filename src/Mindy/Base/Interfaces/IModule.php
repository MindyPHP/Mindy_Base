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
 * @date 12/06/14.06.2014 15:33
 */

namespace Mindy\Base\Interfaces;

/**
 * Interface IModule
 * @package Mindy\Base
 */
interface IModule
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

    /**
     * Install module
     * @void
     */
    public function install();

    /**
     * Uninstall module. Delete tables from database.
     * @void
     */
    public function uninstall();

    /**
     * Upgrade module to new version. Run migrations, update sql.
     * @void
     */
    public function upgrade();

    /**
     * Downgrade module to old version. Delete tables from database if need.
     * @void
     */
    public function downgrade();
}
