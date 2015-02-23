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
 * @date 09/06/14.06.2014 17:45
 */

namespace Mindy\Base\Interfaces;

/**
 * IUserIdentity interface is implemented by a user identity class.
 *
 * An identity represents a way to authenticate a user and retrieve
 * information needed to uniquely identity the user. It is normally
 * used with the {@link CWebApplication::user user application component}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package Mindy\Base
 * @since 1.0
 */
interface IUserIdentity
{
    /**
     * Authenticates the user.
     * The information needed to authenticate the user
     * are usually provided in the constructor.
     * @return boolean whether authentication succeeds.
     */
    public function authenticate();

    /**
     * Returns a value indicating whether the identity is authenticated.
     * @return boolean whether the identity is valid.
     */
    public function getIsAuthenticated();

    /**
     * Returns a value that uniquely represents the identity.
     * @return mixed a value that uniquely represents the identity (e.g. primary key value).
     */
    public function getId();

    /**
     * Returns the display name for the identity (e.g. username).
     * @return string the display name for the identity.
     */
    public function getName();

    /**
     * Returns the additional identity information that needs to be persistent during the user session.
     * @return array additional identity information that needs to be persistent during the user session (excluding {@link id}).
     */
    public function getPersistentStates();
}
