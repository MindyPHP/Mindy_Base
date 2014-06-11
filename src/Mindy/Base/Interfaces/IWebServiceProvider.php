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
 * IWebServiceProvider interface may be implemented by Web service provider classes.
 *
 * If this interface is implemented, the provider instance will be able
 * to intercept the remote method invocation (e.g. for logging or authentication purpose).
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */
interface IWebServiceProvider
{
    /**
     * This method is invoked before the requested remote method is invoked.
     * @param CWebService $service the currently requested Web service.
     * @return boolean whether the remote method should be executed.
     */
    public function beforeWebMethod($service);

    /**
     * This method is invoked after the requested remote method is invoked.
     * @param CWebService $service the currently requested Web service.
     */
    public function afterWebMethod($service);
}
