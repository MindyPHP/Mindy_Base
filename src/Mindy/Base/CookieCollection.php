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
 * @date 10/06/14.06.2014 14:03
 */

namespace Mindy\Base;
use Mindy\Base\Exception\Exception;
use Mindy\Helper\Collection;


/**
 * CCookieCollection implements a collection class to store cookies.
 *
 * You normally access it via {@link CHttpRequest::getCookies()}.
 *
 * Since CCookieCollection extends from {@link CMap}, it can be used
 * like an associative array as follows:
 * <pre>
 * $cookies[$name]=new HttpCookie($name,$value); // sends a cookie
 * $value=$cookies[$name]->value; // reads a cookie value
 * unset($cookies[$name]);  // removes a cookie
 * </pre>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since 1.0
 */
class CookieCollection extends Collection
{
    private $_request;
    private $_initialized = false;
    protected $cookies = [];

    /**
     * Constructor.
     * @param HttpRequest $request owner of this collection.
     */
    public function __construct(HttpRequest $request)
    {
        $this->_request = $request;
        $this->copyfrom($this->getCookies());
        $this->_initialized = true;
    }

    /**
     * @return HttpRequest the request instance
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @return array list of validated cookies
     */
    public function getCookies()
    {
        $this->cookies = [];
        if ($this->_request->enableCookieValidation) {
            $sm = Mindy::app()->securityManager;
            foreach ($_COOKIE as $name => $value) {
                if (is_string($value) && ($value = $sm->validateData($value)) !== false) {
                    $this->cookies[$name] = new HttpCookie($name, @unserialize($value));
                }
            }
        } else {
            foreach ($_COOKIE as $name => $value) {
                $this->cookies[$name] = new HttpCookie($name, $value);
            }
        }
        return $this->cookies;
    }

    /**
     * Adds a cookie with the specified name.
     * This overrides the parent implementation by performing additional
     * operations for each newly added HttpCookie object.
     * @param mixed $name Cookie name.
     * @param HttpCookie $cookie Cookie object.
     * @throws Exception if the item to be inserted is not a HttpCookie object.
     */
    public function add($name, $cookie)
    {
        if ($cookie instanceof HttpCookie) {
            $this->remove($name);
            parent::add($name, $cookie);
            if ($this->_initialized) {
                $this->addCookie($cookie);
            }
        } else {
            throw new Exception(Mindy::t('yii', 'HttpCookieCollection can only hold HttpCookie objects.'));
        }
    }

    /**
     * Removes a cookie with the specified name.
     * This overrides the parent implementation by performing additional
     * cleanup work when removing a HttpCookie object.
     * Since version 1.1.11, the second parameter is available that can be used to specify
     * the options of the HttpCookie being removed. For example, this may be useful when dealing
     * with ".domain.tld" where multiple subdomains are expected to be able to manage cookies:
     *
     * <pre>
     * $options=array('domain'=>'.domain.tld');
     * Mindy::app()->request->cookies['foo']=new HttpCookie('cookie','value',$options);
     * Mindy::app()->request->cookies->remove('cookie',$options);
     * </pre>
     *
     * @param mixed $name Cookie name.
     * @param array $options Cookie configuration array consisting of name-value pairs, available since 1.1.11.
     * @return HttpCookie The removed cookie object.
     */
    public function remove($name, $options = array())
    {
        if (($cookie = parent::remove($name)) !== null) {
            if ($this->_initialized) {
                $cookie->configure($options);
                $this->removeCookie($cookie);
            }
        }

        return $cookie;
    }

    /**
     * Sends a cookie.
     * @param HttpCookie $cookie cookie to be sent
     */
    protected function addCookie($cookie)
    {
        $value = $cookie->value;
        if ($this->_request->enableCookieValidation) {
            $value = Mindy::app()->securityManager->hashData(serialize($value));
        }

        setcookie($cookie->name, $value, $cookie->expire, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httpOnly);
    }

    /**
     * Deletes a cookie.
     * @param HttpCookie $cookie cookie to be deleted
     */
    protected function removeCookie($cookie)
    {
        setcookie($cookie->name, '', 0, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httpOnly);
    }
}
