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
 * IWebUser interface is implemented by a {@link CWebApplication::user user application component}.
 *
 * A user application component represents the identity information
 * for the current user.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package Mindy\Base
 * @since 1.0
 */
interface IWebUser
{
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
     * Returns a value indicating whether the user is a guest (not authenticated).
     * @return boolean whether the user is a guest (not authenticated)
     */
    public function getIsGuest();

    /**
     * Performs access check for this user.
     * @param string $operation the name of the operation that need access check.
     * @param array $params name-value pairs that would be passed to business rules associated
     * with the tasks and roles assigned to the user.
     * @return boolean whether the operations can be performed by this user.
     */
    public function checkAccess($operation, $params = array());

    /**
     * Redirects the user browser to the login page.
     * Before the redirection, the current URL (if it's not an AJAX url) will be
     * kept in {@link returnUrl} so that the user browser may be redirected back
     * to the current page after successful login. Make sure you set {@link loginUrl}
     * so that the user browser can be redirected to the specified login URL after
     * calling this method.
     * After calling this method, the current request processing will be terminated.
     */
    public function loginRequired();
}
